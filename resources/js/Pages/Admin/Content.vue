<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    contents: Array,
    groups: Array,
});

const page = usePage();
const saving = ref(false);

// Build a form model grouped by section
const form = reactive({});

props.contents.forEach((c) => {
    form[c.key] = c.value || '';
});

const byGroup = computed(() => {
    const groups = {};
    props.contents.forEach((c) => {
        const g = c.group || 'general';
        if (!groups[g]) groups[g] = [];
        groups[g].push(c);
    });
    return groups;
});

const groupLabels = {
    hero: '🏠 Hero Section',
    stats: '📊 Stats Strip',
    contact: '📞 Contact & Business',
    footer: '🦶 Footer',
    seo: '🔎 SEO',
    general: '📄 General',
};

function save() {
    saving.value = true;
    router.post('/dashboard/content', {
        contents: props.contents.map((c) => ({
            key: c.key,
            value: form[c.key],
            group: c.group || 'general',
        })),
    }, {
        preserveScroll: true,
        onSuccess: () => { saving.value = false; },
        onError: () => { saving.value = false; },
    });
}

watch(() => page.props.flash?.success, (v) => {
    if (v) {
        saving.value = false;
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Content — Site Management | Edenire.co.tz" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Site Content</h2>
                <ThemeToggle />
            </div>
        </template>

        <div class="py-8 space-y-6">
            <!-- Admin nav -->
            <div class="flex gap-2 flex-wrap bg-white dark:bg-gray-900 rounded-xl shadow p-3">
                <Link href="/admin" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Dashboard</Link>
                <Link href="/admin/properties" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Properties</Link>
                <Link href="/admin/properties/create" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">+ New Property</Link>
                <Link href="/admin/enquiries" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Enquiries</Link>
                <Link href="/dashboard/content" class="px-4 py-2 rounded-lg text-sm font-semibold bg-[#A8E46A] text-[#232126]">Content</Link>
            </div>

            <div v-if="page.props.flash?.success"
                class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3 text-sm">
                ✅ {{ page.props.flash.success }}
            </div>

            <div class="space-y-6">
                <div v-for="(items, group) in byGroup" :key="group"
                    class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ groupLabels[group] || group }}</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">These appear on the public site — changes go live immediately.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="c in items" :key="c.key" class="space-y-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ c.key.replace(/_/g, ' ') }}</label>
                            <textarea v-if="(c.value || '').length > 60" v-model="form[c.key]" rows="3"
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 text-sm"></textarea>
                            <input v-else v-model="form[c.key]" type="text"
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 text-sm" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-4">
                <button @click="save" :disabled="saving"
                    class="w-full bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] font-bold px-8 py-3.5 rounded-xl transition text-lg disabled:opacity-60">
                    {{ saving ? 'Saving…' : '💾 Save Site Content' }}
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
