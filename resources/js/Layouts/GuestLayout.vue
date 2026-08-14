<script setup>
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-10 sm:px-6 dark:bg-gray-950">
        <div class="absolute top-4 right-4 z-10">
            <ThemeToggle />
        </div>

        <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl md:grid-cols-2 dark:bg-gray-900 dark:shadow-gray-950">
            <!-- Left: brand panel (hidden on small screens) -->
            <div class="relative hidden flex-col justify-between bg-[#232126] p-10 text-white md:flex">
                <!-- decorative lime glow -->
                <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-[#A8E46A]/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-[#A8E46A]/10 blur-3xl"></div>

                <div class="relative">
                    <Link href="/" class="inline-flex items-center gap-2">
                        <img src="/img/logo-white.svg" alt="EdeniRE" class="h-9 w-auto" />
                    </Link>
                    <h2 class="mt-10 text-2xl font-bold leading-snug">
                        Find your next home in Tanzania
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/70">
                        Houses, apartments, land, and commercial properties across the country. Verified listings, WhatsApp enquiries, and trusted agents.
                    </p>
                </div>

                <ul class="relative space-y-3 text-sm text-white/80">
                    <li class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#A8E46A] text-[10px] font-bold text-[#232126]">✓</span>
                        Verified listings — no fake ads
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#A8E46A] text-[10px] font-bold text-[#232126]">✓</span>
                        Save favorites &amp; book tours in seconds
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#A8E46A] text-[10px] font-bold text-[#232126]">✓</span>
                        Talk to agents directly on WhatsApp
                    </li>
                </ul>
            </div>

            <!-- Right: form -->
            <div class="px-6 py-8 sm:px-10 sm:py-12">
                <div class="mb-6 md:hidden">
                    <Link href="/" class="inline-flex items-center gap-2">
                        <img src="/img/logo.svg" alt="EdeniRE" class="h-9 w-auto" />
                    </Link>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
