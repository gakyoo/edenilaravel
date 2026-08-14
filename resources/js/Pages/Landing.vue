<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    featured: Array,
    siteContent: Object,
    canLogin: Boolean,
    canRegister: Boolean,
    favoriteIds: Array,
});

// Editable site content (from SiteContent table) with safe fallbacks
const content = computed(() => props.siteContent || {});
const sc = (key, fallback) => content.value[key] || fallback;

// Hero + SEO from content manager
const heroEyebrow = computed(() => sc('hero_eyebrow', 'Edeni Realtors — Tanzania'));
const heroTitle = computed(() => sc('hero_title', 'Find Your Next Home or Investment in Tanzania'));
const heroSubtitle = computed(() => sc('hero_subtitle', 'Houses, apartments, land, and commercial properties across the country. Browse listings, enquire on WhatsApp, and work with verified agents.'));
const seoDescription = computed(() => sc('seo_description', "Tanzania's real estate platform by Edeni Realtors. Browse verified properties for sale and rent across Dar es Salaam, Arusha, Mwanza, Dodoma and Zanzibar. Enquire on WhatsApp."));
const contactWhatsapp = computed(() => sc('contact_whatsapp_raw', '255759210560'));
const contactLocation = computed(() => sc('contact_location', 'Arusha, Tanzania'));
const footerAbout = computed(() => sc('footer_about', "Tanzania's real estate platform by Edeni Realtors. Buy, sell, and rent with confidence."));

const stats = computed(() => [
    { value: sc('stat_1_value', '100+'), label: sc('stat_1_label', 'Properties listed') },
    { value: sc('stat_2_value', '5+'), label: sc('stat_2_label', 'Regions covered') },
    { value: sc('stat_3_value', '24/7'), label: sc('stat_3_label', 'WhatsApp enquiries') },
]);

const waLink = computed(() => `https://wa.me/${contactWhatsapp.value}?text=Hello%20Edenire%2C%20I%20would%20like%20to%20enquire%20about%20properties.`);

// Landing search form
const searchForm = ref({ q: '', type: '', listing: '', price: '' });

const typeOptions = [
    { value: '', label: 'All property types' },
    { value: 'residential', label: 'Residential' },
    { value: 'commercial', label: 'Commercial' },
    { value: 'industrial', label: 'Industrial' },
    { value: 'land', label: 'Land' },
    { value: 'mixed_use', label: 'Mixed use' },
];

const listingOptions = [
    { value: '', label: 'Buy or Rent' },
    { value: 'sale', label: 'For Sale' },
    { value: 'rent', label: 'For Rent' },
];

const priceOptions = [
    { value: '', label: 'Any price' },
    { value: '0-100000000', label: 'Under 100M TZS' },
    { value: '100000000-300000000', label: '100M – 300M TZS' },
    { value: '300000000-600000000', label: '300M – 600M TZS' },
    { value: '600000000-1000000000', label: '600M – 1B TZS' },
    { value: '1000000000-', label: 'Over 1B TZS' },
];

function submitSearch() {
    const params = {};
    const f = searchForm.value;
    if (f.q) params.q = f.q;
    if (f.type) params.type = f.type;
    if (f.listing) params.listing = f.listing;
    if (f.price) {
        const [min, max] = f.price.split('-');
        if (min) params.min_price = min;
        if (max) params.max_price = max;
    }
    router.get('/properties', params);
}

const features = [
    { icon: '🏠', title: 'Verified Listings', text: 'Every property is reviewed before it goes live. No fake listings, no surprises.' },
    { icon: '💬', title: 'WhatsApp Enquiries', text: 'Tanzanians live on WhatsApp — enquire about any property in one tap.' },
    { icon: '📊', title: 'Agent Analytics', text: 'Track views, enquiries, and performance with a real dashboard made for agents.' },
    { icon: '💵', title: 'TZS Pricing', text: 'All prices in Tanzanian Shillings — clear, local, and easy to compare.' },
    { icon: '🗺️', title: 'Nationwide', text: 'From Dar es Salaam to Arusha, Mwanza, Dodoma, and Zanzibar.' },
    { icon: '🔐', title: 'Role-based Access', text: 'Separate experiences for admins, agents, buyers, sellers, and tenants.' },
];
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-950">
        <Head title="Edenire.co.tz — Find Your Home in Tanzania | Real Estate for Sale & Rent">
            <meta name="description" :content="seoDescription" />
        </Head>
        <!-- Nav -->
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100 dark:bg-gray-950/90 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2">
                    <img src="/img/logo.svg" alt="EdeniRE" class="h-9 w-auto" />
                    <span class="hidden sm:inline text-xs text-gray-400 ml-1">by Edeni Realtors</span>
                </Link>
                <nav class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <Link href="/properties" class="hover:text-emerald-700 dark:hover:text-emerald-400">Browse Properties</Link>
                    <Link href="/properties" class="hidden md:inline hover:text-emerald-700 dark:hover:text-emerald-400">For Sale</Link>
                    <Link href="/properties" class="hidden md:inline hover:text-emerald-700 dark:hover:text-emerald-400">For Rent</Link>
                    <ThemeToggle />
                    <Link v-if="$page.props.auth?.user" href="/favorites" class="hidden md:inline hover:text-emerald-700 dark:hover:text-emerald-400">❤️ Saved</Link>
                    <Link v-if="canLogin" href="/login"
                        class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Sign in
                    </Link>
                    <Link v-else href="/dashboard"
                        class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Dashboard
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-700 text-white">
            <div class="max-w-7xl mx-auto px-4 py-20 text-center">
                <p class="text-emerald-200 font-medium mb-3 tracking-wide uppercase text-sm">{{ heroEyebrow }}</p>
                <h1 class="text-4xl sm:text-5xl font-bold mb-4 leading-tight">
                    {{ heroTitle }}
                </h1>
                <p class="text-lg text-emerald-100 max-w-2xl mx-auto mb-8">
                    {{ heroSubtitle }}
                </p>

                <!-- Search form (4 fields) -->
                <form @submit.prevent="submitSearch" class="bg-white rounded-2xl shadow-xl p-4 sm:p-5 max-w-4xl mx-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="lg:col-span-1">
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 text-left">Your search</label>
                            <input v-model="searchForm.q" type="text" placeholder="Area, street, keyword..."
                                class="w-full rounded-lg border-gray-300 text-gray-900 focus:border-[#A8E46A] focus:ring-[#A8E46A]" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 text-left">Property type</label>
                            <select v-model="searchForm.type" class="w-full rounded-lg border-gray-300 text-gray-900 focus:border-[#A8E46A] focus:ring-[#A8E46A]">
                                <option v-for="t in typeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 text-left">Sale or rent</label>
                            <select v-model="searchForm.listing" class="w-full rounded-lg border-gray-300 text-gray-900 focus:border-[#A8E46A] focus:ring-[#A8E46A]">
                                <option v-for="l in listingOptions" :key="l.value" :value="l.value">{{ l.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 text-left">Price range</label>
                            <select v-model="searchForm.price" class="w-full rounded-lg border-gray-300 text-gray-900 focus:border-[#A8E46A] focus:ring-[#A8E46A]">
                                <option v-for="p in priceOptions" :key="p.value" :value="p.value">{{ p.label }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit"
                            class="w-full bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] font-bold px-8 py-3.5 rounded-xl transition text-lg">
                            🔍 Search Properties
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Stats -->
        <section class="border-b border-gray-100 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div v-for="s in stats" :key="s.label">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ s.value }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ s.label }}</div>
                </div>
            </div>
        </section>

        <!-- Featured properties -->
        <section class="max-w-7xl mx-auto px-4 py-14">
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Featured Properties</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Hand-picked listings from our agents</p>
                </div>
                <Link href="/properties" class="text-emerald-700 dark:text-emerald-400 font-semibold hover:underline">
                    View all →
                </Link>
            </div>

            <div v-if="featured.length === 0" class="text-center py-12 text-gray-400">
                New listings are on the way. Check back soon!
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link v-for="p in featured" :key="p.id" :href="p.public_url"
                    class="group bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-lg transition overflow-hidden border border-gray-100 dark:border-gray-800">
                    <div class="relative">
                        <img v-if="p.primary_image_url" :src="p.primary_image_url" :alt="p.title"
                            class="h-44 w-full object-cover group-hover:opacity-90 transition" />
                        <div v-else class="h-44 bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 capitalize group-hover:opacity-80 transition">
                            {{ p.property_type }}
                        </div>
                        <div class="absolute top-2 right-2" @click.prevent.stop>
                            <FavoriteButton :property-id="p.id"
                                :active="favoriteIds?.includes(Number(p.id))"
                                size="sm" />
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-2">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ p.title }}</h3>
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold whitespace-nowrap">{{ p.price_label }}</span>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ p.city }}{{ p.region ? ', ' + p.region : '' }}</p>
                        <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-300 mt-3">
                            <span v-if="p.bedrooms">🛏 {{ p.bedrooms }} bd</span>
                            <span v-if="p.bathrooms">🛁 {{ p.bathrooms }} ba</span>
                            <span v-if="p.building_area">{{ p.building_area }} m²</span>
                            <span class="ml-auto capitalize text-xs text-gray-400">{{ p.listing_type }}</span>
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Why Edenire -->
        <section class="bg-gray-50 dark:bg-gray-900 py-14">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center mb-10">Why Edenire?</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="f in features" :key="f.title"
                        class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="text-3xl mb-3">{{ f.icon }}</div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ f.title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ f.text }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA: Contact us -->
        <section class="bg-[#232126] dark:bg-[#0D0E0F] text-white py-14">
            <div class="max-w-3xl mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold mb-3">Are you selling or looking for a property?</h2>
                <p class="text-gray-300 mb-8">Contact us here — our team will help you find the right property or buyer.</p>
                <a
                    :href="waLink"
                    target="_blank"
                    class="inline-flex items-center gap-3 bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] text-xl font-bold px-10 py-4 rounded-2xl transition shadow-lg shadow-[#A8E46A]/20"
                >
                    💬 Send us a message
                </a>
                <p class="text-xs text-white/40 mt-4">Opens WhatsApp — fastest response</p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-10">
            <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                <div>
                    <div class="text-lg font-bold text-white mb-2">Edenire.co.tz</div>
                    <p class="text-gray-500">{{ footerAbout }}</p>
                </div>
                <div>
                    <div class="font-semibold text-white mb-2">Explore</div>
                    <ul class="space-y-1">
                        <li><Link href="/properties" class="hover:text-emerald-400">All properties</Link></li>
                        <li><Link href="/properties" class="hover:text-emerald-400">For sale</Link></li>
                        <li><Link href="/properties" class="hover:text-emerald-400">For rent</Link></li>
                    </ul>
                </div>
                <div>
                    <div class="font-semibold text-white mb-2">Contact</div>
                    <ul class="space-y-1">
                        <li>📞 +255 759 210 560</li>
                        <li>✉️ info@edenire.co.tz</li>
                        <li>📍 {{ contactLocation }}</li>
                        <li>💬 WhatsApp: 24/7 enquiries</li>
                    </ul>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-4 mt-8 pt-6 border-t border-gray-800 text-xs text-gray-600">
                © {{ new Date().getFullYear() }} Edeni Realtors · Edenire.co.tz — All rights reserved.
            </div>
        </footer>
    </div>
</template>
