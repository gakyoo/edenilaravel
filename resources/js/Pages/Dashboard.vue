<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    metrics: Object,
    activity: Array,
    alerts: Array,
    properties: Object,
    filters: Object,
    filterOptions: Object,
    leads: Array,
    financial: Object,
    tasks: Array,
    upcoming: Array,
    topListings: Array,
    viewsByStatus: Array,
    adminKpis: Object,
    byType: Array,
    byRegion: Array,
    byStatus: Array,
    allEnquiries: Object,
    enquiryFilters: Object,
    allTours: Object,
    tourFilters: Object,
});

// ---------- Tabs (merged admin + dashboard) — deep-linkable via ?tab= ----------
const urlTab = new URL(window.location.href).searchParams.get('tab');
const activeTab = ref(['overview', 'properties', 'enquiries', 'tours'].includes(urlTab) ? urlTab : 'overview');
const tabs = [
    { key: 'overview', label: '📊 Overview' },
    { key: 'properties', label: '🏠 Properties' },
    { key: 'enquiries', label: '💬 Enquiries' },
    { key: 'tours', label: '🗓️ Tours' },
];

const tabTitles = {
    overview: 'Dashboard — Overview',
    properties: 'Properties — Manage Listings',
    enquiries: 'Enquiries — Leads & Messages',
    tours: 'Tours — Viewing Requests',
};

const pageTitle = computed(() => `${tabTitles[activeTab.value]} | Edenire.co.tz`);

function switchTab(key) {
    activeTab.value = key;
    router.get('/dashboard', { tab: key }, { preserveState: true, replace: true });
}

// ---------- Overview filters ----------
const q = ref(props.filters.q || '');
const f = reactive({
    status: props.filters.status || '',
    type: props.filters.type || '',
    region: props.filters.region || '',
    min_price: props.filters.min_price || '',
    max_price: props.filters.max_price || '',
});

function applyFilters() {
    router.get('/dashboard', { q: q.value, ...f }, { preserveState: true, replace: true });
}

function resetFilters() {
    q.value = '';
    f.status = f.type = f.region = f.min_price = f.max_price = '';
    router.get('/dashboard', {}, { preserveState: true, replace: true });
}

// ---------- Property management (merged admin) ----------
const pQ = ref('');
const pF = reactive({ status: '', type: '', region: '' });

function applyPropertyFilters() {
    router.get('/dashboard', { ptab: 'properties', pq: pQ.value, pstatus: pF.status, ptype: pF.type, pregion: pF.region }, { preserveState: true, replace: true });
}

function deleteProperty(property) {
    if (confirm(`Delete "${property.title}"? This can't be undone.`)) {
        router.delete(`/dashboard/properties/${property.id}`, { preserveScroll: true });
    }
}

// ---------- Enquiry management (merged admin) ----------
const eQ = ref(props.enquiryFilters.eq || '');
const eStatus = ref(props.enquiryFilters.estatus || '');

function applyEnquiryFilters() {
    router.get('/dashboard', { etab: 'enquiries', eq: eQ.value, estatus: eStatus.value }, { preserveState: true, replace: true });
}

function setEnquiryStatus(enquiry, status) {
    router.patch(`/dashboard/enquiries/${enquiry.id}`, { status }, { preserveScroll: true });
}

function deleteEnquiry(enquiry) {
    if (confirm(`Delete enquiry from ${enquiry.name || 'anonymous'}?`)) {
        router.delete(`/dashboard/enquiries/${enquiry.id}`, { preserveScroll: true });
    }
}

// ---------- Tour management (merged admin) ----------
const tQ = ref(props.tourFilters.tq || '');
const tStatus = ref(props.tourFilters.tstatus || '');

function applyTourFilters() {
    router.get('/dashboard', { ttab: 'tours', tq: tQ.value, tstatus: tStatus.value }, { preserveState: true, replace: true });
}

function setTourStatus(tour, status) {
    router.patch(`/dashboard/tours/${tour.id}`, { status }, { preserveScroll: true });
}

function deleteTour(tour) {
    if (confirm(`Delete tour request from ${tour.name || 'anonymous'}?`)) {
        router.delete(`/dashboard/tours/${tour.id}`, { preserveScroll: true });
    }
}

// ---------- Tasks ----------
const showTaskForm = ref(false);
const taskForm = useForm({
    title: '',
    notes: '',
    type: 'general',
    priority: 'medium',
    due_date: '',
});

function addTask() {
    taskForm.post('/dashboard/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            taskForm.reset();
            showTaskForm.value = false;
        },
    });
}

function toggleTask(task) {
    router.patch(`/dashboard/tasks/${task.id}`, {
        status: task.status === 'done' ? 'pending' : 'done',
    }, { preserveScroll: true });
}

function deleteTask(task) {
    if (confirm(`Delete task "${task.title}"?`)) {
        router.delete(`/dashboard/tasks/${task.id}`, { preserveScroll: true });
    }
}

// ---------- Status helpers ----------
const statusStyles = {
    active: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    sold: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    rented: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
    off_market: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
};

const alertStyles = {
    info: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800',
    warning: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800',
    danger: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-200 dark:border-red-800',
};

const taskTypeIcons = { follow_up: '📞', document: '📄', inspection: '🔍', showing: '🏠', general: '📌' };

const enquiryStatusColors = {
    new: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    contacted: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    qualified: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    closed: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
};

const channelIcons = { whatsapp: '💬', web_form: '📝', email: '✉️', phone: '📞', in_app: '💬' };

const tourStatusColors = {
    pending: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    confirmed: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    completed: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
};

// ---------- Analytics ----------
const maxViews = computed(() => Math.max(1, ...props.topListings.map((t) => t.views)));

// ---------- Formatting ----------
function formatTZS(value) {
    if (!value) return '0 TZS';
    return Number(value).toLocaleString() + ' TZS';
}

function fmt(n) {
    return Number(n || 0).toLocaleString();
}

function timeAgo(dateStr) {
    if (!dateStr) return 'recently';
    const then = new Date(dateStr);
    const now = new Date();
    const sec = Math.floor((now - then) / 1000);
    if (sec < 60) return 'just now';
    const min = Math.floor(sec / 60);
    if (min < 60) return min + 'm ago';
    const hr = Math.floor(min / 60);
    if (hr < 24) return hr + 'h ago';
    const day = Math.floor(hr / 24);
    if (day < 30) return day + 'd ago';
    return then.toLocaleDateString();
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="pageTitle" />
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dashboard</h2>
        </template>

        <!-- Tabs -->
            <div class="flex gap-2 flex-wrap bg-white dark:bg-gray-900 rounded-xl shadow p-2 mt-4">
            <button v-for="t in tabs" :key="t.key" @click="switchTab(t.key)"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition"
                :class="activeTab === t.key ? 'bg-[#A8E46A] text-[#232126]' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'">
                {{ t.label }}
            </button>
        </div>

        <div class="py-6">
            <!-- ============ TAB: OVERVIEW ============ -->
            <div v-if="activeTab === 'overview'" class="space-y-6">
                <!-- ⚠️ Quick alerts -->
                <div v-if="alerts.length" class="space-y-2">
                    <div v-for="(a, i) in alerts" :key="i"
                        class="rounded-lg border px-4 py-3 text-sm font-medium" :class="alertStyles[a.level]">
                        ⚡ {{ a.text }}
                    </div>
                </div>

                <!-- 📊 Key metrics (dashboard + admin KPIs merged) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Properties</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(metrics.total_properties) }}</div>
                        <div class="text-xs text-gray-400">{{ fmt(metrics.active_listings) }} active · {{ fmt(metrics.pending_sales) }} pending</div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Sold / Rented</div>
                        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ fmt(metrics.sold) }}</div>
                        <div class="text-xs text-gray-400">{{ fmt(adminKpis.off_market) }} off market</div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Value (TZS)</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 truncate">{{ formatTZS(metrics.total_value) }}</div>
                        <div class="text-xs text-gray-400">{{ formatTZS(adminKpis.usd_value) }} USD</div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Engagement</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(metrics.total_views) }}</div>
                        <div class="text-xs text-gray-400">{{ fmt(metrics.new_enquiries) }} new enquiries · {{ fmt(adminKpis.total_users) }} users</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Property snapshot -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Property Snapshot</h3>
                                <Link href="/dashboard/properties/create" class="bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] px-4 py-2 rounded-lg text-sm font-semibold transition">
                                    + Add Property
                                </Link>
                            </div>
                            <div class="space-y-3">
                                <input v-model="q" @keyup.enter="applyFilters" type="text" placeholder="Search title, city, region..."
                                    class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    <select v-model="f.status" @change="applyFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                        <option value="">All statuses</option>
                                        <option v-for="s in filterOptions.statuses" :key="s" :value="s" class="capitalize">{{ s.replace('_', ' ') }}</option>
                                    </select>
                                    <select v-model="f.type" @change="applyFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                        <option value="">All types</option>
                                        <option v-for="t in filterOptions.types" :key="t" :value="t" class="capitalize">{{ t.replace('_', ' ') }}</option>
                                    </select>
                                    <select v-model="f.region" @change="applyFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                        <option value="">All regions</option>
                                        <option v-for="r in filterOptions.regions" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-3 mt-4">
                                <div v-for="p in properties.data" :key="p.id"
                                    class="flex items-center gap-4 border dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <img v-if="p.primary_image_url" :src="p.primary_image_url" :alt="p.title" class="h-14 w-14 rounded-lg object-cover shrink-0" />
                                    <div v-else class="h-14 w-14 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 text-xs shrink-0 capitalize">{{ p.property_type }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ p.title || 'Untitled' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ p.city }}{{ p.region ? ', ' + p.region : '' }} · {{ p.price_label }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">🕐 Modified {{ p.updated_at ? timeAgo(p.updated_at) : 'recently' }}</div>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap capitalize" :class="statusStyles[p.status]">{{ p.status.replace('_', ' ') }}</span>
                                    <div class="flex gap-1 shrink-0">
                                        <Link :href="`/dashboard/properties/${p.id}/edit`" class="text-xs text-[#A8E46A] hover:underline px-2 py-1 font-semibold">Edit</Link>
                                        <a :href="p.public_url" target="_blank" class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1">View</a>
                                        <button @click="deleteProperty(p)" class="text-xs text-red-500 hover:text-red-700 px-2 py-1">Delete</button>
                                    </div>
                                </div>
                                <div v-if="properties.data.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">No properties match your filters.</div>
                            </div>
                            <div class="flex justify-center gap-2 mt-4">
                                <Link v-if="properties.prev_page_url" :href="properties.prev_page_url" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm">← Prev</Link>
                                <span class="px-3 py-1.5 text-sm text-gray-500">{{ properties.current_page }} / {{ properties.last_page }}</span>
                                <Link v-if="properties.next_page_url" :href="properties.next_page_url" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm">Next →</Link>
                            </div>
                        </div>

                        <!-- Analytics -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Top-Performing Listings</h3>
                            <div class="space-y-3">
                                <div v-for="t in topListings" :key="t.title" class="flex items-center gap-3">
                                    <div class="w-40 truncate text-sm text-gray-600 dark:text-gray-300">{{ t.title }}</div>
                                    <div class="flex-1 h-6 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#A8E46A] rounded-full transition-all" :style="{ width: (t.views / maxViews) * 100 + '%' }"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 w-20 text-right">{{ fmt(t.views) }} views · {{ fmt(t.enquiries) }} inq.</div>
                                </div>
                                <div v-if="topListings.length === 0" class="text-sm text-gray-400">No views yet — share your listings!</div>
                            </div>
                        </div>

                        <!-- Tasks -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tasks & Reminders</h3>
                                <button @click="showTaskForm = !showTaskForm" class="text-sm text-[#A8E46A] font-semibold hover:underline">
                                    {{ showTaskForm ? 'Cancel' : '+ Add task' }}
                                </button>
                            </div>
                            <form v-if="showTaskForm" @submit.prevent="addTask" class="space-y-3 mb-4 border dark:border-gray-700 rounded-lg p-3">
                                <input v-model="taskForm.title" type="text" required placeholder="Task title (e.g. Follow up with Masaki buyer)"
                                    class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    <select v-model="taskForm.type" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                        <option value="general">General</option>
                                        <option value="follow_up">Follow-up</option>
                                        <option value="document">Document</option>
                                        <option value="inspection">Inspection</option>
                                        <option value="showing">Showing</option>
                                    </select>
                                    <select v-model="taskForm.priority" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                    <input v-model="taskForm.due_date" type="date" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                                    <button type="submit" class="bg-[#A8E46A] text-[#232126] rounded-lg text-sm font-semibold">Add</button>
                                </div>
                            </form>
                            <div class="space-y-2">
                                <div v-for="t in tasks" :key="t.id" class="flex items-center gap-3 border dark:border-gray-700 rounded-lg p-3">
                                    <button @click="toggleTask(t)" class="shrink-0 text-lg" :class="t.status === 'done' ? 'opacity-40' : ''">{{ t.status === 'done' ? '✅' : '⬜' }}</button>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100" :class="{ 'line-through opacity-40': t.status === 'done' }">
                                            {{ taskTypeIcons[t.type] }} {{ t.title }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ t.priority }}<template v-if="t.due_date"> · due {{ t.due_date }}</template><template v-if="t.property"> · {{ t.property.title }}</template>
                                        </div>
                                    </div>
                                    <button @click="deleteTask(t)" class="text-xs text-red-500 hover:text-red-700 shrink-0">Delete</button>
                                </div>
                                <div v-if="tasks.length === 0" class="text-sm text-gray-400 text-center py-4">No tasks yet.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right column -->
                    <div class="space-y-6">
                        <!-- Activity -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>
                            <div class="space-y-3">
                                <div v-for="(a, i) in activity" :key="i" class="flex items-start gap-3">
                                    <span class="text-lg">{{ a.type === 'enquiry' ? '💬' : a.type === 'tour' ? '🗓️' : '🏠' }}</span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ a.label }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ a.detail }}</div>
                                        <div class="text-[10px] text-gray-400">{{ a.time }}</div>
                                    </div>
                                </div>
                                <div v-if="activity.length === 0" class="text-sm text-gray-400 text-center py-4">No recent activity.</div>
                            </div>
                        </div>

                        <!-- Leads -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Enquiries</h3>
                            <div class="space-y-3">
                                <div v-for="l in leads" :key="l.id" class="border dark:border-gray-700 rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ l.name }}</div>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 capitalize">{{ l.channel }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ l.property || 'General enquiry' }}<template v-if="l.phone"> · {{ l.phone }}</template></div>
                                    <div v-if="l.message" class="text-xs text-gray-600 dark:text-gray-300 mt-1 italic">{{ l.message }}</div>
                                    <div class="text-[10px] text-gray-400 mt-1">{{ l.created_at }}</div>
                                </div>
                                <div v-if="leads.length === 0" class="text-sm text-gray-400 text-center py-4">No enquiries yet.</div>
                            </div>
                        </div>

                        <!-- Financial -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Financial Snapshot</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Total listing value</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatTZS(financial.total_listing_value_tzs) }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Average price</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatTZS(financial.avg_price_tzs) }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">USD listings value</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatTZS(adminKpis.usd_value) }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Users</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ fmt(adminKpis.total_users) }} ({{ fmt(adminKpis.agents) }} agents / {{ fmt(adminKpis.buyers) }} buyers)</span></div>
                            </div>
                        </div>

                        <!-- Quick actions -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                            <div class="grid grid-cols-1 gap-2">
                                <Link href="/dashboard/properties/create" class="bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] rounded-lg px-4 py-2.5 text-sm font-semibold text-center transition">
                                    + Add New Property
                                </Link>
                                <a :href="'/dashboard/export?status=' + f.status + '&region=' + f.region" class="border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg px-4 py-2.5 text-sm font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    ⬇ Export CSV
                                </a>
                                <Link href="/dashboard/properties" class="border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg px-4 py-2.5 text-sm font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-800 transition" @click="activeTab = 'properties'">
                                    🏠 Manage Properties
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ TAB: PROPERTIES (merged admin) ============ -->
            <div v-else-if="activeTab === 'properties'" class="space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5 flex flex-wrap gap-2 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <input v-model="pQ" @keyup.enter="applyPropertyFilters" type="text" placeholder="Search title, city, region..."
                            class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    </div>
                    <select v-model="pF.status" @change="applyPropertyFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All statuses</option>
                        <option v-for="s in filterOptions.statuses" :key="s" :value="s" class="capitalize">{{ s.replace('_', ' ') }}</option>
                    </select>
                    <select v-model="pF.type" @change="applyPropertyFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All types</option>
                        <option v-for="t in filterOptions.types" :key="t" :value="t" class="capitalize">{{ t.replace('_', ' ') }}</option>
                    </select>
                    <select v-model="pF.region" @change="applyPropertyFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All regions</option>
                        <option v-for="r in filterOptions.regions" :key="r" :value="r">{{ r }}</option>
                    </select>
                    <Link href="/dashboard/properties/create" class="bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] px-4 py-2.5 rounded-lg text-sm font-semibold">+ Add Property</Link>
                </div>

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
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs capitalize" :class="statusStyles[p.status]">{{ p.status.replace('_', ' ') }}</span></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ p.views_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="`/dashboard/properties/${p.id}/edit`" class="text-[#A8E46A] hover:underline text-xs font-semibold">Edit</Link>
                                        <a :href="p.public_url" target="_blank" class="text-gray-400 hover:text-gray-600 text-xs">View</a>
                                        <button @click="deleteProperty(p)" class="text-red-500 hover:text-red-700 text-xs font-semibold">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="properties.data.length === 0"><td colspan="6" class="px-4 py-8 text-center text-gray-400">No properties found.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center gap-2">
                    <Link v-if="properties.prev_page_url" :href="properties.prev_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">← Prev</Link>
                    <span class="px-3 py-1.5 text-sm text-gray-500">{{ properties.current_page }} / {{ properties.last_page }}</span>
                    <Link v-if="properties.next_page_url" :href="properties.next_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">Next →</Link>
                </div>

                <!-- Admin-style distribution cards -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">By Status</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="s in byStatus" :key="s.status" class="px-3 py-1.5 rounded-full text-sm capitalize" :class="statusStyles[s.status] || statusStyles.active">{{ s.status.replace('_', ' ') }}: {{ s.c }}</span>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">By Type</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <div v-for="t in byType" :key="t.property_type" class="text-center border dark:border-gray-700 rounded-lg p-3">
                                <div class="text-xl font-bold text-[#A8E46A]">{{ t.c }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ t.property_type.replace('_', ' ') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Top Regions</h3>
                        <div class="space-y-2">
                            <div v-for="r in byRegion" :key="r.region" class="flex items-center gap-3">
                                <span class="w-24 truncate text-sm text-gray-600 dark:text-gray-300">{{ r.region }}</span>
                                <div class="flex-1 h-5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#A8E46A] rounded-full" :style="{ width: (r.c / Math.max(1, byRegion[0]?.c)) * 100 + '%' }"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ r.c }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ TAB: ENQUIRIES (merged admin) ============ -->
            <div v-else-if="activeTab === 'enquiries'" class="space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5 flex flex-wrap gap-2">
                    <input v-model="eQ" @keyup.enter="applyEnquiryFilters" type="text" placeholder="Search name, email, phone..."
                        class="flex-1 min-w-[200px] rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    <select v-model="eStatus" @change="applyEnquiryFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All statuses</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="qualified">Qualified</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div class="space-y-3">
                    <div v-for="e in allEnquiries.data" :key="e.id" class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ channelIcons[e.channel] || '💬' }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ e.name || 'Anonymous' }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full capitalize" :class="enquiryStatusColors[e.status]">{{ e.status }}</span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    <template v-if="e.property">{{ e.property.title }} · </template>{{ e.email || 'no email' }}<template v-if="e.phone"> · {{ e.phone }}</template>
                                </div>
                                <p v-if="e.message" class="text-sm text-gray-700 dark:text-gray-300 mt-2 italic">"{{ e.message }}"</p>
                                <div class="text-xs text-gray-400 mt-1">{{ e.created_at }}</div>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                <button v-for="s in ['new', 'contacted', 'qualified', 'closed']" :key="s" @click="setEnquiryStatus(e, s)"
                                    class="text-xs px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-700 capitalize hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                                    :class="e.status === s ? 'bg-[#A8E46A] text-[#232126] border-[#A8E46A]' : 'text-gray-500 dark:text-gray-400'">
                                    {{ s }}
                                </button>
                                <button @click="deleteEnquiry(e)" class="text-xs px-3 py-1.5 rounded-full text-red-500 hover:text-red-700 border border-red-200 dark:border-red-800">Delete</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="allEnquiries.data.length === 0" class="bg-white dark:bg-gray-900 rounded-xl shadow p-10 text-center text-gray-400">No enquiries found.</div>
                </div>

                <div class="flex justify-center gap-2">
                    <Link v-if="allEnquiries.prev_page_url" :href="allEnquiries.prev_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">← Prev</Link>
                    <span class="px-3 py-1.5 text-sm text-gray-500">{{ allEnquiries.current_page }} / {{ allEnquiries.last_page }}</span>
                    <Link v-if="allEnquiries.next_page_url" :href="allEnquiries.next_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">Next →</Link>
                </div>
            </div>

            <!-- ============ TAB: TOURS (merged admin) ============ -->
            <div v-else-if="activeTab === 'tours'" class="space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5 flex flex-wrap gap-2">
                    <input v-model="tQ" @keyup.enter="applyTourFilters" type="text" placeholder="Search name, email, phone..."
                        class="flex-1 min-w-[200px] rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    <select v-model="tStatus" @change="applyTourFilters" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="space-y-3">
                    <div v-for="t in allTours.data" :key="t.id" class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-lg">🗓️</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ t.name || 'Anonymous' }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full capitalize" :class="tourStatusColors[t.status]">{{ t.status }}</span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    <template v-if="t.property">{{ t.property.title }} · </template>
                                    {{ t.email || 'no email' }}<template v-if="t.phone"> · {{ t.phone }}</template>
                                </div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">
                                    📅 {{ t.preferred_date }} at {{ t.preferred_time }}
                                </div>
                                <p v-if="t.message" class="text-sm text-gray-600 dark:text-gray-300 mt-2 italic">"{{ t.message }}"</p>
                                <div class="text-xs text-gray-400 mt-1">{{ t.created_at }}</div>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                <button v-for="s in ['pending', 'confirmed', 'completed', 'cancelled']" :key="s" @click="setTourStatus(t, s)"
                                    class="text-xs px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-700 capitalize hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                                    :class="t.status === s ? 'bg-[#A8E46A] text-[#232126] border-[#A8E46A]' : 'text-gray-500 dark:text-gray-400'">
                                    {{ s }}
                                </button>
                                <button @click="deleteTour(t)" class="text-xs px-3 py-1.5 rounded-full text-red-500 hover:text-red-700 border border-red-200 dark:border-red-800">Delete</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="allTours.data.length === 0" class="bg-white dark:bg-gray-900 rounded-xl shadow p-10 text-center text-gray-400">No tour requests found.</div>
                </div>

                <div class="flex justify-center gap-2">
                    <Link v-if="allTours.prev_page_url" :href="allTours.prev_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">← Prev</Link>
                    <span class="px-3 py-1.5 text-sm text-gray-500">{{ allTours.current_page }} / {{ allTours.last_page }}</span>
                    <Link v-if="allTours.next_page_url" :href="allTours.next_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">Next →</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
