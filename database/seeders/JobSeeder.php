<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobFormField;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Nhân viên Kinh doanh B2B',
                'slug' => 'nhan-vien-kinh-doanh-b2b',
                'department' => 'Khối Kinh doanh',
                'location' => 'Hà Nội',
                'description' => "Phụ trách phát triển khách hàng doanh nghiệp tại khu vực miền Bắc. Lập kế hoạch tiếp cận, xây dựng quan hệ và chốt hợp đồng cho nhóm sản phẩm chính của AnPhat.\n\nMôi trường năng động, lương cứng + hoa hồng theo doanh số, được training bài bản 2 tuần đầu.",
                'requirements' => "- Tốt nghiệp cao đẳng trở lên, ưu tiên ngành Kinh tế / QTKD\n- 1+ năm kinh nghiệm sales B2B\n- Kỹ năng đàm phán, thuyết trình tốt\n- Có laptop và phương tiện đi lại",
                'commission_amount' => 2000000,
                'fields' => [
                    ['label' => 'Số năm kinh nghiệm sales B2B', 'type' => 'select', 'options' => ['Chưa có', 'Dưới 1 năm', '1–3 năm', '3–5 năm', 'Trên 5 năm'], 'is_required' => true],
                    ['label' => 'Mức lương kỳ vọng (VND/tháng)', 'type' => 'text', 'options' => null, 'is_required' => true],
                    ['label' => 'Bạn đang sử dụng phương tiện gì để đi gặp khách?', 'type' => 'radio', 'options' => ['Xe máy', 'Ô tô', 'Khác'], 'is_required' => true],
                    ['label' => 'Mô tả ngắn về thành tích sales nổi bật nhất', 'type' => 'textarea', 'options' => null, 'is_required' => false],
                ],
            ],
            [
                'title' => 'Lập trình viên Backend (Laravel)',
                'slug' => 'lap-trinh-vien-backend-laravel',
                'department' => 'Khối Công nghệ',
                'location' => 'Hà Nội / Remote',
                'description' => "Tham gia phát triển hệ thống nội bộ và sản phẩm SaaS tuyển dụng của AnPhat. Làm việc trong team 5 người (3 BE, 1 FE, 1 QA), code review nghiêm túc, deploy hằng tuần.\n\nStack chính: Laravel 12, PHP 8.2, MySQL, Redis, Inertia + Vue 3.",
                'requirements' => "- 2+ năm Laravel / PHP\n- Hiểu Eloquent ORM, queue, caching\n- Viết test (PHPUnit / Pest) là điểm cộng\n- Đọc/viết tiếng Anh kỹ thuật ổn",
                'commission_amount' => 3000000,
                'fields' => [
                    ['label' => 'Số năm kinh nghiệm Laravel', 'type' => 'select', 'options' => ['Dưới 1 năm', '1–2 năm', '2–4 năm', 'Trên 4 năm'], 'is_required' => true],
                    ['label' => 'Mức lương kỳ vọng (VND/tháng)', 'type' => 'text', 'options' => null, 'is_required' => true],
                    ['label' => 'Đã làm việc với queue/cache trong Laravel chưa?', 'type' => 'radio', 'options' => ['Đã từng', 'Chỉ nghe qua', 'Chưa'], 'is_required' => true],
                    ['label' => 'Link GitHub / portfolio (nếu có)', 'type' => 'text', 'options' => null, 'is_required' => false],
                    ['label' => 'Dự án bạn tâm đắc nhất, kể ngắn gọn', 'type' => 'textarea', 'options' => null, 'is_required' => false],
                ],
            ],
            [
                'title' => 'Chuyên viên Marketing Performance',
                'slug' => 'chuyen-vien-marketing-performance',
                'department' => 'Khối Marketing',
                'location' => 'Hà Nội',
                'description' => "Vận hành các chiến dịch quảng cáo Facebook Ads, Google Ads cho nhóm sản phẩm chủ lực. Phân tích hiệu quả, tối ưu CPL, làm việc cùng team Content và Designer.",
                'requirements' => "- 1+ năm chạy Facebook/Google Ads\n- Thành thạo GA4, Looker Studio\n- Tư duy số liệu rõ ràng\n- Có kinh nghiệm ngành tuyển dụng / giáo dục là điểm cộng",
                'commission_amount' => 1500000,
                'fields' => [
                    ['label' => 'Ngân sách quảng cáo lớn nhất bạn từng vận hành mỗi tháng', 'type' => 'select', 'options' => ['Dưới 50 triệu', '50–200 triệu', '200–500 triệu', 'Trên 500 triệu'], 'is_required' => true],
                    ['label' => 'Kênh quảng cáo bạn mạnh nhất', 'type' => 'radio', 'options' => ['Facebook Ads', 'Google Ads', 'TikTok Ads', 'Đa kênh'], 'is_required' => true],
                    ['label' => 'Mức lương kỳ vọng (VND/tháng)', 'type' => 'text', 'options' => null, 'is_required' => true],
                    ['label' => 'Mô tả 1 case bạn tự hào nhất (KPI, ngân sách, kết quả)', 'type' => 'textarea', 'options' => null, 'is_required' => false],
                ],
            ],
        ];

        foreach ($jobs as $data) {
            $fields = $data['fields'];
            unset($data['fields']);

            $job = Job::updateOrCreate(['slug' => $data['slug']], array_merge($data, ['is_active' => true]));

            $job->formFields()->delete();
            foreach ($fields as $i => $field) {
                JobFormField::create([
                    'job_id' => $job->id,
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'options' => $field['options'],
                    'is_required' => $field['is_required'],
                    'order' => $i + 1,
                ]);
            }
        }
    }
}
