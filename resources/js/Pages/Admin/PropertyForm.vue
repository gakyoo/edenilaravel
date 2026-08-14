<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    property: Object,
    agents: Array,
    statuses: Array,
    types: Array,
});

const isEdit = !!props.property;

const form = useForm({
    title: props.property?.title || '',
    description: props.property?.description || '',
    agent_id: props.property?.agent_id || '',
    parcel_number: props.property?.parcel_number || '',
    address_line: props.property?.address_line || '',
    city: props.property?.city || '',
    region: props.property?.region || '',
    country: props.property?.country || 'Tanzania',
    latitude: props.property?.latitude || '',
    longitude: props.property?.longitude || '',
    property_type: props.property?.property_type || 'residential',
    listing_type: props.property?.listing_type || 'sale',
    lot_size: props.property?.lot_size || '',
    building_area: props.property?.building_area || '',
    bedrooms: props.property?.bedrooms || '',
    bathrooms: props.property?.bathrooms || '',
    stories: props.property?.stories || '',
    year_built: props.property?.year_built || '',
    parking_spaces: props.property?.parking_spaces || '',
    price: props.property?.price || '',
    currency: props.property?.currency || 'TZS',
    is_negotiable: props.property?.is_negotiable ?? true,
    status: props.property?.status || 'active',
    is_featured: props.property?.is_featured ?? false,
    amenities: props.property?.amenities || [],
    listed_at: props.property?.listed_at ? props.property.listed_at.slice(0, 10) : '',
});

const amenityInput = ref('');
const primaryPhoto = ref(null);
const galleryPhotos = ref([]);

function addAmenity() {
    const v = amenityInput.value.trim().toLowerCase().replace(/\s+/g, '_');
    if (v && !form.amenities.includes(v)) {
        form.amenities.push(v);
    }
    amenityInput.value = '';
}

function removeAmenity(a) {
    form.amenities = form.amenities.filter((x) => x !== a);
}

function onPrimaryChange(e) {
    primaryPhoto.value = e.target.files[0];
}

function onGalleryChange(e) {
    galleryPhotos.value = Array.from(e.target.files);
}

function submit() {
    if (isEdit) {
        form.post(`/dashboard/properties/${props.property.id}`, {
            forceFormData: true,
            preserveScroll: true,
        });
    } else {
        form.post('/dashboard/properties', {
            forceFormData: true,
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${isEdit ? 'Edit' : 'Create'} Property | Edenire.co.tz`" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ isEdit ? 'Edit Property' : 'Create Property' }}</h2>
                <ThemeToggle />
            </div>
        </template>

        <div class="py-8 max-w-4xl">
            <div class="flex gap-2 flex-wrap bg-white dark:bg-gray-900 rounded-xl shadow p-3 mb-6">
                <Link href="/dashboard" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Dashboard</Link>
                <Link href="/dashboard" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Properties</Link>
                <Link href="/dashboard/properties/create" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">+ New Property</Link>
                <Link href="/dashboard" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Enquiries</Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Errors -->
                <div v-if="Object.keys(form.errors).length" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 text-sm text-red-700 dark:text-red-300">
                    <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
                </div>

                <!-- Basics -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Basic Information</h3>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Title *</label>
                        <input v-model="form.title" type="text" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Description</label>
                        <textarea v-model="form.description" rows="4" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Agent</label>
                            <select v-model="form.agent_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                <option value="">— Company-owned (no agent) —</option>
                                <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.company_name || a.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Parcel / APN number</label>
                            <input v-model="form.parcel_number" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Address line</label>
                            <input v-model="form.address_line" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Listed date</label>
                            <input v-model="form.listed_at" type="date" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Location</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">City</label>
                            <input v-model="form.city" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Region</label>
                            <input v-model="form.region" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Country</label>
                            <input v-model="form.country" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Latitude</label>
                            <input v-model="form.latitude" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Longitude</label>
                            <input v-model="form.longitude" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                    </div>
                </div>

                <!-- Type & details -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Type &amp; Details</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Property type *</label>
                            <select v-model="form.property_type" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                <option v-for="t in types" :key="t" :value="t" class="capitalize">{{ t.replace('_', ' ') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Listing type *</label>
                            <select v-model="form.listing_type" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                <option value="sale">For Sale</option>
                                <option value="rent">For Rent</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Status *</label>
                            <select v-model="form.status" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s.replace('_', ' ') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Year built</label>
                            <input v-model="form.year_built" type="number" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Bedrooms</label>
                            <input v-model="form.bedrooms" type="number" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Bathrooms</label>
                            <input v-model="form.bathrooms" type="number" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Building area (m²)</label>
                            <input v-model="form.building_area" type="number" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Lot size (m²)</label>
                            <input v-model="form.lot_size" type="number" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Stories</label>
                            <input v-model="form.stories" type="number" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Parking spaces</label>
                            <input v-model="form.parking_spaces" type="number" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Pricing</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Price *</label>
                            <input v-model="form.price" type="number" required min="0" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Currency *</label>
                            <select v-model="form.currency" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
                                <option value="TZS">TZS</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input v-model="form.is_negotiable" type="checkbox" class="rounded dark:bg-gray-800" /> Negotiable
                            </label>
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input v-model="form.is_featured" type="checkbox" class="rounded dark:bg-gray-800" /> Featured
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Amenities -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 space-y-3">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Amenities</h3>
                    <div class="flex gap-2">
                        <input v-model="amenityInput" @keyup.enter.prevent="addAmenity" type="text" placeholder="e.g. security, water_tank, generator"
                            class="flex-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
                        <button type="button" @click="addAmenity" class="bg-gray-100 dark:bg-gray-800 px-4 rounded-lg text-sm font-semibold">Add</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="a in form.amenities" :key="a" class="bg-[#A8E46A]/10 text-[#70A83C] dark:text-[#A8E46A] px-3 py-1 rounded-full text-sm capitalize">
                            {{ a.replace(/_/g, ' ') }}
                            <button type="button" @click="removeAmenity(a)" class="ml-1 text-red-500">×</button>
                        </span>
                    </div>
                </div>

                <!-- Photos -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Photos</h3>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Primary photo</label>
                        <input @change="onPrimaryChange" type="file" accept="image/*"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:text-gray-300 text-sm p-2" />
                        <p v-if="primaryPhoto" class="text-xs text-gray-400 mt-1">Selected: {{ primaryPhoto.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Gallery photos (multiple)</label>
                        <input @change="onGalleryChange" type="file" accept="image/*" multiple
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:text-gray-300 text-sm p-2" />
                        <p v-if="galleryPhotos.length" class="text-xs text-gray-400 mt-1">{{ galleryPhotos.length }} file(s) selected</p>
                    </div>
                    <div v-if="isEdit && property.media?.length" class="flex flex-wrap gap-2">
                        <img v-for="m in property.media" :key="m.id" :src="'/storage/' + m.path" class="h-16 w-20 rounded object-cover" />
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="bg-[#A8E46A] hover:bg-[#8CC84F] text-[#232126] px-8 py-3 rounded-xl font-semibold transition disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Save Changes' : 'Create Property') }}
                    </button>
                    <Link href="/dashboard" class="border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 px-6 py-3 rounded-xl font-semibold text-center">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
