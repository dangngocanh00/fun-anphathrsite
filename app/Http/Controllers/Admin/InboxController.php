<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use App\Services\AIScreeningService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class InboxController extends Controller
{
    private const MANAGER_ROLES = ['admin', 'hr_manager'];

    public function index(): Response
    {
        $candidates = Candidate::query()
            ->with(['job:id,title,slug,department'])
            ->where('current_stage', 1)
            ->whereNull('assigned_hr_id')
            ->latest()
            ->get()
            ->map(fn (Candidate $c) => [
                'id' => $c->id,
                'full_name' => $c->full_name,
                'phone' => $c->phone,
                'email' => $c->email,
                'cv_link' => $c->cv_link,
                'created_at' => $c->created_at->toIso8601String(),
                'job' => $c->job ? [
                    'id' => $c->job->id,
                    'title' => $c->job->title,
                    'department' => $c->job->department,
                ] : null,
                'ai_score' => $c->ai_score,
                'ai_analyzed_at' => $c->ai_analyzed_at?->toIso8601String(),
            ]);

        $hrs = User::query()
            ->role(['hr', 'hr_manager'])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'roles' => $u->getRoleNames(),
            ]);

        return Inertia::render('admin/inbox', [
            'candidates' => $candidates,
            'hrs' => $hrs,
            'can' => [
                'assign' => Auth::user()->hasAnyRole(self::MANAGER_ROLES),
                'analyze' => Auth::user()->hasAnyRole(self::MANAGER_ROLES),
            ],
        ]);
    }

    public function assign(Request $request, Candidate $candidate): RedirectResponse
    {
        $this->authorizeManager();

        $data = $request->validate([
            'assigned_hr_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $candidate->update(['assigned_hr_id' => $data['assigned_hr_id']]);

        return back()->with('success', 'Đã gán HR phụ trách cho '.$candidate->full_name.'.');
    }

    public function analyze(Candidate $candidate, AIScreeningService $service): RedirectResponse
    {
        $this->authorizeManager();

        try {
            $service->analyze($candidate);

            return back()->with('success', 'Đã phân tích AI xong cho '.$candidate->full_name.'.');
        } catch (Throwable $e) {
            return back()->with('error', 'AI phân tích thất bại: '.$e->getMessage());
        }
    }

    private function authorizeManager(): void
    {
        if (! Auth::user()->hasAnyRole(self::MANAGER_ROLES)) {
            throw new AuthorizationException('Chỉ admin hoặc trưởng nhóm HR mới được thực hiện thao tác này.');
        }
    }
}
