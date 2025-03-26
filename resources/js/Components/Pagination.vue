<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-end gap-2">
        <template v-for="(l, key) in links" :key="key">
            <!-- Disabled pagination button -->
            <div 
                v-if="l.url === null" 
                class="px-3 py-2 text-sm text-dark-400 bg-dark-50 border border-dark-200 rounded-full cursor-not-allowed"
                v-html="l.label"
            />

            <!-- Clickable pagination buttons -->
            <Link 
                v-else
                :href="l.url"
                class="px-3 py-2 text-sm border rounded-full transition-all duration-200 ease-in-out"
                :class="{ 
                    'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 hover:border-indigo-700': l.active,
                    'text-dark-700 border-dark-200 hover:bg-dark-50 hover:text-indigo-600': !l.active 
                }"
                :preserve-scroll="true"
                :preserve-state="true"
                v-html="l.label"
            />
        </template>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
})
</script>
