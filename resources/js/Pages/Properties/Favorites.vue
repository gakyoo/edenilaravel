<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import FavoriteButton from '@/Components/FavoriteButton.vue';

const props = defineProps({
    properties: Object,
    favoriteIds: Array,
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <Head title="My Saved Properties — Edenire.co.tz">
            <meta name="description" content="Your saved favorite properties on Edenire.co.tz" />
        </Head>

        <header class="bg-white shadow-sm dark:bg-gray-900 dark:shadow-gray-950">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="flex items-center"><img src="/img/logo.svg" alt="EdeniRE" class="h-9 w-auto" /></Link>
                <nav class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <Link href="/properties" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Properties</Link>
                    <Link v-if="$page.props.auth?.user" href="/dashboard" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Dashboard</Link>
                    <Link v-else href="/login" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Sign in</Link>
                    <ThemeToggle />
                </nav>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">❤️ My Saved Properties</h1>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Properties you've saved — remove them anytime, or request a tour.</p>

            <div v-if="properties.data.length === 0" class="text-center py-20 text-gray-500 dark:text-gray-400">
                <div class="text-5xl mb-4">🏠</div>
                <p class="text-lg font-medium mb-1">No saved properties yet</p>
                <p class="text-sm mb-6">Tap the heart on any property to save it here.</p>
                <Link href="/properties" class="inline-block bg-[#A8E46A] text-[#232126] font-semibold px-6 py-3 rounded-xl transition hover:bg-[#8CC84F]">
                    Browse Properties
                </Link>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="property in properties.data" :key="property.id"
                    class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden dark:bg-gray-900 dark:shadow-gray-950 border border-gray-100 dark:border-gray-800">
                    <div class="relative">
                        <Link :href="property.public_url">
                            <img v-if="property.primary_image_url" :src="property.primary_image_url" :alt="property.title"
                                class="h-48 w-full object-cover" />
                            <div v-else class="h-48 bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 capitalize">
                                {{ property.property_type }}
                            </div>
                        </Link>
                        <div class="absolute top-2 right-2">
                            <FavoriteButton :property-id="property.id" :active="true" variant="icon" />
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-2">
                            <Link :href="property.public_url" class="font-semibold text-lg text-gray-900 dark:text-gray-100 hover:underline">{{ property.title || 'Property' }}</Link>
                            <span class="text-[#70A83C] dark:text-[#A8E46A] font-bold whitespace-nowrap">{{ property.price_label }}</span>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ property.city }}{{ property.region ? ', ' + property.region : '' }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-300">
                                <span v-if="property.bedrooms">🛏 {{ property.bedrooms }} bd</span>
                                <span v-if="property.bathrooms">🛁 {{ property.bathrooms }} ba</span>
                                <span v-if="property.building_area">{{ property.building_area }} m²</span>
                            </div>
                            <Link :href="property.public_url"
                                class="text-xs font-semibold text-[#70A83C] dark:text-[#A8E46A] hover:underline whitespace-nowrap">
                                View →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center gap-2 mt-8">
                <Link v-if="properties.prev_page_url" :href="properties.prev_page_url" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 dark:text-gray-100 rounded-lg">← Prev</Link>
                <span class="px-4 py-2 text-sm text-gray-500">{{ properties.current_page }} / {{ properties.last_page }}</span>
                <Link v-if="properties.next_page_url" :href="properties.next_page_url" class="px-4 py-2 bg-[#A8E46A] text-[#232126] rounded-lg">Next →</Link>
            </div>
        </div>
    </div>
</template>
