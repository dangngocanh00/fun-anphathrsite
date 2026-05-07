<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const roleLabels = {
    admin: 'Quản trị viên',
    hr_manager: 'Trưởng nhóm HR',
    hr: 'Nhân viên HR',
}

const primaryRole = computed(() => user.value?.roles?.[0] ?? null)
const roleLabel = computed(() => (primaryRole.value ? roleLabels[primaryRole.value] ?? primaryRole.value : '—'))

const logout = () => {
    router.post('/admin/logout')
}
</script>

<template>
    <Head title="Dashboard — AnPhat HR" />

    <div class="min-h-screen flex bg-[#f8fafc]">
        <aside class="hidden md:flex md:w-64 flex-col bg-[#1B2B4B] text-white p-6">
            <div class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl bg-[#0D7C66] flex items-center justify-center font-bold text-lg">A</div>
                <div>
                    <p class="font-bold tracking-tight">AnPhat HR</p>
                    <p class="text-xs text-white/60">Tuyển dụng nội bộ</p>
                </div>
            </div>
            <nav class="space-y-1 text-sm">
                <a class="block px-3 py-2 rounded-lg bg-white/10 text-white font-medium" href="/admin/dashboard">Tổng quan</a>
                <span class="block px-3 py-2 rounded-lg text-white/50 cursor-not-allowed">Hồ sơ mới</span>
                <span class="block px-3 py-2 rounded-lg text-white/50 cursor-not-allowed">Pipeline</span>
                <span class="block px-3 py-2 rounded-lg text-white/50 cursor-not-allowed">Ứng viên</span>
                <span class="block px-3 py-2 rounded-lg text-white/50 cursor-not-allowed">Vị trí tuyển</span>
                <span class="block px-3 py-2 rounded-lg text-white/50 cursor-not-allowed">Hiệu suất</span>
            </nav>
            <div class="mt-auto pt-6 border-t border-white/10 text-xs text-white/50">
                v0.1 — đang phát triển
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-6">
                <div>
                    <p class="text-xs text-slate-400">Trang chủ / Tổng quan</p>
                    <h1 class="text-base font-semibold text-[#1B2B4B] tracking-tight">Dashboard</h1>
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

            <main class="flex-1 p-6">
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
                            Các module đang được xây dựng: Inbox, Pipeline Kanban, AI sàng lọc CV…
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
