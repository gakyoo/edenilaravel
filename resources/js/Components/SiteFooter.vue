<script setup>
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Shared footer — reads editable site content when available, falls back to defaults
const page = usePage();
const content = computed(() => page.props.siteContent || {});

const footerAbout = computed(() => content.value.footer_about
    || "Tanzania's real estate platform by Edeni Realtors. Buy, sell, and rent with confidence.");

const contactLocation = computed(() => content.value.contact_location || 'Arusha, Tanzania');

const waLink = computed(() => {
    const wa = content.value.contact_whatsapp_raw || '255759210560';
    return `https://wa.me/${wa}?text=Hello%20Edenire%2C%20I%20would%20like%20to%20enquire%20about%20properties.`;
});
</script>

<template>
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
</template>
