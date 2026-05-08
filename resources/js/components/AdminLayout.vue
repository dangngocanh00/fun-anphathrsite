<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import NavProgress from './NavProgress.vue'
import Toast from './Toast.vue'

defineProps({
    title: { type: String, default: '' },
    breadcrumb: { type: String, default: '' },
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

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
    { label: 'Ứng viên', href: '/admin/candidates', show: true },
    { label: 'Vị trí tuyển', href: '/admin/jobs', show: isAdmin.value },
    { label: 'Hiệu suất', href: '/admin/performance', show: isManager.value },
].filter((item) => item.show))

const drawerOpen = ref(false)
watch(currentPath, () => { drawerOpen.value = false })

const logout = () => router.post('/admin/logout')
</script>

<template>
    <NavProgress />
    <Toast />

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

        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-150"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="drawerOpen"
                    class="md:hidden fixed inset-0 z-[55] bg-slate-900/60 backdrop-blur-sm"
                    @click="drawerOpen = false"
                ></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-200 ease-out"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-150 ease-in"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <aside
                    v-if="drawerOpen"
                    class="md:hidden fixed left-0 top-0 bottom-0 w-72 z-[56] bg-[#1B2B4B] text-white p-6 flex flex-col"
                >
                    <div class="flex items-center justify-between mb-8">
                        <Link href="/admin/dashboard" class="flex items-center gap-3" @click="drawerOpen = false">
                            <div class="w-10 h-10 rounded-xl bg-[#0D7C66] flex items-center justify-center font-bold text-lg">A</div>
                            <div>
                                <p class="font-bold tracking-tight">AnPhat HR</p>
                                <p class="text-xs text-white/60">Tuyển dụng nội bộ</p>
                            </div>
                        </Link>
                        <button
                            type="button"
                            class="text-white/60 hover:text-white p-1"
                            @click="drawerOpen = false"
                            aria-label="Đóng menu"
                        >✕</button>
                    </div>
                    <nav class="space-y-1 text-sm">
                        <Link
                            v-for="item in nav"
                            :key="item.href"
                            :href="item.href"
                            :class="[
                                'block px-3 py-2.5 rounded-lg font-medium transition-colors duration-200',
                                isActive(item.href) ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white hover:bg-white/5',
                            ]"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>
                    <div class="mt-auto pt-6 border-t border-white/10">
                        <p class="text-xs text-white/50 mb-3">{{ user?.name }} · {{ roleLabel }}</p>
                        <button
                            type="button"
                            class="text-xs font-medium text-white/60 hover:text-white"
                            @click="logout"
                        >
                            Đăng xuất
                        </button>
                    </div>
                </aside>
            </Transition>
        </Teleport>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-14 sm:h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        class="md:hidden p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 flex-shrink-0"
                        @click="drawerOpen = true"
                        aria-label="Mở menu"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-slate-400 truncate">{{ breadcrumb || 'Admin' }}</p>
                        <h1 class="text-sm sm:text-base font-semibold text-[#1B2B4B] tracking-tight truncate">{{ title }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-[#1B2B4B] leading-tight">{{ user?.name }}</p>
                        <p class="text-xs text-slate-500">{{ roleLabel }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-[#0D7C66] text-white flex items-center justify-center font-semibold text-sm">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <button
                        type="button"
                        class="hidden sm:inline-flex text-xs font-medium text-slate-500 hover:text-[#0D7C66] transition-colors duration-200"
                        @click="logout"
                    >
                        Đăng xuất
                    </button>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 page-fade">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.page-fade {
    animation: fadeIn 200ms ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
