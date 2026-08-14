<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
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
});

// ---------- Filters ----------
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
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    sold: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    rented: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
    off_market: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
};

const alertStyles = {
    info: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800',
    warning: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800',
    danger: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-200 dark:border-red-800',
};

const taskTypeIcons = { follow_up: '📞', document: '📄', inspection: '🔍', showing: '🏠', general: '📌' };

// ---------- Analytics ----------
const maxViews = computed(() => Math.max(1, ...props.topListings.map((t) => t.views)));

// ---------- Formatting ----------
function formatTZS(value) {
    if (!value) return '0 TZS';
    return Number(value).toLocaleString() + ' TZS';
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Dashboard</h2>
                <ThemeToggle />
            </div>
        </template>

        <div class="py-8">
            <!-- ⚠️ Quick alerts -->
            <div v-if="alerts.length" class="mb-6 space-y-2">
                <div v-for="(a, i) in alerts" :key="i"
                    class="rounded-lg border px-4 py-3 text-sm font-medium"
                    :class="alertStyles[a.level]">
                    ⚡ {{ a.text }}
                </div>
            </div>

            <!-- 📊 Key metrics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Properties</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ metrics.total_properties }}</div>
                    <div class="text-xs text-gray-400">{{ metrics.active_listings }} active</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Active Listings</div>
                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ metrics.active_listings }}</div>
                    <div class="text-xs text-gray-400">{{ metrics.pending_sales }} pending</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Value (TZS)</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 truncate">{{ formatTZS(metrics.total_value) }}</div>
                    <div class="text-xs text-gray-400">{{ metrics.sold }} sold/rented</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Engagement</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ metrics.total_views }}</div>
                    <div class="text-xs text-gray-400">{{ metrics.new_enquiries }} new enquiries · {{ metrics.open_tasks }} tasks</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 🏠 Property snapshot + filters -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Property Snapshot</h3>
                            <a :href="'/admin/properties/create'" target="_blank"
                                class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                + Add Property
                            </a>
                        </div>

                        <!-- Search & filter bar -->
                        <div class="space-y-3 mb-4">
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
                                <input v-model="f.min_price" @change="applyFilters" type="number" placeholder="Min price"
                                    class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                                <input v-model="f.max_price" @change="applyFilters" type="number" placeholder="Max price"
                                    class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                                <button @click="resetFilters" class="rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    Reset filters
                                </button>
                            </div>
                        </div>

                        <!-- Property list -->
                        <div class="space-y-3">
                            <div v-for="p in properties.data" :key="p.id"
                                class="flex items-center gap-4 border dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <img v-if="p.primary_image_url" :src="p.primary_image_url" :alt="p.title"
                                    class="h-14 w-14 rounded-lg object-cover shrink-0" />
                                <div v-else class="h-14 w-14 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 text-xs shrink-0 capitalize">
                                    {{ p.property_type }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ p.title || 'Untitled' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ p.city }}{{ p.region ? ', ' + p.region : '' }} · {{ p.price_label }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap capitalize" :class="statusStyles[p.status]">
                                    {{ p.status.replace('_', ' ') }}
                                </span>
                                <div class="flex gap-1 shrink-0">
                                    <a :href="`/properties/${p.id}`" target="_blank" class="text-xs text-gray-500 hover:text-emerald-700 dark:hover:text-emerald-400 px-2 py-1">View</a>
                                    <a :href="`/admin/properties/${p.id}/edit`" target="_blank" class="text-xs text-gray-500 hover:text-emerald-700 dark:hover:text-emerald-400 px-2 py-1">Edit</a>
                                </div>
                            </div>
                            <div v-if="properties.data.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No properties match your filters.
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="flex justify-center gap-2 mt-4">
                            <Link v-if="properties.prev_page_url" :href="properties.prev_page_url" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm">← Prev</Link>
                            <span class="px-3 py-1.5 text-sm text-gray-500">{{ properties.current_page }} / {{ properties.last_page }}</span>
                            <Link v-if="properties.next_page_url" :href="properties.next_page_url" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm">Next →</Link>
                        </div>
                    </div>

                    <!-- 📈 Analytics widget -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Top-Performing Listings</h3>
                        <div class="space-y-3">
                            <div v-for="t in topListings" :key="t.title" class="flex items-center gap-3">
                                <div class="w-40 truncate text-sm text-gray-600 dark:text-gray-300">{{ t.title }}</div>
                                <div class="flex-1 h-6 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-600 dark:bg-emerald-500 rounded-full transition-all"
                                        :style="{ width: (t.views / maxViews) * 100 + '%' }"></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 w-20 text-right">{{ t.views }} views · {{ t.enquiries }} inq.</div>
                            </div>
                            <div v-if="topListings.length === 0" class="text-sm text-gray-400">No views yet — share your listings!</div>
                        </div>
                    </div>

                    <!-- 📋 Tasks -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tasks & Reminders</h3>
                            <button @click="showTaskForm = !showTaskForm"
                                class="text-sm text-emerald-700 dark:text-emerald-400 font-semibold hover:underline">
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
                                <input v-model="taskForm.due_date" type="date"
                                    class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                                <button type="submit" class="bg-emerald-700 text-white rounded-lg text-sm font-semibold">Add</button>
                            </div>
                        </form>

                        <div class="space-y-2">
                            <div v-for="t in tasks" :key="t.id" class="flex items-center gap-3 border dark:border-gray-700 rounded-lg p-3">
                                <button @click="toggleTask(t)" class="shrink-0 text-lg"
                                    :class="t.status === 'done' ? 'opacity-40' : ''">
                                    {{ t.status === 'done' ? '✅' : '⬜' }}
                                </button>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 line-through decoration-gray-400" :class="{ 'line-through opacity-40': t.status === 'done' }">
                                        {{ taskTypeIcons[t.type] }} {{ t.title }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ t.priority }}<template v-if="t.due_date"> · due {{ t.due_date }}</template><template v-if="t.property"> · {{ t.property.title }}</template>
                                    </div>
                                </div>
                                <button @click="deleteTask(t)" class="text-xs text-red-500 hover:text-red-700 shrink-0">Delete</button>
                            </div>
                            <div v-if="tasks.length === 0" class="text-sm text-gray-400 text-center py-4">No tasks yet. Add follow-ups, document deadlines, and inspections.</div>
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="space-y-6">
                    <!-- 🕐 Recent activity -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>
                        <div class="space-y-3">
                            <div v-for="(a, i) in activity" :key="i" class="flex items-start gap-3">
                                <span class="text-lg">{{ a.type === 'enquiry' ? '💬' : '🏠' }}</span>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ a.label }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ a.detail }}</div>
                                    <div class="text-[10px] text-gray-400">{{ a.time }}</div>
                                </div>
                            </div>
                            <div v-if="activity.length === 0" class="text-sm text-gray-400 text-center py-4">No recent activity.</div>
                        </div>
                    </div>

                    <!-- 💬 Leads -->
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

                    <!-- 💰 Financial snapshot -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Financial Snapshot</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Total listing value</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatTZS(financial.total_listing_value_tzs) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Average price</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatTZS(financial.avg_price_tzs) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Rental value (rent listings)</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatTZS(financial.rental_income_tzs) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">USD listings</span><span class="font-semibold text-gray-900 dark:text-gray-100">{{ financial.usd_listings }}</span></div>
                        </div>
                    </div>

                    <!-- 📅 Upcoming -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Upcoming Deadlines</h3>
                        <div class="space-y-2">
                            <div v-for="u in upcoming" :key="u.id" class="flex items-center justify-between text-sm border dark:border-gray-700 rounded-lg p-2.5">
                                <span class="text-gray-700 dark:text-gray-300 truncate">{{ u.title }}</span>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 whitespace-nowrap ml-2">{{ u.due_date }}</span>
                            </div>
                            <div v-if="upcoming.length === 0" class="text-sm text-gray-400 text-center py-4">Nothing scheduled.</div>
                        </div>
                    </div>

                    <!-- ⚡ Quick actions -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-1 gap-2">
                            <a href="/admin/properties/create" target="_blank" class="bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg px-4 py-2.5 text-sm font-semibold text-center transition">
                                + Add New Property
                            </a>
                            <a :href="'/dashboard/export?status=' + f.status + '&region=' + f.region" class="border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg px-4 py-2.5 text-sm font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                ⬇ Export CSV
                            </a>
                            <a href="/admin" target="_blank" class="border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg px-4 py-2.5 text-sm font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                ⚙ Admin Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
