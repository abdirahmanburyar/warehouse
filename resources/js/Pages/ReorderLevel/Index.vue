<template>

    <Head title="Reorder Levels" />
    <AuthenticatedLayout title="Reorder Levels" description="Manage reorder levels for inventory items"
        img="/assets/images/products.png">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reorder Levels
            </h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header with Create Button -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Reorder Level Management</h3>
                        <p class="text-sm text-gray-600 mt-1">Manage reorder levels for inventory items</p>
                    </div>
                    <Link :href="route('reorder-levels.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Reorder Level
                    </Link>
                </div>

                <!-- Success Message -->
                <div v-if="$page.props.flash.success"
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Search and Filters -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input id="search" v-model="search" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Search reorder levels..." />
                        </div>
                    </div>
                    <div class="sm:w-48">
                        <label for="per_page" class="sr-only">Items per page</label>
                        <select id="per_page" v-model="per_page" @change="props.filters.page = 1"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    AMC
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lead Time
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Reorder Level
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="reorderLevel in props.reorderLevels.data" :key="reorderLevel.id"
                                class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ reorderLevel.product?.name || 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ID: {{ reorderLevel.product?.productID || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ reorderLevel.amc }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ reorderLevel.lead_time }} days
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ reorderLevel.reorder_level }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(reorderLevel.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <Link :href="route('reorder-levels.edit', reorderLevel.id)"
                                            class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors duration-200"
                                            title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        </Link>
                                        <button
                                            @click="deleteReorderLevel(reorderLevel.id, reorderLevel.product?.name || 'this item')"
                                            class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors duration-200"
                                            title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="props.reorderLevels.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No reorder levels</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new reorder level.</p>
                    <div class="mt-6">
                        <Link :href="route('reorder-levels.create')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Add Reorder Level
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="props.reorderLevels.data.length > 0" class="mt-6">
                    <TailwindPagination :data="props.reorderLevels" :limit="2"
                        @pagination-change-page="handlePageChange" class="flex justify-center" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    reorderLevels: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const per_page = ref(props.filters.per_page || 25);
const page = ref(props.filters.page || 1);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const deleteReorderLevel = async (id, itemName) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete the reorder level for "${itemName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            await router.delete(route('reorder-levels.destroy', id));

            // Show success message
            Swal.fire(
                'Deleted!',
                'The reorder level has been deleted successfully.',
                'success'
            );
        } catch (error) {
            // Show error message
            Swal.fire(
                'Error!',
                'Something went wrong while deleting the reorder level.',
                'error'
            );
        }
    }
};

// Watch for changes and reload data
watch([
    () => search.value,
    () => per_page.value,
    () => page.value,
], () => {
    reloadReorderLevels();
});

const reloadReorderLevels = () => {
    const query = {};
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (page.value) query.page = page.value;

    router.get(route("reorder-levels.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["reorderLevels", "filters"],
    });
};

const handlePageChange = (pageNumber) => {
    props.filters.page = pageNumber;
};
</script>