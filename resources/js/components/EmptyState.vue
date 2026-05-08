<script setup>
defineProps({
    icon: { type: String, default: 'inbox' },
    title: { type: String, default: 'Chưa có dữ liệu' },
    description: { type: String, default: '' },
})
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
        <div class="w-14 h-14 rounded-2xl bg-[#ecfdf7] text-[#0D7C66] flex items-center justify-center mb-4">
            <svg v-if="icon === 'inbox'" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
                <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
            </svg>
            <svg v-else-if="icon === 'users'" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <svg v-else-if="icon === 'briefcase'" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
            <svg v-else-if="icon === 'search'" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <svg v-else-if="icon === 'chart'" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18"/>
                <path d="M7 16l4-4 4 4 5-6"/>
            </svg>
            <svg v-else class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-[#1B2B4B]">{{ title }}</p>
        <p v-if="description" class="text-xs text-slate-500 mt-1.5 max-w-sm leading-relaxed">{{ description }}</p>
        <slot />
    </div>
</template>
