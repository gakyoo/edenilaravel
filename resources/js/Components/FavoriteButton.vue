<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    propertyId: { type: [Number, String], required: true },
    active: { type: Boolean, default: false },
    variant: { type: String, default: 'pill' }, // 'pill' | 'icon'
    size: { type: String, default: 'md' },      // 'sm' | 'md' | 'lg'
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
    sm: 'px-2.5 py-1.5 text-xs',
    md: 'px-3.5 py-2 text-sm',
    lg: 'px-5 py-3 text-sm',
};
</script>

<template>
    <button
        type="button"
        @click="toggle"
        :title="active ? 'Remove from favorites' : 'Save to favorites'"
        class="inline-flex items-center justify-center gap-1.5 rounded-xl font-semibold shadow transition select-none"
        :class="[
            sizeClasses[size],
            active
                ? 'bg-[#232126] text-[#A8E46A] hover:bg-black'
                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700',
            variant === 'icon' ? '!p-2.5' : '',
        ]"
    >
        <svg v-if="active" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4.5 h-4.5 text-red-500 shrink-0">
            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4.5 h-4.5 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
        </svg>
        <span v-if="variant === 'pill'">{{ active ? 'Saved' : 'Save' }}</span>
    </button>
</template>
