<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import EmptyState from '../../components/EmptyState.vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ today_applications: 0, in_pipeline: 0, hired_this_month: 0, inbox_pending: 0 }),
    },
    recent_candidates: { type: Array, default: () => [] },
})

const page = usePage()
const user = computed(() => page.props.auth?.user)
const roleLabels = { admin: 'Quản trị viên', hr_manager: 'Trưởng nhóm HR', hr: 'Nhân viên HR' }
const primaryRole = computed(() => user.value?.roles?.[0] ?? null)
const roleLabel = computed(() => primaryRole.value ? (roleLabels[primaryRole.value] ?? primaryRole.value) : '—')

const stageLabels = {
    1: 'Tiếp nhận',
    2: 'Sơ vấn',
    3: 'Sàng lọc',
    4: 'PV chuyên môn',
    5: 'Thử việc',
    6: 'Ký HĐ',
}

const stageTone = (s) => ({
    1: 'bg-slate-100 text-slate-700',
    2: 'bg-blue-50 text-blue-700',
    3: 'bg-indigo-50 text-indigo-700',
    4: 'bg-purple-50 text-purple-700',
    5: 'bg-amber-50 text-amber-700',
    6: 'bg-emerald-50 text-emerald-700',
}[s] ?? 'bg-slate-100 text-slate-600')

const formatRelative = (iso) => {
    if (!iso) return '—'
    const diff = Date.now() - new Date(iso).getTime()
    const m = Math.round(diff / 60000)
    if (m < 1) return 'vừa xong'
    if (m < 60) return `${m} phút trước`
    const h = Math.round(m / 60)
    if (h < 24) return `${h} giờ trước`
    const d = Math.round(h / 24)
    if (d < 30) return `${d} ngày trước`
    return new Date(iso).toLocaleDateString('vi-VN')
}
</script>

<template>
    <Head title="Dashboard — AnPhat HR" />

    <AdminLayout title="Dashboard" breadcrumb="Trang chủ / Tổng quan">
        <div class="max-w-6xl space-y-6">
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-[#1B2B4B] tracking-tight">
                            Xin chào, {{ user?.name }} 👋
                        </h2>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Bạn đang đăng nhập với vai trò
                            <span class="inline-flex items-center px-2.5 py-0.5 ml-1 rounded-full text-xs font-medium bg-[#ecfdf7] text-[#0D7C66]">
                                {{ roleLabel }}
                            </span>
                        </p>
                    </div>
                    <Link
                        v-if="stats.inbox_pending > 0"
                        href="/admin/inbox"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-50 border border-red-100 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 hover:border-red-200 transition-all"
                    >
                        <span class="relative flex w-2 h-2">
                            <span class="animate-ping absolute inline-flex w-full h-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full w-2 h-2 bg-red-500"></span>
                        </span>
                        {{ stats.inbox_pending }} hồ sơ chờ phân HR →
                    </Link>
                </div>

                <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-100 p-5">
                        <p class="text-xs text-slate-500">Hồ sơ mới hôm nay</p>
                        <p class="text-2xl font-bold text-[#1B2B4B] mt-1">{{ stats.today_applications }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-5">
                        <p class="text-xs text-slate-500">Đang trong pipeline</p>
                        <p class="text-2xl font-bold text-[#1B2B4B] mt-1">{{ stats.in_pipeline }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Bước 1 đến 5</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-5">
                        <p class="text-xs text-slate-500">Đã ký HĐ tháng này</p>
                        <p class="text-2xl font-bold text-[#0D7C66] mt-1">{{ stats.hired_this_month }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[#1B2B4B] tracking-tight">Hồ sơ gần đây</h3>
                        <p class="text-xs text-slate-500 mt-0.5">5 ứng viên nộp hồ sơ mới nhất.</p>
                    </div>
                    <Link href="/admin/pipeline" class="text-xs font-semibold text-[#0D7C66] hover:underline">
                        Xem pipeline →
                    </Link>
                </div>

                <EmptyState
                    v-if="recent_candidates.length === 0"
                    icon="users"
                    title="Chưa có hồ sơ nào"
                    description="Hồ sơ mới sẽ tự động xuất hiện ở đây sau khi ứng viên gửi đơn từ trang công khai."
                />

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr class="text-left">
                                <th class="px-6 py-3 font-medium">Ứng viên</th>
                                <th class="px-6 py-3 font-medium">Vị trí</th>
                                <th class="px-6 py-3 font-medium">Bước hiện tại</th>
                                <th class="px-6 py-3 font-medium text-right">Thời gian nộp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="c in recent_candidates" :key="c.id" class="hover:bg-slate-50/60">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-[#1B2B4B]">{{ c.full_name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-[#1B2B4B]">{{ c.job_title || '—' }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ c.job_department || '' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold', stageTone(c.current_stage)]">
                                        {{ c.current_stage }}. {{ stageLabels[c.current_stage] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-500 whitespace-nowrap">
                                    {{ formatRelative(c.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
