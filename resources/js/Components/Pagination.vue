<template>
    <div v-if="links.length > 3" class="flex flex-wrap -mb-1">
        <template v-for="(l, key) in links" :key="key">
            <!-- Disabled pagination button (e.g., 'Previous' when on the first page) -->
            <div 
                v-if="l.url === null" 
                class="mr-1 mb-1 px-4 py-2 text-sm text-gray-400 border rounded"
                v-html="l.label"
            />

            <!-- Clickable pagination buttons -->
            <Link 
                v-else
                :href="l.url"
                class="mr-1 mb-1 px-4 py-2 text-sm border rounded hover:bg-gray-100"
                :class="{ 'bg-blue-600 text-white hover:bg-blue-700': l.active }"
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
