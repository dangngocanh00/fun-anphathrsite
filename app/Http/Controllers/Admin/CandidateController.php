<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Job;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    private const MANAGER_ROLES = ['admin', 'hr_manager'];

    public function index(Request $request): Response
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(self::MANAGER_ROLES);

        $filters = [
            'search' => trim((string) $request->string('search')),
            'job_id' => $request->integer('job_id') ?: null,
            'stage' => $request->integer('stage') ?: null,
            'assigned_hr_id' => $isManager ? ($request->integer('assigned_hr_id') ?: null) : null,
        ];

        $query = Candidate::query()
            ->with(['job:id,title,department', 'assignedHr:id,name'])
            ->when(! $isManager, fn ($q) => $q->where('assigned_hr_id', $user->id))
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where(function ($w) use ($term) {
                    $w->where('full_name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($filters['job_id'], fn ($q, $v) => $q->where('job_id', $v))
            ->when($filters['stage'], fn ($q, $v) => $q->where('current_stage', $v))
            ->when($filters['assigned_hr_id'], fn ($q, $v) => $q->where('assigned_hr_id', $v))
            ->latest('created_at');

        $page = $query->paginate(20)->withQueryString();

        $page->getCollection()->transform(fn (Candidate $c) => [
            'id' => $c->id,
            'full_name' => $c->full_name,
            'phone' => $c->phone,
            'email' => $c->email,
            'current_stage' => $c->current_stage,
            'ai_score' => $c->ai_score,
            'created_at' => $c->created_at->toIso8601String(),
            'job' => $c->job ? [
                'id' => $c->job->id,
                'title' => $c->job->title,
                'department' => $c->job->department,
            ] : null,
            'assigned_hr' => $c->assignedHr ? [
                'id' => $c->assignedHr->id,
                'name' => $c->assignedHr->name,
            ] : null,
        ]);

        $jobs = Job::query()
            ->orderBy('title')
            ->get(['id', 'title', 'department']);

        $hrs = $isManager
            ? User::query()->role(['hr', 'hr_manager'])->orderBy('name')->get(['id', 'name'])
            : collect();

        return Inertia::render('admin/candidates/index', [
            'candidates' => $page,
            'filters' => $filters,
            'jobs' => $jobs,
            'hrs' => $hrs,
            'stages' => PipelineController::STAGES,
            'can' => [
                'filter_by_hr' => $isManager,
                'delete' => $user->hasRole('admin'),
            ],
        ]);
    }

    public function show(Candidate $candidate): Response
    {
        $this->authorizeAccess($candidate);

        $candidate->load([
            'job:id,title,slug,department,location,commission_amount',
            'assignedHr:id,name,email',
            'answers.field:id,label,type',
            'pipelineLogs.mover:id,name',
            'interviewNotes.user:id,name',
        ]);

        return Inertia::render('admin/candidates/show', [
            'candidate' => [
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
                ])->values(),
                'logs' => $candidate->pipelineLogs->map(fn ($l) => [
                    'id' => $l->id,
                    'from_stage' => $l->from_stage,
                    'to_stage' => $l->to_stage,
                    'note' => $l->note,
                    'mover' => $l->mover?->name,
                    'at' => $l->created_at->toIso8601String(),
                ])->values(),
                'notes' => $candidate->interviewNotes->map(fn ($n) => [
                    'id' => $n->id,
                    'note' => $n->note,
                    'result' => $n->result,
                    'user' => $n->user?->name,
                    'at' => $n->created_at->toIso8601String(),
                ])->values(),
            ],
            'stages' => PipelineController::STAGES,
            'can' => [
                'delete' => Auth::user()->hasRole('admin'),
            ],
        ]);
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        if (! Auth::user()->hasRole('admin')) {
            throw new AuthorizationException('Chỉ admin mới được xoá ứng viên.');
        }

        $name = $candidate->full_name;
        $candidate->delete();

        return redirect()
            ->route('admin.candidates.index')
            ->with('success', "Đã xoá hồ sơ \"{$name}\".");
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
