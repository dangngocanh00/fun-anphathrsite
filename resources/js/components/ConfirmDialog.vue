<script setup>
const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Xác nhận' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Xác nhận' },
    cancelLabel: { type: String, default: 'Huỷ' },
    tone: { type: String, default: 'danger' },
    loading: { type: Boolean, default: false },
})

const emit = defineEmits(['confirm', 'cancel'])

const confirmClass = () => ({
    danger: 'bg-red-600 hover:bg-red-700',
    primary: 'bg-[#0D7C66] hover:bg-[#0c6553]',
}[props.tone] ?? 'bg-[#0D7C66] hover:bg-[#0c6553]')
</script>

<template>
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
                v-if="open"
                class="fixed inset-0 z-[80] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="loading || emit('cancel')"
            >
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 animate-[pop_180ms_ease-out]">
                    <h3 class="text-lg font-bold text-[#1B2B4B] tracking-tight">{{ title }}</h3>
                    <p v-if="message" class="text-sm text-slate-600 mt-2 leading-relaxed">
                        <slot>{{ message }}</slot>
                    </p>
                    <div v-else class="mt-2 text-sm text-slate-600 leading-relaxed">
                        <slot />
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900 disabled:opacity-50"
                            :disabled="loading"
                            @click="emit('cancel')"
                        >{{ cancelLabel }}</button>
                        <button
                            type="button"
                            :class="['rounded-lg text-white px-4 py-1.5 text-sm font-semibold disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2', confirmClass()]"
                            :disabled="loading"
                            @click="emit('confirm')"
                        >
                            <svg v-if="loading" class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
                                <path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            {{ loading ? 'Đang xử lý…' : confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
@keyframes pop {
    from { transform: scale(0.96); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
