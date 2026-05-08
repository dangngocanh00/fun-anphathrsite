<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Cập nhật file README.md với nội dung đầy đủ bằng tiếng Việt, gồm:

1. Giới thiệu project: AnPhat Recruitment System — hệ thống tuyển dụng nội bộ gồm public site + HR dashboard

2. Tech stack: Laravel 12, Vue 3, Inertia.js, Tailwind CSS, MySQL, Spatie Permission

3. Yêu cầu môi trường:
   - PHP 8.2+ (XAMPP)
   - Composer
   - Node.js 18+
   - MySQL (XAMPP)

4. Các bước chạy local lần đầu:
   - Clone repo
   - composer install
   - npm install
   - cp .env.example .env + cấu hình DB
   - php artisan key:generate
   - Tạo database anphat_recruitment trong phpMyAdmin
   - php artisan migrate --seed
   - php artisan serve (terminal 1)
   - npm run dev (terminal 2)
   - Vào http://localhost:8000

5. Reset database về data mẫu:
   - php artisan migrate:fresh --seed

6. Tài khoản test (LOCAL ONLY — KHÔNG dùng production):
   admin@anphat.test / password — Admin (toàn quyền)
   manager1@anphat.test / password — HR Trưởng nhóm
   manager2@anphat.test / password — HR Trưởng nhóm
   hr1@anphat.test / password — Nhân viên HR
   hr2@anphat.test / password — Nhân viên HR
   hr3@anphat.test / password — Nhân viên HR

7. Cấu trúc thư mục chính:
   app/Http/Controllers/Admin/ — controllers HR dashboard
   app/Http/Controllers/ — controllers public site
   app/Models/ — Eloquent models
   app/Services/ — AIScreeningService
   resources/js/pages/admin/ — Vue pages HR dashboard
   resources/js/pages/ — Vue pages public site
   resources/js/components/ — shared components
   database/migrations/ — migrations
   database/seeders/ — seeders mẫu

8. Phân quyền:
   admin — toàn quyền, quản lý JD, form, commission, tài khoản HR
   hr_manager — xem toàn bộ pipeline, gán hồ sơ, xem hiệu suất team
   hr — xử lý hồ sơ được gán, xem commission bản thân

9. Biến môi trường quan trọng cần cấu hình:
   DB_DATABASE, DB_USERNAME, DB_PASSWORD
   ANTHROPIC_API_KEY (để dùng AI screening — có thể bỏ qua)

10. Lưu ý: thêm warning to rõ ràng rằng tài khoản test và ANTHROPIC_API_KEY không được commit lên production
