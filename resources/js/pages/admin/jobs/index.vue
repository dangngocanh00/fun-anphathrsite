<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '../../../components/AdminLayout.vue'

defineProps({
    jobs: { type: Array, default: () => [] },
})

const confirmDelete = ref(null)

const formatVnd = (n) => {
    if (!n) return '0₫'
    return new Intl.NumberFormat('vi-VN').format(n) + '₫'
}

const toggleActive = (job) => {
    router.patch(`/admin/jobs/${job.id}/toggle`, {}, { preserveScroll: true })
}

const deleteJob = (job) => {
    router.delete(`/admin/jobs/${job.id}`, {
        preserveScroll: true,
        onFinish: () => { confirmDelete.value = null },
    })
}
</script>

<template>
    <Head title="Vị trí tuyển — AnPhat HR" />

    <AdminLayout title="Vị trí tuyển" breadcrumb="Admin / Vị trí tuyển">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#1B2B4B] tracking-tight">
                        Tất cả vị trí
                        <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-2.5 py-0.5 text-xs font-semibold">
                            {{ jobs.length }}
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Quản lý JD, hoa hồng, form sơ vấn của từng vị trí.</p>
                </div>
                <Link
                    href="/admin/jobs/create"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#0D7C66] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] transition-all"
                >
                    + Tạo vị trí mới
                </Link>
            </div>

            <div v-if="jobs.length === 0" class="px-6 py-16 text-center text-sm text-slate-500">
                Chưa có vị trí nào. Bấm "Tạo vị trí mới" để bắt đầu.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr class="text-left">
                            <th class="px-6 py-3 font-medium">Vị trí</th>
                            <th class="px-6 py-3 font-medium">Khối / Khu vực</th>
                            <th class="px-6 py-3 font-medium text-right">Hoa hồng</th>
                            <th class="px-6 py-3 font-medium text-center">Ứng viên</th>
                            <th class="px-6 py-3 font-medium text-center">Câu hỏi</th>
                            <th class="px-6 py-3 font-medium text-center">Trạng thái</th>
                            <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="job in jobs" :key="job.id" class="hover:bg-slate-50/60">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[#1B2B4B]">{{ job.title }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 font-mono">/{{ job.slug }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <p>{{ job.department || '—' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ job.location || '—' }}</p>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-[#0D7C66]">{{ formatVnd(job.commission_amount) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-2.5 py-0.5 text-xs font-semibold">
                                    {{ job.candidates_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Link
                                    :href="`/admin/jobs/${job.id}/form`"
                                    class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-2.5 py-0.5 text-xs font-semibold hover:bg-[#0D7C66]/10 hover:text-[#0D7C66] transition-colors"
                                >
                                    {{ job.fields_count }} câu
                                </Link>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    type="button"
                                    @click="toggleActive(job)"
                                    :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border transition-colors',
                                        job.is_active
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100'
                                            : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200',
                                    ]"
                                >
                                    {{ job.is_active ? 'Đang tuyển' : 'Tạm dừng' }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end items-center gap-1.5">
                                    <Link
                                        :href="`/admin/jobs/${job.id}/form`"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                                    >
                                        Form
                                    </Link>
                                    <Link
                                        :href="`/admin/jobs/${job.id}/edit`"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                                    >
                                        Sửa
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-red-100 text-red-600 px-2.5 py-1 text-xs font-semibold hover:bg-red-50 transition-all"
                                        @click="confirmDelete = job"
                                    >
                                        Xoá
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="confirmDelete"
                class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="confirmDelete = null"
            >
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Xác nhận xoá</h3>
                    <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                        Xoá vị trí <span class="font-semibold text-[#1B2B4B]">"{{ confirmDelete.title }}"</span>? Hồ sơ ứng viên hiện tại sẽ giữ lại nhưng vị trí này sẽ không còn xuất hiện trên trang công khai.
                    </p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900" @click="confirmDelete = null">Huỷ</button>
                        <button type="button" class="rounded-lg bg-red-600 text-white px-4 py-1.5 text-sm font-semibold hover:bg-red-700" @click="deleteJob(confirmDelete)">Xoá vị trí</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
