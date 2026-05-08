<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import ScoreBadge from '../../components/ScoreBadge.vue'

const props = defineProps({
    candidates: { type: Array, default: () => [] },
    hrs: { type: Array, default: () => [] },
    can: { type: Object, default: () => ({ assign: false, analyze: false }) },
})

const assignOpen = ref(null)
const assignChoice = ref({})
const analyzing = ref({})

const formatDate = (iso) => {
    if (!iso) return '—'
    const d = new Date(iso)
    return d.toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const submitAssign = (candidate) => {
    const hrId = assignChoice.value[candidate.id]
    if (!hrId) return
    router.post(`/admin/inbox/${candidate.id}/assign`, { assigned_hr_id: Number(hrId) }, {
        preserveScroll: true,
        onSuccess: () => { assignOpen.value = null },
    })
}

const analyze = (candidate) => {
    analyzing.value[candidate.id] = true
    router.post(`/admin/inbox/${candidate.id}/analyze`, {}, {
        preserveScroll: true,
        onFinish: () => { analyzing.value[candidate.id] = false },
    })
}
</script>

<template>
    <Head title="Hồ sơ mới — AnPhat HR" />

    <AdminLayout title="Hồ sơ mới" breadcrumb="Admin / Inbox">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#1B2B4B] tracking-tight">
                        Hồ sơ chờ xử lý
                        <span class="ml-2 inline-flex items-center rounded-full bg-[#0D7C66]/10 text-[#0D7C66] px-2.5 py-0.5 text-xs font-semibold">
                            {{ candidates.length }}
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Hồ sơ ở bước "Tiếp nhận" và chưa có HR phụ trách.</p>
                </div>
            </div>

            <div v-if="candidates.length === 0" class="px-6 py-16 text-center text-sm text-slate-500">
                Hiện không có hồ sơ mới nào cần xử lý.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr class="text-left">
                            <th class="px-6 py-3 font-medium">Ứng viên</th>
                            <th class="px-6 py-3 font-medium">Vị trí</th>
                            <th class="px-6 py-3 font-medium">Ngày nộp</th>
                            <th class="px-6 py-3 font-medium">CV</th>
                            <th class="px-6 py-3 font-medium">AI</th>
                            <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="c in candidates" :key="c.id" class="hover:bg-slate-50/60">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[#1B2B4B]">{{ c.full_name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ c.phone }}<span v-if="c.email"> · {{ c.email }}</span></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-[#1B2B4B]">{{ c.job?.title }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ c.job?.department }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ formatDate(c.created_at) }}</td>
                            <td class="px-6 py-4">
                                <a
                                    :href="c.cv_link"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-[#0D7C66] hover:underline text-sm font-medium"
                                >
                                    Mở CV ↗
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <ScoreBadge :score="c.ai_score" />
                                <p v-if="c.ai_analyzed_at" class="text-[11px] text-slate-400 mt-1">{{ formatDate(c.ai_analyzed_at) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end items-center gap-2">
                                    <button
                                        v-if="can.analyze"
                                        type="button"
                                        :disabled="analyzing[c.id]"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-[#1B2B4B] hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all disabled:opacity-50"
                                        @click="analyze(c)"
                                    >
                                        <span v-if="!analyzing[c.id]">{{ c.ai_score === null ? 'Phân tích AI' : 'Phân tích lại' }}</span>
                                        <span v-else>Đang phân tích…</span>
                                    </button>

                                    <div v-if="can.assign" class="relative">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-lg bg-[#0D7C66] px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-[#0c6553] transition-all"
                                            @click="assignOpen = assignOpen === c.id ? null : c.id"
                                        >
                                            Gán HR ▾
                                        </button>
                                        <div
                                            v-if="assignOpen === c.id"
                                            class="absolute right-0 mt-2 w-64 rounded-xl bg-white border border-slate-200 shadow-lg p-3 z-10"
                                        >
                                            <p class="text-xs font-semibold text-[#1B2B4B] mb-2">Chọn HR phụ trách</p>
                                            <select
                                                v-model="assignChoice[c.id]"
                                                class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                            >
                                                <option :value="undefined">— Chọn —</option>
                                                <option v-for="hr in hrs" :key="hr.id" :value="hr.id">
                                                    {{ hr.name }} ({{ hr.roles?.[0] }})
                                                </option>
                                            </select>
                                            <div class="flex justify-end gap-2 mt-3">
                                                <button
                                                    type="button"
                                                    class="text-xs text-slate-500 hover:text-slate-700"
                                                    @click="assignOpen = null"
                                                >Huỷ</button>
                                                <button
                                                    type="button"
                                                    class="rounded-lg bg-[#0D7C66] text-white text-xs font-semibold px-3 py-1.5 hover:bg-[#0c6553] disabled:opacity-50"
                                                    :disabled="!assignChoice[c.id]"
                                                    @click="submitAssign(c)"
                                                >
                                                    Xác nhận
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-4 text-xs text-slate-400">
            * Sau khi gán HR, hồ sơ sẽ rời khỏi Inbox và xuất hiện trong Pipeline ở bước "Tiếp nhận" để HR phụ trách xử lý tiếp.
        </p>
    </AdminLayout>
</template>
