<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    propertyId: { type: [Number, String], required: true },
    active: { type: Boolean, default: false },
    size: { type: String, default: 'md' }, // 'sm' | 'md' | 'lg'
});

function toggle(e) {
    e.preventDefault();
    e.stopPropagation();

    if (props.active) {
        router.delete(`/properties/${props.propertyId}/favorite`, { preserveScroll: true });
    } else {
        router.post(`/properties/${props.propertyId}/favorite`, {}, { preserveScroll: true });
    }
}

const sizeClasses = {
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12',
};

const iconClasses = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6',
};
</script>

<template>
    <button
        type="button"
        @click="toggle"
        :title="active ? 'Remove from favorites' : 'Save to favorites'"
        :aria-label="active ? 'Remove from favorites' : 'Save to favorites'"
        class="inline-flex items-center justify-center rounded-full transition"
        :class="[
            sizeClasses[size],
            active
                ? 'text-[#70A83C] dark:text-[#A8E46A]'
                : 'text-gray-900 hover:bg-gray-900/5 dark:text-gray-200 dark:hover:bg-white/10',
        ]"
    >
        <!-- Active: filled green heart -->
        <svg v-if="active" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="shrink-0" :class="iconClasses[size]">
            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
        </svg>
        <!-- Inactive: outlined heart, black on light theme / white-gray on dark theme -->
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0" :class="iconClasses[size]">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
        </svg>
    </button>
</template>
