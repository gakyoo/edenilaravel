import { ref, watch, onMounted } from 'vue'

const STORAGE_KEY = 'edenire-theme'

// 'light' | 'dark' | 'system'
const theme = ref(localStorage.getItem(STORAGE_KEY) || 'system')

function applyTheme(mode) {
    const root = document.documentElement
    const isDark =
        mode === 'dark' ||
        (mode === 'system' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)

    root.classList.toggle('dark', isDark)
}

function setTheme(mode) {
    theme.value = mode
    localStorage.setItem(STORAGE_KEY, mode)
    applyTheme(mode)
}

function initTheme() {
    applyTheme(theme.value)
    // Follow system changes when in 'system' mode
    window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', (e) => {
            if (theme.value === 'system') {
                document.documentElement.classList.toggle('dark', e.matches)
            }
        })
}

export function useTheme() {
    onMounted(initTheme)
    return { theme, setTheme }
}
