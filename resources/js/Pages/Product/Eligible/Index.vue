<template>
    <AuthenticatedLayout
        title="Eligible Items"
        description="Manage product eligibility for facilities"
        img="/assets/images/products.png"
        >
        <div class="mb-6">
            <Link :href="route('products.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Products
            </Link>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Eligible Items</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage product eligibility for different facility types</p>
                </div>
                <Link
                    :href="route('products.eligible.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Eligible Item
                </Link>
            </div>
        </div>

        <!-- Import Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Import Eligible Items</h3>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-1">
                        <input
                            type="file"
                            id="importFile"
                            accept=".xlsx,.xls"
                            @change="handleFileUpload"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors duration-200"
                        />
                    </div>
                    <button
                        @click="submitImport"
                        :disabled="importing || !selectedFile"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="importing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ importing ? "Importing..." : "Import" }}
                    </button>
                </div>
                <div v-if="importError" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-red-600">{{ importError }}</span>
                    </div>
                </div>
                <div v-if="importSuccess" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-green-600">{{ importSuccess }}</span>
                    </div>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                    <p class="font-medium mb-1">Upload an Excel file (.xlsx/.xls) with columns:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-500">
                        <li>item_description</li>
                        <li>facility_type</li>
                    </ul>
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
                                placeholder="Search eligible items..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-64">
                            <Multiselect
                                v-model="facilityType"
                                :options="facilityTypes"
                                :close-on-select="true"
                                :searchable="true"
                                :show-labels="false"
                                placeholder="Select Facility Type"
                                class="text-sm"
                            />
                        </div>
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

        <!-- Eligible Items Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="!eligibleItems.data.length" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No eligible items</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new eligible item or importing from Excel.</p>
                <div class="mt-6 flex justify-center space-x-3">
                    <Link :href="route('products.eligible.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Create Eligible Item
                    </Link>
                </div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 w-[350px] py-3 text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                                Item Name
                            </th>
                            <th class="px-6 w-[350px] py-3 text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                                Facility Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in eligibleItems.data" :key="item.id" class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ item.product?.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ item.facility_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <Link
                                        :href="route('products.eligible.edit', item.id)"
                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-200 p-1 rounded-md hover:bg-blue-50"
                                        title="Edit Eligible Item"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="destroy(item)"
                                        class="text-red-600 hover:text-red-900 transition-colors duration-200 p-1 rounded-md hover:bg-red-50"
                                        title="Delete Eligible Item"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
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
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import axios from "axios";
import { debounce } from "lodash";
import { useToast } from "vue-toastification";
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import * as XLSX from "xlsx";

const toast = useToast();

// Import form handling
const selectedFile = ref(null);
const importing = ref(false);
const importError = ref("");
const importSuccess = ref("");

const props = defineProps({
    eligibleItems: Object,
    filters: Object,
});

const handleFileUpload = (e) => {
    selectedFile.value = e.target.files[0];
};

const submitImport = async () => {
    const maxFileSize = 50 * 1024 * 1024; // 50MB
    if (!selectedFile.value) {
        importError.value = "Please select a file to import";
        return;
    }

    if (selectedFile.value.size > maxFileSize) {
        importError.value = "File size must be less than 50MB";
        return;
    }

    // Validate file type
    if (
        ![
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-excel",
        ].includes(selectedFile.value.type)
    ) {
        importError.value = "Please select a file to import";
        return;
    }

    importing.value = true;
    importError.value = "";
    importSuccess.value = "";

    try {
        const formData = new FormData();
        formData.append("file", selectedFile.value);

        const response = await axios.post(
            route("products.eligible.import"),
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            }
        );

        importSuccess.value = response.data.message;
        selectedFile.value = null;
        // Reset file input
        document.getElementById("importFile").value = "";
        toast.success("Import started successfully");
        toast.info(
            "Processing in background. This may take a few minutes for large files."
        );
    } catch (error) {
        console.error("Import error:", error);
        importError.value = error.response?.data?.message || "Import failed";
        toast.error(importError.value);
    } finally {
        importing.value = false;
    }
};

const facilityTypes = [
    'All',
    "Regional Hospital",
    "District Hospital",
    "Health Centre",
    "Primary Health Unit",
];

const search = ref(props.filters.search || "");
const perPage = ref(props.filters.per_page || "10");
const facilityType = ref(props.filters.facility_type || "");

function getResults(page = 1) {
    props.filters.page = page;
}

function updateRoute() {
    const query = {};

    if (search.value) query.search = search.value;
    if (facilityType.value) query.facility_type = facilityType.value;
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
        () => facilityType.value,
    ],
    () => {
        updateRoute();
    }
);

const isDeleteing = ref(false);
const isImporting = ref(false);
function destroy(item) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            isDeleteing.value = true;
            await axios
                .get(route("products.eligible.destroy", item.id))
                .then(() => {
                    isDeleteing.value = false;
                    toast.success("Eligible item deleted successfully");
                    updateRoute();
                })
                .catch(() => {
                    isDeleteing.value = false;
                    toast.error("Error deleting eligible item");
                });
        }
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
