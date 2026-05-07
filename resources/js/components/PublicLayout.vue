<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="min-h-screen flex flex-col bg-[#f8fafc] text-[#0F172A]">
        <header class="sticky top-0 z-30 bg-white/85 backdrop-blur border-b border-slate-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2.5 group">
                    <span class="w-9 h-9 rounded-xl bg-[#0D7C66] text-white font-extrabold flex items-center justify-center text-base group-hover:shadow-md transition-all duration-200">A</span>
                    <span class="font-bold tracking-tight text-[#1B2B4B]">AnPhat <span class="text-[#0D7C66]">Tuyển dụng</span></span>
                </Link>

                <nav class="hidden md:flex items-center gap-7 text-sm">
                    <Link href="/" class="text-slate-600 hover:text-[#0D7C66] transition-colors duration-200 font-medium">Vị trí tuyển</Link>
                    <a href="#lien-he" class="text-slate-600 hover:text-[#0D7C66] transition-colors duration-200 font-medium">Liên hệ</a>
                </nav>

                <Link
                    href="/admin/login"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 text-sm font-semibold text-[#1B2B4B] shadow-sm hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all duration-200"
                >
                    Đăng nhập HR
                </Link>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer id="lien-he" class="border-t border-slate-100 bg-white mt-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 grid gap-8 md:grid-cols-3">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-[#0D7C66] text-white font-extrabold flex items-center justify-center">A</span>
                        <span class="font-bold tracking-tight text-[#1B2B4B]">AnPhat</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-3 leading-relaxed">
                        Hệ thống tuyển dụng nội bộ của AnPhat — minh bạch, nhanh chóng,
                        công bằng cho mọi ứng viên.
                    </p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#1B2B4B] mb-3">Liên hệ ứng tuyển</p>
                    <ul class="text-sm text-slate-600 space-y-2">
                        <li><span class="text-slate-400">Hotline:</span> <a class="hover:text-[#0D7C66] font-medium" href="tel:0989123456">0989 123 456</a></li>
                        <li><span class="text-slate-400">Email:</span> <a class="hover:text-[#0D7C66] font-medium" href="mailto:tuyendung@anphat.vn">tuyendung@anphat.vn</a></li>
                        <li><span class="text-slate-400">Zalo:</span> <a class="hover:text-[#0D7C66] font-medium" href="https://zalo.me/0989123456" target="_blank" rel="noopener">zalo.me/0989123456</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#1B2B4B] mb-3">Văn phòng</p>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Tầng 8, Tòa nhà AnPhat<br />
                        Số 1 Đường ABC, Cầu Giấy, Hà Nội
                    </p>
                </div>
            </div>
            <div class="border-t border-slate-100">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 text-xs text-slate-400 flex justify-between flex-wrap gap-2">
                    <span>© {{ new Date().getFullYear() }} AnPhat. All rights reserved.</span>
                    <span>Powered by AnPhat HR System</span>
                </div>
            </div>
        </footer>
    </div>
</template>
