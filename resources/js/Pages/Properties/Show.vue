<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    property: Object,
});

const waLink = computed(() => {
    const phone = props.property.agent?.phone || '255000000000';
    const text = encodeURIComponent(`Hello, I'm interested in ${props.property.title || 'this property'} (${props.property.price_label}) listed on Edenire.co.tz`);
    return `https://wa.me/${phone.replace(/\D/g, '')}?text=${text}`;
});
</script>

<template>
    <GuestLayout>
        <div class="max-w-5xl mx-auto px-4 py-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="h-72 bg-gray-200 flex items-center justify-center text-gray-400 text-xl">
                    {{ property.property_type }} — Photo coming soon
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ property.title || 'Property' }}</h1>
                            <p class="text-gray-500 mt-1">{{ property.address_line }}{{ property.city ? ', ' + property.city : '' }}{{ property.region ? ', ' + property.region : '' }}, {{ property.country }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-emerald-700">{{ property.price_label }}</div>
                            <div class="text-sm text-gray-500 capitalize">{{ property.listing_type }} • {{ property.status }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 my-6 bg-gray-50 rounded-xl p-4">
                        <div v-if="property.bedrooms" class="text-center"><div class="text-2xl">🛏</div><div class="text-sm text-gray-600">{{ property.bedrooms }} Bedrooms</div></div>
                        <div v-if="property.bathrooms" class="text-center"><div class="text-2xl">🛁</div><div class="text-sm text-gray-600">{{ property.bathrooms }} Bathrooms</div></div>
                        <div v-if="property.building_area" class="text-center"><div class="text-2xl">📐</div><div class="text-sm text-gray-600">{{ property.building_area }} m²</div></div>
                        <div v-if="property.lot_size" class="text-center"><div class="text-2xl">🌳</div><div class="text-sm text-gray-600">{{ property.lot_size }} m² lot</div></div>
                    </div>

                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Description</h2>
                    <p class="text-gray-700 leading-relaxed">{{ property.description || 'No description yet.' }}</p>

                    <div v-if="property.amenities && property.amenities.length" class="mt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Amenities</h2>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="a in property.amenities" :key="a" class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-sm">{{ a }}</span>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Contact Agent</h2>
                        <div v-if="property.agent" class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <div class="font-medium">{{ property.agent.company_name || property.agent.name || 'Agent' }}</div>
                                <div class="text-sm text-gray-500">{{ property.agent.phone || 'Phone on request' }}</div>
                            </div>
                            <div class="flex gap-3">
                                <a :href="waLink" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                                    💬 Enquire on WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
