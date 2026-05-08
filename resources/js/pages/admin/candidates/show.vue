<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '../../../components/AdminLayout.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'
import ScoreBadge from '../../../components/ScoreBadge.vue'

const props = defineProps({
    candidate: { type: Object, required: true },
    stages: { type: Object, required: true },
    can: { type: Object, default: () => ({ delete: false }) },
})

const noteForm = ref({ note: '', result: 'pending' })
const noteSubmitting = ref(false)
const moveSubmitting = ref(false)

const confirmDelete = ref(false)
const deleting = ref(false)
const submitDelete = () => {
    if (deleting.value) return
    deleting.value = true
    router.delete(`/admin/candidates/${props.candidate.id}`, {
        onFinish: () => { deleting.value = false; confirmDelete.value = false },
    })
}

const submitNote = () => {
    if (noteSubmitting.value || !noteForm.value.note.trim()) return
    noteSubmitting.value = true
    router.post(`/admin/pipeline/${props.candidate.id}/note`, noteForm.value, {
        preserveScroll: true,
        onSuccess: () => { noteForm.value = { note: '', result: 'pending' } },
        onFinish: () => { noteSubmitting.value = false },
    })
}

const moveStage = (toStage) => {
    if (moveSubmitting.value || toStage === props.candidate.current_stage) return
    moveSubmitting.value = true
    router.post(`/admin/pipeline/${props.candidate.id}/move`, { to_stage: toStage }, {
        preserveScroll: true,
        onFinish: () => { moveSubmitting.value = false },
    })
}

const formatDate = (iso) => {
    if (!iso) return '—'
    return new Date(iso).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatVnd = (n) => new Intl.NumberFormat('vi-VN').format(Number(n) || 0) + '₫'

const resultLabel = (r) => ({ pass: 'Đạt', fail: 'Loại', pending: 'Cân nhắc' }[r] ?? r)
const resultClass = (r) => ({
    pass: 'bg-emerald-50 text-emerald-700 border-emerald-100',
    fail: 'bg-red-50 text-red-700 border-red-100',
    pending: 'bg-amber-50 text-amber-700 border-amber-100',
}[r] ?? 'bg-slate-50 text-slate-600')

const currentStageLabel = computed(() => props.stages[props.candidate.current_stage] ?? '—')
const aiAnalyzed = computed(() => !!props.candidate.ai?.analyzed_at)
</script>

<template>
    <Head :title="`${candidate.full_name} — AnPhat HR`" />

    <AdminLayout :title="candidate.full_name" :breadcrumb="`Admin / Ứng viên / ${candidate.full_name}`">
        <div class="max-w-5xl space-y-5">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <Link href="/admin/candidates" class="text-sm text-slate-500 hover:text-[#0D7C66]">← Danh sách ứng viên</Link>
                <button
                    v-if="can.delete"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-100 text-red-600 px-3 py-1.5 text-xs font-semibold hover:bg-red-50 transition-all"
                    @click="confirmDelete = true"
                >
                    Xoá ứng viên
                </button>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-[#0D7C66]/10 text-[#0D7C66] px-2.5 py-0.5 text-xs font-semibold">
                                Bước {{ candidate.current_stage }} · {{ currentStageLabel }}
                            </span>
                            <ScoreBadge :score="candidate.ai?.score ?? null" />
                        </div>
                        <h2 class="text-2xl font-bold text-[#1B2B4B] tracking-tight mt-2">{{ candidate.full_name }}</h2>
                        <p class="text-sm text-slate-500 mt-1">
                            Ứng tuyển vị trí
                            <span class="font-semibold text-[#1B2B4B]">{{ candidate.job?.title }}</span>
                            <span v-if="candidate.job?.department" class="text-slate-400"> · {{ candidate.job.department }}</span>
                        </p>
                    </div>
                    <a
                        :href="candidate.cv_link"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0D7C66] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] transition-all"
                    >
                        Mở CV trên Google Drive ↗
                    </a>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-xl border border-slate-100 p-4">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Liên hệ</p>
                        <p class="text-sm text-[#1B2B4B] mt-1.5"><span class="text-slate-400">SĐT:</span> {{ candidate.phone }}</p>
                        <p class="text-sm text-[#1B2B4B] mt-0.5"><span class="text-slate-400">Email:</span> {{ candidate.email || '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-4">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">HR phụ trách</p>
                        <p class="text-sm text-[#1B2B4B] mt-1.5">{{ candidate.assigned_hr?.name || 'Chưa gán' }}</p>
                        <p v-if="candidate.assigned_hr?.email" class="text-xs text-slate-500 mt-0.5">{{ candidate.assigned_hr.email }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-4">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Hoa hồng vị trí</p>
                        <p class="text-sm font-semibold text-[#0D7C66] mt-1.5">{{ formatVnd(candidate.job?.commission_amount) }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Nộp lúc: {{ formatDate(candidate.created_at) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-[#1B2B4B] tracking-tight">Đánh giá AI</h3>
                    <ScoreBadge :score="candidate.ai?.score ?? null" size="lg" />
                </div>
                <div v-if="aiAnalyzed" class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-red-100 bg-red-50/50 p-4">
                        <p class="text-xs font-semibold text-red-700 mb-1.5">Red flags</p>
                        <ul v-if="candidate.ai.flags?.length" class="text-xs text-red-700 space-y-1 list-disc pl-4">
                            <li v-for="(f, i) in candidate.ai.flags" :key="i">{{ f }}</li>
                        </ul>
                        <p v-else class="text-xs text-red-700/60 italic">Không có cảnh báo.</p>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                        <p class="text-xs font-semibold text-emerald-700 mb-1.5">Câu hỏi gợi ý</p>
                        <ul v-if="candidate.ai.questions?.length" class="text-xs text-emerald-800 space-y-1 list-disc pl-4">
                            <li v-for="(q, i) in candidate.ai.questions" :key="i">{{ q }}</li>
                        </ul>
                        <p v-else class="text-xs text-emerald-700/60 italic">AI chưa gợi ý.</p>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-400 italic">
                    Chưa được phân tích bởi AI. Quay lại
                    <Link href="/admin/inbox" class="text-[#0D7C66] hover:underline font-medium">Hồ sơ mới</Link>
                    để chạy phân tích.
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <h3 class="text-base font-bold text-[#1B2B4B] tracking-tight mb-3">Câu trả lời sơ vấn</h3>
                <div v-if="candidate.answers.length" class="space-y-2">
                    <div v-for="(a, i) in candidate.answers" :key="i" class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <p class="text-xs font-medium text-slate-500">{{ a.label }}</p>
                        <p class="text-sm text-[#1B2B4B] mt-0.5 whitespace-pre-line">{{ a.answer }}</p>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-400 italic">Ứng viên không trả lời câu hỏi nào.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <h3 class="text-base font-bold text-[#1B2B4B] tracking-tight mb-3">Chuyển bước</h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="(name, num) in stages"
                        :key="num"
                        type="button"
                        :disabled="Number(num) === candidate.current_stage || moveSubmitting"
                        :class="[
                            'text-xs font-semibold rounded-lg px-3 py-1.5 border transition-all',
                            Number(num) === candidate.current_stage
                                ? 'border-[#0D7C66] bg-[#0D7C66] text-white cursor-default'
                                : 'border-slate-200 text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66] disabled:opacity-50 disabled:cursor-not-allowed',
                        ]"
                        @click="moveStage(Number(num))"
                    >
                        {{ num }}. {{ name }}
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-3">
                    Mỗi lần chuyển bước sẽ ghi log với người thực hiện và thời gian.
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <h3 class="text-base font-bold text-[#1B2B4B] tracking-tight mb-3">Ghi chú phỏng vấn / hotline</h3>

                <div v-if="candidate.notes.length" class="space-y-2 mb-5">
                    <div v-for="n in candidate.notes" :key="n.id" class="rounded-xl border border-slate-100 p-3">
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

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/40">
                    <p class="text-xs font-semibold text-[#1B2B4B] mb-2">Thêm ghi chú mới</p>
                    <textarea
                        v-model="noteForm.note"
                        rows="3"
                        placeholder="Tóm tắt cuộc gọi hotline / phỏng vấn..."
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                    ></textarea>
                    <div class="mt-3 flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex gap-2">
                            <label v-for="opt in ['pending', 'pass', 'fail']" :key="opt" class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" v-model="noteForm.result" :value="opt" class="text-[#0D7C66] focus:ring-[#0D7C66]/30 border-slate-300" />
                                <span :class="['text-xs px-2 py-0.5 rounded-full border', resultClass(opt)]">{{ resultLabel(opt) }}</span>
                            </label>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg bg-[#0D7C66] text-white text-xs font-semibold px-4 py-1.5 hover:bg-[#0c6553] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!noteForm.note.trim() || noteSubmitting"
                            @click="submitNote"
                        >
                            {{ noteSubmitting ? 'Đang lưu…' : 'Lưu ghi chú' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <h3 class="text-base font-bold text-[#1B2B4B] tracking-tight mb-3">Lịch sử pipeline</h3>
                <ol v-if="candidate.logs.length" class="relative border-l-2 border-[#0D7C66]/20 ml-2 space-y-4">
                    <li v-for="log in candidate.logs" :key="log.id" class="pl-5 relative">
                        <span class="absolute -left-[7px] top-1.5 w-3 h-3 rounded-full bg-[#0D7C66] border-2 border-white shadow"></span>
                        <p class="text-sm text-[#1B2B4B]">
                            <span v-if="log.from_stage" class="text-slate-400">{{ stages[log.from_stage] }} →</span>
                            <span class="font-semibold">{{ stages[log.to_stage] }}</span>
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ formatDate(log.at) }}<span v-if="log.mover"> · bởi {{ log.mover }}</span>
                        </p>
                        <p v-if="log.note" class="text-sm text-slate-600 mt-1 italic">"{{ log.note }}"</p>
                    </li>
                </ol>
                <p v-else class="text-sm text-slate-400 italic">Chưa có chuyển bước nào.</p>
            </div>
        </div>

        <ConfirmDialog
            :open="confirmDelete"
            title="Xoá ứng viên"
            :message="`Xoá hồ sơ “${candidate.full_name}”? Toàn bộ câu trả lời, lịch sử pipeline và ghi chú phỏng vấn sẽ bị ẩn (soft delete). Sau khi xoá bạn sẽ được đưa về danh sách ứng viên.`"
            confirm-label="Xoá hồ sơ"
            :loading="deleting"
            @confirm="submitDelete"
            @cancel="confirmDelete = false"
        />
    </AdminLayout>
</template>
