<script setup>
import { usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const page = usePage()

const toasts = ref([])
let nextId = 1

const TIMEOUT = 3000

const push = (type, message) => {
    if (!message) return
    const id = nextId++
    toasts.value.push({ id, type, message })
    setTimeout(() => dismiss(id), TIMEOUT)
}

const dismiss = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
}

watch(
    () => page.props.flash?.success,
    (msg) => msg && push('success', msg),
    { immediate: true },
)
watch(
    () => page.props.flash?.error,
    (msg) => msg && push('error', msg),
    { immediate: true },
)

const toneClass = (type) => ({
    success: 'bg-white border-[#0D7C66]/20 text-[#0D7C66]',
    error: 'bg-white border-red-200 text-red-700',
}[type] ?? 'bg-white border-slate-200 text-slate-700')

const iconClass = (type) => ({
    success: 'bg-[#0D7C66] text-white',
    error: 'bg-red-500 text-white',
}[type] ?? 'bg-slate-200 text-slate-600')
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[70] flex flex-col gap-2 pointer-events-none max-w-sm w-[calc(100vw-2rem)] sm:w-auto">
            <TransitionGroup
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-1"
            >
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    :class="['rounded-xl border shadow-lg px-4 py-3 flex items-start gap-3 pointer-events-auto', toneClass(t.type)]"
                    role="status"
                >
                    <span :class="['flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold', iconClass(t.type)]">
                        {{ t.type === 'success' ? '✓' : '!' }}
                    </span>
                    <p class="text-sm font-medium leading-snug flex-1 break-words">{{ t.message }}</p>
                    <button
                        type="button"
                        class="text-slate-300 hover:text-slate-500 text-sm leading-none flex-shrink-0"
                        @click="dismiss(t.id)"
                        aria-label="Đóng"
                    >✕</button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
