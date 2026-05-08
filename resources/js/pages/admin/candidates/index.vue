<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AdminLayout from '../../../components/AdminLayout.vue'
import EmptyState from '../../../components/EmptyState.vue'
import ScoreBadge from '../../../components/ScoreBadge.vue'

const props = defineProps({
    candidates: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', job_id: null, stage: null, assigned_hr_id: null }) },
    jobs: { type: Array, default: () => [] },
    hrs: { type: Array, default: () => [] },
    stages: { type: Object, default: () => ({}) },
    can: { type: Object, default: () => ({ filter_by_hr: false }) },
})

const search = ref(props.filters.search ?? '')
const jobId = ref(props.filters.job_id ?? null)
const stage = ref(props.filters.stage ?? null)
const hrId = ref(props.filters.assigned_hr_id ?? null)

let searchTimer = null
const applyFilters = (replace = true) => {
    router.get('/admin/candidates', {
        search: search.value || undefined,
        job_id: jobId.value || undefined,
        stage: stage.value || undefined,
        assigned_hr_id: hrId.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace })
}

watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(true), 300)
})
watch([jobId, stage, hrId], () => applyFilters(true))

const resetFilters = () => {
    search.value = ''
    jobId.value = null
    stage.value = null
    hrId.value = null
}

const stageTone = (s) => ({
    1: 'bg-slate-100 text-slate-700',
    2: 'bg-blue-50 text-blue-700',
    3: 'bg-indigo-50 text-indigo-700',
    4: 'bg-purple-50 text-purple-700',
    5: 'bg-amber-50 text-amber-700',
    6: 'bg-emerald-50 text-emerald-700',
}[s] ?? 'bg-slate-100 text-slate-600')

const formatDate = (iso) => {
    if (!iso) return '—'
    return new Date(iso).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
    <Head title="Ứng viên — AnPhat HR" />

    <AdminLayout title="Ứng viên" breadcrumb="Admin / Ứng viên">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-4 mb-5 grid gap-3 md:grid-cols-12 items-center">
            <div class="md:col-span-4">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔎</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Tìm theo tên hoặc số điện thoại…"
                        class="w-full rounded-lg border border-slate-200 pl-9 pr-3 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                    />
                </div>
            </div>
            <div class="md:col-span-3">
                <select
                    v-model="jobId"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                >
                    <option :value="null">Tất cả vị trí</option>
                    <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.title }}</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <select
                    v-model="stage"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                >
                    <option :value="null">Tất cả bước</option>
                    <option v-for="(name, num) in stages" :key="num" :value="Number(num)">{{ num }}. {{ name }}</option>
                </select>
            </div>
            <div v-if="can.filter_by_hr" class="md:col-span-2">
                <select
                    v-model="hrId"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                >
                    <option :value="null">Tất cả HR</option>
                    <option v-for="h in hrs" :key="h.id" :value="h.id">{{ h.name }}</option>
                </select>
            </div>
            <div :class="['md:col-span-1 flex justify-end', can.filter_by_hr ? '' : 'md:col-span-3']">
                <button
                    v-if="search || jobId || stage || hrId"
                    type="button"
                    class="text-xs font-semibold text-slate-500 hover:text-[#0D7C66]"
                    @click="resetFilters"
                >
                    Xoá lọc
                </button>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#1B2B4B] tracking-tight">
                        {{ candidates.total }} ứng viên
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Hiển thị {{ candidates.from ?? 0 }}–{{ candidates.to ?? 0 }} / {{ candidates.total }} kết quả.
                    </p>
                </div>
            </div>

            <EmptyState
                v-if="candidates.data.length === 0"
                icon="search"
                title="Không tìm thấy ứng viên"
                description="Thử mở rộng bộ lọc, xoá từ khoá tìm kiếm, hoặc đợi ứng viên mới gửi hồ sơ."
            />

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr class="text-left">
                            <th class="px-6 py-3 font-medium">Ứng viên</th>
                            <th class="px-6 py-3 font-medium">Vị trí</th>
                            <th class="px-6 py-3 font-medium">Bước</th>
                            <th class="px-6 py-3 font-medium">HR phụ trách</th>
                            <th class="px-6 py-3 font-medium">AI</th>
                            <th class="px-6 py-3 font-medium text-right">Ngày nộp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="c in candidates.data"
                            :key="c.id"
                            class="hover:bg-slate-50/60 cursor-pointer transition-colors"
                            @click="router.visit(`/admin/candidates/${c.id}`)"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[#1B2B4B]">{{ c.full_name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ c.phone }}<span v-if="c.email"> · {{ c.email }}</span>
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-[#1B2B4B]">{{ c.job?.title ?? '—' }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ c.job?.department }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap', stageTone(c.current_stage)]">
                                    {{ c.current_stage }}. {{ stages[c.current_stage] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="c.assigned_hr" class="text-sm text-[#1B2B4B]">{{ c.assigned_hr.name }}</span>
                                <span v-else class="text-xs text-slate-400 italic">Chưa gán</span>
                            </td>
                            <td class="px-6 py-4">
                                <ScoreBadge :score="c.ai_score" />
                            </td>
                            <td class="px-6 py-4 text-right text-slate-500 whitespace-nowrap">
                                {{ formatDate(c.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="candidates.last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <p class="text-xs text-slate-500">
                    Trang {{ candidates.current_page }} / {{ candidates.last_page }}
                </p>
                <nav class="flex items-center gap-1">
                    <Link
                        v-for="(link, idx) in candidates.links"
                        :key="idx"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'min-w-[32px] h-8 px-2.5 inline-flex items-center justify-center rounded-lg text-xs font-semibold transition-all',
                            link.active
                                ? 'bg-[#0D7C66] text-white'
                                : link.url
                                    ? 'border border-slate-200 text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66]'
                                    : 'border border-slate-100 text-slate-300 cursor-not-allowed pointer-events-none',
                        ]"
                        preserve-scroll
                        preserve-state
                    />
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>
