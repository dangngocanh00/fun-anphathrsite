<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'
import PublicLayout from '../../components/PublicLayout.vue'

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

const submit = () => {
    form.post(`/jobs/${props.job.slug}/apply`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('full_name', 'phone', 'email', 'cv_link')
            for (const key of Object.keys(form.answers)) form.answers[key] = ''
        },
    })
}

const fieldError = (id) => form.errors[`answers.${id}`]
</script>

<template>
    <Head :title="`${job.title} — AnPhat Tuyển dụng`" />

    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-8 pb-4">
            <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-[#0D7C66] transition-colors">
                ← Quay lại danh sách vị trí
            </Link>
        </div>

        <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-16">
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-[#0D7C66]/10 text-[#0D7C66] px-2.5 py-0.5 text-xs font-semibold">
                        {{ job.department }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs font-medium">Đang tuyển</span>
                </div>
                <h1 class="mt-3 text-2xl md:text-3xl font-extrabold text-[#1B2B4B] tracking-tight leading-tight">
                    {{ job.title }}
                </h1>
                <p v-if="job.location" class="mt-2 text-sm text-slate-500 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    {{ job.location }}
                </p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-5">
                <div class="lg:col-span-3 space-y-6">
                    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                        <h2 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Mô tả công việc</h2>
                        <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ job.description }}</div>
                    </div>

                    <div v-if="job.requirements" class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8">
                        <h2 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Yêu cầu ứng viên</h2>
                        <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ job.requirements }}</div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="lg:sticky lg:top-20 rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-7">
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
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.full_name }"
                                />
                                <p v-if="form.errors.full_name" class="mt-1 text-xs text-red-600">{{ form.errors.full_name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Số điện thoại <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    placeholder="0989 xxx xxx"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.phone }"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="ban@example.com"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Link CV (Google Drive) <span class="text-red-500">*</span></label>
                                <input
                                    v-model="form.cv_link"
                                    type="url"
                                    required
                                    placeholder="https://drive.google.com/file/d/..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                    :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.cv_link }"
                                />
                                <p v-if="form.errors.cv_link" class="mt-1 text-xs text-red-600">{{ form.errors.cv_link }}</p>
                                <p v-else class="mt-1 text-[11px] text-slate-400">
                                    Mở quyền chia sẻ "Anyone with the link" trên Google Drive trước khi gửi.
                                </p>
                            </div>

                            <div v-if="job.fields.length" class="pt-3 border-t border-slate-100 space-y-4">
                                <p class="text-xs font-semibold text-[#1B2B4B] uppercase tracking-wide">Câu hỏi sơ vấn</p>

                                <div v-for="field in job.fields" :key="field.id">
                                    <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">
                                        {{ field.label }}
                                        <span v-if="field.is_required" class="text-red-500">*</span>
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
                                        :required="field.is_required"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm placeholder:text-slate-400 transition-all focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': fieldError(field.id) }"
                                    />

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
