<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    properties: Object,
    filters: Object,
    filterOptions: Object,
});

const q = ref(props.filters.q || '');
const sort = ref(props.filters.sort || '');
const type = ref(props.filters.type || '');
const listing = ref(props.filters.listing || '');
const region = ref(props.filters.region || '');
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');
const bedrooms = ref(props.filters.bedrooms || '');
const bathrooms = ref(props.filters.bathrooms || '');
const minArea = ref(props.filters.min_area || '');
const radiusKm = ref(props.filters.radius_km || '');
const lat = ref(props.filters.lat || '');
const lng = ref(props.filters.lng || '');

const showMore = ref(false);

function buildParams() {
    const p = {};
    if (q.value) p.q = q.value;
    if (sort.value) p.sort = sort.value;
    if (type.value) p.type = type.value;
    if (listing.value) p.listing = listing.value;
    if (region.value) p.region = region.value;
    if (minPrice.value) p.min_price = minPrice.value;
    if (maxPrice.value) p.max_price = maxPrice.value;
    if (bedrooms.value) p.bedrooms = bedrooms.value;
    if (bathrooms.value) p.bathrooms = bathrooms.value;
    if (minArea.value) p.min_area = minArea.value;
    if (radiusKm.value) p.radius_km = radiusKm.value;
    if (lat.value) p.lat = lat.value;
    if (lng.value) p.lng = lng.value;
    return p;
}

function applyFilters() {
    router.get('/properties', buildParams(), { preserveState: true, replace: true });
}

function resetFilters() {
    q.value = ''; sort.value = ''; type.value = ''; listing.value = ''; region.value = '';
    minPrice.value = ''; maxPrice.value = ''; bedrooms.value = ''; bathrooms.value = ''; minArea.value = '';
    radiusKm.value = ''; lat.value = ''; lng.value = '';
    router.get('/properties', {}, { preserveState: true, replace: true });
}

// "Near me" — uses browser geolocation then sorts by distance
const locating = ref(false);
const geoError = ref('');

function nearMe() {
    if (!navigator.geolocation) {
        geoError.value = 'Geolocation not supported in this browser.';
        return;
    }
    locating.value = true;
    geoError.value = '';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            lat.value = pos.coords.latitude.toFixed(6);
            lng.value = pos.coords.longitude.toFixed(6);
            if (!radiusKm.value) radiusKm.value = '50';
            sort.value = 'distance';
            locating.value = false;
            applyFilters();
        },
        (err) => {
            locating.value = false;
            geoError.value = err.code === 1 ? 'Location permission denied.' : 'Could not get location.';
        },
        { enableHighAccuracy: true, timeout: 10000 },
    );
}

const types = [
    { value: '', label: 'All property types' },
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

const sortOptions = [
    { value: '', label: 'Sort: Recommended' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'title_asc', label: 'Alphabetical: A–Z' },
    { value: 'title_desc', label: 'Alphabetical: Z–A' },
    { value: 'newest', label: 'Newest First' },
    { value: 'views', label: 'Most Viewed' },
    { value: 'area_desc', label: 'Largest Area' },
    { value: 'distance', label: 'Closest to Location' },
];

const bedOptions = [
    { value: '', label: 'Any bedrooms' },
    { value: '1', label: '1+' },
    { value: '2', label: '2+' },
    { value: '3', label: '3+' },
    { value: '4', label: '4+' },
    { value: '5', label: '5+' },
];

const bathOptions = [
    { value: '', label: 'Any bathrooms' },
    { value: '1', label: '1+' },
    { value: '2', label: '2+' },
    { value: '3', label: '3+' },
    { value: '4', label: '4+' },
];

const radiusOptions = [
    { value: '', label: 'Any distance' },
    { value: '5', label: 'Within 5 km' },
    { value: '10', label: 'Within 10 km' },
    { value: '25', label: 'Within 25 km' },
    { value: '50', label: 'Within 50 km' },
    { value: '100', label: 'Within 100 km' },
];

const hasActiveFilters = computed(() => Object.keys(buildParams()).length > 0);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <Head title="Browse Properties — Houses, Apartments, Land & Commercial in Tanzania">
            <meta name="description" content="Browse houses, apartments, land, and commercial properties for sale and rent across Tanzania — Dar es Salaam, Arusha, Zanzibar and more. Enquire on WhatsApp." />
        </Head>

        <header class="bg-white shadow-sm dark:bg-gray-900 dark:shadow-gray-950">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="text-xl font-bold text-[#70A83C] dark:text-[#A8E46A]">Edenire.co.tz</Link>
                <nav class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <Link href="/properties" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Properties</Link>
                    <Link v-if="$page.props.auth?.user" href="/dashboard" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Dashboard</Link>
                    <Link v-else href="/login" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Sign in</Link>
                    <ThemeToggle />
                </nav>
            </div>
        </header>

        <!-- Hero with search -->
        <div class="bg-[#232126] dark:bg-[#232126] text-white">
            <div class="max-w-7xl mx-auto px-4 py-12 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold mb-3">Find Your Home in Tanzania</h1>
                <p class="text-lg mb-6 text-gray-300">Browse houses, apartments, land, and commercial properties across the country.</p>
                <div class="flex gap-2 justify-center flex-wrap">
                    <input v-model="q" @keyup.enter="applyFilters" type="text" placeholder="Search city, region, area, or keyword..."
                        class="rounded-lg px-4 py-3 text-gray-900 w-full max-w-md" />
                    <button @click="applyFilters" class="bg-[#A8E46A] hover:bg-[#8CC84F] px-6 py-3 rounded-lg font-semibold transition">
                        Search
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Filter bar -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4 mb-6">
                <div class="flex flex-wrap gap-3 items-end">
                    <!-- Sort -->
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Sort by</label>
                        <select v-model="sort" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                            <option v-for="s in sortOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>
                    <!-- Type -->
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Property type</label>
                        <select v-model="type" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                            <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>
                    <!-- Listing -->
                    <div class="flex-1 min-w-[130px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Listing</label>
                        <select v-model="listing" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                            <option v-for="l in listings" :key="l.value" :value="l.value">{{ l.label }}</option>
                        </select>
                    </div>
                    <!-- Region -->
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Region</label>
                        <select v-model="region" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                            <option value="">All regions</option>
                            <option v-for="r in filterOptions.regions" :key="r" :value="r">{{ r }}</option>
                        </select>
                    </div>
                    <!-- Near me -->
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Location</label>
                        <button @click="nearMe" :disabled="locating"
                            class="w-full rounded-lg border border-[#A8E46A] text-[#70A83C] dark:text-[#A8E46A] font-semibold text-sm px-3 py-2 hover:bg-[#A8E46A]/5 transition disabled:opacity-50">
                            {{ locating ? 'Locating...' : '📍 Near me' }}
                        </button>
                    </div>
                    <!-- Radius -->
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Radius</label>
                        <select v-model="radiusKm" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                            <option v-for="r in radiusOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
                        </select>
                    </div>
                    <!-- More filters toggle -->
                    <div class="flex items-end">
                        <button @click="showMore = !showMore"
                            class="rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            {{ showMore ? '− Fewer' : '+ More' }}
                        </button>
                    </div>
                    <div class="flex items-end">
                        <button v-if="hasActiveFilters" @click="resetFilters"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-red-500 hover:text-red-700">
                            ✕ Clear
                        </button>
                    </div>
                </div>

                <!-- Advanced filters -->
                <div v-if="showMore" class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 border-t dark:border-gray-700 pt-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Min price</label>
                        <input v-model="minPrice" @change="applyFilters" type="number" min="0" placeholder="e.g. 50000000"
                            class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Max price</label>
                        <input v-model="maxPrice" @change="applyFilters" type="number" min="0" placeholder="e.g. 500000000"
                            class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Bedrooms</label>
                        <select v-model="bedrooms" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                            <option v-for="b in bedOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Bathrooms</label>
                        <select v-model="bathrooms" @change="applyFilters" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                            <option v-for="b in bathOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Min area (m²)</label>
                        <input v-model="minArea" @change="applyFilters" type="number" min="0" placeholder="e.g. 100"
                            class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    </div>
                    <!-- Manual lat/lng (fallback if geolocation denied) -->
                    <div class="col-span-2 md:col-span-3 flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Latitude</label>
                            <input v-model="lat" type="text" placeholder="e.g. -3.3833"
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Longitude</label>
                            <input v-model="lng" type="text" placeholder="e.g. 36.6833"
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <button @click="applyFilters" class="bg-[#A8E46A] text-[#232126] rounded-lg px-4 py-2 text-sm font-semibold whitespace-nowrap">
                            Apply
                        </button>
                    </div>
                </div>

                <p v-if="geoError" class="text-xs text-red-500 mt-2">{{ geoError }}</p>
            </div>

            <!-- Results count -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ properties.total }} propert{{ properties.total === 1 ? 'y' : 'ies' }} found
                <span v-if="sort === 'distance'" class="ml-2 text-[#70A83C] dark:text-[#A8E46A]">· sorted by distance</span>
            </div>

            <div v-if="properties.data.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
                No properties match your filters. Try widening the search.
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link v-for="property in properties.data" :key="property.id" :href="property.public_url"
                    class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden dark:bg-gray-900 dark:shadow-gray-950">
                    <img v-if="property.primary_image_url" :src="property.primary_image_url" :alt="property.title"
                        class="h-48 w-full object-cover" />
                    <div v-else class="h-48 bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 capitalize">
                        {{ property.property_type }}
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-2">
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ property.title || 'Property' }}</h3>
                            <span class="text-[#70A83C] dark:text-[#A8E46A] font-bold whitespace-nowrap">{{ property.price_label }}</span>
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
                <span class="px-4 py-2 text-sm text-gray-500">{{ properties.current_page }} / {{ properties.last_page }}</span>
                <Link v-if="properties.next_page_url" :href="properties.next_page_url" class="px-4 py-2 bg-[#A8E46A] text-[#232126] rounded-lg">Next →</Link>
            </div>
        </div>
    </div>
</template>
