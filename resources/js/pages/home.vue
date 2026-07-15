<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '../components/PublicLayout.vue'

defineProps({
    groups: { type: Array, default: () => [] },
    totalJobs: { type: Number, default: 0 },
})
</script>

<template>
    <Head title="Cơ hội nghề nghiệp — AnPhat" />

    <PublicLayout>
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-[#ecfdf7] via-white to-[#f8fafc]"></div>
            <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-16 pb-12 md:pt-24 md:pb-16">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0D7C66]/10 text-[#0D7C66] px-3 py-1 text-xs font-semibold tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34D399]"></span>
                        Đang mở {{ totalJobs }} vị trí
                    </span>
                    <h1 class="mt-4 text-3xl md:text-5xl font-extrabold text-[#1B2B4B] tracking-tight leading-tight">
                        Cùng AnPhat <br class="hidden md:block" />
                        xây dựng đội ngũ <br class="hidden md:block" /><span class="text-[#0D7C66]">nhiệt huyết</span>
                    </h1>
                    <p class="mt-5 text-base md:text-lg text-slate-600 leading-relaxed max-w-xl">
                        Chúng tôi đang tìm kiếm những đồng đội cùng chí hướng — minh bạch về lương,
                        rõ ràng về lộ trình, và luôn đặt khách hàng lên hàng đầu.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="#vi-tri-tuyen" class="inline-flex items-center gap-2 rounded-full bg-[#0D7C66] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] hover:shadow-md transition-all duration-200">
                            Xem vị trí đang tuyển →
                        </a>
                        <a href="#lien-he" class="inline-flex items-center gap-2 rounded-full bg-white border border-slate-200 px-5 py-2.5 text-sm font-semibold text-[#1B2B4B] hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all duration-200">
                            Liên hệ HR
                        </a>
                    </div>
                </div>

                <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl">
                    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                        <p class="text-2xl font-extrabold text-[#0D7C66]">{{ totalJobs }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Vị trí đang tuyển</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                        <p class="text-2xl font-extrabold text-[#0D7C66]">{{ groups.length }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Khối / phòng ban</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                        <p class="text-2xl font-extrabold text-[#0D7C66]">48h</p>
                        <p class="text-xs text-slate-500 mt-0.5">Phản hồi hồ sơ</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                        <p class="text-2xl font-extrabold text-[#0D7C66]">100%</p>
                        <p class="text-xs text-slate-500 mt-0.5">Lương minh bạch</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="vi-tri-tuyen" class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-[#1B2B4B] tracking-tight">Vị trí đang tuyển</h2>
                    <p class="text-sm text-slate-500 mt-1">Chọn vị trí phù hợp để xem mô tả chi tiết và ứng tuyển ngay.</p>
                </div>
            </div>

            <div v-if="groups.length === 0" class="rounded-2xl bg-white border border-dashed border-slate-200 p-10 text-center">
                <p class="text-slate-500">Hiện tại chưa có vị trí tuyển dụng nào. Vui lòng quay lại sau.</p>
            </div>

            <div v-else class="space-y-12">
                <div v-for="group in groups" :key="group.department">
                    <div class="flex items-center gap-3 mb-5">
                        <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight" v-html="group.department"></h3>
                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-2.5 py-0.5 text-xs font-medium">
                            {{ group.jobs.length }} vị trí
                        </span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="job in group.jobs"
                            :key="job.id"
                            :href="`/jobs/${job.slug}`"
                            class="group rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 p-5 flex flex-col"
                        >
                            <span class="inline-flex self-start items-center rounded-full bg-[#0D7C66]/10 text-[#0D7C66] px-2.5 py-0.5 text-[11px] font-semibold tracking-wide" v-html="job.department"></span>
                            <h4 class="mt-3 text-base font-semibold text-[#1B2B4B] group-hover:text-[#0D7C66] transition-colors leading-snug" v-html="job.title"></h4>
                            <p v-if="job.location" class="mt-1.5 text-sm text-slate-500 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                <span v-html="job.location"></span>
                            </p>
                            <div class="mt-auto pt-5 flex items-center justify-between">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs font-medium">Đang tuyển</span>
                                <span class="text-sm font-semibold text-[#0D7C66] group-hover:translate-x-0.5 transition-transform duration-200">Xem chi tiết →</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
