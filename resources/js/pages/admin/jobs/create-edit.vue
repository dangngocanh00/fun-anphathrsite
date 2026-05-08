<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '../../../components/AdminLayout.vue'
import { slugifyVi } from '../../../utils/slugify.js'

const props = defineProps({
    job: { type: Object, default: null },
})

const isEdit = computed(() => !!props.job)
const pageTitle = computed(() => isEdit.value ? `Sửa: ${props.job.title}` : 'Tạo vị trí mới')

const form = useForm({
    title: props.job?.title ?? '',
    department: props.job?.department ?? '',
    location: props.job?.location ?? '',
    description: props.job?.description ?? '',
    requirements: props.job?.requirements ?? '',
    commission_amount: props.job?.commission_amount ?? 0,
})

const previewSlug = computed(() => slugifyVi(form.title) || '(trống)')

const submit = () => {
    if (isEdit.value) {
        form.put(`/admin/jobs/${props.job.id}`)
    } else {
        form.post('/admin/jobs')
    }
}

const formatVnd = (n) => new Intl.NumberFormat('vi-VN').format(Number(n) || 0)
</script>

<template>
    <Head :title="`${pageTitle} — AnPhat HR`" />

    <AdminLayout :title="pageTitle" :breadcrumb="`Admin / Vị trí tuyển / ${isEdit ? 'Sửa' : 'Tạo mới'}`">
        <div class="max-w-4xl">
            <div class="mb-4">
                <Link href="/admin/jobs" class="text-sm text-slate-500 hover:text-[#0D7C66]">← Quay lại danh sách</Link>
            </div>

            <form class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 md:p-8 space-y-6" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-semibold text-[#1B2B4B] mb-1.5">
                        Tên vị trí <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        required
                        placeholder="Vd: Trưởng phòng Kinh doanh khu vực miền Bắc"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.title }"
                    />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                    <p class="mt-1.5 text-[11px] text-slate-400">
                        Slug công khai: <span class="font-mono text-[#0D7C66]">/jobs/{{ previewSlug }}</span>
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-[#1B2B4B] mb-1.5">Khối / Phòng ban</label>
                        <input
                            v-model="form.department"
                            type="text"
                            placeholder="Vd: Khối Kinh doanh"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#1B2B4B] mb-1.5">Địa điểm</label>
                        <input
                            v-model="form.location"
                            type="text"
                            placeholder="Vd: Hà Nội / Remote"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#1B2B4B] mb-1.5">
                        Mô tả công việc <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="6"
                        required
                        placeholder="Mô tả chi tiết về công việc, môi trường, quyền lợi..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                    <p class="mt-1.5 text-[11px] text-slate-400">Hỗ trợ xuống dòng. Sẽ hiển thị nguyên trên trang công khai.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#1B2B4B] mb-1.5">Yêu cầu ứng viên</label>
                    <textarea
                        v-model="form.requirements"
                        rows="5"
                        placeholder="- Tốt nghiệp..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                    />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-[#1B2B4B] mb-1.5">
                            Đơn giá hoa hồng (VND) <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="form.commission_amount"
                            type="number"
                            min="0"
                            step="100000"
                            required
                            placeholder="2000000"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                            :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.commission_amount }"
                        />
                        <p v-if="form.errors.commission_amount" class="mt-1 text-xs text-red-600">{{ form.errors.commission_amount }}</p>
                        <p class="mt-1.5 text-[11px] text-slate-400">
                            ≈ <span class="font-semibold text-[#0D7C66]">{{ formatVnd(form.commission_amount) }}₫</span> mỗi ứng viên tuyển được.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <Link href="/admin/jobs" class="text-sm text-slate-500 hover:text-slate-700">Huỷ</Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-[#0D7C66] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] disabled:opacity-60 disabled:cursor-not-allowed transition-all"
                    >
                        <span v-if="form.processing">Đang lưu…</span>
                        <span v-else-if="isEdit">Lưu thay đổi</span>
                        <span v-else>Tạo vị trí + cấu hình form</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
