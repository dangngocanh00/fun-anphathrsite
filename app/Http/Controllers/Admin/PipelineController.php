<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\InterviewNote;
use App\Models\Job;
use App\Models\PipelineLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PipelineController extends Controller
{
    public const STAGES = [
        1 => 'Tiếp nhận hồ sơ',
        2 => 'Sàng lọc / Test năng lực',
        3 => 'Phỏng vấn chuyên môn',
        4 => 'Loại CV',
        5 => 'Thử việc',
        6 => 'Ký hợp đồng',
    ];

    public const REJECTED_STAGE = 4;

    private const MANAGER_ROLES = ['admin', 'hr_manager'];

    public function index(Request $request): Response
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(self::MANAGER_ROLES);

        $filters = [
            'job_id' => $request->integer('job_id') ?: null,
            'assigned_hr_id' => $request->integer('assigned_hr_id') ?: null,
            'stage' => $request->integer('stage') ?: null,
        ];

        $hireCutoff = Carbon::now()->subMonth();

        $query = Candidate::query()
            ->with(['job:id,title,slug,department', 'assignedHr:id,name'])
            ->where(function ($q) use ($hireCutoff) {
                $q->where('current_stage', '!=', 6)
                    ->orWhere('updated_at', '>=', $hireCutoff);
            })
            ->when($filters['job_id'], fn ($q, $v) => $q->where('job_id', $v))
            ->when($filters['stage'], fn ($q, $v) => $q->where('current_stage', $v))
            ->when(
                $filters['assigned_hr_id'],
                fn ($q, $v) => $q->where('assigned_hr_id', $v),
                fn ($q) => $isManager ? $q : $q->where('assigned_hr_id', $user->id),
            )
            ->latest('updated_at');

        $candidates = $query->get()->map(fn (Candidate $c) => $this->cardPayload($c));

        $byStage = collect(self::STAGES)->map(function ($name, $stage) use ($candidates) {
            return [
                'stage' => $stage,
                'name' => $name,
                'cards' => $candidates->where('current_stage', $stage)->values(),
            ];
        })->values();

        $jobs = Job::query()->orderBy('title')->get(['id', 'title', 'department']);
        $hrs = User::query()->role(['hr', 'hr_manager'])->orderBy('name')->get(['id', 'name']);

        return Inertia::render('admin/pipeline', [
            'columns' => $byStage,
            'stages' => self::STAGES,
            'filters' => $filters,
            'jobs' => $jobs,
            'hrs' => $hrs,
            'can' => [
                'manage' => $isManager,
            ],
        ]);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        $this->authorizeAccess($candidate);

        $candidate->load([
            'job:id,title,slug,department,location',
            'assignedHr:id,name,email',
            'answers.field:id,label,type',
            'pipelineLogs.mover:id,name',
            'interviewNotes.user:id,name',
        ]);

        return response()->json([
            'id' => $candidate->id,
            'full_name' => $candidate->full_name,
            'phone' => $candidate->phone,
            'email' => $candidate->email,
            'cv_link' => $candidate->cv_link,
            'current_stage' => $candidate->current_stage,
            'created_at' => $candidate->created_at->toIso8601String(),
            'job' => $candidate->job,
            'assigned_hr' => $candidate->assignedHr,
            'ai' => [
                'score' => $candidate->ai_score,
                'flags' => $candidate->ai_flags ?? [],
                'questions' => $candidate->ai_questions ?? [],
                'analyzed_at' => $candidate->ai_analyzed_at?->toIso8601String(),
            ],
            'answers' => $candidate->answers->map(fn ($a) => [
                'label' => $a->field?->label,
                'answer' => $a->answer,
            ]),
            'logs' => $candidate->pipelineLogs->map(fn ($l) => [
                'id' => $l->id,
                'from_stage' => $l->from_stage,
                'to_stage' => $l->to_stage,
                'note' => $l->note,
                'mover' => $l->mover?->name,
                'at' => $l->created_at->toIso8601String(),
            ]),
            'notes' => $candidate->interviewNotes->map(fn ($n) => [
                'id' => $n->id,
                'note' => $n->note,
                'result' => $n->result,
                'user' => $n->user?->name,
                'at' => $n->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function move(Request $request, Candidate $candidate): RedirectResponse
    {
        $this->authorizeAccess($candidate);

        $data = $request->validate([
            'to_stage' => ['required', 'integer', 'between:1,6'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $from = $candidate->current_stage;
        $to = (int) $data['to_stage'];

        if ($from === $to) {
            return back()->with('error', 'Ứng viên đã ở bước này rồi.');
        }

        DB::transaction(function () use ($candidate, $from, $to, $data) {
            $candidate->update(['current_stage' => $to]);

            PipelineLog::create([
                'candidate_id' => $candidate->id,
                'from_stage' => $from,
                'to_stage' => $to,
                'moved_by' => Auth::id(),
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('success', "Đã chuyển {$candidate->full_name} sang bước ".self::STAGES[$to].'.');
    }

    public function note(Request $request, Candidate $candidate): RedirectResponse
    {
        $this->authorizeAccess($candidate);

        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'result' => ['required', 'in:pass,fail,pending'],
        ]);

        InterviewNote::create([
            'candidate_id' => $candidate->id,
            'user_id' => Auth::id(),
            'note' => $data['note'],
            'result' => $data['result'],
        ]);

        return back()->with('success', 'Đã lưu ghi chú phỏng vấn.');
    }

    private function cardPayload(Candidate $c): array
    {
        return [
            'id' => $c->id,
            'full_name' => $c->full_name,
            'phone' => $c->phone,
            'current_stage' => $c->current_stage,
            'ai_score' => $c->ai_score,
            'job' => $c->job ? [
                'id' => $c->job->id,
                'title' => $c->job->title,
                'department' => $c->job->department,
            ] : null,
            'assigned_hr' => $c->assignedHr ? [
                'id' => $c->assignedHr->id,
                'name' => $c->assignedHr->name,
            ] : null,
            'updated_at' => $c->updated_at->toIso8601String(),
        ];
    }

    private function authorizeAccess(Candidate $candidate): void
    {
        $user = Auth::user();
        if ($user->hasAnyRole(self::MANAGER_ROLES)) {
            return;
        }

        if ($candidate->assigned_hr_id !== $user->id) {
            throw new AuthorizationException('Bạn không phụ trách ứng viên này.');
        }
    }
}
