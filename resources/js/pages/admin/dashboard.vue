<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const roleLabels = { admin: 'Quản trị viên', hr_manager: 'Trưởng nhóm HR', hr: 'Nhân viên HR' }
const primaryRole = computed(() => user.value?.roles?.[0] ?? null)
const roleLabel = computed(() => primaryRole.value ? (roleLabels[primaryRole.value] ?? primaryRole.value) : '—')
</script>

<template>
    <Head title="Dashboard — AnPhat HR" />

    <AdminLayout title="Dashboard" breadcrumb="Trang chủ / Tổng quan">
        <div class="max-w-5xl">
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-8">
                <h2 class="text-2xl font-bold text-[#1B2B4B] tracking-tight">
                    Xin chào, {{ user?.name }} 👋
                </h2>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Bạn đang đăng nhập với vai trò
                    <span class="inline-flex items-center px-2.5 py-0.5 ml-1 rounded-full text-xs font-medium bg-[#ecfdf7] text-[#0D7C66]">
                        {{ roleLabel }}
                    </span>
                </p>

                <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-100 p-5">
                        <p class="text-xs text-slate-500">Hồ sơ mới hôm nay</p>
                        <p class="text-2xl font-bold text-[#1B2B4B] mt-1">—</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-5">
                        <p class="text-xs text-slate-500">Đang trong pipeline</p>
                        <p class="text-2xl font-bold text-[#1B2B4B] mt-1">—</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-5">
                        <p class="text-xs text-slate-500">Đã ký HĐ tháng này</p>
                        <p class="text-2xl font-bold text-[#1B2B4B] mt-1">—</p>
                    </div>
                </div>

                <p class="mt-8 text-xs text-slate-400">
                    Truy cập <a href="/admin/inbox" class="text-[#0D7C66] hover:underline">Hồ sơ mới</a>
                    hoặc <a href="/admin/pipeline" class="text-[#0D7C66] hover:underline">Pipeline</a> để bắt đầu.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>
