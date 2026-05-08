<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\CandidateFormAnswer;
use App\Models\Job;
use App\Models\PipelineLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $hrs = User::role('hr')->get();
        $managers = User::role('hr_manager')->get();
        $jobs = Job::with('formFields')->get()->keyBy('slug');

        if ($jobs->isEmpty() || $hrs->isEmpty()) {
            return;
        }

        $samples = [
            [
                'job' => 'nhan-vien-kinh-doanh-b2b',
                'full_name' => 'Trần Minh Hoàng',
                'phone' => '0931000111',
                'email' => 'hoang.tm@example.com',
                'cv_link' => 'https://drive.google.com/file/d/1abcSAMPLE001/view',
                'stage' => 1,
                'assigned' => false,
                'ai' => null,
                'answers' => ['1–3 năm', '15000000', 'Xe máy', 'Đã chốt deal 800tr cho khách FPT năm 2024.'],
            ],
            [
                'job' => 'nhan-vien-kinh-doanh-b2b',
                'full_name' => 'Lê Thu Hà',
                'phone' => '0931000112',
                'email' => 'ha.lt@example.com',
                'cv_link' => 'https://drive.google.com/file/d/1abcSAMPLE002/view',
                'stage' => 1,
                'assigned' => false,
                'ai' => ['score' => 82, 'flags' => ['Chưa có kinh nghiệm B2B chuyên sâu'], 'questions' => ['Bạn xử lý khách từ chối như thế nào?']],
                'answers' => ['Dưới 1 năm', '12000000', 'Xe máy', 'Tham gia training sales 3 tháng tại Vinamilk.'],
            ],
            [
                'job' => 'lap-trinh-vien-backend-laravel',
                'full_name' => 'Phạm Quốc Anh',
                'phone' => '0931000113',
                'email' => 'anh.pq@example.com',
                'cv_link' => 'https://drive.google.com/file/d/1abcSAMPLE003/view',
                'stage' => 2,
                'assigned' => true,
                'ai' => ['score' => 88, 'flags' => [], 'questions' => ['Bạn đã viết unit test ở mức độ nào?', 'Kinh nghiệm với Redis queue?']],
                'answers' => ['2–4 năm', '25000000', 'Đã từng', 'github.com/anhpq', 'Build hệ thống chấm công cho 5k nhân sự.'],
            ],
            [
                'job' => 'lap-trinh-vien-backend-laravel',
                'full_name' => 'Đỗ Thành Nam',
                'phone' => '0931000114',
                'email' => 'nam.dt@example.com',
                'cv_link' => 'https://drive.google.com/file/d/1abcSAMPLE004/view',
                'stage' => 3,
                'assigned' => true,
                'ai' => ['score' => 64, 'flags' => ['Mới chỉ làm side project', 'Chưa từng deploy production'], 'questions' => ['Bạn deploy app Laravel ra sao?']],
                'answers' => ['1–2 năm', '18000000', 'Đã từng', '', 'Làm side project blog cá nhân.'],
            ],
            [
                'job' => 'lap-trinh-vien-backend-laravel',
                'full_name' => 'Nguyễn Khánh Linh',
                'phone' => '0931000115',
                'email' => 'linh.nk@example.com',
                'cv_link' => 'https://drive.google.com/file/d/1abcSAMPLE005/view',
                'stage' => 4,
                'assigned' => true,
                'ai' => ['score' => 91, 'flags' => [], 'questions' => ['Mong đợi growth path 2 năm tới?']],
                'answers' => ['Trên 4 năm', '35000000', 'Đã từng', 'github.com/linhnk', 'Lead backend tại startup fintech 50 người.'],
            ],
            [
                'job' => 'chuyen-vien-marketing-performance',
                'full_name' => 'Bùi Hải Yến',
                'phone' => '0931000116',
                'email' => 'yen.bh@example.com',
                'cv_link' => 'https://drive.google.com/file/d/1abcSAMPLE006/view',
                'stage' => 1,
                'assigned' => false,
                'ai' => ['score' => 41, 'flags' => ['Ngân sách từng vận hành nhỏ', 'Chưa có case ngành tuyển dụng'], 'questions' => ['Bạn đo lường CPL như thế nào?']],
                'answers' => ['Dưới 50 triệu', 'Facebook Ads', '14000000', 'Chạy ads cho 1 shop thời trang nhỏ.'],
            ],
            [
                'job' => 'chuyen-vien-marketing-performance',
                'full_name' => 'Vũ Đăng Khoa',
                'phone' => '0931000117',
                'email' => 'khoa.vd@example.com',
                'cv_link' => 'https://drive.google.com/file/d/1abcSAMPLE007/view',
                'stage' => 5,
                'assigned' => true,
                'ai' => ['score' => 79, 'flags' => ['Mảng B2B Google Ads còn ít kinh nghiệm'], 'questions' => ['Cách bạn split test creative?']],
                'answers' => ['200–500 triệu', 'Đa kênh', '22000000', 'Giảm CPL 30% cho khách EdTech trong Q3.'],
            ],
        ];

        foreach ($samples as $i => $s) {
            $job = $jobs->get($s['job']);
            if (! $job) {
                continue;
            }

            $createdAt = Carbon::now()->subDays(7 - $i);

            $candidate = Candidate::updateOrCreate(
                ['phone' => $s['phone'], 'job_id' => $job->id],
                [
                    'full_name' => $s['full_name'],
                    'email' => $s['email'],
                    'cv_link' => $s['cv_link'],
                    'current_stage' => $s['stage'],
                    'assigned_hr_id' => $s['assigned'] ? ($hrs[$i % $hrs->count()]->id ?? null) : null,
                    'ai_score' => $s['ai']['score'] ?? null,
                    'ai_flags' => $s['ai']['flags'] ?? null,
                    'ai_questions' => $s['ai']['questions'] ?? null,
                    'ai_analyzed_at' => $s['ai'] ? $createdAt->copy()->addHour() : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );

            CandidateFormAnswer::where('candidate_id', $candidate->id)->delete();
            $fields = $job->formFields->values();
            foreach ($s['answers'] as $idx => $answer) {
                if (! isset($fields[$idx]) || $answer === '') {
                    continue;
                }
                CandidateFormAnswer::create([
                    'candidate_id' => $candidate->id,
                    'field_id' => $fields[$idx]->id,
                    'answer' => $answer,
                ]);
            }

            PipelineLog::where('candidate_id', $candidate->id)->delete();
            if ($s['stage'] > 1) {
                $mover = $managers->first()?->id ?? $hrs->first()?->id;
                for ($stage = 2; $stage <= $s['stage']; $stage++) {
                    PipelineLog::create([
                        'candidate_id' => $candidate->id,
                        'from_stage' => $stage - 1,
                        'to_stage' => $stage,
                        'moved_by' => $mover,
                        'note' => null,
                        'created_at' => $createdAt->copy()->addHours($stage),
                        'updated_at' => $createdAt->copy()->addHours($stage),
                    ]);
                }
            }
        }
    }
}
