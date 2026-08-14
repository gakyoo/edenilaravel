<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    myTours: Array,
    favorites: Array,
    savedSearches: Array,
});

const tourStatusColors = {
    pending: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    confirmed: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    completed: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
};

function deleteSearch(s) {
    if (confirm(`Delete saved search "${s.label || s.name}"?`)) {
        router.delete(`/saved-searches/${s.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Dashboard | Edenire.co.tz" />
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">My Dashboard</h2>
        </template>

        <div class="py-6 space-y-6">
            <!-- Tour bookings -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">🗓️ My Tour Bookings</h3>
                <div v-if="myTours.length === 0" class="text-sm text-gray-400 text-center py-6">
                    No tour bookings yet — browse properties and schedule a visit.
                </div>
                <div class="space-y-3">
                    <div v-for="t in myTours" :key="t.id" class="border dark:border-gray-700 rounded-lg p-3 flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                <Link :href="t.property?.public_url" class="hover:underline">{{ t.property?.title || 'Property' }}</Link>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                📅 {{ t.preferred_date }} at {{ t.preferred_time }}
                                <template v-if="t.property"> · {{ t.property.city }}{{ t.property.region ? ', ' + t.property.region : '' }}</template>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Requested {{ t.created_at }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full capitalize" :class="tourStatusColors[t.status]">{{ t.status }}</span>
                    </div>
                </div>
            </div>

            <!-- Favorites (wishlist) -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">❤️ My Wishlist</h3>
                    <Link href="/favorites" class="text-sm text-[#70A83C] dark:text-[#A8E46A] font-semibold hover:underline">View all →</Link>
                </div>
                <div v-if="favorites.length === 0" class="text-sm text-gray-400 text-center py-6">
                    No saved properties yet — tap the heart on any property.
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link v-for="p in favorites" :key="p.id" :href="p.public_url"
                        class="group border dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-lg transition">
                        <img v-if="p.primary_image_url" :src="p.primary_image_url" :alt="p.title"
                            class="h-28 w-full object-cover group-hover:opacity-90 transition" />
                        <div v-else class="h-28 bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 text-sm capitalize">
                            {{ p.property_type }}
                        </div>
                        <div class="p-3">
                            <div class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate">{{ p.title }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ p.city }}{{ p.region ? ', ' + p.region : '' }}</div>
                            <div class="text-[#70A83C] dark:text-[#A8E46A] font-bold text-sm mt-1">{{ p.price_label }}</div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Saved searches -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">🔍 My Saved Searches</h3>
                <div v-if="savedSearches.length === 0" class="text-sm text-gray-400 text-center py-6">
                    Save a search from the results page to get back to it quickly.
                </div>
                <div class="space-y-2">
                    <div v-for="s in savedSearches" :key="s.id" class="flex items-center gap-3 border dark:border-gray-700 rounded-lg p-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ s.label }}</div>
                        </div>
                        <Link :href="s.url" class="text-xs text-[#70A83C] dark:text-[#A8E46A] font-semibold hover:underline whitespace-nowrap">Run search →</Link>
                        <button @click="deleteSearch(s)" class="text-xs text-red-500 hover:text-red-700 whitespace-nowrap">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
