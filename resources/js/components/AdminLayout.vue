<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
    title: { type: String, default: '' },
    breadcrumb: { type: String, default: '' },
})

const page = usePage()
const user = computed(() => page.props.auth?.user)
const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)

const roleLabels = { admin: 'Quản trị viên', hr_manager: 'Trưởng nhóm HR', hr: 'Nhân viên HR' }
const primaryRole = computed(() => user.value?.roles?.[0] ?? null)
const roleLabel = computed(() => primaryRole.value ? (roleLabels[primaryRole.value] ?? primaryRole.value) : '—')

const currentPath = computed(() => page.url.split('?')[0])
const isActive = (path) => currentPath.value === path || currentPath.value.startsWith(path + '/')

const isAdmin = computed(() => user.value?.roles?.includes('admin'))
const isManager = computed(() => isAdmin.value || user.value?.roles?.includes('hr_manager'))

const nav = computed(() => [
    { label: 'Tổng quan', href: '/admin/dashboard', show: true },
    { label: 'Hồ sơ mới', href: '/admin/inbox', show: true },
    { label: 'Pipeline', href: '/admin/pipeline', show: true },
    { label: 'Vị trí tuyển', href: '/admin/jobs', show: isAdmin.value },
    { label: 'Hiệu suất', href: '/admin/performance', show: isManager.value },
].filter((item) => item.show))

const logout = () => router.post('/admin/logout')
</script>

<template>
    <div class="min-h-screen flex bg-[#f8fafc]">
        <aside class="hidden md:flex md:w-64 flex-col bg-[#1B2B4B] text-white p-6 sticky top-0 h-screen">
            <Link href="/admin/dashboard" class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl bg-[#0D7C66] flex items-center justify-center font-bold text-lg">A</div>
                <div>
                    <p class="font-bold tracking-tight">AnPhat HR</p>
                    <p class="text-xs text-white/60">Tuyển dụng nội bộ</p>
                </div>
            </Link>
            <nav class="space-y-1 text-sm">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'block px-3 py-2 rounded-lg font-medium transition-colors duration-200',
                        isActive(item.href) ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white hover:bg-white/5',
                    ]"
                >
                    {{ item.label }}
                </Link>
            </nav>
            <div class="mt-auto pt-6 border-t border-white/10 text-xs text-white/50">v0.1 — đang phát triển</div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-6 sticky top-0 z-20">
                <div>
                    <p class="text-xs text-slate-400">{{ breadcrumb || 'Admin' }}</p>
                    <h1 class="text-base font-semibold text-[#1B2B4B] tracking-tight">{{ title }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-medium text-[#1B2B4B] leading-tight">{{ user?.name }}</p>
                        <p class="text-xs text-slate-500">{{ roleLabel }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-[#0D7C66] text-white flex items-center justify-center font-semibold text-sm">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <button
                        type="button"
                        class="text-xs font-medium text-slate-500 hover:text-[#0D7C66] transition-colors duration-200"
                        @click="logout"
                    >
                        Đăng xuất
                    </button>
                </div>
            </header>

            <div v-if="flashSuccess || flashError" class="px-6 pt-4">
                <div
                    v-if="flashSuccess"
                    class="rounded-xl bg-[#ecfdf7] border border-[#0D7C66]/20 px-4 py-3 text-sm text-[#0D7C66] font-medium"
                >
                    {{ flashSuccess }}
                </div>
                <div
                    v-if="flashError"
                    class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700 font-medium"
                >
                    {{ flashError }}
                </div>
            </div>

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
