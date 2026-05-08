<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    code: { type: Number, default: 500 },
})
</script>

<template>
    <Head title="Đã xảy ra lỗi — AnPhat" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-red-50 via-white to-[#f8fafc] px-4">
        <div class="text-center max-w-md">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#0D7C66] shadow-md mb-6">
                <span class="text-white font-extrabold text-3xl">A</span>
            </div>

            <p class="text-7xl sm:text-8xl font-black text-red-200 tracking-tighter leading-none">{{ code }}</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-[#1B2B4B] tracking-tight">
                Đã có lỗi xảy ra
            </h1>
            <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                Hệ thống đang gặp sự cố tạm thời. Vui lòng thử lại sau ít phút hoặc liên hệ
                <a class="text-[#0D7C66] hover:underline font-medium" href="mailto:tuyendung@anphat.vn">tuyendung@anphat.vn</a>
                nếu vấn đề vẫn xảy ra.
            </p>

            <div class="mt-7 flex flex-wrap items-center justify-center gap-2.5">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#0D7C66] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] transition-all"
                >
                    Về trang chủ
                </Link>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-[#1B2B4B] hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                    @click="$event => window.location.reload()"
                >
                    Tải lại trang
                </button>
            </div>
        </div>

        <p class="mt-10 text-xs text-slate-400">
            © {{ new Date().getFullYear() }} AnPhat. Hệ thống tuyển dụng nội bộ.
        </p>
    </div>
</template>
