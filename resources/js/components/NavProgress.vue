<script setup>
import { router } from '@inertiajs/vue3'
import { onBeforeUnmount, onMounted, ref } from 'vue'

const visible = ref(false)
const progress = ref(0)
let startTimer = null
let tickTimer = null

const stopTick = () => {
    if (tickTimer) { clearInterval(tickTimer); tickTimer = null }
}

const onStart = () => {
    if (startTimer) clearTimeout(startTimer)
    startTimer = setTimeout(() => {
        visible.value = true
        progress.value = 12
        stopTick()
        tickTimer = setInterval(() => {
            if (progress.value < 88) {
                progress.value += (90 - progress.value) * 0.08
            }
        }, 200)
    }, 120)
}

const onFinish = () => {
    if (startTimer) { clearTimeout(startTimer); startTimer = null }
    stopTick()
    if (visible.value) {
        progress.value = 100
        setTimeout(() => {
            visible.value = false
            progress.value = 0
        }, 200)
    }
}

let stopStart, stopFinish, stopError
onMounted(() => {
    stopStart = router.on('start', onStart)
    stopFinish = router.on('finish', onFinish)
    stopError = router.on('error', onFinish)
})
onBeforeUnmount(() => {
    stopStart?.()
    stopFinish?.()
    stopError?.()
    if (startTimer) clearTimeout(startTimer)
    stopTick()
})
</script>

<template>
    <div
        v-show="visible"
        class="fixed top-0 left-0 right-0 z-[60] h-0.5 pointer-events-none"
    >
        <div
            class="h-full bg-[#0D7C66] shadow-[0_0_8px_rgba(13,124,102,0.5)] transition-all duration-200 ease-out"
            :style="{ width: progress + '%' }"
        ></div>
    </div>
</template>
