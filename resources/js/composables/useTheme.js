import { ref, watch, onMounted } from 'vue'

const STORAGE_KEY = 'edenire-theme'

// 'light' | 'dark' — single toggle, no system/device mode
function initialTheme() {
    const stored = localStorage.getItem(STORAGE_KEY)
    // Migrate any old 'system' value to the actual resolved preference
    if (stored === 'system') {
        const resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
        localStorage.setItem(STORAGE_KEY, resolved)
        return resolved
    }
    return stored === 'dark' ? 'dark' : 'light'
}

const theme = ref(initialTheme())

function applyTheme(mode) {
    document.documentElement.classList.toggle('dark', mode === 'dark')
}

function setTheme(mode) {
    theme.value = mode
    localStorage.setItem(STORAGE_KEY, mode)
    applyTheme(mode)
}

function toggleTheme() {
    setTheme(theme.value === 'dark' ? 'light' : 'dark')
}

function initTheme() {
    applyTheme(theme.value)
}

export function useTheme() {
    onMounted(initTheme)
    return { theme, setTheme, toggleTheme }
}
