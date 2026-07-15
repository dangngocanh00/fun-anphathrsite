<script setup>
import { onMounted, ref, watch } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    multiline: { type: Boolean, default: true },
    minHeight: { type: String, default: '120px' },
    invalid: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const editor = ref(null)
const active = ref({ bold: false, italic: false, underline: false })

onMounted(() => {
    if (editor.value) editor.value.innerHTML = props.modelValue || ''
})

watch(() => props.modelValue, (val) => {
    if (editor.value && (val || '') !== editor.value.innerHTML) {
        editor.value.innerHTML = val || ''
    }
})

const onInput = () => {
    emit('update:modelValue', editor.value.innerHTML)
}

const updateActiveState = () => {
    active.value = {
        bold: document.queryCommandState('bold'),
        italic: document.queryCommandState('italic'),
        underline: document.queryCommandState('underline'),
    }
}

const exec = (command) => {
    editor.value.focus()
    document.execCommand(command, false, null)
    onInput()
    updateActiveState()
}

const onKeydown = (e) => {
    if (!props.multiline && e.key === 'Enter') {
        e.preventDefault()
    }
}
</script>

<template>
    <div>
        <div class="inline-flex items-center gap-1 mb-1.5 rounded-lg border border-slate-200 bg-slate-50 p-1">
            <button
                type="button"
                tabindex="-1"
                title="In đậm (Ctrl+B)"
                :class="[
                    'w-7 h-7 rounded-md text-sm font-bold flex items-center justify-center transition-colors',
                    active.bold ? 'bg-[#0D7C66] text-white' : 'text-slate-600 hover:bg-white hover:shadow-sm',
                ]"
                @mousedown.prevent="exec('bold')"
            >B</button>
            <button
                type="button"
                tabindex="-1"
                title="In nghiêng (Ctrl+I)"
                :class="[
                    'w-7 h-7 rounded-md text-sm italic flex items-center justify-center transition-colors',
                    active.italic ? 'bg-[#0D7C66] text-white' : 'text-slate-600 hover:bg-white hover:shadow-sm',
                ]"
                @mousedown.prevent="exec('italic')"
            >I</button>
            <button
                type="button"
                tabindex="-1"
                title="Gạch chân (Ctrl+U)"
                :class="[
                    'w-7 h-7 rounded-md text-sm underline flex items-center justify-center transition-colors',
                    active.underline ? 'bg-[#0D7C66] text-white' : 'text-slate-600 hover:bg-white hover:shadow-sm',
                ]"
                @mousedown.prevent="exec('underline')"
            >U</button>
        </div>
        <div
            ref="editor"
            contenteditable="true"
            :data-placeholder="placeholder"
            :style="multiline ? { minHeight } : {}"
            :class="[
                'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm leading-relaxed',
                'focus:border-[#0D7C66] focus:ring-2 focus:ring-[#0D7C66]/20 focus:outline-none',
                'empty:before:content-[attr(data-placeholder)] empty:before:text-slate-400',
                multiline ? '' : 'whitespace-nowrap overflow-x-auto',
                invalid ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : '',
            ]"
            @input="onInput"
            @keydown="onKeydown"
            @keyup="updateActiveState"
            @mouseup="updateActiveState"
            @focus="updateActiveState"
        ></div>
    </div>
</template>
