<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Không tìm thấy trang — AnPhat" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-[#ecfdf7] via-white to-[#f8fafc] px-4">
        <div class="text-center max-w-md">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#0D7C66] shadow-md mb-6">
                <span class="text-white font-extrabold text-3xl">A</span>
            </div>

            <p class="text-7xl sm:text-8xl font-black text-[#1B2B4B]/10 tracking-tighter leading-none">404</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-[#1B2B4B] tracking-tight">
                Không tìm thấy trang
            </h1>
            <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                Trang bạn đang tìm có thể đã bị xoá, đổi tên, hoặc tạm thời không truy cập được.
            </p>

            <div class="mt-7 flex flex-wrap items-center justify-center gap-2.5">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#0D7C66] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] transition-all"
                >
                    Về trang chủ
                </Link>
                <Link
                    href="/admin/login"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-[#1B2B4B] hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                >
                    Đăng nhập HR
                </Link>
            </div>
        </div>

        <p class="mt-10 text-xs text-slate-400">
            © {{ new Date().getFullYear() }} AnPhat. Hệ thống tuyển dụng nội bộ.
        </p>
    </div>
</template>
