<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '../../../components/AdminLayout.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'
import EmptyState from '../../../components/EmptyState.vue'

const props = defineProps({
    accounts: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    public_url: { type: String, default: '' },
})

const roleLabels = { admin: 'Quản trị viên', hr_manager: 'Trưởng nhóm HR', hr: 'Nhân viên HR' }

const formatDate = (iso) => {
    if (!iso) return '—'
    return new Date(iso).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const refLinkOf = (code) => code ? `${props.public_url}/ref/${code}` : ''
const isValidRef = (code) => /^[A-Za-z0-9]{4,20}$/.test(code ?? '')

const copiedFor = ref(null)
const copyRef = async (code) => {
    if (!code) return
    try {
        await navigator.clipboard.writeText(refLinkOf(code))
        copiedFor.value = code
        setTimeout(() => { if (copiedFor.value === code) copiedFor.value = null }, 1800)
    } catch {}
}

const createOpen = ref(false)
const createForm = useForm({ name: '', email: '', password: '', role: 'hr', ref_code: '' })
const createPreview = computed(() => isValidRef(createForm.ref_code) ? refLinkOf(createForm.ref_code) : '')
const submitCreate = () => {
    createForm.post('/admin/accounts', {
        preserveScroll: true,
        onSuccess: () => { createForm.reset(); createOpen.value = false },
    })
}

const roleEditing = ref(null)
const roleEditForm = useForm({ role: 'hr' })
const openRoleEdit = (acc) => {
    roleEditing.value = acc
    roleEditForm.role = acc.role
    roleEditForm.clearErrors()
}
const submitRole = () => {
    if (!roleEditing.value) return
    roleEditForm.put(`/admin/accounts/${roleEditing.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { roleEditing.value = null },
    })
}

const refEditing = ref(null)
const refEditForm = useForm({ ref_code: '' })
const refEditPreview = computed(() => isValidRef(refEditForm.ref_code) ? refLinkOf(refEditForm.ref_code) : '')
const openRefEdit = (acc) => {
    refEditing.value = acc
    refEditForm.ref_code = acc.ref_code ?? ''
    refEditForm.clearErrors()
}
const submitRef = () => {
    if (!refEditing.value) return
    refEditForm.put(`/admin/accounts/${refEditing.value.id}/ref-code`, {
        preserveScroll: true,
        onSuccess: () => { refEditing.value = null },
    })
}

const resetting = ref(null)
const resetForm = useForm({ password: '' })
const openReset = (acc) => {
    resetting.value = acc
    resetForm.reset()
    resetForm.clearErrors()
}
const submitReset = () => {
    if (!resetting.value) return
    resetForm.post(`/admin/accounts/${resetting.value.id}/reset-password`, {
        preserveScroll: true,
        onSuccess: () => { resetting.value = null },
    })
}

const togglingId = ref(null)
const toggle = (acc) => {
    togglingId.value = acc.id
    router.patch(`/admin/accounts/${acc.id}/toggle`, {}, {
        preserveScroll: true,
        onFinish: () => { togglingId.value = null },
    })
}

const pendingDelete = ref(null)
const deleting = ref(false)
const askDelete = (acc) => { pendingDelete.value = acc }
const confirmDelete = () => {
    if (!pendingDelete.value || deleting.value) return
    deleting.value = true
    router.delete(`/admin/accounts/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { pendingDelete.value = null },
        onFinish: () => { deleting.value = false },
    })
}
</script>

<template>
    <Head title="Tài khoản HR — AnPhat HR" />

    <AdminLayout title="Tài khoản HR" breadcrumb="Admin / Tài khoản HR">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-base font-bold text-[#1B2B4B] tracking-tight">
                        {{ accounts.length }} tài khoản
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Quản lý HR / Trưởng nhóm. Mỗi HR có ref link riêng để giới thiệu ứng viên — admin tự đặt mã ref.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#0D7C66] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0c6553] transition-all"
                    @click="createOpen = true"
                >
                    + Tạo tài khoản
                </button>
            </div>

            <EmptyState
                v-if="accounts.length === 0"
                icon="users"
                title="Chưa có tài khoản nào"
                description="Bấm “Tạo tài khoản” để thêm HR đầu tiên."
            />

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr class="text-left">
                            <th class="px-6 py-3 font-medium">Tài khoản</th>
                            <th class="px-6 py-3 font-medium">Vai trò</th>
                            <th class="px-6 py-3 font-medium">Ref link</th>
                            <th class="px-6 py-3 font-medium">Trạng thái</th>
                            <th class="px-6 py-3 font-medium">Tạo lúc</th>
                            <th class="px-6 py-3 font-medium text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="acc in accounts" :key="acc.id" class="hover:bg-slate-50/60">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[#1B2B4B]">
                                    {{ acc.name }}
                                    <span v-if="acc.is_self" class="ml-1 text-[10px] font-bold text-[#0D7C66] uppercase">(bạn)</span>
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ acc.email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-2.5 py-0.5 text-xs font-semibold">
                                    {{ roleLabels[acc.role] ?? acc.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="acc.ref_code" class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <code class="text-[11px] font-mono text-[#0D7C66] bg-[#ecfdf7] px-2 py-0.5 rounded">{{ acc.ref_code }}</code>
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-slate-500 hover:text-[#0D7C66] transition-colors"
                                            @click="copyRef(acc.ref_code)"
                                        >
                                            {{ copiedFor === acc.ref_code ? '✓ Đã copy' : 'Copy link' }}
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-mono truncate max-w-xs">
                                        {{ refLinkOf(acc.ref_code) }}
                                    </p>
                                </div>
                                <p v-else class="text-xs text-slate-400 italic">Chưa đặt mã ref</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border',
                                        acc.is_active
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                            : 'bg-slate-100 text-slate-500 border-slate-200',
                                    ]"
                                >
                                    {{ acc.is_active ? 'Hoạt động' : 'Vô hiệu' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ formatDate(acc.created_at) }}</td>
                            <td class="px-6 py-4">
                                <div v-if="acc.role === 'admin'" class="text-right text-xs text-slate-400 italic">
                                    Không thể quản lý admin
                                </div>
                                <div v-else class="flex justify-end items-center gap-1.5 flex-wrap">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                                        @click="openRoleEdit(acc)"
                                    >Đổi vai trò</button>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                                        @click="openRefEdit(acc)"
                                    >{{ acc.ref_code ? 'Đổi ref' : 'Đặt ref' }}</button>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-[#0D7C66] hover:text-[#0D7C66] transition-all"
                                        @click="openReset(acc)"
                                    >Reset MK</button>
                                    <button
                                        type="button"
                                        :disabled="acc.is_self || togglingId === acc.id"
                                        :class="[
                                            'rounded-lg border px-2.5 py-1 text-xs font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed',
                                            acc.is_active
                                                ? 'border-amber-200 text-amber-700 hover:bg-amber-50'
                                                : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50',
                                        ]"
                                        @click="toggle(acc)"
                                    >
                                        {{ acc.is_active ? 'Vô hiệu hoá' : 'Kích hoạt' }}
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="acc.is_self"
                                        class="rounded-lg border border-red-100 text-red-600 px-2.5 py-1 text-xs font-semibold hover:bg-red-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        @click="askDelete(acc)"
                                    >Xoá</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="createOpen" class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" @click.self="createForm.processing || (createOpen = false)">
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Tạo tài khoản mới</h3>
                        <button type="button" class="text-slate-400 hover:text-slate-700" @click="createOpen = false">✕</button>
                    </div>
                    <form class="px-6 py-5 space-y-4" @submit.prevent="submitCreate">
                        <div>
                            <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Họ tên <span class="text-red-500">*</span></label>
                            <input v-model="createForm.name" type="text" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none" :class="{ 'border-red-300': createForm.errors.name }" />
                            <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-600">{{ createForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input v-model="createForm.email" type="email" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none" :class="{ 'border-red-300': createForm.errors.email }" />
                            <p v-if="createForm.errors.email" class="mt-1 text-xs text-red-600">{{ createForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Mật khẩu khởi tạo <span class="text-red-500">*</span></label>
                            <input v-model="createForm.password" type="text" required minlength="8" placeholder="Tối thiểu 8 ký tự" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none" :class="{ 'border-red-300': createForm.errors.password }" />
                            <p v-if="createForm.errors.password" class="mt-1 text-xs text-red-600">{{ createForm.errors.password }}</p>
                            <p v-else class="mt-1 text-[11px] text-slate-400">Người dùng nên đổi sau khi đăng nhập lần đầu.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">Vai trò</label>
                            <select v-model="createForm.role" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none">
                                <option v-for="r in roles" :key="r" :value="r">{{ roleLabels[r] }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1B2B4B] mb-1.5">
                                Mã ref <span class="text-red-500">*</span>
                                <span class="text-[10px] font-normal text-slate-400 ml-1">(4–20 ký tự, chỉ chữ + số)</span>
                            </label>
                            <input
                                v-model="createForm.ref_code"
                                type="text"
                                required
                                minlength="4"
                                maxlength="20"
                                pattern="[A-Za-z0-9]{4,20}"
                                placeholder="Vd: nguyenvana"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-mono focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                                :class="{ 'border-red-300': createForm.errors.ref_code }"
                            />
                            <p v-if="createForm.errors.ref_code" class="mt-1 text-xs text-red-600">{{ createForm.errors.ref_code }}</p>
                            <p v-else-if="createPreview" class="mt-1 text-[11px] text-[#0D7C66] font-mono break-all">{{ createPreview }}</p>
                            <p v-else class="mt-1 text-[11px] text-slate-400">Ref link sẽ là <span class="font-mono">{{ public_url }}/ref/&lt;mã&gt;</span></p>
                        </div>
                    </form>
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900" :disabled="createForm.processing" @click="createOpen = false">Huỷ</button>
                        <button type="button" class="rounded-lg bg-[#0D7C66] text-white px-4 py-1.5 text-sm font-semibold hover:bg-[#0c6553] disabled:opacity-60" :disabled="createForm.processing" @click="submitCreate">
                            {{ createForm.processing ? 'Đang lưu…' : 'Tạo tài khoản' }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="roleEditing" class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" @click.self="roleEditForm.processing || (roleEditing = null)">
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Đổi vai trò: {{ roleEditing.name }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Vai trò hiện tại: <span class="font-semibold">{{ roleLabels[roleEditing.role] }}</span></p>
                    <select v-model="roleEditForm.role" class="mt-4 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none">
                        <option v-for="r in roles" :key="r" :value="r">{{ roleLabels[r] }}</option>
                    </select>
                    <p v-if="roleEditForm.errors.role" class="mt-1 text-xs text-red-600">{{ roleEditForm.errors.role }}</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900" :disabled="roleEditForm.processing" @click="roleEditing = null">Huỷ</button>
                        <button type="button" class="rounded-lg bg-[#0D7C66] text-white px-4 py-1.5 text-sm font-semibold hover:bg-[#0c6553] disabled:opacity-60" :disabled="roleEditForm.processing" @click="submitRole">
                            {{ roleEditForm.processing ? 'Đang lưu…' : 'Lưu vai trò' }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="refEditing" class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" @click.self="refEditForm.processing || (refEditing = null)">
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Mã ref của {{ refEditing.name }}</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Hiện tại:
                        <code v-if="refEditing.ref_code" class="font-mono text-[#0D7C66]">{{ refEditing.ref_code }}</code>
                        <span v-else class="italic">chưa đặt</span>
                    </p>
                    <label class="block text-xs font-semibold text-[#1B2B4B] mt-4 mb-1.5">
                        Mã ref mới
                        <span class="text-[10px] font-normal text-slate-400 ml-1">(4–20 ký tự, chỉ chữ + số)</span>
                    </label>
                    <input
                        v-model="refEditForm.ref_code"
                        type="text"
                        required
                        minlength="4"
                        maxlength="20"
                        pattern="[A-Za-z0-9]{4,20}"
                        placeholder="Vd: nguyenvana"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-mono focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                        :class="{ 'border-red-300': refEditForm.errors.ref_code }"
                    />
                    <p v-if="refEditForm.errors.ref_code" class="mt-1 text-xs text-red-600">{{ refEditForm.errors.ref_code }}</p>
                    <p v-else-if="refEditPreview" class="mt-1 text-[11px] text-[#0D7C66] font-mono break-all">{{ refEditPreview }}</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900" :disabled="refEditForm.processing" @click="refEditing = null">Huỷ</button>
                        <button type="button" class="rounded-lg bg-[#0D7C66] text-white px-4 py-1.5 text-sm font-semibold hover:bg-[#0c6553] disabled:opacity-60" :disabled="refEditForm.processing || !isValidRef(refEditForm.ref_code)" @click="submitRef">
                            {{ refEditForm.processing ? 'Đang lưu…' : 'Lưu mã ref' }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="resetting" class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" @click.self="resetForm.processing || (resetting = null)">
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">Đặt lại mật khẩu</h3>
                    <p class="text-sm text-slate-500 mt-1">Cho tài khoản <span class="font-semibold text-[#1B2B4B]">{{ resetting.email }}</span>.</p>
                    <input v-model="resetForm.password" type="text" minlength="8" placeholder="Mật khẩu mới (≥ 8 ký tự)" class="mt-4 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none" :class="{ 'border-red-300': resetForm.errors.password }" />
                    <p v-if="resetForm.errors.password" class="mt-1 text-xs text-red-600">{{ resetForm.errors.password }}</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900" :disabled="resetForm.processing" @click="resetting = null">Huỷ</button>
                        <button type="button" class="rounded-lg bg-[#0D7C66] text-white px-4 py-1.5 text-sm font-semibold hover:bg-[#0c6553] disabled:opacity-60" :disabled="resetForm.processing || !resetForm.password" @click="submitReset">
                            {{ resetForm.processing ? 'Đang lưu…' : 'Đặt lại' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <ConfirmDialog
            :open="pendingDelete !== null"
            title="Xoá tài khoản"
            :message="pendingDelete ? `Xoá tài khoản ${pendingDelete.email}? Hồ sơ ứng viên đã gán cho HR này vẫn được giữ lại nhưng sẽ không còn HR phụ trách.` : ''"
            confirm-label="Xoá tài khoản"
            :loading="deleting"
            @confirm="confirmDelete"
            @cancel="pendingDelete = null"
        />
    </AdminLayout>
</template>
