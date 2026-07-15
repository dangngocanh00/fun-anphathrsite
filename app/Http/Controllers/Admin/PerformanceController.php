<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $this->parseMonth($request->string('month')->toString());

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $candidates = Candidate::query()
            ->with('job:id,commission_amount')
            ->whereNotNull('assigned_hr_id')
            ->whereBetween('created_at', [$start, $end])
            ->get(['id', 'job_id', 'assigned_hr_id', 'current_stage']);

        $hrs = User::query()
            ->role(['hr', 'hr_manager'])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (User $u) use ($candidates) {
                $own = $candidates->where('assigned_hr_id', $u->id);

                $assigned = $own->count();
                // "Loại CV" (stage 4) có thể đến từ bất kỳ bước nào nên không dùng >= liên tục được nữa —
                // chỉ đếm ứng viên còn đang thực sự ở/đi qua từng bước tiến, loại trừ ứng viên đã bị loại.
                $tested = $own->whereIn('current_stage', [2, 3, 5, 6])->count();
                $interviewed = $own->whereIn('current_stage', [3, 5, 6])->count();
                $probation = $own->whereIn('current_stage', [5, 6])->count();
                $hired = $own->where('current_stage', 6)->count();
                $rejected = $own->where('current_stage', PipelineController::REJECTED_STAGE)->count();

                $commission = $own
                    ->where('current_stage', 6)
                    ->sum(fn (Candidate $c) => (int) ($c->job?->commission_amount ?? 0));

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'role' => $u->getRoleNames()->first(),
                    'funnel' => [
                        'assigned' => $assigned,
                        'tested' => $tested,
                        'interviewed' => $interviewed,
                        'probation' => $probation,
                        'hired' => $hired,
                        'rejected' => $rejected,
                    ],
                    'commission' => $commission,
                    'conversion_rate' => $assigned > 0 ? round($hired / $assigned * 100, 1) : 0.0,
                ];
            })
            ->values();

        $totals = [
            'assigned' => $hrs->sum('funnel.assigned'),
            'tested' => $hrs->sum('funnel.tested'),
            'interviewed' => $hrs->sum('funnel.interviewed'),
            'probation' => $hrs->sum('funnel.probation'),
            'hired' => $hrs->sum('funnel.hired'),
            'rejected' => $hrs->sum('funnel.rejected'),
            'commission' => $hrs->sum('commission'),
        ];

        return Inertia::render('admin/performance', [
            'month' => $month->format('Y-m'),
            'monthLabel' => $month->translatedFormat('m/Y'),
            'rows' => $hrs,
            'totals' => $totals,
            'monthOptions' => $this->monthOptions(),
        ]);
    }

    private function parseMonth(string $input): Carbon
    {
        if ($input === '') {
            return Carbon::now()->startOfMonth();
        }
        try {
            return Carbon::createFromFormat('Y-m', $input)->startOfMonth();
        } catch (\Throwable $e) {
            return Carbon::now()->startOfMonth();
        }
    }

    private function monthOptions(): array
    {
        $options = [];
        $cursor = Carbon::now()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $options[] = [
                'value' => $cursor->format('Y-m'),
                'label' => 'Tháng '.$cursor->format('m/Y'),
            ];
            $cursor->subMonth();
        }
        return $options;
    }
}
