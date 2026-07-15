<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AdminLayout from '../../../components/AdminLayout.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'
import EmptyState from '../../../components/EmptyState.vue'
import { stripHtml } from '../../../utils/richtext.js'

const props = defineProps({
    job: { type: Object, required: true },
    fields: { type: Array, default: () => [] },
})

const localFields = ref([...props.fields])
watch(() => props.fields, (v) => { localFields.value = [...v] })

const typeLabels = {
    text: 'Text 1 dòng',
    textarea: 'Văn bản dài',
    select: 'Dropdown',
    radio: 'Radio (chọn 1)',
    date: 'Ngày tháng (dd/mm/yyyy)',
}

const draggingIdx = ref(null)
const dragOverIdx = ref(null)

const onDragStart = (idx) => { draggingIdx.value = idx }
const onDragOver = (e, idx) => { e.preventDefault(); dragOverIdx.value = idx }
const onDragLeave = (idx) => { if (dragOverIdx.value === idx) dragOverIdx.value = null }
const onDrop = (idx) => {
    if (draggingIdx.value === null || draggingIdx.value === idx) {
        draggingIdx.value = null
        dragOverIdx.value = null
        return
    }
    const moved = [...localFields.value]
    const [item] = moved.splice(draggingIdx.value, 1)
    moved.splice(idx, 0, item)
    localFields.value = moved
    draggingIdx.value = null
    dragOverIdx.value = null
    persistOrder()
}
const persistOrder = () => {
    router.post(`/admin/jobs/${props.job.id}/form/reorder`, {
        ids: localFields.value.map((f) => f.id),
    }, { preserveScroll: true, preserveState: true })
}

const editing = ref(null)
const blank = () => ({ id: null, label: '', type: 'text', options: [], is_required: false })

const openCreate = () => { editing.value = blank() }
const openEdit = (field) => {
    editing.value = {
        id: field.id,
        label: field.label,
        type: field.type,
        options: [...(field.options ?? [])],
        is_required: field.is_required,
    }
}
const closeModal = () => {
    if (submitting.value) return
    editing.value = null
    modalErrors.value = {}
}

const modalErrors = ref({})
const submitting = ref(false)

const isChoice = (t) => t === 'select' || t === 'radio'

const addOption = () => { editing.value.options.push('') }
const removeOption = (idx) => { editing.value.options.splice(idx, 1) }

const submitField = () => {
    if (submitting.value) return

    if (!editing.value.label.trim()) { modalErrors.value = { label: 'Vui lòng nhập nhãn câu hỏi.' }; return }
    if (isChoice(editing.value.type)) {
        const opts = editing.value.options.map((o) => o.trim()).filter(Boolean)
        if (opts.length < 2) { modalErrors.value = { options: 'Cần ít nhất 2 phương án.' }; return }
        editing.value.options = opts
    }

    const payload = {
        label: editing.value.label.trim(),
        type: editing.value.type,
        is_required: editing.value.is_required,
        options: isChoice(editing.value.type) ? editing.value.options : null,
    }

    const url = editing.value.id
        ? `/admin/jobs/${props.job.id}/form/fields/${editing.value.id}`
        : `/admin/jobs/${props.job.id}/form/fields`
    const method = editing.value.id ? 'put' : 'post'

    submitting.value = true
    modalErrors.value = {}

    router[method](url, payload, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null
            modalErrors.value = {}
        },
        onError: (errors) => { modalErrors.value = errors },
        onFinish: () => { submitting.value = false },
    })
}

const pendingDelete = ref(null)
const deleting = ref(false)

const askDeleteField = (field) => { pendingDelete.value = field }
const confirmDelete = () => {
    if (!pendingDelete.value || deleting.value) return
    deleting.value = true
    router.delete(`/admin/jobs/${props.job.id}/form/fields/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { pendingDelete.value = null },
        onFinish: () => { deleting.value = false },
    })
}
</script>

<template>
    <Head :title="`Form: ${stripHtml(job.title)} — AnPhat HR`" />

    <AdminLayout :title="`Form sơ vấn: ${stripHtml(job.title)}`" breadcrumb="Admin / Vị trí tuyển / Form">
        <div class="max-w-4xl">
            <div class="mb-4 flex items-center gap-3">
                <Link href="/admin/jobs" class="text-sm text-slate-500 hover:text-[#0D7C66]">← Danh sách vị trí</Link>
                <span class="text-slate-300">·</span>
                <Link :href="`/admin/jobs/${job.id}/edit`" class="text-sm text-slate-500 hover:text-[#0D7C66]">Sửa thông tin JD</Link>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1B2B4B] tracking-tight">Câu hỏi sơ vấn</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Kéo thả để đổi thứ tự. Ứng viên sẽ thấy form theo đúng trình tự bên dưới.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="openCreate"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0D7C66] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] transition-all"
                    >
                        + Thêm câu hỏi
                    </button>
                </div>

                <EmptyState
                    v-if="localFields.length === 0"
                    icon="inbox"
                    title="Chưa có câu hỏi sơ vấn"
                    description="Vị trí này sẽ chỉ hỏi thông tin cơ bản (tên, SĐT, email, CV). Bấm “Thêm câu hỏi” để cấu hình câu hỏi tuỳ biến."
                />

                <ul v-else class="divide-y divide-slate-100">
                    <li
                        v-for="(field, idx) in localFields"
                        :key="field.id"
                        :class="[
                            'px-6 py-4 flex items-center gap-4 transition-colors',
                            dragOverIdx === idx ? 'bg-[#ecfdf7]' : '',
                            draggingIdx === idx ? 'opacity-50' : '',
                        ]"
                        draggable="true"
                        @dragstart="onDragStart(idx)"
                        @dragover="onDragOver($event, idx)"
                        @dragleave="onDragLeave(idx)"
                        @drop="onDrop(idx)"
                    >
                        <span class="cursor-grab active:cursor-grabbing text-slate-300 select-none" title="Kéo để sắp xếp">⋮⋮</span>
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                            {{ idx + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-[#1B2B4B] truncate">{{ field.label }}</p>
                                <span v-if="field.is_required" class="inline-flex items-center rounded-full bg-red-50 text-red-700 px-2 py-0.5 text-[10px] font-bold">Bắt buộc</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ typeLabels[field.type] }}
                                <span v-if="field.options?.length" class="text-slate-400">· {{ field.options.length }} phương án: {{ field.options.join(' / ') }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                @click="openEdit(field)"
                                class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                            >Sửa</button>
                            <button
                                type="button"
                                @click="askDeleteField(field)"
                                class="rounded-lg border border-red-100 text-red-600 px-2.5 py-1 text-xs font-semibold hover:bg-red-50 transition-all"
                            >Xoá</button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="editing"
                class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="closeModal"
            >
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">
                            {{ editing.id ? 'Sửa câu hỏi' : 'Thêm câu hỏi' }}
                        </h3>
                        <button
                            type="button"
                            class="text-slate-400 hover:text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed"
                            :disabled="submitting"
                            @click="closeModal"
                        >✕</button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Nhãn câu hỏi <span class="text-red-500">*</span></label>
                            <input
                                v-model="editing.label"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                :class="{ 'border-red-300': modalErrors.label }"
                                placeholder="Vd: Số năm kinh nghiệm trong vai trò tương tự"
                            />
                            <p v-if="modalErrors.label" class="mt-1 text-xs text-red-600">{{ modalErrors.label }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Loại câu hỏi</label>
                            <select
                                v-model="editing.type"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                            >
                                <option v-for="(label, value) in typeLabels" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>

                        <div v-if="isChoice(editing.type)">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-[#1B2B4B]">Các phương án</label>
                                <button type="button" class="text-xs font-semibold text-[#0D7C66] hover:underline" @click="addOption">+ Thêm phương án</button>
                            </div>
                            <div v-if="editing.options.length === 0" class="rounded-xl border border-dashed border-slate-200 px-3 py-3 text-xs text-slate-400 text-center">
                                Chưa có phương án. Bấm "Thêm phương án" để bắt đầu.
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="(opt, idx) in editing.options" :key="idx" class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400 w-5">{{ idx + 1 }}.</span>
                                    <input
                                        v-model="editing.options[idx]"
                                        type="text"
                                        class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                        placeholder="Nhập phương án"
                                    />
                                    <button type="button" class="text-xs text-red-500 hover:text-red-700 font-semibold" @click="removeOption(idx)">Xoá</button>
                                </div>
                            </div>
                            <p v-if="modalErrors.options" class="mt-1.5 text-xs text-red-600">{{ modalErrors.options }}</p>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="editing.is_required" type="checkbox" class="rounded text-[#0D7C66] focus:ring-[#0D7C66]/30 border-slate-300" />
                            <span class="text-sm text-[#1B2B4B] font-medium">Bắt buộc trả lời</span>
                        </label>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="submitting"
                            @click="closeModal"
                        >Huỷ</button>
                        <button
                            type="button"
                            class="rounded-lg bg-[#0D7C66] text-white px-4 py-1.5 text-sm font-semibold hover:bg-[#0c6553] disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2"
                            :disabled="submitting"
                            @click="submitField"
                        >
                            <svg v-if="submitting" class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25" />
                                <path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            <span v-if="submitting">Đang lưu…</span>
                            <span v-else>{{ editing.id ? 'Lưu thay đổi' : 'Thêm câu hỏi' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <ConfirmDialog
            :open="pendingDelete !== null"
            title="Xoá câu hỏi"
            :message="pendingDelete ? `Xoá câu hỏi “${pendingDelete.label}”? Câu trả lời cũ của ứng viên cho câu này cũng sẽ bị xoá.` : ''"
            confirm-label="Xoá câu hỏi"
            :loading="deleting"
            @confirm="confirmDelete"
            @cancel="pendingDelete = null"
        />
    </AdminLayout>
</template>
