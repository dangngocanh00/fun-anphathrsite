# AnPhat Recruitment System - Planning

## Tech Stack
- Backend: Laravel 12 + PHP 8.2
- Frontend: Vue 3 + Inertia.js
- CSS: Tailwind CSS
- Database: MySQL (anphat_recruitment)
- Auth & Roles: Spatie Laravel Permission
- AI: Anthropic Claude API

## Cấu trúc phân quyền (Roles)
- admin: toàn quyền, quản lý JD, form, commission, tài khoản HR
- hr_manager: xem toàn bộ pipeline, gán hồ sơ, xem hiệu suất team
- hr: xử lý hồ sơ được gán, xem commission bản thân

## Public Site (ứng viên)
### Routes
- GET / → danh sách vị trí tuyển dụng, lọc theo khối/phòng ban
- GET /jobs/{slug} → chi tiết JD + form sơ vấn + apply
### Tính năng
- Hiển thị danh sách job đang tuyển (is_active = true)
- Mỗi job có form câu hỏi sơ vấn riêng (admin cấu hình)
- Ứng viên điền thông tin cơ bản + nhập link CV Google Drive
- Sau khi apply → tạo candidate + application trong DB

## HR Dashboard (nội bộ)
### Routes
- GET /admin/login
- GET /admin/dashboard → tổng quan số liệu
- GET /admin/inbox → hồ sơ mới chưa xử lý
- GET /admin/pipeline → Kanban 6 bước
- GET /admin/candidates → danh sách tất cả ứng viên
- GET /admin/jobs → quản lý JD
- GET /admin/jobs/{id}/form → cấu hình form câu hỏi sơ vấn
- GET /admin/performance → hiệu suất HR + commission
- GET /admin/settings → cài đặt chung

### Pipeline 6 bước (cố định)
1. Tiếp nhận hồ sơ
2. Sơ vấn (hotline)
3. Sàng lọc / Test năng lực
4. Phỏng vấn chuyên môn
5. Thử việc
6. Ký hợp đồng

### AI Sàng lọc CV
- Đọc link CV Google Drive
- So sánh với JD gốc đã cấu hình
- Output: điểm phù hợp (0-100), red flags, gợi ý câu hỏi phỏng vấn

### Commission
- Cố định theo vị trí (admin cài đơn giá)
- Commission = số ứng viên tuyển được × đơn giá vị trí

## Database Schema

### users
- id, name, email, password
- role: admin | hr_manager | hr

### jobs
- id, title, slug, department, location
- description (JD đầy đủ)
- requirements
- commission_amount (đơn giá hoa hồng)
- is_active
- timestamps

### job_form_fields (câu hỏi sơ vấn theo job)
- id, job_id, label, type (text|select|radio|textarea)
- options (JSON - cho select/radio)
- is_required, order
- timestamps

### candidates
- id, full_name, phone, email
- cv_link (Google Drive link)
- job_id, assigned_hr_id
- current_stage (1-6)
- ai_score, ai_flags (JSON), ai_questions (JSON)
- ai_analyzed_at
- timestamps

### candidate_form_answers
- id, candidate_id, field_id, answer
- timestamps

### pipeline_logs
- id, candidate_id, from_stage, to_stage
- moved_by (user_id)
- note
- timestamps

### interview_notes (ghi chú sơ vấn hotline)
- id, candidate_id, user_id
- note, result (pass|fail|pending)
- timestamps

## Thứ tự build
1. Migrations + Models + Relations
2. Auth + Roles (Spatie)
3. Public site: trang chủ + JD + form apply
4. Admin: login + dashboard + inbox
5. Admin: pipeline Kanban
6. Admin: quản lý JD + cấu hình form
7. AI screening service
8. Admin: hiệu suất + commission

## Design System

### Brand Colors
- Primary: #0D7C66 (xanh lá đậm)
- Secondary: #1B2B4B (xanh than)
- Accent: #34D399 (xanh lá sáng - dùng cho CTA, badge)
- Background: #F8FAFC
- Text: #0F172A

### Typography
- Font: Be Vietnam Pro (Google Fonts)
- Heading: font-bold, tracking-tight
- Body: font-normal, leading-relaxed

### Style
- Border radius: rounded-xl cho card, rounded-full cho badge
- Shadow: shadow-sm mặc định, shadow-md khi hover
- Spacing: padding 24px card, gap 16px grid
- Transition: transition-all duration-200

### Public Site Layout
- Header: logo trái + nav + nút "Đăng nhập HR" phải
- Hero section: headline lớn + số liệu công ty
- Job listing: grid 2-3 cột, card có department tag + badge trạng thái
- Footer: thông tin liên hệ, hotline, Zalo

### Admin Dashboard Layout  
- Sidebar cố định bên trái, màu #1B2B4B
- Header trắng với breadcrumb + avatar HR
- Content area background #F8FAFC