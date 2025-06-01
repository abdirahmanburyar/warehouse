<template>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-4">
            <div>
                <Link :href="route('inventories.index')" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Inventory
                </Link>
                <h3 class="text-xl font-bold text-gray-900 mt-1">Locations Management</h3>
            </div>
            <Link :href="route('inventories.location.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition duration-150 ease-in-out flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create New Location
            </Link>
        </div>

        <div class="">
            <div class="flex flex-wrap gap-6 items-start">
                <div class="w-1/2 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input 
                            type="text" 
                            v-model="search" 
                            placeholder="Search by location name..." 
                            class="pl-10 w-full border-black rounded-full shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                        <button v-if="search" @click="search = ''; getLocations()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="w-[300px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <Multiselect 
                            v-model="warehouse" 
                            :options="warehouses" 
                            placeholder="Select Warehouse" 
                            :searchable="true"
                            :close-on-select="true" 
                            :show-labels="false"
                            :allow-empty="true"
                            class="multiselect--with-icon"
                        > </Multiselect>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-6 mb-3">
                <select
                    v-model="per_page"
                    class="rounded-full border-black shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 w-[200px] pl-3 pr-8 py-2 text-sm"
                >
                    <option :value="6">6 per page</option>
                    <option :value="25">25 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                </select>
            </div>
        </div>
        
        <div class="overflow-auto mb-[80px]">
            <div class="text-gray-900">
                <table class="min-w-full border border-gray-200 divide-y divide-gray-200 rounded-xl overflow-hidden">
                    <thead style="background-color: #eef1f8" class="rounded-t-xl overflow-hidden">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4">SN</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4">Location</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4">Warehouse</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="!locations.data || locations.data.length === 0">
                            <td colspan="4" class="px-3 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="text-lg font-medium">No locations found</span>
                                    <p class="text-sm text-gray-400 mt-1">Try adjusting your search or filters</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(l, i) in locations.data" :key="l.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">{{ i + 1 }}</td>
                            <td class="px-4 py-4 whitespace-nowrap font-medium">{{ l.location }}</td>
                            <td class="px-4 py-4 whitespace-nowrap">{{ l.warehouse?.name }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                <button @click="editLocation(l.id)" class="inline-flex items-center px-3 py-1.5 border border-indigo-500 text-indigo-600 rounded-full hover:bg-indigo-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="locations.data && locations.data.length > 0" class="mt-6 px-4 py-3 flex items-center justify-end border-t border-gray-200 sm:px-6 bg-white rounded-b-xl">
                <TailwindPagination
                    :data="locations"
                    @pagination-change-page="getResults"
                    :limit="5"
                    class="flex items-center space-x-3 text-sm"
                >
                    <template #prev-nav>
                        <span
                            class="flex items-center justify-center w-8 h-8 border border-gray-300 rounded-full hover:bg-gray-50 cursor-pointer"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </span>
                    </template>
                    <template #next-nav>
                        <span
                            class="flex items-center justify-center w-8 h-8 border border-gray-300 rounded-full hover:bg-gray-50 cursor-pointer"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </span>
                    </template>
                    <template #default="{ page, url, isActive }">
                        <a
                            :href="url"
                            @click.prevent="getResults(page)"
                            class="flex items-center justify-center w-8 h-8 border text-sm font-medium rounded-full cursor-pointer"
                            :class="{
                                'bg-indigo-600 border-indigo-600 text-white': isActive,
                                'bg-white border-gray-300 text-gray-700 hover:bg-gray-50': !isActive,
                            }"
                        >
                            {{ page }}
                        </a>
                    </template>
                </TailwindPagination>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import moment from "moment";
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from 'laravel-vue-pagination';

const props = defineProps({
    locations: {
        required: true,
        type: Object
    },
    warehouses: Array,
    filters: Object
});

// Create a computed reference to the locations data for easier access
const locations = computed(() => props.locations);

const search = ref(props.filters?.search || '');
const warehouse = ref(props.filters?.warehouse || []);
const per_page = ref(props.filters?.per_page || 25);

watch([
    () => search.value,
    () => warehouse.value,
    () => per_page.value,
], () => {
    getLocations();
});

function getLocations() {
    const query = {}
    if(search.value) query.search = search.value;
    if(warehouse.value) query.warehouse = warehouse.value;
    if(per_page.value) query.per_page = per_page.value;
    if(props.filters.page) query.page = props.filters.page;
    
    router.get(route('inventories.location.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            'locations', 'warehouses', 'filters'
        ]
    });
}

function getResults(page = 1) {
    // Initialize query object safely
    const query = {};
    
    // Set the page parameter
    query.page = page;
    
    // Add search value if it exists
    if(search.value) query.search = search.value;
    
    // Add warehouse filter if it exists
    if(warehouse.value) query.warehouse = warehouse.value;
    
    // Add per_page parameter if it exists
    if(per_page.value) query.per_page = per_page.value;
    
    // Navigate to the new page with all filters preserved
    router.get(route('inventories.location.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['locations', 'warehouses', 'filters']
    });
}


function editLocation(id) {
    router.get(route('inventories.location.edit', id));
}
</script>

<style scoped>
.filter-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    z-index: 1;
}

.multiselect--with-icon .multiselect__tags {
    padding-left: 35px !important;
}

.multiselect--rounded .multiselect__tags {
    border-radius: 9999px !important;
    border: 1px solid black !important;
}

.multiselect--rounded .multiselect__content-wrapper {
    border-radius: 1rem !important;
    margin-top: 5px;
}
</style>

<style>
/* Make all filter inputs have border radius of 50% with black borders */
.multiselect__tags {
    border-radius: 50px !important;
    border: 1px solid black !important;
}

/* Style the text input to match the rounded filter style */
input[type="text"] {
    border-radius: 50px !important;
    border: 1px solid black !important;
}

/* Style the dropdown menu to match */
.multiselect__content-wrapper {
    border-radius: 20px !important;
    border: 1px solid black !important;
    margin-top: 5px;
    overflow: hidden;
}

/* Add padding for icons in search input */
.search-with-icon {
    position: relative;
}

.search-with-icon input {
    padding-left: 40px !important;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: #6b7280;
}

/* Style for filter icons */
.filter-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: #6b7280;
}

.multiselect--with-icon .multiselect__tags {
    padding-left: 40px !important;
}
</style>