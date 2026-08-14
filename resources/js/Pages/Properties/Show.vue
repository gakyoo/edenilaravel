<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import PropertyMap from '@/Components/PropertyMap.vue';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    property: Object,
    similar: Array,
    favoriteIds: Array,
});

const isFavorite = computed(() => props.favoriteIds?.includes(Number(props.property.id)));

// ---------- Tour scheduling ----------
const tourOpen = ref(false);
const tourSent = ref(false);
const tourForm = useForm({
    name: '',
    email: '',
    phone: '',
    preferred_date: '',
    preferred_time: '',
    message: '',
});

function openTourModal() {
    const u = $page.props.auth?.user;
    if (u) {
        tourForm.name = tourForm.name || u.name || '';
        tourForm.email = tourForm.email || u.email || '';
        tourForm.phone = tourForm.phone || u.phone || '';
    }
    tourSent.value = false;
    tourOpen.value = true;
}

function submitTour() {
    tourForm.post(`/properties/${props.property.id}/tour`, {
        preserveScroll: true,
        onSuccess: () => {
            tourSent.value = true;
            tourOpen.value = false;
            tourForm.reset();
        },
    });
}

const todayStr = new Date().toISOString().split('T')[0];

// ---------- Gallery ----------
const images = computed(() => {
    const list = props.property.media?.length
        ? props.property.media.map((m) => '/storage/' + m.path)
        : (props.property.primary_image_url ? [props.property.primary_image_url] : []);
    return list;
});

const activeIndex = ref(0);
const activeImage = computed(() => images.value[activeIndex.value] || null);

function prev() {
    activeIndex.value = (activeIndex.value - 1 + images.value.length) % images.value.length;
}
function next() {
    activeIndex.value = (activeIndex.value + 1) % images.value.length;
}

// ---------- Lightbox ----------
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);
const largeMapOpen = ref(false);

function openLightbox(index = 0) {
    if (!images.value.length) return;
    lightboxIndex.value = index;
    activeIndex.value = index;
    lightboxOpen.value = true;
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    lightboxOpen.value = false;
    document.body.style.overflow = '';
}

function lightboxPrev() {
    lightboxIndex.value = (lightboxIndex.value - 1 + images.value.length) % images.value.length;
    activeIndex.value = lightboxIndex.value;
}

function lightboxNext() {
    lightboxIndex.value = (lightboxIndex.value + 1) % images.value.length;
    activeIndex.value = lightboxIndex.value;
}

// Keyboard: Esc closes, arrows navigate (bind on mount/unmount)
function onKey(e) {
    if (!lightboxOpen.value) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lightboxPrev();
    if (e.key === 'ArrowRight') lightboxNext();
}
onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    document.body.style.overflow = '';
});

// ---------- WhatsApp ----------
const waLink = computed(() => {
    const phone = props.property.agent?.phone || '255759210560';
    const text = encodeURIComponent(`Hello, I'm interested in ${props.property.title || 'this property'} (${props.property.price_label}) listed on Edenire.co.tz`);
    return `https://wa.me/${phone.replace(/\D/g, '')}?text=${text}`;
});

// ---------- Formatting ----------
const statusLabel = computed(() => props.property.status.replace('_', ' '));
const statusBadgeClass = computed(() => {
    const map = {
        active: 'bg-[#A8E46A]/15 text-[#70A83C] dark:text-[#A8E46A]',
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
        sold: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        rented: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
        off_market: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[props.property.status] || map.active;
});

const listingLabel = computed(() => props.property.listing_type === 'rent' ? 'For Rent' : 'For Sale');

const seoTitle = computed(() => {
    const p = props.property;
    // Dedupe when city and region are the same (e.g. "Arusha, Arusha")
    const loc = [p.city, p.region].filter(Boolean);
    const uniqueLoc = [...new Set(loc)].join(', ');
    return `${p.title || 'Property'}${uniqueLoc ? ' - ' + uniqueLoc : ''} | ${listingLabel.value} | ${p.price_label}`;
});

const pricePerUnit = computed(() => {
    const p = props.property;
    if (!p.price) return null;
    const num = Number(p.price);
    if (p.listing_type === 'rent') return `${num.toLocaleString()} ${p.currency}/mo`;
    if (p.building_area) return `${Math.round(num / p.building_area).toLocaleString()} ${p.currency}/m²`;
    return null;
});

const details = computed(() => {
    const p = props.property;
    return [
        { label: 'Property type', value: p.property_type?.replace('_', ' ') },
        { label: 'Listing type', value: listingLabel.value },
        { label: 'Year built', value: p.year_built },
        { label: 'Bedrooms', value: p.bedrooms },
        { label: 'Bathrooms', value: p.bathrooms },
        { label: 'Building area', value: p.building_area ? `${p.building_area} m²` : null },
        { label: 'Lot size', value: p.lot_size ? `${p.lot_size} m²` : null },
        { label: 'Stories', value: p.stories },
        { label: 'Parking', value: p.parking_spaces ? `${p.parking_spaces} spaces` : null },
        { label: 'Zoning', value: p.zoning_classification },
        { label: 'Negotiable', value: p.is_negotiable ? 'Yes' : 'No' },
    ].filter((d) => d.value !== null && d.value !== undefined && d.value !== '');
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <Head :title="seoTitle">
            <meta name="description" :content="`${property.title || 'Property'} in ${property.city || property.region || 'Tanzania'} — ${property.price_label}. ${property.description || ''}`.slice(0, 160)" />
            <meta property="og:title" :content="seoTitle" />
            <meta property="og:image" :content="property.primary_image_url" />
        </Head>
        <!-- Top nav -->
        <header class="bg-white dark:bg-gray-900 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="flex items-center"><img src="/img/logo.svg" alt="EdeniRE" class="h-9 w-auto" /></Link>
                <nav class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <Link href="/properties" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Properties</Link>
                    <Link v-if="$page.props.auth?.user" href="/favorites" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">❤️ Saved</Link>
                    <Link v-if="$page.props.auth?.user" href="/dashboard" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Dashboard</Link>
                    <Link v-else href="/login" class="hover:text-[#70A83C] dark:hover:text-[#A8E46A]">Sign in</Link>
                    <ThemeToggle />
                </nav>
            </div>
        </header>

        <!-- Breadcrumb (carries filters) -->
        <div class="max-w-7xl mx-auto px-4 pt-4 text-xs text-gray-500 dark:text-gray-400">
            <Link href="/properties" class="hover:underline">Tanzania</Link>
            <span class="mx-1">›</span>
            <Link v-if="property.region" :href="`/properties?region=${encodeURIComponent(property.region)}`" class="hover:underline">
                {{ property.region }}
            </Link>
            <template v-if="property.region"><span class="mx-1">›</span></template>
            <Link v-if="property.city" :href="`/properties?region=${encodeURIComponent(property.region || '')}&city=${encodeURIComponent(property.city)}`" class="hover:underline">
                {{ property.city }}
            </Link>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Photo gallery (Zillow-style hero) -->
            <div class="relative bg-gray-900 rounded-2xl overflow-hidden" v-if="activeImage">
                <img :src="activeImage" :alt="property.title" class="w-full h-[320px] sm:h-[440px] object-cover cursor-zoom-in"
                    @click="openLightbox(activeIndex)" />
                <button @click="prev"
                    class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full w-10 h-10 flex items-center justify-center text-xl transition">‹</button>
                <button @click="next"
                    class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full w-10 h-10 flex items-center justify-center text-xl transition">›</button>
                <span class="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-2.5 py-1 rounded-full">
                    {{ activeIndex + 1 }} / {{ images.length }}
                </span>
                <button @click="openLightbox(activeIndex)"
                    class="absolute bottom-3 left-3 bg-black/60 hover:bg-black/80 text-white text-xs px-2.5 py-1.5 rounded-full transition">
                    ⛶ Fullscreen
                </button>
            </div>
            <div v-else class="h-[320px] sm:h-[440px] bg-gray-200 dark:bg-gray-800 rounded-2xl flex items-center justify-center text-gray-400 capitalize">
                {{ property.property_type }} — No photos yet
            </div>

            <!-- Thumbnail strip -->
            <div v-if="images.length > 1" class="flex gap-2 mt-3 overflow-x-auto pb-1">
                <button v-for="(img, i) in images" :key="i" @click="openLightbox(i)"
                    class="shrink-0 rounded-lg overflow-hidden transition"
                    :class="i === activeIndex ? 'ring-2 ring-[#A8E46A]' : 'opacity-70 hover:opacity-100'">
                    <img :src="img" class="h-14 w-20 object-cover" :alt="`Photo ${i + 1}`" />
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
                <!-- MAIN COLUMN -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Header: status + address + price -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wide px-3 py-1.5 rounded-full"
                                :class="statusBadgeClass">{{ statusLabel }}</span>
                            <span class="text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                {{ listingLabel }}
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ property.title || 'Property' }}
                        </h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">
                            {{ property.address_line }}{{ property.city ? ', ' + property.city : '' }}{{ property.region ? ', ' + property.region : '' }}, {{ property.country }}
                        </p>

                        <div class="mt-4 flex items-end justify-between flex-wrap gap-4">
                            <div>
                                <div class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ property.price_label }}
                                </div>
                                <div v-if="pricePerUnit" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ pricePerUnit }}
                                </div>
                            </div>
                            <div class="text-right text-xs text-gray-400">
                                <div>Listed {{ property.listed_at ? new Date(property.listed_at).toLocaleDateString() : 'recently' }}</div>
                                <div>{{ property.views_count }} views · {{ property.enquiries_count }} enquiries · {{ property.tours_count || 0 }} tours</div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2 flex-wrap">
                            <FavoriteButton :property-id="property.id" :active="isFavorite" />
                            <button type="button" @click="openTourModal"
                                class="bg-[#232126] text-[#A8E46A] px-4 py-2 rounded-xl text-sm font-semibold shadow hover:bg-black transition">
                                🗓️ Schedule a Tour
                            </button>
                        </div>

                        <div v-if="tourSent" class="mt-4 bg-[#A8E46A]/15 text-[#70A83C] dark:text-[#A8E46A] border border-[#A8E46A]/40 rounded-lg px-4 py-3 text-sm font-medium">
                            🎉 Tour requested! We'll confirm your visit soon.
                        </div>
                    </div>

                    <!-- Facts bar (beds/baths/sqft) -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.bedrooms || '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Beds</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.bathrooms || '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Baths</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.building_area || '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">m²</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.lot_size || '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Lot m²</div>
                        </div>
                    </div>

                    <!-- About this home (What's special) -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">About this home</h2>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ property.description || 'No description available for this property yet. Contact the agent for more details.' }}
                        </p>
                    </div>

                    <!-- Facts & features -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Facts & features</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                            <div v-for="d in details" :key="d.label" class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2 text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ d.label }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ d.value }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div v-if="property.amenities && property.amenities.length" class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Amenities</h2>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="a in property.amenities" :key="a"
                                class="bg-[#A8E46A]/10 text-[#70A83C] dark:text-[#A8E46A] px-3 py-1.5 rounded-full text-sm capitalize">{{ a }}</span>
                        </div>
                    </div>

                    <!-- Similar homes -->
                    <div v-if="similar.length" class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Similar homes</h2>
                            <Link href="/properties" class="text-sm text-[#70A83C] dark:text-[#A8E46A] font-semibold hover:underline">See all →</Link>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Link v-for="s in similar" :key="s.id" :href="s.public_url"
                                class="group border dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-lg transition">
                                <img v-if="s.primary_image_url" :src="s.primary_image_url" :alt="s.title"
                                    class="h-36 w-full object-cover group-hover:opacity-90 transition" />
                                <div v-else class="h-36 bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 text-sm capitalize">
                                    {{ s.property_type }}
                                </div>
                                <div class="p-3">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate">{{ s.title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ s.city }}{{ s.region ? ', ' + s.region : '' }}</div>
                                    <div class="text-[#70A83C] dark:text-[#A8E46A] font-bold text-sm mt-1">{{ s.price_label }}</div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR: contact agent (Zillow-style sticky) -->
                <div class="lg:col-span-1">
                    <div class="lg:sticky lg:top-4 space-y-4">
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-4">Contact Agent</h3>
                            <div v-if="property.agent" class="mb-4">
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ property.agent.company_name || property.agent.name || 'Agent' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ property.agent.phone || 'Phone on request' }}
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Edeni Realtors — Arusha, Tanzania
                            </div>

                            <a :href="waLink" target="_blank"
                                class="block w-full bg-[#25D366] hover:bg-[#1EBE5B] text-white font-bold text-center px-4 py-3 rounded-xl transition">
                                💬 WhatsApp
                            </a>
                            <button type="button" @click="openTourModal"
                                class="block w-full mt-2 bg-[#232126] hover:bg-black text-[#A8E46A] font-bold text-center px-4 py-3 rounded-xl transition">
                                🗓️ Schedule a Tour
                            </button>
                            <a :href="`tel:${(property.agent?.phone || '').replace(/\D/g, '')}`"
                                class="block w-full mt-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold text-center px-4 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                📞 Call
                            </a>

                            <div class="mt-4 text-xs text-gray-400 dark:text-gray-500 text-center">
                                Property ID: #{{ property.id }}
                            </div>
                        </div>

                        <!-- Map (OpenStreetMap) -->
                        <div v-if="property.latitude && property.longitude" class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-900 dark:text-gray-100">Location</h3>
                                <button @click="largeMapOpen = true"
                                    class="text-xs font-semibold text-[#70A83C] dark:text-[#A8E46A] hover:underline px-2 py-1 border border-[#70A83C]/30 dark:border-[#A8E46A]/30 rounded-lg transition">
                                    ⛶ View Larger Map
                                </button>
                            </div>
                            <PropertyMap
                                :lat="property.latitude"
                                :lng="property.longitude"
                                :title="property.title || 'Property'"
                                height="240px"
                            />
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-2 text-center">
                                📍 {{ property.latitude }}, {{ property.longitude }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ⛶ Lightbox overlay -->
        <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center" @click.self="closeLightbox">
            <!-- Close -->
            <button @click="closeLightbox"
                class="absolute top-4 right-4 text-white/80 hover:text-white text-4xl w-12 h-12 flex items-center justify-center rounded-full hover:bg-white/10 transition z-10">
                ✕
            </button>

            <!-- Prev / Next -->
            <button @click="lightboxPrev"
                class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white/25 text-white rounded-full w-12 h-12 flex items-center justify-center text-3xl transition z-10">
                ‹
            </button>
            <button @click="lightboxNext"
                class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white/25 text-white rounded-full w-12 h-12 flex items-center justify-center text-3xl transition z-10">
                ›
            </button>

            <!-- Image -->
            <img v-if="images[lightboxIndex]" :src="images[lightboxIndex]" :alt="property.title"
                class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl" />

            <!-- Caption + counter -->
            <div class="absolute bottom-5 left-0 right-0 text-center text-white/80">
                <div class="text-sm font-medium">{{ property.title }}</div>
                <div class="text-xs text-white/50 mt-1">{{ lightboxIndex + 1 }} / {{ images.length }}</div>
            </div>

            <!-- Hint -->
            <div class="absolute top-4 left-4 text-white/40 text-xs hidden sm:block">
                ← → navigate · Esc close
            </div>
        </div>

        <!-- 🗓️ Tour scheduling modal -->
        <Modal :show="tourOpen" max-width="lg" @close="tourOpen = false">
            <div class="p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">🗓️ Schedule a Tour</h3>
                    <button @click="tourOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">✕</button>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                    {{ property.title || 'This property' }} — we'll confirm your visit by phone or WhatsApp.
                </p>

                <form @submit.prevent="submitTour" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Your name *</label>
                            <input v-model="tourForm.name" type="text" required placeholder="e.g. Amina Hassan"
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                            <p v-if="tourForm.errors.name" class="text-xs text-red-500 mt-1">{{ tourForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Phone *</label>
                            <input v-model="tourForm.phone" type="tel" required placeholder="e.g. +255 7xx xxx xxx"
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                            <p v-if="tourForm.errors.phone" class="text-xs text-red-500 mt-1">{{ tourForm.errors.phone }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Email *</label>
                            <input v-model="tourForm.email" type="email" required placeholder="you@example.com"
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                            <p v-if="tourForm.errors.email" class="text-xs text-red-500 mt-1">{{ tourForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Preferred date *</label>
                            <input v-model="tourForm.preferred_date" type="date" :min="todayStr" required
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                            <p v-if="tourForm.errors.preferred_date" class="text-xs text-red-500 mt-1">{{ tourForm.errors.preferred_date }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Preferred time *</label>
                            <input v-model="tourForm.preferred_time" type="time" required
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                            <p v-if="tourForm.errors.preferred_time" class="text-xs text-red-500 mt-1">{{ tourForm.errors.preferred_time }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Message (optional)</label>
                            <textarea v-model="tourForm.message" rows="3" placeholder="Anything we should know? e.g. I'd like to view the garden and parking."
                                class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-1">
                        <button type="submit" :disabled="tourForm.processing"
                            class="flex-1 bg-[#232126] text-[#A8E46A] font-bold px-6 py-3 rounded-xl transition hover:bg-black disabled:opacity-50">
                            {{ tourForm.processing ? 'Sending…' : 'Request Tour' }}
                        </button>
                        <button type="button" @click="tourOpen = false"
                            class="px-4 py-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 text-sm font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ⛶ Fullscreen map modal -->
        <div v-if="largeMapOpen" class="fixed inset-0 z-50 bg-black/90 flex flex-col" @click.self="largeMapOpen = false">
            <div class="flex items-center justify-between px-4 py-3 text-white">
                <div class="font-semibold">{{ property.title || 'Property' }}</div>
                <button @click="largeMapOpen = false" class="text-white/80 hover:text-white text-3xl w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/10 transition">✕</button>
            </div>
            <div class="flex-1 px-4 pb-4">
                <PropertyMap
                    :lat="property.latitude"
                    :lng="property.longitude"
                    :title="property.title || 'Property'"
                    height="100%"
                    :zoom="16"
                />
            </div>
            <div class="px-4 pb-3 text-center text-white/60 text-xs">
                📍 {{ property.latitude }}, {{ property.longitude }} — OpenStreetMap
            </div>
        </div>
    </div>
</template>
