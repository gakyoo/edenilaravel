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
    <div ref="mapEl" :style="{ height, width: '100%' }" class="rounded-xl overflow-hidden z-0"></div>
</template>
