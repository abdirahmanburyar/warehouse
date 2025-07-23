<template>
    <AuthenticatedLayout title="Locations Management" description="Manage Locations" img="/assets/images/location.png">
        <div class="mb-[100px]">
            <!-- Header & Actions -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Locations Management</h1>
                <div class="flex flex-wrap gap-2 md:gap-4 items-center">
                    <Link
                        :href="route('inventories.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Inventory
                    </Link>
                    <Link
                        :href="route('inventories.location.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create New Location
                    </Link>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-4 border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-end">
                    <div class="col-span-1 md:col-span-2 min-w-0">
                        <input
                            v-model="search"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="Search by location name..."
                        />
                    </div>
                    <div class="col-span-1 min-w-0">
                        <Multiselect
                            v-model="warehouse"
                            :options="warehouses"
                            placeholder="Select Warehouse"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            class="multiselect--with-icon w-full"
                        />
                    </div>
                </div>
                <div class="flex justify-end items-center gap-4 mt-3">
                    <select
                        v-model="per_page"
                        class="rounded-full border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-[200px] mb-3"
                        @change="props.filters.page = 1"
                    >
                        <option :value="6">6 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                        <option :value="100">100 per page</option>
                    </select>
                </div>
            </div>

            <!-- Locations Table -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <table class="w-full overflow-hidden text-sm text-left table-sm rounded-t-lg">
                    <thead>
                        <tr style="background-color: #F4F7FB;">
                            <th class="px-3 py-2 text-xs font-bold rounded-tl-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Location</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Warehouse</th>
                            <th class="px-3 py-2 text-xs font-bold rounded-tr-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                        <template v-if="props.locations.data.length === 0">
                            <tr>
                                <td colspan="3" class="text-center py-8 text-gray-500 bg-gray-50">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>No locations found.</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr
                                v-for="(location, index) in props.locations.data"
                                :key="location.id"
                                class="hover:bg-gray-50 transition-colors duration-150 border-b"
                                style="border-bottom: 1px solid #B7C6E6;"
                            >
                                <td class="px-3 py-2 text-xs font-medium text-gray-800 align-middle">
                                    {{ location.location }}
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-700 align-middle">
                                    {{ location.warehouse }}
                                </td>
                                <td class="px-3 py-2 text-xs align-middle">
                                    <div class="flex items-center justify-center space-x-3">
                                        <Link
                                            :href="route('inventories.location.edit', location.id)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-white hover:bg-indigo-600 border-2 border-indigo-200 hover:border-indigo-600 rounded-lg transition-all duration-200 transform hover:scale-105"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                        <div class="text-sm text-gray-700 font-medium">
                            Showing <span class="font-bold text-gray-900">{{ props.locations.meta.from || 0 }}</span> to 
                            <span class="font-bold text-gray-900">{{ props.locations.meta.to || 0 }}</span> of 
                            <span class="font-bold text-gray-900">{{ props.locations.meta.total || 0 }}</span> locations
                        </div>
                        <TailwindPagination
                            :data="props.locations"
                            :limit="2"
                            class="flex items-center space-x-2"
                            @pagination-change-page="getResults"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { TailwindPagination } from "laravel-vue-pagination";

const props = defineProps({
    locations: {
        required: true,
        type: Object,
    },
    warehouses: Array,
    filters: Object,
});

// Create a computed reference to the locations data for easier access
const locations = computed(() => props.locations);

const search = ref(props.filters?.search || "");
const warehouse = ref(props.filters?.warehouse || []);
const per_page = ref(props.filters?.per_page || 25);

watch([() => search.value, () => warehouse.value, () => per_page.value, () => props.filters.page], () => {
    getLocations();
});

function getLocations() {
    const query = {};
    if (search.value) query.search = search.value;
    if (warehouse.value) query.warehouse = warehouse.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("inventories.location.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["locations"],
    });
}

function getResults(page = 1) {
    // Initialize query object safely
    const query = {};

    // Set the page parameter
    query.page = page;

    // Add search value if it exists
    if (search.value) query.search = search.value;

    // Add warehouse filter if it exists
    if (warehouse.value) query.warehouse = warehouse.value;

    // Add per_page parameter if it exists
    if (per_page.value) query.per_page = per_page.value;

    // Navigate to the new page with all filters preserved
    router.get(route("inventories.location.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["locations", "warehouses", "filters"],
    });
}

function editLocation(id) {
    router.get(route("inventories.location.edit", id));
}
</script>
