<script setup>
import { computed } from 'vue'

const props = defineProps({
    score: { type: [Number, null], default: null },
    size: { type: String, default: 'sm' },
})

const tone = computed(() => {
    if (props.score === null || props.score === undefined) return 'neutral'
    if (props.score < 50) return 'red'
    if (props.score < 75) return 'yellow'
    return 'green'
})

const classes = computed(() => {
    const base = props.size === 'lg'
        ? 'inline-flex items-center px-3 py-1 rounded-full text-sm font-bold'
        : 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold'
    const toneMap = {
        neutral: 'bg-slate-100 text-slate-500',
        red: 'bg-red-50 text-red-700 border border-red-100',
        yellow: 'bg-amber-50 text-amber-700 border border-amber-100',
        green: 'bg-emerald-50 text-emerald-700 border border-emerald-100',
    }
    return `${base} ${toneMap[tone.value]}`
})

const label = computed(() => {
    if (props.score === null || props.score === undefined) return 'Chưa AI'
    return `${props.score}/100`
})
</script>

<template>
    <span :class="classes">{{ label }}</span>
</template>
