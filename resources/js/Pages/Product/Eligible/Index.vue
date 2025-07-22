<template>
    <AuthenticatedLayout
        title="Facility Types"
        description="Manage facility types for product organization"
        img="/assets/images/products.png"
    >
        <Head title="Facility Types" />

        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
            <Link :href="route('products.index')" class="hover:text-gray-900 transition-colors duration-200">
                Products
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-gray-900">Facility Types</span>
        </div>

        <div class="bg-white">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Facility Types</h2>
                    </div>
                    <div class="flex items-center space-x-3">
                        <Link
                            :href="route('products.eligible.create')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create Eligible Item
                        </Link>
                        <Link
                            :href="route('products.facility-types.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            New Facility Type
                        </Link>
                    </div>
                </div>
            </div>

        <!-- Search and Filters Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search facility types..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <select 
                            v-model="perPage" 
                            class="px-3 py-2.5 w-[200px] border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            @change="props.filters.page = 1"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Facility Types Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="!eligibleItems.data.length" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No facility types found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new facility type.</p>
                <div class="mt-6">
                    <Link :href="route('products.facility-types.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Facility Type
                    </Link>
                </div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Facility Type Name
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="item in eligibleItems.data"
                            :key="item.id"
                            class="hover:bg-gray-50 transition-colors duration-200"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        {{ item.name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': item.is_active,
                                        'bg-red-100 text-red-800': !item.is_active,
                                    }"
                                >
                                    <span 
                                        class="w-2 h-2 rounded-full mr-2" 
                                        :class="{
                                            'bg-green-400': item.is_active,
                                            'bg-red-400': !item.is_active,
                                        }"
                                    ></span>
                                    {{ item.is_active ? "Active" : "Inactive" }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <Link
                                        :href="route('products.facility-types.edit', { facilityType: item.id })"
                                        class="inline-flex items-center p-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                                        title="Edit Facility Type"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </Link>
                                    <button
                                        @click="confirmToggleStatus(item)"
                                        :class="{
                                            'opacity-50 cursor-wait': loadingItems.has(item.id),
                                            'bg-gray-200': !item.is_active,
                                            'bg-green-600': item.is_active,
                                        }"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                        :disabled="loadingItems.has(item.id)"
                                        title="Toggle Status"
                                    >
                                        <span
                                            :class="{
                                                'translate-x-5': item.is_active,
                                                'translate-x-0': !item.is_active,
                                                'bg-gray-400 animate-pulse': loadingItems.has(item.id),
                                            }"
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        ></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <TailwindPagination
                :data="eligibleItems"
                :limit="2"
                @pagination-change-page="getResults"
            />
        </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import axios from "axios";
import { debounce } from "lodash";
import { useToast } from "vue-toastification";
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from "sweetalert2";

import * as XLSX from "xlsx";

const toast = useToast();

// Import form handling
const selectedFile = ref(null);
const importing = ref(false);
const importError = ref("");
const importSuccess = ref("");

const props = defineProps({
    eligibleItems: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },

});



const search = ref(props.filters.search || "");
const perPage = ref(props.filters.per_page || "10");

function getResults(page = 1) {
    props.filters.page = page;
}

function updateRoute() {
    const query = {};

    if (search.value) query.search = search.value;
    if (perPage.value) {
        // props.filters.page = 1;
        query.per_page = perPage.value;
    }
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("products.eligible.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["eligibleItems"],
    });
}

watch(
    [
        () => search.value,
        () => perPage.value,
        () => props.filters.page,
    ],
    () => {
        updateRoute();
    }
);

const isDeleteing = ref(false);
const isImporting = ref(false);
const loadingItems = ref(new Set());

function confirmToggleStatus(item) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${item.is_active ? 'deactivate' : 'activate'} this facility type?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            toggleStatus(item);
        }
    });
}

function toggleStatus(item) {
    loadingItems.value.add(item.id);
    
    axios.get(route('products.facility-types.toggle-status', { facilityType: item.id }))
        .then(response => {
            if (response.data.success) {
                toast.success(response.data.message);
                // Refresh the page to get updated data
                router.reload();
            } else {
                toast.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error toggling status:', error);
            toast.error('An error occurred while updating the status');
        })
        .finally(() => {
            loadingItems.value.delete(item.id);
        });
}

async function handleExcelUpload(event) {
    try {
        isImporting.value = true;
        const file = event.target.files[0];

        if (!file) {
            toast.error("Please select a file");
            isImporting.value = false;
            return;
        }

        // Check file type
        const fileType = file.name.split(".").pop().toLowerCase();
        if (!["xlsx", "xls"].includes(fileType)) {
            toast.error("Please upload a valid Excel file (.xlsx or .xls)");
            isImporting.value = false;
            event.target.value = null;
            return;
        }

        // Confirm import
        const result = await Swal.fire({
            title: "Import Eligible Items",
            text: "Are you sure you want to import this file?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, import!",
        });

        if (!result.isConfirmed) {
            isImporting.value = false;
            event.target.value = null;
            return;
        }

        // Create FormData and append file
        const formData = new FormData();
        formData.append("file", file);

        // Send to backend
        const response = await axios.post(
            route("products.eligible.import"),
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            }
        );

        toast.success(response.data.message);
        toast.info(
            "Processing in background. Page will refresh in 10 seconds."
        );

        setTimeout(() => {
            updateRoute();
        }, 10000);
    } catch (error) {
        console.error("Import error:", error);
        const errorMessage =
            error.response?.data?.message ||
            error.message ||
            "Failed to import eligible items";
        toast.error(errorMessage);
    } finally {
        isImporting.value = false;
        event.target.value = null;
    }
}
</script>
