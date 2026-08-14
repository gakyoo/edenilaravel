<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Fix default marker icons broken by Vite bundling
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const props = defineProps({
    lat: [Number, String],
    lng: [Number, String],
    title: { type: String, default: 'Property' },
    height: { type: String, default: '280px' },
    zoom: { type: Number, default: 15 },
});

const mapEl = ref(null);
let map = null;
let marker = null;
let fromMarker = null;
let measureLine = null;

const measuring = ref(false);
const distance = ref(null);
const fromLabel = ref('');
const searching = ref(false);
const geoStatus = ref('');
const placeQuery = ref('');

function formatDistance(m) {
    if (m == null) return '';
    return m < 1000 ? `${Math.round(m)} m` : `${(m / 1000).toFixed(m < 10000 ? 2 : 1)} km`;
}

function initMap() {
    if (!mapEl.value || map) return;

    const lat = Number(props.lat);
    const lng = Number(props.lng);
    if (!lat || !lng) return;

    map = L.map(mapEl.value).setView([lat, lng], props.zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    marker = L.marker([lat, lng])
        .addTo(map)
        .bindPopup(`<strong>${props.title}</strong><br>${lat.toFixed(5)}, ${lng.toFixed(5)}`)
        .openPopup();
}

function destroyMap() {
    if (map) {
        map.remove();
        map = null;
        marker = null;
        fromMarker = null;
        measureLine = null;
        distance.value = null;
        measuring.value = false;
        fromLabel.value = '';
    }
}

function clearMeasure() {
    if (!map) return;
    if (fromMarker) { map.removeLayer(fromMarker); fromMarker = null; }
    if (measureLine) { map.removeLayer(measureLine); measureLine = null; }
    distance.value = null;
    fromLabel.value = '';
    measuring.value = false;
}

function drawMeasure(lat2, lng2, label) {
    if (!map) return;
    const p = L.latLng(Number(props.lat), Number(props.lng));
    const q = L.latLng(lat2, lng2);

    clearMeasure();

    const icon = L.divIcon({
        className: '',
        html: '<div style="width:22px;height:22px;border-radius:50%;background:#2563eb;border:3px solid #fff;box-shadow:0 0 0 2px #2563eb;"></div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
    });

    fromMarker = L.marker(q, { icon }).addTo(map).bindPopup(`<strong>${label}</strong>`);
    measureLine = L.polyline([p, q], { color: '#A8E46A', weight: 3, dashArray: '6 6' }).addTo(map);

    distance.value = map.distance(p, q);
    fromLabel.value = label;
    measuring.value = true;

    map.fitBounds([p, q], { padding: [45, 45] });
    setTimeout(() => marker.openPopup(), 250);
}

function measureMyLocation() {
    if (!map) return;
    if (!navigator.geolocation) {
        geoStatus.value = 'Geolocation not supported on this browser';
        setTimeout(() => { geoStatus.value = ''; }, 4000);
        return;
    }
    geoStatus.value = 'Locating you…';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            geoStatus.value = '';
            drawMeasure(pos.coords.latitude, pos.coords.longitude, 'My location');
        },
        (err) => {
            geoStatus.value = err.code === 1
                ? 'Location permission denied — use the place search instead'
                : 'Could not get your location — use the place search instead';
            setTimeout(() => { geoStatus.value = ''; }, 5000);
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

async function searchPlace() {
    const q = placeQuery.value.trim();
    if (!q || !map) return;
    searching.value = true;
    geoStatus.value = 'Searching…';
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=tz&limit=1`,
            { headers: { Accept: 'application/json' } }
        );
        const data = await res.json();
        if (data && data.length) {
            const hit = data[0];
            const label = hit.display_name.split(',').slice(0, 3).join(',').trim();
            drawMeasure(Number(hit.lat), Number(hit.lon), label);
            geoStatus.value = '';
        } else {
            geoStatus.value = 'No place found — try another name';
            setTimeout(() => { geoStatus.value = ''; }, 4000);
        }
    } catch (e) {
        geoStatus.value = 'Search failed — check connection';
        setTimeout(() => { geoStatus.value = ''; }, 4000);
    } finally {
        searching.value = false;
    }
}

onMounted(() => {
    // Small delay so the container has layout dimensions
    setTimeout(initMap, 50);
});

onBeforeUnmount(destroyMap);

watch(() => [props.lat, props.lng], () => {
    destroyMap();
    setTimeout(initMap, 50);
});
</script>

<template>
    <div :style="{ height, width: '100%' }" class="relative rounded-xl overflow-hidden z-0">
        <div ref="mapEl" class="absolute inset-0"></div>

        <!-- Measure toolbar -->
        <div class="absolute inset-x-2 top-2 flex flex-col gap-1.5 pointer-events-none z-[1000]">
            <div v-if="distance !== null"
                class="mx-auto bg-[#232126]/90 text-[#A8E46A] px-3 py-1.5 rounded-full text-sm font-semibold shadow">
                📏 {{ fromLabel }} → property: {{ formatDistance(distance) }}
            </div>
            <div class="flex flex-wrap gap-1.5 justify-end pointer-events-auto">
                <input v-model="placeQuery" @keyup.enter="searchPlace" type="text"
                    placeholder="Place e.g. Njiro, Arusha"
                    class="w-44 bg-white text-gray-900 border border-gray-300 rounded-lg px-3 py-1.5 text-sm shadow focus:border-[#A8E46A] focus:ring-[#A8E46A]" />
                <button type="button" @click="searchPlace" :disabled="searching"
                    class="bg-white text-gray-800 border border-gray-300 px-3 py-1.5 rounded-lg text-sm font-semibold shadow hover:bg-gray-50 transition">
                    🔎 Measure
                </button>
                <button type="button" @click="measureMyLocation"
                    class="bg-[#232126] text-[#A8E46A] px-3 py-1.5 rounded-lg text-sm font-semibold shadow hover:bg-black transition">
                    📍 My location
                </button>
                <button v-if="measuring" type="button" @click="clearMeasure"
                    class="bg-white text-red-600 border border-gray-300 px-3 py-1.5 rounded-lg text-sm font-semibold shadow hover:bg-red-50 transition">
                    ✕ Clear
                </button>
            </div>
            <div v-if="geoStatus"
                class="mx-auto bg-white/95 text-gray-800 px-3 py-1 rounded-full text-xs shadow">
                {{ geoStatus }}
            </div>
        </div>
    </div>
</template>
