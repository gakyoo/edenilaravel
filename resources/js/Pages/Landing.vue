<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    featured: Array,
    canLogin: Boolean,
    canRegister: Boolean,
});

const stats = [
    { value: '100+', label: 'Properties listed' },
    { value: '5+', label: 'Regions covered' },
    { value: '24/7', label: 'WhatsApp enquiries' },
    { value: 'TZS & USD', label: 'Multi-currency' },
];

const features = [
    { icon: '🏠', title: 'Verified Listings', text: 'Every property is reviewed before it goes live. No fake listings, no surprises.' },
    { icon: '💬', title: 'WhatsApp Enquiries', text: 'Tanzanians live on WhatsApp — enquire about any property in one tap.' },
    { icon: '📊', title: 'Agent Analytics', text: 'Track views, enquiries, and performance with a real dashboard made for agents.' },
    { icon: '💱', title: 'TZS & USD', text: 'Prices in shillings or dollars — with mortgage and ROI tools coming soon.' },
    { icon: '🗺️', title: 'Nationwide', text: 'From Dar es Salaam to Arusha, Mwanza, Dodoma, and Zanzibar.' },
    { icon: '🔐', title: 'Role-based Access', text: 'Separate experiences for admins, agents, buyers, sellers, and tenants.' },
];
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-950">
        <Head title="Edenire.co.tz — Find Your Home in Tanzania | Real Estate for Sale & Rent">
            <meta name="description" content="Tanzania's real estate platform by Edeni Realtors. Browse verified properties for sale and rent across Dar es Salaam, Arusha, Mwanza, Dodoma and Zanzibar. Enquire on WhatsApp." />
        </Head>
        <!-- Nav -->
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100 dark:bg-gray-950/90 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2">
                    <span class="text-2xl">🏘️</span>
                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">Edenire<span class="text-emerald-600 dark:text-emerald-400">.co.tz</span></span>
                    <span class="hidden sm:inline text-xs text-gray-400 ml-1">by Edeni Realtors</span>
                </Link>
                <nav class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <Link href="/properties" class="hover:text-emerald-700 dark:hover:text-emerald-400">Browse Properties</Link>
                    <Link href="/properties" class="hidden md:inline hover:text-emerald-700 dark:hover:text-emerald-400">For Sale</Link>
                    <Link href="/properties" class="hidden md:inline hover:text-emerald-700 dark:hover:text-emerald-400">For Rent</Link>
                    <ThemeToggle />
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
                <p class="text-emerald-200 font-medium mb-3 tracking-wide uppercase text-sm">Edeni Realtors — Tanzania</p>
                <h1 class="text-4xl sm:text-5xl font-bold mb-4 leading-tight">
                    Find Your Next Home or Investment<br class="hidden sm:block"> in Tanzania
                </h1>
                <p class="text-lg text-emerald-100 max-w-2xl mx-auto mb-8">
                    Houses, apartments, land, and commercial properties across the country.
                    Browse listings, enquire on WhatsApp, and work with verified agents.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <Link href="/properties"
                        class="bg-white text-emerald-800 font-semibold px-8 py-3.5 rounded-xl hover:bg-emerald-50 transition text-lg">
                        🔍 Browse Properties
                    </Link>
                    <Link href="/register"
                        class="border-2 border-white/40 hover:bg-white/10 text-white font-semibold px-8 py-3.5 rounded-xl transition text-lg">
                        List Your Property
                    </Link>
                </div>
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
                <Link v-for="p in featured" :key="p.id" :href="`/properties/${p.id}`"
                    class="group bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-lg transition overflow-hidden border border-gray-100 dark:border-gray-800">
                    <img v-if="p.primary_image_url" :src="p.primary_image_url" :alt="p.title"
                        class="h-44 w-full object-cover group-hover:opacity-90 transition" />
                    <div v-else class="h-44 bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400 capitalize group-hover:opacity-80 transition">
                        {{ p.property_type }}
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

        <!-- CTA -->
        <section class="bg-emerald-800 text-white py-14">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold mb-3">Are you a real estate agent?</h2>
                <p class="text-emerald-100 mb-6">List your properties, track views and enquiries, and grow your business with Edenire's agent dashboard.</p>
                <div class="flex gap-3 justify-center flex-wrap">
                    <Link href="/register" class="bg-white text-emerald-800 font-semibold px-8 py-3 rounded-xl hover:bg-emerald-50 transition">
                        Create Agent Account
                    </Link>
                    <Link href="/login" class="border-2 border-white/40 hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                        Sign In
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-10">
            <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                <div>
                    <div class="text-lg font-bold text-white mb-2">Edenire.co.tz</div>
                    <p class="text-gray-500">Tanzania's real estate platform by Edeni Realtors. Buy, sell, and rent with confidence.</p>
                </div>
                <div>
                    <div class="font-semibold text-white mb-2">Explore</div>
                    <ul class="space-y-1">
                        <li><Link href="/properties" class="hover:text-emerald-400">All properties</Link></li>
                        <li><Link href="/properties" class="hover:text-emerald-400">For sale</Link></li>
                        <li><Link href="/properties" class="hover:text-emerald-400">For rent</Link></li>
                        <li><Link href="/register" class="hover:text-emerald-400">List your property</Link></li>
                    </ul>
                </div>
                <div>
                    <div class="font-semibold text-white mb-2">Contact</div>
                    <ul class="space-y-1">
                        <li>📞 +255 (0) 000 000 000</li>
                        <li>✉️ info@edenire.co.tz</li>
                        <li>📍 Dar es Salaam, Tanzania</li>
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
