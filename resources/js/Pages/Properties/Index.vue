<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    properties: Object,
    filters: Object,
});

const q = ref(props.filters.q || '');

function search() {
    router.get('/properties', { q: q.value }, { preserveState: true, replace: true });
}

const types = [
    { value: '', label: 'All types' },
    { value: 'residential', label: 'Residential' },
    { value: 'commercial', label: 'Commercial' },
    { value: 'industrial', label: 'Industrial' },
    { value: 'land', label: 'Land' },
    { value: 'mixed_use', label: 'Mixed use' },
];

const listings = [
    { value: '', label: 'Buy or Rent' },
    { value: 'sale', label: 'For Sale' },
    { value: 'rent', label: 'For Rent' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <header class="bg-white shadow-sm dark:bg-gray-900 dark:shadow-gray-950">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="text-xl font-bold text-emerald-700 dark:text-emerald-400">Edenire.co.tz</Link>
                <nav class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <Link href="/properties" class="hover:text-emerald-700 dark:hover:text-emerald-400">Properties</Link>
                    <Link v-if="$page.props.auth?.user" href="/dashboard" class="hover:text-emerald-700 dark:hover:text-emerald-400">Dashboard</Link>
                    <Link v-else href="/login" class="hover:text-emerald-700 dark:hover:text-emerald-400">Sign in</Link>
                    <ThemeToggle />
                </nav>
            </div>
        </header>

        <div class="bg-emerald-700 dark:bg-emerald-900 text-white">
            <div class="max-w-7xl mx-auto px-4 py-12 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold mb-3">Find Your Home in Tanzania</h1>
                <p class="text-lg mb-6 text-emerald-100">Browse houses, apartments, land, and commercial properties across the country.</p>
                <div class="flex gap-2 justify-center flex-wrap">
                    <input v-model="q" @keyup.enter="search" type="text" placeholder="Search city, region, or keyword..."
                        class="rounded-lg px-4 py-3 text-gray-900 w-full max-w-md dark:bg-gray-800 dark:text-gray-100" />
                    <button @click="search" class="bg-emerald-900 hover:bg-emerald-950 dark:bg-gray-900 dark:hover:bg-gray-950 px-6 py-3 rounded-lg font-semibold transition">
                        Search
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex gap-3 mb-6 flex-wrap">
                <select v-model="filters.type" @change="router.get('/properties', filters, { preserveState: true, replace: true })"
                    class="rounded-lg border-gray-300 px-3 py-2 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                    <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
                <select v-model="filters.listing" @change="router.get('/properties', filters, { preserveState: true, replace: true })"
                    class="rounded-lg border-gray-300 px-3 py-2 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                    <option v-for="l in listings" :key="l.value" :value="l.value">{{ l.label }}</option>
                </select>
            </div>

            <div v-if="properties.data.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
                No properties found yet. Check back soon!
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link v-for="property in properties.data" :key="property.id" :href="`/properties/${property.id}`"
                    class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden dark:bg-gray-900 dark:shadow-gray-950">
                    <div class="h-48 bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 capitalize">
                        {{ property.property_type }}
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-2">
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ property.title || 'Property' }}</h3>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold whitespace-nowrap">{{ property.price_label }}</span>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ property.city }}{{ property.region ? ', ' + property.region : '' }}</p>
                        <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-300 mt-3">
                            <span v-if="property.bedrooms">🛏 {{ property.bedrooms }} bd</span>
                            <span v-if="property.bathrooms">🛁 {{ property.bathrooms }} ba</span>
                            <span v-if="property.building_area">{{ property.building_area }} m²</span>
                        </div>
                    </div>
                </Link>
            </div>

            <div class="flex justify-center gap-2 mt-8">
                <Link v-if="properties.prev_page_url" :href="properties.prev_page_url" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 dark:text-gray-100 rounded-lg">← Prev</Link>
                <Link v-if="properties.next_page_url" :href="properties.next_page_url" class="px-4 py-2 bg-emerald-700 text-white rounded-lg">Next →</Link>
            </div>
        </div>
    </div>
</template>
