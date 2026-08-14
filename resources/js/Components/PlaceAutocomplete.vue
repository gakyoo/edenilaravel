<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: String,
    placeholder: { type: String, default: 'Search an address, area, or place...' },
    country: { type: String, default: 'Tanzania' },
});

const emit = defineEmits(['update:modelValue', 'select']);

const query = ref(props.modelValue || '');
const results = ref([]);
const open = ref(false);
const loading = ref(false);
const error = ref('');
let debounceTimer = null;

watch(query, (val) => {
    emit('update:modelValue', val);
    clearTimeout(debounceTimer);
    if (!val || val.trim().length < 3) {
        results.value = [];
        open.value = false;
        return;
    }
    loading.value = true;
    error.value = '';
    debounceTimer = setTimeout(search, 400);
});

async function search() {
    const url = new URL('https://nominatim.openstreetmap.org/search');
    url.searchParams.set('format', 'json');
    url.searchParams.set('q', query.value.trim());
    url.searchParams.set('limit', '6');
    url.searchParams.set('countrycodes', 'tz');
    url.searchParams.set('addressdetails', '1');

    try {
        const res = await fetch(url, {
            headers: { 'User-Agent': 'Edenire.co.tz/1.0 (real estate platform)' },
        });
        if (!res.ok) throw new Error('Search failed');
        results.value = await res.json();
        open.value = results.value.length > 0;
    } catch (e) {
        error.value = 'Could not reach OpenStreetMap. Check connection.';
        results.value = [];
    } finally {
        loading.value = false;
    }
}

function selectPlace(place) {
    query.value = place.display_name;
    results.value = [];
    open.value = false;
    emit('select', place);
}

function onBlur() {
    // Delay so click on a suggestion registers first
    setTimeout(() => { open.value = false; }, 200);
}
</script>

<template>
    <div class="relative">
        <div class="relative">
            <input
                v-model="query"
                type="text"
                :placeholder="placeholder"
                @focus="open = results.length > 0"
                @blur="onBlur"
                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 pr-9"
            />
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                {{ loading ? '…' : '🔍' }}
            </span>
        </div>

        <!-- Suggestions dropdown -->
        <div v-if="open && results.length" class="absolute z-30 mt-1 w-full bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto">
            <button
                v-for="r in results"
                :key="r.place_id"
                type="button"
                @mousedown.prevent="selectPlace(r)"
                class="block w-full text-left px-3 py-2.5 hover:bg-[#A8E46A]/10 text-sm text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-gray-800 last:border-0 transition"
            >
                <span class="block font-medium">{{ r.name || r.display_name.split(',')[0] }}</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">{{ r.display_name }}</span>
            </button>
        </div>

        <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
    </div>
</template>
