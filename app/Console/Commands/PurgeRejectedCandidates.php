<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\PipelineController;
use App\Models\Candidate;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeRejectedCandidates extends Command
{
    protected $signature = 'candidates:purge-rejected';

    protected $description = 'Xoá mềm các ứng viên đã nằm ở cột "Loại CV" quá 7 ngày';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(7);

        $candidates = Candidate::query()
            ->where('current_stage', PipelineController::REJECTED_STAGE)
            ->where('updated_at', '<=', $cutoff)
            ->get();

        foreach ($candidates as $candidate) {
            $candidate->delete();
        }

        $this->info("Đã xoá {$candidates->count()} ứng viên ở cột \"Loại CV\" quá 7 ngày.");

        return self::SUCCESS;
    }
}
