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
            >{{ l.label }}</Link>
        </template>
    </div>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    links: Array,
    type: {
        type: String,
        default: 'page'
    }
});

const handleClick = (url) => {
    if (!url) return;
    
    try {
        // Get the current URL's search params
        const currentParams = new URLSearchParams(window.location.search);
        
        // Get the target page from the URL
        const targetUrl = new URL(url, window.location.origin);
        const targetPage = targetUrl.searchParams.get('page');
        
        if (targetPage) {
            // Update the appropriate page parameter based on type
            switch (props.type) {
                case 'categories':
                    currentParams.set('categories_page', targetPage);
                    break;
                case 'dosages':
                    currentParams.set('dosages_page', targetPage);
                    break;
                case 'eligible':
                    currentParams.set('eligible_page', targetPage);
                    break;
                default:
                    currentParams.set('page', targetPage);
            }
        }
        
        // Construct the new URL with all parameters
        const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
        
        // Determine which data to reload based on type
        const only = (() => {
            switch (props.type) {
                case 'categories':
                    return ['categories', 'filters'];
                case 'dosages':
                    return ['dosages', 'filters'];
                case 'eligible':
                    return ['eligibleItems', 'filters'];
                default:
                    return ['products', 'filters'];
            }
        })();
        
        router.get(newUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only
        });
    } catch (error) {
        console.error('Error handling pagination:', error);
    }
};
</script>
