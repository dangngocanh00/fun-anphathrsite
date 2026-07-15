<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import PublicLayout from '../../components/PublicLayout.vue'
import { stripHtml } from '../../utils/richtext.js'
import { isoToDmy } from '../../utils/date.js'

const props = defineProps({
    job: { type: Object, required: true },
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)

const initialAnswers = props.job.fields.reduce((acc, f) => {
    acc[f.id] = ''
    return acc
}, {})

const form = useForm({
    full_name: '',
    phone: '',
    email: '',
    cv_link: '',
    answers: reactive({ ...initialAnswers }),
})

const dateInputValues = reactive({})

const onDateChange = (fieldId, isoValue) => {
    dateInputValues[fieldId] = isoValue
    form.answers[fieldId] = isoToDmy(isoValue)
}

const localErrors = ref({})

const TEXTAREA_MAX = 2000

const validators = {
    full_name: (v) => !v.trim() ? 'Vui lòng nhập họ và tên.' : '',
    phone: (v) => {
        const trimmed = v.trim()
        if (!trimmed) return 'Vui lòng nhập số điện thoại.'
        if (!/^[\d\s\-+()]{8,16}$/.test(trimmed)) return 'Số điện thoại không hợp lệ.'
        return ''
    },
    email: (v) => {
        const trimmed = v.trim()
        if (!trimmed) return 'Vui lòng nhập email.'
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed) ? '' : 'Email không hợp lệ.'
    },
    cv_link: (v) => {
        const trimmed = v.trim()
        if (!trimmed) return 'Vui lòng dán link CV.'
        try {
            const u = new URL(trimmed)
            if (!/(^|\.)(drive|docs)\.google\.com$/i.test(u.host)) {
                return 'Link phải thuộc drive.google.com hoặc docs.google.com.'
            }
            return ''
        } catch {
            return 'Link CV không hợp lệ.'
        }
    },
}

const validateField = (key) => {
    const fn = validators[key]
    if (!fn) return
    localErrors.value[key] = fn(form[key] ?? '')
}

const errorFor = (key) => localErrors.value[key] || form.errors[key]

const fieldError = (id) => form.errors[`answers.${id}`]

const driveFileLabel = computed(() => {
    const url = form.cv_link.trim()
    if (!url) return ''
    try {
        const u = new URL(url)
        if (!/(^|\.)(drive|docs)\.google\.com$/i.test(u.host)) return ''
        const m = u.pathname.match(/\/(?:file|document|spreadsheets|presentation)\/d\/([\w-]{15,})/)
        if (m) return `📄 Drive ID: ${m[1].slice(0, 8)}…${m[1].slice(-4)}`
        const id = u.searchParams.get('id')
        if (id) return `📄 Drive ID: ${id.slice(0, 8)}…${id.slice(-4)}`
        return `📄 Drive: ${u.host}${u.pathname}`
    } catch {
        return ''
    }
})

const submit = () => {
    for (const key of Object.keys(validators)) validateField(key)
    if (Object.values(localErrors.value).some(Boolean)) return

    form.post(`/jobs/${props.job.slug}/apply`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('full_name', 'phone', 'email', 'cv_link')
            for (const key of Object.keys(form.answers)) form.answers[key] = ''
            for (const key of Object.keys(dateInputValues)) dateInputValues[key] = ''
            localErrors.value = {}
        },
    })
}
</script>

<template>
    <Head :title="`${stripHtml(job.title)} — AnPhat Tuyển dụng`" />

    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 sm:pt-8 pb-4">
            <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-[#0D7C66] transition-colors">
                ← Quay lại danh sách vị trí
            </Link>
        </div>

        <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-16">
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 sm:p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-[#0D7C66]/10 text-[#0D7C66] px-2.5 py-0.5 text-xs font-semibold" v-html="job.department"></span>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs font-medium">Đang tuyển</span>
                </div>
                <h1 class="mt-3 text-xl sm:text-2xl md:text-3xl font-extrabold text-[#1B2B4B] tracking-tight leading-tight" v-html="job.title"></h1>
                <p v-if="job.location" class="mt-2 text-sm text-slate-500 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    <span v-html="job.location"></span>
                </p>
            </div>

            <div class="mt-5 sm:mt-6 grid gap-5 sm:gap-6 lg:grid-cols-5">
                <div class="lg:col-span-3 space-y-5 sm:space-y-6">
                    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 sm:p-6 md:p-8">
                        <h2 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Mô tả công việc</h2>
                        <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line" v-html="job.description"></div>
                    </div>

                    <div v-if="job.requirements" class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 sm:p-6 md:p-8">
                        <h2 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Yêu cầu ứng viên</h2>
                        <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line" v-html="job.requirements"></div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="lg:sticky lg:top-20 rounded-2xl bg-white border border-slate-100 shadow-sm p-5 sm:p-6 md:p-7">
                        <h2 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Ứng tuyển ngay</h2>
                        <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                            Điền thông tin liên hệ và link CV để HR gọi lại trong vòng 48 giờ.
                        </p>

                        <div
                            v-if="flashSuccess"
                            class="mt-4 rounded-xl bg-[#ecfdf7] border border-[#0D7C66]/20 px-4 py-3 text-sm text-[#0D7C66] font-medium"
                        >
                            {{ flashSuccess }}
                        </div>

                        <form class="mt-5 space-y-4" @submit.prevent="submit">
                            <div>
                                <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Họ và tên <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.full_name"
                                    type="text"
                                    required
                                    placeholder="Nguyễn Văn A"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': errorFor('full_name') }"
                                    @blur="validateField('full_name')"
                                    @input="localErrors.full_name && validateField('full_name')"
                                />
                                <p v-if="errorFor('full_name')" class="mt-1 text-xs text-red-600">{{ errorFor('full_name') }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Số điện thoại <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    placeholder="0989 xxx xxx"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': errorFor('phone') }"
                                    @blur="validateField('phone')"
                                    @input="localErrors.phone && validateField('phone')"
                                />
                                <p v-if="errorFor('phone')" class="mt-1 text-xs text-red-600">{{ errorFor('phone') }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Email <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    placeholder="ban@example.com"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': errorFor('email') }"
                                    @blur="validateField('email')"
                                    @input="localErrors.email && validateField('email')"
                                />
                                <p v-if="errorFor('email')" class="mt-1 text-xs text-red-600">{{ errorFor('email') }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Link CV (Google Drive) <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.cv_link"
                                    type="url"
                                    required
                                    placeholder="https://drive.google.com/file/d/..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': errorFor('cv_link') }"
                                    @blur="validateField('cv_link')"
                                    @input="localErrors.cv_link && validateField('cv_link')"
                                />
                                <p v-if="errorFor('cv_link')" class="mt-1 text-xs text-red-600">{{ errorFor('cv_link') }}</p>
                                <p v-else-if="driveFileLabel" class="mt-1 text-[11px] text-[#0D7C66] font-medium font-mono">{{ driveFileLabel }}</p>
                                <p v-else class="mt-1 text-[11px] text-slate-400">
                                    Mở quyền chia sẻ "Anyone with the link" trên Google Drive trước khi gửi.
                                </p>
                            </div>

                            <div v-if="job.fields.length" class="pt-3 border-t border-slate-100 space-y-4">
                                <p class="text-xs font-semibold text-[#1B2B4B] uppercase tracking-wide">Câu hỏi sơ vấn</p>

                                <div v-for="field in job.fields" :key="field.id">
                                    <label class="flex items-center justify-between text-xs font-semibold text-[#1B2B4B] mb-1.5">
                                        <span>
                                            {{ field.label }}
                                            <span v-if="field.is_required" class="text-red-500">*</span>
                                        </span>
                                        <span
                                            v-if="field.type === 'textarea'"
                                            :class="[
                                                'text-[11px] font-normal',
                                                (form.answers[field.id]?.length ?? 0) > TEXTAREA_MAX ? 'text-red-500' : 'text-slate-400',
                                            ]"
                                        >
                                            {{ form.answers[field.id]?.length ?? 0 }} / {{ TEXTAREA_MAX }}
                                        </span>
                                    </label>

                                    <input
                                        v-if="field.type === 'text'"
                                        v-model="form.answers[field.id]"
                                        type="text"
                                        :required="field.is_required"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': fieldError(field.id) }"
                                    />

                                    <textarea
                                        v-else-if="field.type === 'textarea'"
                                        v-model="form.answers[field.id]"
                                        rows="3"
                                        :maxlength="TEXTAREA_MAX"
                                        :required="field.is_required"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': fieldError(field.id) }"
                                    />

                                    <input
                                        v-else-if="field.type === 'date'"
                                        type="date"
                                        :value="dateInputValues[field.id]"
                                        :required="field.is_required"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': fieldError(field.id) }"
                                        @change="onDateChange(field.id, $event.target.value)"
                                    />
                                    <p v-if="field.type === 'date' && form.answers[field.id]" class="mt-1 text-[11px] text-[#0D7C66] font-medium">
                                        Đã chọn: {{ form.answers[field.id] }}
                                    </p>

                                    <select
                                        v-else-if="field.type === 'select'"
                                        v-model="form.answers[field.id]"
                                        :required="field.is_required"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': fieldError(field.id) }"
                                    >
                                        <option value="" disabled>-- Chọn một mục --</option>
                                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>

                                    <div v-else-if="field.type === 'radio'" class="space-y-1.5 mt-1">
                                        <label
                                            v-for="opt in field.options"
                                            :key="opt"
                                            class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer"
                                        >
                                            <input
                                                v-model="form.answers[field.id]"
                                                type="radio"
                                                :value="opt"
                                                :required="field.is_required"
                                                class="text-[#0D7C66] focus:ring-[#0D7C66]/30 border-slate-300"
                                            />
                                            {{ opt }}
                                        </label>
                                    </div>

                                    <p v-if="fieldError(field.id)" class="mt-1 text-xs text-red-600">{{ fieldError(field.id) }}</p>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full rounded-xl bg-[#0D7C66] py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-[#0c6553] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0D7C66]/40 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span v-if="!form.processing">Gửi hồ sơ ứng tuyển</span>
                                <span v-else>Đang gửi…</span>
                            </button>

                            <p class="text-[11px] text-slate-400 text-center leading-relaxed">
                                Bằng việc nhấn gửi, bạn đồng ý cho AnPhat lưu trữ và xử lý thông tin ứng tuyển này.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
