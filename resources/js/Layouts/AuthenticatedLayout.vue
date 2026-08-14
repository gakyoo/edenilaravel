<script setup>
import { ref } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';

const showingSidebar = ref(false);

const navItems = [
    { href: '/dashboard?tab=overview', label: 'Dashboard', icon: '📊', active: (path) => path === '/dashboard' || path.startsWith('/dashboard?tab=overview') },
    { href: '/dashboard?tab=properties', label: 'Properties', icon: '🏠', active: (path) => path.includes('tab=properties') },
    { href: '/dashboard/properties/create', label: 'Add Property', icon: '➕', active: (path) => path.includes('/properties/create') },
    { href: '/dashboard?tab=enquiries', label: 'Enquiries', icon: '💬', active: (path) => path.includes('tab=enquiries') },
];
</script>

<template>
    <div class="min-h-screen bg-[#EBEBEB] dark:bg-[#232126]">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-[#232126] dark:bg-[#0D0E0F] text-white transform transition-transform duration-200 lg:translate-x-0"
            :class="showingSidebar ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Brand (no default logo) -->
            <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10">
                <span class="text-2xl">🏘️</span>
                <div>
                    <div class="font-bold text-[#A8E46A] leading-tight">Edenire.co.tz</div>
                    <div class="text-[10px] text-white/50 tracking-wide">Edeni Realtors · Backend</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="px-3 py-4 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition"
                    :class="item.active($page.url)
                        ? 'bg-[#A8E46A] text-[#232126]'
                        : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    @click="showingSidebar = false"
                >
                    <span class="text-base">{{ item.icon }}</span>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User footer -->
            <div class="absolute bottom-0 inset-x-0 p-4 border-t border-white/10">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-[#A8E46A] text-[#232126] flex items-center justify-center font-bold">
                        {{ ($page.props.auth.user.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</div>
                        <div class="text-[11px] text-white/50 truncate">{{ $page.props.auth.user.email }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <Link href="/profile" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-white/70 hover:bg-white/10 hover:text-white transition">
                        ⚙️ Profile
                    </Link>
                    <Link href="/logout" method="post" as="button"
                        class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-red-300 hover:bg-red-500/10 transition">
                        🚪 Log Out
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Mobile overlay -->
        <div v-if="showingSidebar" class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="showingSidebar = false"></div>

        <!-- Main area -->
        <div class="lg:pl-64">
            <!-- Top bar (slim, no default white header) -->
            <header class="sticky top-0 z-20 bg-[#EBEBEB]/90 dark:bg-[#232126]/90 backdrop-blur border-b border-gray-200 dark:border-white/10">
                <div class="flex items-center justify-between h-14 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button
                            class="lg:hidden text-gray-600 dark:text-gray-300 text-xl p-1"
                            @click="showingSidebar = true"
                            aria-label="Open menu"
                        >☰</button>
                        <slot name="header" />
                    </div>
                    <ThemeToggle />
                </div>
            </header>

            <!-- Content (constrained width) -->
            <main class="mx-auto w-full max-w-6xl px-4 sm:px-6 py-6">
                <slot />
            </main>
        </div>
    </div>
</template>
