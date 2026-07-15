<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import ScoreBadge from '../../components/ScoreBadge.vue'
import { stripHtml } from '../../utils/richtext.js'

const props = defineProps({
    columns: { type: Array, default: () => [] },
    stages: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    jobs: { type: Array, default: () => [] },
    hrs: { type: Array, default: () => [] },
    can: { type: Object, default: () => ({ manage: false }) },
})

const stageLabels = computed(() => props.stages)

const filterJob = ref(props.filters.job_id ?? null)
const filterHr = ref(props.filters.assigned_hr_id ?? null)

watch([filterJob, filterHr], () => {
    router.get('/admin/pipeline', {
        job_id: filterJob.value || undefined,
        assigned_hr_id: filterHr.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true })
})

const draggingId = ref(null)
const dragOverStage = ref(null)

const onDragStart = (e, card) => {
    draggingId.value = card.id
    e.dataTransfer.effectAllowed = 'move'
    e.dataTransfer.setData('text/plain', String(card.id))
}
const onDragOver = (e, stage) => {
    e.preventDefault()
    dragOverStage.value = stage
}
const onDragLeave = (stage) => {
    if (dragOverStage.value === stage) dragOverStage.value = null
}
const onDrop = (e, toStage) => {
    e.preventDefault()
    const id = Number(e.dataTransfer.getData('text/plain') || draggingId.value)
    dragOverStage.value = null
    draggingId.value = null
    if (!id) return

    const card = props.columns.flatMap((c) => c.cards).find((c) => c.id === id)
    if (!card || card.current_stage === toStage) return

    router.post(`/admin/pipeline/${id}/move`, { to_stage: toStage }, {
        preserveScroll: true,
        preserveState: true,
    })
}

const detail = ref(null)
const detailLoading = ref(false)
const noteForm = ref({ note: '', result: 'pending' })
const noteSubmitting = ref(false)

const openDetail = async (card) => {
    detailLoading.value = true
    detail.value = { id: card.id, full_name: card.full_name, loading: true }
    try {
        const res = await fetch(`/admin/pipeline/${card.id}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        if (!res.ok) throw new Error('Không tải được chi tiết')
        detail.value = await res.json()
    } catch (e) {
        detail.value = null
        alert(e.message)
    } finally {
        detailLoading.value = false
    }
}
const closeDetail = () => {
    detail.value = null
    noteForm.value = { note: '', result: 'pending' }
}

const submitNote = () => {
    if (!detail.value || !noteForm.value.note.trim()) return
    noteSubmitting.value = true
    router.post(`/admin/pipeline/${detail.value.id}/note`, noteForm.value, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: async () => {
            noteForm.value = { note: '', result: 'pending' }
            await openDetail({ id: detail.value.id, full_name: detail.value.full_name })
        },
        onFinish: () => { noteSubmitting.value = false },
    })
}

const moveFromModal = (toStage) => {
    if (!detail.value || detail.value.current_stage === toStage) return
    router.post(`/admin/pipeline/${detail.value.id}/move`, { to_stage: toStage }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => openDetail({ id: detail.value.id, full_name: detail.value.full_name }),
    })
}

const formatDate = (iso) => {
    if (!iso) return '—'
    return new Date(iso).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const resultLabel = (r) => ({ pass: 'Đạt', fail: 'Loại', pending: 'Cân nhắc' }[r] ?? r)
const resultClass = (r) => ({
    pass: 'bg-emerald-50 text-emerald-700 border-emerald-100',
    fail: 'bg-red-50 text-red-700 border-red-100',
    pending: 'bg-amber-50 text-amber-700 border-amber-100',
}[r] ?? 'bg-slate-50 text-slate-600')
</script>

<template>
    <Head title="Pipeline — AnPhat HR" />

    <AdminLayout title="Pipeline tuyển dụng" breadcrumb="Admin / Pipeline">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500">Vị trí</label>
                <select
                    v-model="filterJob"
                    class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                >
                    <option :value="null">Tất cả</option>
                    <option v-for="j in jobs" :key="j.id" :value="j.id">{{ stripHtml(j.title) }}</option>
                </select>
            </div>
            <div v-if="can.manage" class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-500">HR phụ trách</label>
                <select
                    v-model="filterHr"
                    class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                >
                    <option :value="null">Tất cả</option>
                    <option v-for="h in hrs" :key="h.id" :value="h.id">{{ h.name }}</option>
                </select>
            </div>
            <button
                v-if="filterJob || filterHr"
                type="button"
                class="ml-auto text-xs font-medium text-slate-500 hover:text-[#0D7C66]"
                @click="filterJob = null; filterHr = null"
            >
                Xoá lọc
            </button>
            <p class="ml-auto text-xs text-slate-400">Kéo thả thẻ giữa các cột để chuyển bước.</p>
        </div>

        <div class="overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max">
                <div
                    v-for="col in columns"
                    :key="col.stage"
                    :class="[
                        'w-72 shrink-0 rounded-2xl border transition-all duration-200',
                        dragOverStage === col.stage ? 'border-[#0D7C66] bg-[#ecfdf7]' :
                            col.name === 'Loại CV' ? 'border-red-200 bg-red-50/40' : 'border-slate-200 bg-slate-50',
                    ]"
                    @dragover="onDragOver($event, col.stage)"
                    @dragleave="onDragLeave(col.stage)"
                    @drop="onDrop($event, col.stage)"
                >
                    <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                        <p class="text-sm font-semibold text-[#1B2B4B] tracking-tight">
                            <span
                                :class="[
                                    'inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold mr-1.5',
                                    col.name === 'Loại CV' ? 'bg-red-500' : 'bg-[#1B2B4B]',
                                ]"
                            >{{ col.stage }}</span>
                            {{ col.name }}
                        </p>
                        <span class="inline-flex items-center rounded-full bg-white border border-slate-200 px-2 py-0.5 text-xs text-slate-600">
                            {{ col.cards.length }}
                        </span>
                    </div>
                    <p v-if="col.name === 'Loại CV'" class="px-4 pt-2 text-[11px] text-red-500 font-medium">
                        🗑 Tự động xoá sau 7 ngày
                    </p>

                    <div class="p-3 space-y-2 min-h-[120px]">
                        <p v-if="col.cards.length === 0" class="text-xs text-slate-400 text-center py-6">Chưa có ứng viên</p>

                        <div
                            v-for="card in col.cards"
                            :key="card.id"
                            draggable="true"
                            class="rounded-xl bg-white border border-slate-200 p-3 shadow-sm hover:shadow-md hover:border-[#0D7C66]/40 transition-all cursor-grab active:cursor-grabbing"
                            :class="{ 'opacity-50': draggingId === card.id }"
                            @dragstart="onDragStart($event, card)"
                            @click="openDetail(card)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-sm text-[#1B2B4B] leading-tight">{{ card.full_name }}</p>
                                <ScoreBadge :score="card.ai_score" />
                            </div>
                            <p class="text-xs text-slate-500 mt-1.5 leading-snug">{{ stripHtml(card.job?.title) }}</p>
                            <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-400">
                                <span v-if="card.assigned_hr">👤 {{ card.assigned_hr.name }}</span>
                                <span v-else class="italic">Chưa gán HR</span>
                                <span>{{ formatDate(card.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-3 text-xs text-slate-400 leading-relaxed">
            * Ứng viên đã ở bước "Ký hợp đồng" trên 1 tháng được ẩn tự động khỏi Kanban để giảm nhiễu. Ứng viên ở cột "Loại CV" quá 7 ngày sẽ tự động bị xoá. Vẫn xem được đầy đủ trong
            <Link href="/admin/candidates" class="text-[#0D7C66] hover:underline font-medium">danh sách ứng viên</Link>.
        </p>

        <Teleport to="body">
            <div
                v-if="detail"
                class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
                @click.self="closeDetail"
            >
                <div class="w-full sm:max-w-3xl bg-white sm:rounded-2xl shadow-xl overflow-hidden max-h-[95vh] flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0">
                        <div>
                            <p class="text-xs text-slate-400">Chi tiết ứng viên</p>
                            <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">{{ detail.full_name }}</h3>
                        </div>
                        <button type="button" class="text-slate-400 hover:text-slate-700" @click="closeDetail">✕</button>
                    </div>

                    <div v-if="detailLoading || detail.loading" class="p-12 text-center text-slate-400">Đang tải…</div>

                    <div v-else class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                        <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Thông tin liên hệ</p>
                                <ul class="mt-2 text-sm text-[#1B2B4B] space-y-1.5">
                                    <li><span class="text-slate-400 w-16 inline-block">SĐT:</span> {{ detail.phone }}</li>
                                    <li><span class="text-slate-400 w-16 inline-block">Email:</span> {{ detail.email || '—' }}</li>
                                    <li>
                                        <span class="text-slate-400 w-16 inline-block">CV:</span>
                                        <a :href="detail.cv_link" target="_blank" rel="noopener" class="text-[#0D7C66] hover:underline">Mở Google Drive ↗</a>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Vị trí ứng tuyển</p>
                                <p class="mt-2 text-sm font-semibold text-[#1B2B4B]">{{ stripHtml(detail.job?.title) }}</p>
                                <p class="text-xs text-slate-500">{{ stripHtml(detail.job?.department) }} · {{ stripHtml(detail.job?.location) }}</p>
                                <p class="text-xs text-slate-500 mt-2">
                                    HR phụ trách:
                                    <span class="font-medium text-[#1B2B4B]">{{ detail.assigned_hr?.name || 'Chưa gán' }}</span>
                                </p>
                            </div>
                        </section>

                        <section>
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Đánh giá AI</p>
                                <ScoreBadge :score="detail.ai?.score ?? null" size="lg" />
                            </div>
                            <div v-if="detail.ai?.analyzed_at" class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-red-100 bg-red-50/50 p-3">
                                    <p class="text-xs font-semibold text-red-700 mb-1.5">Red flags</p>
                                    <ul v-if="detail.ai.flags?.length" class="text-xs text-red-700 space-y-1 list-disc pl-4">
                                        <li v-for="(f, i) in detail.ai.flags" :key="i">{{ f }}</li>
                                    </ul>
                                    <p v-else class="text-xs text-red-700/60 italic">Không có cảnh báo.</p>
                                </div>
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3">
                                    <p class="text-xs font-semibold text-emerald-700 mb-1.5">Câu hỏi gợi ý</p>
                                    <ul v-if="detail.ai.questions?.length" class="text-xs text-emerald-800 space-y-1 list-disc pl-4">
                                        <li v-for="(q, i) in detail.ai.questions" :key="i">{{ q }}</li>
                                    </ul>
                                    <p v-else class="text-xs text-emerald-700/60 italic">AI chưa gợi ý.</p>
                                </div>
                            </div>
                            <p v-else class="mt-2 text-xs text-slate-400 italic">Chưa được phân tích bởi AI. Quay lại Inbox để chạy phân tích.</p>
                        </section>

                        <section>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Câu trả lời sơ vấn</p>
                            <div v-if="detail.answers?.length" class="space-y-2">
                                <div v-for="(a, i) in detail.answers" :key="i" class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                                    <p class="text-xs font-medium text-slate-500">{{ a.label }}</p>
                                    <p class="text-sm text-[#1B2B4B] mt-0.5 whitespace-pre-line">{{ a.answer }}</p>
                                </div>
                            </div>
                            <p v-else class="text-xs text-slate-400 italic">Không có câu trả lời.</p>
                        </section>

                        <section>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Ghi chú phỏng vấn / hotline</p>
                            <div v-if="detail.notes?.length" class="space-y-2 mb-3">
                                <div v-for="n in detail.notes" :key="n.id" class="rounded-xl border border-slate-100 p-3">
                                    <div class="flex items-center justify-between text-xs text-slate-400">
                                        <span class="font-medium text-[#1B2B4B]">{{ n.user || 'HR' }}</span>
                                        <span>{{ formatDate(n.at) }}</span>
                                    </div>
                                    <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold mt-1.5', resultClass(n.result)]">
                                        {{ resultLabel(n.result) }}
                                    </span>
                                    <p class="text-sm text-[#1B2B4B] mt-2 whitespace-pre-line">{{ n.note }}</p>
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/40">
                                <p class="text-xs font-semibold text-[#1B2B4B] mb-2">Thêm ghi chú mới</p>
                                <textarea
                                    v-model="noteForm.note"
                                    rows="3"
                                    placeholder="Tóm tắt cuộc gọi hotline / phỏng vấn..."
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                ></textarea>
                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <div class="flex gap-2">
                                        <label v-for="opt in ['pending','pass','fail']" :key="opt" class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" v-model="noteForm.result" :value="opt" class="text-[#0D7C66] focus:ring-[#0D7C66]/30 border-slate-300" />
                                            <span :class="['text-xs px-2 py-0.5 rounded-full border', resultClass(opt)]">{{ resultLabel(opt) }}</span>
                                        </label>
                                    </div>
                                    <button
                                        type="button"
                                        class="rounded-lg bg-[#0D7C66] text-white text-xs font-semibold px-4 py-1.5 hover:bg-[#0c6553] transition-all disabled:opacity-50"
                                        :disabled="!noteForm.note.trim() || noteSubmitting"
                                        @click="submitNote"
                                    >
                                        {{ noteSubmitting ? 'Đang lưu…' : 'Lưu ghi chú' }}
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Chuyển bước</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="(name, stageNum) in stageLabels"
                                    :key="stageNum"
                                    type="button"
                                    :disabled="Number(stageNum) === detail.current_stage"
                                    :class="[
                                        'text-xs font-semibold rounded-lg px-3 py-1.5 border transition-all',
                                        Number(stageNum) === detail.current_stage
                                            ? 'border-[#0D7C66] bg-[#0D7C66] text-white cursor-default'
                                            : 'border-slate-200 text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66]',
                                    ]"
                                    @click="moveFromModal(Number(stageNum))"
                                >
                                    {{ stageNum }}. {{ name }}
                                </button>
                            </div>
                        </section>

                        <section v-if="detail.logs?.length">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Lịch sử pipeline</p>
                            <ul class="space-y-1.5 text-xs text-slate-600">
                                <li v-for="log in detail.logs" :key="log.id" class="flex items-center justify-between border-l-2 border-[#0D7C66] pl-3 py-1">
                                    <span>
                                        <span v-if="log.from_stage">{{ stageLabels[log.from_stage] }} → </span>
                                        <span class="font-semibold text-[#1B2B4B]">{{ stageLabels[log.to_stage] }}</span>
                                        <span v-if="log.mover" class="text-slate-400"> · {{ log.mover }}</span>
                                    </span>
                                    <span class="text-slate-400">{{ formatDate(log.at) }}</span>
                                </li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
