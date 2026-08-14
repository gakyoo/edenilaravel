<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kpis: Object,
    byType: Array,
    byRegion: Array,
    byStatus: Array,
    recentProperties: Array,
    recentEnquiries: Array,
});

function fmt(n) {
    return Number(n || 0).toLocaleString();
}

function fmtValue(v) {
    if (!v) return '0';
    return Number(v).toLocaleString();
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
        <Head title="Admin — Site Management | Edenire.co.tz" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Admin Dashboard</h2>
                <ThemeToggle />
            </div>
        </template>

        <div class="py-8 space-y-6">
            <!-- Admin nav -->
            <div class="flex gap-2 flex-wrap bg-white dark:bg-gray-900 rounded-xl shadow p-3">
                <Link href="/admin" class="px-4 py-2 rounded-lg text-sm font-semibold bg-[#0076FF] text-white">Dashboard</Link>
                <Link href="/admin/properties" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Properties</Link>
                <Link href="/admin/properties/create" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">+ New Property</Link>
                <Link href="/admin/enquiries" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Enquiries</Link>
            </div>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Properties</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(kpis.total) }}</div>
                    <div class="text-xs text-gray-400">{{ kpis.active }} active · {{ kpis.pending }} pending</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Sold / Rented</div>
                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ fmt(kpis.sold) }}</div>
                    <div class="text-xs text-gray-400">{{ kpis.off_market }} off market</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Views</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(kpis.total_views) }}</div>
                    <div class="text-xs text-gray-400">{{ fmt(kpis.total_favorites) }} favorites</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Conversion Rate</div>
                    <div class="text-2xl font-bold text-[#0076FF]">{{ kpis.conversion_rate }}%</div>
                    <div class="text-xs text-gray-400">{{ kpis.new_enquiries_7d }} enquiries / 7d</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Portfolio Value (TZS)</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 truncate">{{ fmtValue(kpis.tzs_value) }}</div>
                    <div class="text-xs text-gray-400">TZS</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Portfolio Value (USD)</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 truncate">{{ fmtValue(kpis.usd_value) }}</div>
                    <div class="text-xs text-gray-400">USD</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Users</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(kpis.total_users) }}</div>
                    <div class="text-xs text-gray-400">{{ kpis.agents }} agents · {{ kpis.buyers }} buyers</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Open Tasks</div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ fmt(kpis.open_tasks) }}</div>
                    <div class="text-xs text-gray-400">across all users</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Distribution by status/type/region -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Properties by Status</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="s in byStatus" :key="s.status"
                                class="px-3 py-1.5 rounded-full text-sm capitalize" :class="statusColors[s.status] || statusColors.active">
                                {{ s.status.replace('_', ' ') }}: {{ s.c }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Properties by Type</h3>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                            <div v-for="t in byType" :key="t.property_type" class="text-center border dark:border-gray-700 rounded-lg p-3">
                                <div class="text-xl font-bold text-[#0076FF]">{{ t.c }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ t.property_type.replace('_', ' ') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Top Regions</h3>
                        <div class="space-y-2">
                            <div v-for="r in byRegion" :key="r.region" class="flex items-center gap-3">
                                <span class="w-28 text-sm text-gray-600 dark:text-gray-300">{{ r.region }}</span>
                                <div class="flex-1 h-5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0076FF] rounded-full"
                                        :style="{ width: (r.c / Math.max(1, byRegion[0].c)) * 100 + '%' }"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ r.c }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent items -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Properties</h3>
                        <div class="space-y-3">
                            <Link v-for="p in recentProperties" :key="p.id" :href="`/admin/properties/${p.id}/edit`"
                                class="flex items-center gap-3 border dark:border-gray-700 rounded-lg p-2.5 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <img v-if="p.primary_image_url" :src="p.primary_image_url" class="h-10 w-10 rounded object-cover" />
                                <div v-else class="h-10 w-10 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ p.title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ p.city || p.region }} · {{ p.price_label }}</div>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Enquiries</h3>
                        <div class="space-y-3">
                            <div v-for="e in recentEnquiries" :key="e.id" class="border dark:border-gray-700 rounded-lg p-2.5">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ e.name || 'Anonymous' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ e.property?.title || 'General' }}<template v-if="e.phone"> · {{ e.phone }}</template></div>
                            </div>
                            <div v-if="recentEnquiries.length === 0" class="text-sm text-gray-400 text-center py-3">No enquiries yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
