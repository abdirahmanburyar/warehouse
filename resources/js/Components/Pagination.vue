<template>
    <div v-if="links.length > 3" class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null" 
                 class="mr-1 mb-1 px-4 py-2 text-sm text-gray-400 border rounded"
                 v-html="link.label" />
            <button v-else
                  @click="navigateToPage(link.url)"
                  class="mr-1 mb-1 px-4 py-2 text-sm border rounded hover:bg-gray-100"
                  :class="{ 'bg-blue-600 text-white hover:bg-blue-700': link.active }"
                  v-html="link.label" />
        </template>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
    props: {
        links: Array,
    },
    methods: {
        navigateToPage(url) {
            // Parse the URL to get the page parameter
            const urlObj = new URL(url, window.location.origin);
            const page = urlObj.searchParams.get('page');
            
            // Get current query parameters
            const currentParams = new URLSearchParams(window.location.search);
            
            // Create a new params object with all current parameters
            const params = {};
            for (const [key, value] of currentParams.entries()) {
                params[key] = value;
            }
            
            // Add or update the page parameter
            params.page = page;
            
            // Navigate using Inertia router to preserve all parameters
            router.get(window.location.pathname, params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['products', 'categories']
            });
        }
    }
};
</script>
