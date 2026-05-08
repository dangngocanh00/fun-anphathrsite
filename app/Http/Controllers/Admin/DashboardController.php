<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $stats = [
            'today_applications' => Candidate::query()
                ->whereDate('created_at', $today)
                ->count(),
            'in_pipeline' => Candidate::query()
                ->whereBetween('current_stage', [1, 5])
                ->count(),
            'hired_this_month' => Candidate::query()
                ->where('current_stage', 6)
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->count(),
            'inbox_pending' => Candidate::query()
                ->where('current_stage', 1)
                ->whereNull('assigned_hr_id')
                ->count(),
        ];

        $recent = Candidate::query()
            ->with('job:id,title,department')
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'full_name', 'job_id', 'current_stage', 'created_at'])
            ->map(fn (Candidate $c) => [
                'id' => $c->id,
                'full_name' => $c->full_name,
                'job_title' => $c->job?->title,
                'job_department' => $c->job?->department,
                'current_stage' => $c->current_stage,
                'created_at' => $c->created_at->toIso8601String(),
            ]);

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recent_candidates' => $recent,
        ]);
    }
}
