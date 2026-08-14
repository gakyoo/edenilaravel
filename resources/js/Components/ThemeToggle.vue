<script setup>
import { useTheme } from '@/composables/useTheme'

const { theme, toggleTheme } = useTheme()
</script>

<template>
    <button
        type="button"
        :title="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
        @click="toggleTheme"
    >
        <span v-if="theme === 'dark'">☀️</span>
        <span v-else>🌙</span>
        <span class="hidden sm:inline">{{ theme === 'dark' ? 'Light' : 'Dark' }}</span>
    </button>
</template>
