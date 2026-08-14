<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    enquiries: Object,
    filters: Object,
});

const q = ref(props.filters.q || '');
const f = ref({ status: props.filters.status || '' });

function apply() {
    router.get('/admin/enquiries', { q: q.value, ...f.value }, { preserveState: true, replace: true });
}

function setStatus(e, status) {
    router.patch(`/admin/enquiries/${e.id}`, { status }, { preserveScroll: true });
}

function del(e) {
    if (confirm(`Delete enquiry from ${e.name || 'anonymous'}?`)) {
        router.delete(`/admin/enquiries/${e.id}`, { preserveScroll: true });
    }
}

const statusColors = {
    new: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    contacted: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    qualified: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    closed: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
};

const channelIcons = { whatsapp: '💬', web_form: '📝', email: '✉️', phone: '📞', in_app: '💬' };
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Admin — Enquiries | Edenire.co.tz" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Manage Enquiries</h2>
                <ThemeToggle />
            </div>
        </template>

        <div class="py-8 space-y-6">
            <div class="flex gap-2 flex-wrap bg-white dark:bg-gray-900 rounded-xl shadow p-3">
                <Link href="/admin" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Dashboard</Link>
                <Link href="/admin/properties" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Properties</Link>
                <Link href="/admin/properties/create" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">+ New Property</Link>
                <Link href="/admin/enquiries" class="px-4 py-2 rounded-lg text-sm font-semibold bg-[#A8E46A] text-[#232126]">Enquiries</Link>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-5 flex flex-wrap gap-2">
                <input v-model="q" @keyup.enter="apply" type="text" placeholder="Search name, email, phone..."
                    class="flex-1 min-w-[200px] rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                <select v-model="f.status" @change="apply" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                    <option value="">All statuses</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="qualified">Qualified</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <!-- Enquiry list -->
            <div class="space-y-3">
                <div v-for="e in enquiries.data" :key="e.id" class="bg-white dark:bg-gray-900 rounded-xl shadow p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ channelIcons[e.channel] || '💬' }}</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ e.name || 'Anonymous' }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full capitalize" :class="statusColors[e.status]">{{ e.status }}</span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <template v-if="e.property">{{ e.property.title }} · </template>{{ e.email || 'no email' }}<template v-if="e.phone"> · {{ e.phone }}</template>
                            </div>
                            <p v-if="e.message" class="text-sm text-gray-700 dark:text-gray-300 mt-2 italic">"{{ e.message }}"</p>
                            <div class="text-xs text-gray-400 mt-1">{{ e.created_at }}</div>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <button v-for="s in ['new', 'contacted', 'qualified', 'closed']" :key="s"
                                @click="setStatus(e, s)"
                                class="text-xs px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-700 capitalize hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                                :class="e.status === s ? 'bg-[#A8E46A] text-[#232126] border-[#A8E46A]' : 'text-gray-500 dark:text-gray-400'">
                                {{ s }}
                            </button>
                            <button @click="del(e)" class="text-xs px-3 py-1.5 rounded-full text-red-500 hover:text-red-700 border border-red-200 dark:border-red-800">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="enquiries.data.length === 0" class="bg-white dark:bg-gray-900 rounded-xl shadow p-10 text-center text-gray-400">
                    No enquiries found.
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center gap-2">
                <Link v-if="enquiries.prev_page_url" :href="enquiries.prev_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">← Prev</Link>
                <span class="px-3 py-1.5 text-sm text-gray-500">{{ enquiries.current_page }} / {{ enquiries.last_page }}</span>
                <Link v-if="enquiries.next_page_url" :href="enquiries.next_page_url" class="px-3 py-1.5 bg-white dark:bg-gray-900 rounded-lg text-sm">Next →</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
