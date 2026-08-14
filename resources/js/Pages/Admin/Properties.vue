<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    properties: Object,
    filters: Object,
    statuses: Array,
    types: Array,
    regions: Array,
});

const q = ref(props.filters.q || '');
const f = ref({ status: props.filters.status || '', type: props.filters.type || '', region: props.filters.region || '' });

function apply() {
    router.get('/admin/properties', { q: q.value, ...f.value }, { preserveState: true, replace: true });
}

function del(property) {
    if (confirm(`Delete "${property.title}"? This can't be undone.`)) {
        router.delete(`/admin/properties/${property.id}`, { preserveScroll: true });
    }
}

const statusColors = {
    active: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    sold: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    rented: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
    off_market: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Admin — Properties | Edenire.co.tz" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Manage Properties</h2>
                <ThemeToggle />
            </div>
        </template>

        <div class="py-8 space-y-6">
            <div class="flex gap-2 flex-wrap bg-white dark:bg-gray-900 rounded-xl shadow p-3">
                <Link href="/admin" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Dashboard</Link>
                <Link href="/admin/properties" class="px-4 py-2 rounded-lg text-sm font-semibold bg-[#A8E46A] text-[#232126]">Properties</Link>
                <Link href="/admin/properties/create" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">+ New Property</Link>
                <Link href="/admin/enquiries" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Enquiries</Link>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5 space-y-3">
                <div class="flex flex-wrap gap-2 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <input v-model="q" @keyup.enter="apply" type="text" placeholder="Search title, city, region..."
                            class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    </div>
                    <select v-model="f.status" @change="apply" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All statuses</option>
                        <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s.replace('_', ' ') }}</option>
                    </select>
                    <select v-model="f.type" @change="apply" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All types</option>
                        <option v-for="t in types" :key="t" :value="t" class="capitalize">{{ t.replace('_', ' ') }}</option>
                    </select>
                    <select v-model="f.region" @change="apply" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All regions</option>
                        <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
                    </select>
                    <Link href="/admin/properties/create" class="bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] px-4 py-2.5 rounded-lg text-sm font-semibold">+ Add Property</Link>
                </div>
            </div>

            <!-- Property table -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase">
                            <th class="px-4 py-3">Property</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Views</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in properties.data" :key="p.id" class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img v-if="p.primary_image_url" :src="p.primary_image_url" class="h-10 w-12 rounded object-cover" />
                                    <div v-else class="h-10 w-12 rounded bg-gray-200 dark:bg-gray-700"></div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100 truncate max-w-[220px]">{{ p.title }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ p.city || '—' }}{{ p.region ? ', ' + p.region : '' }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">{{ p.price_label }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs capitalize" :class="statusColors[p.status]">{{ p.status.replace('_', ' ') }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ p.views_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link :href="`/admin/properties/${p.id}/edit`" class="text-[#A8E46A] hover:underline text-xs font-semibold">Edit</Link>
                                    <a :href="`/properties/${p.id}`" target="_blank" class="text-gray-400 hover:text-gray-600 text-xs">View</a>
                                    <button @click="del(p)" class="text-red-500 hover:text-red-700 text-xs font-semibold">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="properties.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">No properties found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center gap-2">
                <Link v-if="properties.prev_page_url" :href="properties.prev_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">← Prev</Link>
                <span class="px-3 py-1.5 text-sm text-gray-500">{{ properties.current_page }} / {{ properties.last_page }}</span>
                <Link v-if="properties.next_page_url" :href="properties.next_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">Next →</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
