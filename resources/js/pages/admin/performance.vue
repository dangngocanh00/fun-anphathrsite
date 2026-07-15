<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import EmptyState from '../../components/EmptyState.vue'

const props = defineProps({
    month: { type: String, default: '' },
    monthLabel: { type: String, default: '' },
    rows: { type: Array, default: () => [] },
    totals: { type: Object, default: () => ({}) },
    monthOptions: { type: Array, default: () => [] },
})

const selectedMonth = ref(props.month)
watch(selectedMonth, (m) => {
    router.get('/admin/performance', { month: m }, { preserveState: true, preserveScroll: true, replace: true })
})

const formatVnd = (n) => new Intl.NumberFormat('vi-VN').format(Number(n) || 0) + '₫'

const stages = [
    { key: 'assigned', label: 'Hồ sơ gán', tone: 'bg-slate-200' },
    { key: 'tested', label: 'Sàng lọc', tone: 'bg-[#34D399]/60' },
    { key: 'interviewed', label: 'PV chuyên môn', tone: 'bg-[#34D399]/75' },
    { key: 'probation', label: 'Thử việc', tone: 'bg-[#0D7C66]/85' },
    { key: 'hired', label: 'Ký HĐ', tone: 'bg-[#0D7C66]' },
    { key: 'rejected', label: 'Loại CV', tone: 'bg-red-300' },
]

const maxAssigned = computed(() => Math.max(1, ...props.rows.map((r) => r.funnel.assigned)))

const widthPct = (count) => Math.round((count / maxAssigned.value) * 100)

const roleLabels = { admin: 'Quản trị viên', hr_manager: 'Trưởng nhóm', hr: 'Nhân viên' }
</script>

<template>
    <Head title="Hiệu suất — AnPhat HR" />

    <AdminLayout title="Hiệu suất tuyển dụng" breadcrumb="Admin / Hiệu suất">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap items-center gap-3">
            <label class="text-xs font-semibold text-slate-500">Tháng</label>
            <select
                v-model="selectedMonth"
                class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
            >
                <option v-for="opt in monthOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
            <p class="ml-auto text-xs text-slate-400">
                Số liệu tính theo ngày ứng viên nộp hồ sơ trong tháng {{ monthLabel }}.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6 mb-6">
            <div v-for="s in stages" :key="s.key" class="rounded-2xl bg-white border border-slate-100 shadow-sm p-4">
                <p class="text-xs text-slate-500">{{ s.label }}</p>
                <p class="text-2xl font-extrabold text-[#1B2B4B] mt-1">{{ totals[s.key] ?? 0 }}</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#1B2B4B] tracking-tight">Hiệu suất từng HR</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Phễu chuyển đổi 6 bước + commission tháng {{ monthLabel }}.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400">Tổng commission tháng</p>
                    <p class="text-lg font-bold text-[#0D7C66]">{{ formatVnd(totals.commission) }}</p>
                </div>
            </div>

            <EmptyState
                v-if="rows.length === 0"
                icon="chart"
                title="Chưa có dữ liệu hiệu suất"
                description="Tháng này chưa có HR nào được gán hồ sơ. Thử chọn tháng khác từ bộ lọc phía trên."
            />

            <div v-else class="divide-y divide-slate-100">
                <div v-for="row in rows" :key="row.id" class="px-6 py-5">
                    <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#0D7C66]/10 text-[#0D7C66] flex items-center justify-center font-bold text-sm">
                                {{ row.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-semibold text-[#1B2B4B]">{{ row.name }}</p>
                                <p class="text-xs text-slate-500">{{ roleLabels[row.role] ?? row.role }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-6 text-right">
                            <div>
                                <p class="text-[11px] text-slate-400">Tỉ lệ chuyển đổi</p>
                                <p class="text-sm font-bold text-[#1B2B4B]">{{ row.conversion_rate }}%</p>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-400">Commission</p>
                                <p class="text-sm font-bold text-[#0D7C66]">{{ formatVnd(row.commission) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-6 gap-2 text-center">
                        <div v-for="s in stages" :key="s.key" class="space-y-1.5">
                            <div class="h-16 rounded-lg bg-slate-100 relative overflow-hidden">
                                <div
                                    :class="['absolute bottom-0 left-0 right-0 transition-all duration-300', s.tone]"
                                    :style="{ height: widthPct(row.funnel[s.key]) + '%' }"
                                ></div>
                                <span class="absolute inset-0 flex items-center justify-center font-bold text-sm text-[#1B2B4B] z-10">
                                    {{ row.funnel[s.key] }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-tight">{{ s.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-4 text-xs text-slate-400">
            * Commission = Σ đơn giá vị trí × số ứng viên đã ký HĐ trong tháng. Sàng lọc → Thử việc tính cộng dồn (đang ở bước đó hoặc xa hơn).
            Loại CV đếm riêng, không cộng dồn vào các bước sau.
        </p>
    </AdminLayout>
</template>
