<script setup>
import { useTheme } from '@/composables/useTheme'

const { theme, setTheme } = useTheme()

const modes = [
    { key: 'light', icon: '☀️', label: 'Light' },
    { key: 'dark', icon: '🌙', label: 'Dark' },
    { key: 'system', icon: '🖥️', label: 'Device' },
]
</script>

<template>
    <div class="flex items-center gap-1 rounded-full bg-gray-200 p-1 dark:bg-gray-700" role="group" aria-label="Theme">
        <button
            v-for="mode in modes"
            :key="mode.key"
            type="button"
            :title="`${mode.label} mode`"
            :aria-label="`${mode.label} mode`"
            class="rounded-full px-2.5 py-1.5 text-sm transition"
            :class="theme === mode.key
                ? 'bg-white shadow dark:bg-gray-900'
                : 'opacity-60 hover:opacity-100'"
            @click="setTheme(mode.key)"
        >
            <span class="mr-1">{{ mode.icon }}</span>
            <span class="hidden sm:inline">{{ mode.label }}</span>
        </button>
    </div>
</template>
