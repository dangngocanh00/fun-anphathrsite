<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const page = usePage()
const flashError = computed(() => page.props.flash?.error)

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Đăng nhập HR — AnPhat" />

    <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gradient-to-br from-[#f8fafc] via-white to-[#ecfdf7]">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#0D7C66] shadow-md mb-4">
                    <span class="text-white font-extrabold text-2xl tracking-tight">A</span>
                </div>
                <h1 class="text-2xl font-bold text-[#1B2B4B] tracking-tight">AnPhat Tuyển Dụng</h1>
                <p class="text-sm text-slate-500 mt-1">Hệ thống quản lý nhân sự nội bộ</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-bold text-[#1B2B4B] tracking-tight">Đăng nhập</h2>
                <p class="text-sm text-slate-500 mt-1 mb-6 leading-relaxed">
                    Vui lòng dùng tài khoản nội bộ được cấp.
                </p>

                <div
                    v-if="flashError"
                    class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700"
                >
                    {{ flashError }}
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#1B2B4B] mb-1.5">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            autofocus
                            placeholder="ban@anphat.test"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-[#0F172A] shadow-sm placeholder:text-slate-400 transition-all duration-200 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                            :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-[#1B2B4B] mb-1.5">
                            Mật khẩu
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-[#0F172A] shadow-sm placeholder:text-slate-400 transition-all duration-200 focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none"
                            :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 select-none cursor-pointer">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-[#0D7C66] focus:ring-[#0D7C66]/30"
                        />
                        <span class="text-sm text-slate-600">Ghi nhớ đăng nhập</span>
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-[#0D7C66] py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#0c6553] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0D7C66]/40 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span v-if="!form.processing">Đăng nhập</span>
                        <span v-else>Đang xử lý…</span>
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">
                © {{ new Date().getFullYear() }} AnPhat. Tài khoản do quản trị cấp phát.
            </p>
        </div>
    </div>
</template>
