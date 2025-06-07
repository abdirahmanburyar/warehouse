<template>
    <AuthenticatedLayout title="Review Your Reports" description="Facilities - Monthly Consumptions"
        img="/assets/images/report.png">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-[100px]">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Monthly Consumption Report</h1>

                <!-- We'll move the product filter to the item column header -->

                <!-- Filters in a single row -->
                <div class="mb-6 flex flex-wrap items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                        <Multiselect v-model="filters.facility_id"
                            :options="[{ id: null, name: 'All Facilities' }, ...facilities]" :searchable="true"
                            :close-on-select="true" :show-labels="false" :allow-empty="true"
                            placeholder="Select Facility" track-by="id" label="name" class="mt-1">
                            <template v-slot:option="{ option }">
                                <div>
                                    <span>{{ option.name }} {{ option.id ? `(${option.facility_type})` : '' }}</span>
                                </div>
                            </template>
                        </Multiselect>
                    </div>

                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Month</label>
                        <input type="month" v-model="filters.start_month"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    </div>

                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Month</label>
                        <input type="month" v-model="filters.end_month"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    </div>

                    <div class="flex space-x-2">
                        <button @click="clearFilters"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Clear
                        </button>
                        <button @click="exportToExcel" v-if="pivotData.length > 0" :disabled="loading"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export to Excel
                        </button>
                        <button @click="applyFilters" :disabled="loading"
                            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ loading ? 'Generating' : 'Generate' }}
                        </button>

                        <!-- Excel Upload Button -->
                        <button @click="openUploadModal"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Upload Excel
                        </button>
                    </div>
                </div>

                <!-- Facility Information -->
                <div v-if="facilityInfo && !loading" class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-xl font-semibold mb-3 text-center">Facility Information</h2>

                    <div class="flex flex-col md:flex-row justify-between max-w-4xl mx-auto mb-4 text-sm">
                        <div class="flex-1 p-3">
                            <table class="w-full">
                                <tr>
                                    <td class="font-medium pr-2 py-1 align-top">Name:</td>
                                    <td>{{ facilityInfo.name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium pr-2 py-1 align-top">Type:</td>
                                    <td>{{ facilityInfo.facility_type }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium pr-2 py-1 align-top">Email:</td>
                                    <td>{{ facilityInfo.email || 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium pr-2 py-1 align-top">Phone:</td>
                                    <td>{{ facilityInfo.phone || 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium pr-2 py-1 align-top">Address:</td>
                                    <td>{{ facilityInfo.address || 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="flex-1 p-3 border-t md:border-t-0 md:border-l border-gray-200">
                            <table class="w-full" v-if="facilityInfo.user">
                                <tr>
                                    <td class="font-medium pr-2 py-1 align-top">Manager:</td>
                                    <td>{{ facilityInfo.user.name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium pr-2 py-1 align-top">Email:</td>
                                    <td>{{ facilityInfo.user.email }}</td>
                                </tr>
                            </table>
                            <p v-else class="text-gray-500 italic py-2">No manager assigned</p>
                        </div>
                    </div>

                    <div class="text-center text-sm text-gray-600">
                        <p>Report Period: {{ formatMonth(filters.start_month) }} to {{ formatMonth(filters.end_month) }}
                        </p>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table v-if="filteredPivotData && filteredPivotData.length > 0"
                        class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr class="border-b border-gray-200">
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SN
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex flex-col">
                                        <span class="mb-2">Items Description</span>
                                        <div v-if="pivotData && pivotData.length > 0" class="flex">
                                            <input v-model="localFilters.productSearch" type="text"
                                                placeholder="Filter items..."
                                                class="block w-full text-sm font-normal rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                            <button v-if="localFilters.productSearch"
                                                @click="localFilters.productSearch = ''"
                                                class="ml-1 px-2 text-xs text-gray-500 hover:text-gray-700 focus:outline-none">
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </th>
                                <!-- Regular month columns and average columns after every 3 months -->
                                <template v-for="(month, index) in months" :key="month">
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ formatMonthShort(month) }}-{{ formatYear(month) }}
                                    </th>
                                    <!-- Add average column after every 3 months -->
                                    <th v-if="(index + 1) % 4 === 0 && index > 0"
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-sky-500">
                                        AMC
                                    </th>
                                </template>
                                <!-- Add final average if months count is not divisible by 4 -->
                                <th v-if="months.length % 4 !== 0 && months.length > 0"
                                    class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-sky-500">
                                    AMC
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(row, index) in filteredPivotData" :key="index" class="border-b border-gray-200">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200 text-center">
                                    {{ row.sn }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">
                                    {{ row.item_name }}
                                </td>

                                <!-- Regular month columns and average columns after every 3 months -->
                                <template v-for="(month, index) in months" :key="month">
                                    <!-- Regular month column -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200 text-center">
                                        <span>{{ row[month] || 0 }}</span>
                                    </td>

                                    <!-- Average column after every 3 months -->
                                    <td v-if="(index + 1) % 4 === 0 && index > 0"
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white border-r border-gray-200 text-center bg-sky-500">
                                        {{ calculateThreeMonthAverage(row, index) }}
                                    </td>
                                </template>

                                <!-- Add final average if months count is not divisible by 4 -->
                                <td v-if="months.length % 4 !== 0 && months.length > 0"
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white border-r border-gray-200 text-center bg-sky-500">
                                    {{ calculateRemainingMonthsAverage(row) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-else-if="pivotData && pivotData.length > 0 && filteredPivotData.length === 0"
                        class="text-center py-10 text-gray-500">
                        No products match your search criteria. Clear the search to see all products.
                    </div>
                    <div v-else-if="loading" class="text-center py-10 text-gray-500">
                        <svg class="animate-spin h-10 w-10 mx-auto mb-4 text-orange-500"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p>Loading consumption data...</p>
                    </div>
                    <div v-else class="text-center py-10 text-gray-500">
                        No consumption data found for the selected filters.
                    </div>
                </div>
            </div>
        </div>

        <!-- Excel Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-[60%] mx-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Upload Consumption Data</h3>
                    <button @click="showUploadModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid md:grid-cols-2 sm:grid-cols-1 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Facility</label>
                        <Multiselect v-model="modalFacilityId" :options="facilities" :searchable="true"
                            :close-on-select="true" :show-labels="false" :allow-empty="false"
                            placeholder="Select Facility" track-by="id" label="name" class="mt-1">
                            <template v-slot:option="{ option }">
                                <div>
                                    <span>{{ option.name }} {{ option.facility_type ? `(${option.facility_type})` : ''
                                        }}</span>
                                </div>
                            </template>
                        </Multiselect>
                        <p v-if="modalFacilityError" class="mt-1 text-sm text-red-600">{{ modalFacilityError }}</p>
                    </div>
                    <div>
                        <label for="month_year" class="block text-sm font-medium text-gray-700 mb-1">Month Year</label>
                        <input type="month" id="month_year" v-model="month_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Excel File</label>
                    <div class="flex items-center justify-center w-full">
                        <label
                            class="flex flex-col w-full h-32 border-2 border-dashed hover:bg-gray-100 hover:border-gray-300 rounded-lg">
                            <div class="flex flex-col items-center justify-center pt-7" v-if="!selectedFile">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-12 h-12 text-gray-400 group-hover:text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">Select
                                    Excel file
                                </p>
                            </div>
                            <div class="flex flex-col items-center justify-center pt-7" v-else>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <p class="pt-1 text-sm text-gray-700">{{ selectedFile.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                            </div>
                            <input type="file" class="opacity-0" @change="handleFileSelect" accept=".xlsx, .xls" />
                        </label>
                    </div>
                    <p v-if="fileError" class="mt-1 text-sm text-red-600">{{ fileError }}</p>
                </div>

                <div class="flex justify-end space-x-2">
                    <button @click="showUploadModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button @click="uploadFile" :disabled="uploading"
                        class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                        <svg v-if="uploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{ uploading ? 'Uploading...' : 'Upload' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import * as XLSX from 'xlsx';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import axios from 'axios';

const props = defineProps({
    pivotData: Array,
    months: Array,
    facilities: Array,
    products: Array,
    facilityInfo: Object,
    yearMonths: Array,
});

// For local filtering after report is loaded
const localFilters = ref({
    productSearch: ''
});

// Filtered data based on text search
const filteredPivotData = computed(() => {
    if (!props.pivotData || !localFilters.value.productSearch) {
        return props.pivotData;
    }

    const searchTerm = localFilters.value.productSearch.toLowerCase();
    return props.pivotData.filter(item =>
        item.item_name && item.item_name.toLowerCase().includes(searchTerm)
    );
});

// Initialize filters with props values or defaults
const filters = ref({
    facility_id: props.filters?.facility_id || null,
    start_month: props.filters?.start_month || '',
    end_month: props.filters?.end_month || ''
});

// Loading states
const loading = ref(false);
const uploading = ref(false);
const fileInput = ref(null);

// Upload modal states
const showUploadModal = ref(false);
const modalFacilityId = ref(null);
const modalFacilityError = ref(null);
const selectedFile = ref(null);
const fileError = ref(null);

// Apply filters and reload data
function applyFilters() {
    // Validate required fields
    if (!filters.value.facility_id) {
        Swal.fire({
            title: 'Missing Information',
            text: 'Please select a facility',
            icon: 'warning',
            confirmButtonColor: '#f97316'
        });
        return;
    }
    if (!filters.value.start_month) {
        Swal.fire({
            title: 'Missing Information',
            text: 'Please select a start month',
            icon: 'warning',
            confirmButtonColor: '#f97316'
        });
        return;
    }
    if (!filters.value.end_month) {
        Swal.fire({
            title: 'Missing Information',
            text: 'Please select an end month',
            icon: 'warning',
            confirmButtonColor: '#f97316'
        });
        return;
    }

    // Set loading state
    loading.value = true;

    // Show loading indicator
    Swal.fire({
        title: 'Generating Report',
        text: 'Preparing report data...',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Directly fetch the data with current filters - don't clear filters
    router.get(route('reports.monthlyConsumption'), {
        facility_id: filters.value.facility_id,
        product_id: filters.value.product_id,
        start_month: filters.value.start_month,
        end_month: filters.value.end_month,
        is_submitted: true
    }, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            // Reset loading state when request completes
            loading.value = false;
            Swal.close();
        }
    });
}

// Clear all filters and reset to default values
function clearFilters() {
    // Show loading indicator
    Swal.fire({
        title: 'Clearing Data',
        text: 'Clearing all filters, facility information, and report data...',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Reset filters to default values
    filters.value = {
        facility_id: null,
        product_id: null,
        start_month: new Date().getFullYear() + '-01', // January of current year
        end_month: new Date().getFullYear() + '-12',   // December of current year
        is_submitted: false
    };

    // Remove query parameters from URL and reset the view
    const baseUrl = window.location.pathname;
    window.history.replaceState({}, document.title, baseUrl);

    // Always clear the data display completely
    router.visit(route('reports.monthlyConsumption'), {
        method: 'get',
        data: {},
        preserveState: false,
        replace: true,
        onSuccess: () => {
            // Show a success message
            Swal.fire({
                title: 'Data Cleared',
                text: 'All filters, facility information, and report data have been cleared',
                icon: 'success',
                confirmButtonColor: '#f97316',
                timer: 2000,
                timerProgressBar: true
            });
        }
    });
}

// Format month from YYYY-MM to MMM YYYY (e.g., 2025-05 to May 2025)
function formatMonth(monthStr) {
    const [year, month] = monthStr.split('-');
    const date = new Date(year, parseInt(month) - 1);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

// Format month from YYYY-MM to MMM (e.g., 2025-05 to May)
function formatMonthShort(monthStr) {
    const [year, month] = monthStr.split('-');
    const date = new Date(year, parseInt(month) - 1);
    return date.toLocaleDateString('en-US', { month: 'short' });
}

// Open the upload modal
function openUploadModal() {
    showUploadModal.value = true;

    // Set the selected facility in the modal
    if (filters.value.facility_id) {
        // Find the facility object that matches the ID
        const selectedFacility = props.facilities.find(f => f.id === filters.value.facility_id);
        modalFacilityId.value = selectedFacility || null;
    } else {
        modalFacilityId.value = null;
    }

    selectedFile.value = null;
    fileError.value = null;
    modalFacilityError.value = null;
}

// Handle file selection in the modal
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    if (!validTypes.includes(file.type)) {
        fileError.value = 'Please select a valid Excel file (.xlsx or .xls)';
        selectedFile.value = null;
        return;
    }

    selectedFile.value = file;
    fileError.value = null;
}

// Format file size for display
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

const month_year = ref('');

// Upload file from the modal
async function uploadFile() {
    // Reset errors
    fileError.value = null;
    modalFacilityError.value = null;

    // Validate inputs
    if (!modalFacilityId.value) {
        modalFacilityError.value = 'Please select a facility';
        return;
    }

    if (!selectedFile.value) {
        fileError.value = 'Please select an Excel file';
        return;
    }

    // Get the facility ID from the selected facility object
    const facilityId = modalFacilityId.value.id;

    // Create form data
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('facility_id', parseInt(facilityId));
    formData.append('month_year', month_year.value);

    // Set uploading state
    uploading.value = true;

    // Upload the file
    await axios.post(route('reports.upload-consumption'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
        .then((response) => {
            uploading.value = false;
            console.log(response);
            // Close modal and reset form
            // showUploadModal.value = false;
            // selectedFile.value = null;

            // // Store the facility object before resetting modalFacilityId
            // const uploadedFacility = modalFacilityId.value;
            // modalFacilityId.value = null;
            // month_year.value = null;

            // // Update filters to match the uploaded facility (using the full facility object)
            // filters.value.facility_id = uploadedFacility;

            // // Set date range from February to December of current year
            // const currentYear = new Date().getFullYear();
            // filters.value.start_month = `${currentYear}-02`; // February
            // filters.value.end_month = `${currentYear}-12`;   // December

            // // Log the selected facility for debugging
            // console.log('Selected facility after upload:', filters.value.facility_id);

            // // Refresh report data with explicit facility_id
            // applyFilters();
        })
        .catch((error) => {
            uploading.value = false;
            console.log(error);
            // Show error message
            Swal.fire({
                title: 'Upload Failed',
                text: 'An error occurred while uploading the file',
                icon: 'error',
                confirmButtonColor: '#f97316'
            });
        });
}

// Legacy file upload handler (keeping for backward compatibility)
async function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate facility selection - must be a specific facility with an integer ID
    if (!Number.isInteger(Number(filters.value.facility_id))) {
        Swal.fire({
            title: 'Specific Facility Required',
            text: 'Please select a specific facility before uploading consumption data. "All Facilities" option cannot be used for uploads.',
            icon: 'warning',
            confirmButtonColor: '#f97316'
        });
        if (fileInput.value) fileInput.value.value = null;
        return;
    }

    uploading.value = true;

    try {
        // Create form data
        const formData = new FormData();
        formData.append('file', file);
        formData.append('facility_id', filters.value.facility_id);

        // Upload the file
        const response = await axios.post(route('reports.upload-consumption'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        // Show success message
        Swal.fire({
            title: 'Upload Successful',
            text: response.data.message,
            icon: 'success',
            confirmButtonColor: '#f97316'
        });

        // Reload the data
        applyFilters();
    } catch (error) {
        // Show error message
        Swal.fire({
            title: 'Upload Failed',
            text: error.response?.data?.message || 'An error occurred while uploading the file.',
            icon: 'error',
            confirmButtonColor: '#f97316'
        });
    } finally {
        uploading.value = false;
        if (fileInput.value) fileInput.value.value = null; // Reset file input
    }
}

// Format year from YYYY-MM to YY (e.g., 2025-05 to 25)
function formatYear(monthStr) {
    const [year] = monthStr.split('-');
    return year.slice(-2); // Return just the last two digits
}

// Calculate total for a row
function calculateRowTotal(row) {
    return props.months.reduce((total, month) => {
        return total + (parseInt(row[month]) || 0);
    }, 0);
}

// Calculate total for a column
function calculateColumnTotal(month) {
    return props.pivotData.reduce((total, row) => {
        return total + (parseInt(row[month]) || 0);
    }, 0);
}

// Calculate grand total
function calculateGrandTotal() {
    return props.pivotData.reduce((total, row) => {
        return total + calculateRowTotal(row);
    }, 0);
}

// Calculate average consumption for three months
function calculateThreeMonthAverage(row, currentIndex) {
    // Get the three months we need to average
    const startIndex = currentIndex - 2;
    const months = props.months.slice(startIndex, currentIndex + 1);

    // Calculate the sum of these three months
    let sum = 0;
    let count = 0;

    months.forEach(month => {
        const value = parseInt(row[month] || 0);
        sum += value;
        count++;
    });

    // Calculate and format the average
    const average = count > 0 ? sum / count : 0;
    return average.toFixed(1);
}

// Calculate average for remaining months (when not divisible by 3)
function calculateRemainingMonthsAverage(row) {
    const remainingCount = props.months.length % 3;
    if (remainingCount === 0) return 0;

    // Get the remaining months
    const startIndex = props.months.length - remainingCount;
    const months = props.months.slice(startIndex);

    // Calculate the sum of these months
    let sum = 0;
    let count = 0;

    months.forEach(month => {
        const value = parseInt(row[month] || 0);
        sum += value;
        count++;
    });

    // Calculate and format the average
    const average = count > 0 ? sum / count : 0;
    return average.toFixed(1);
}

// Export data to Excel
function exportToExcel() {
    try {
        // Create worksheet data
        const wsData = [];

        // Prepare header row to calculate total columns
        const headerRow = ['SN', 'Items Description'];
        if (props.months && props.months.length > 0) {
            props.months.forEach(month => {
                headerRow.push(`${formatMonthShort(month)}-${formatYear(month)}`);
            });
        }
        const totalColumns = headerRow.length;

        // Add facility information if available
        if (props.facilityInfo) {
            // Title row
            wsData.push(['Facility Information']);

            // Facility details
            wsData.push(['Name', props.facilityInfo.name]);
            wsData.push(['Type', props.facilityInfo.facility_type]);
            wsData.push(['Email', props.facilityInfo.email || 'N/A']);
            wsData.push(['Phone', props.facilityInfo.phone || 'N/A']);
            wsData.push(['Address', props.facilityInfo.address || 'N/A']);

            // Manager details
            if (props.facilityInfo.user) {
                wsData.push(['Manager', props.facilityInfo.user.name]);
                wsData.push(['Manager Email', props.facilityInfo.user.email]);
            } else {
                wsData.push(['Manager', 'Not Assigned']);
            }

            // Report period
            wsData.push(['Report Period',
                `${formatMonth(filters.value.start_month)} to ${formatMonth(filters.value.end_month)}`]);

            // Empty row as separator
            wsData.push([]);
        }

        // Prepare the header row with average columns
        const excelHeaderRow = ['SN', 'Items'];

        // Add month headers and average headers
        props.months.forEach((month, index) => {
            // Add regular month header
            excelHeaderRow.push(`${formatMonthShort(month)}-${formatYear(month)}`);

            // Add average header after every 3 months
            if ((index + 1) % 3 === 0 && index > 0) {
                excelHeaderRow.push('AMC');
            }
        });

        // Add final average header if needed
        if (props.months.length % 3 !== 0 && props.months.length > 0) {
            excelHeaderRow.push('AMC');
        }

        // Add the header row to the worksheet
        wsData.push(excelHeaderRow);

        // Add data rows with averages
        props.pivotData.forEach(row => {
            const dataRow = [row.sn, row.item_name || 'N/A'];

            // Add month values and average values
            props.months.forEach((month, index) => {
                // Add regular month value
                dataRow.push(row[month] || 0);

                // Add average value after every 3 months
                if ((index + 1) % 3 === 0 && index > 0) {
                    dataRow.push(calculateThreeMonthAverage(row, index));
                }
            });

            // Add final average if needed
            if (props.months.length % 3 !== 0 && props.months.length > 0) {
                dataRow.push(calculateRemainingMonthsAverage(row));
            }

            wsData.push(dataRow);
        });

        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(wsData);

        // Add styling to AMC columns in Excel
        // Track AMC column indices
        const amcColumns = [];
        let colIndex = 2; // Start after SN and Items columns

        // Find AMC column indices
        props.months.forEach((month, index) => {
            colIndex++; // Move to next column after each month
            if ((index + 1) % 3 === 0 && index > 0) {
                amcColumns.push(colIndex);
                colIndex++; // Move past the AMC column
            }
        });

        // Add final AMC column if needed
        if (props.months.length % 3 !== 0 && props.months.length > 0) {
            amcColumns.push(colIndex);
        }

        // Apply sky blue color to all cells in AMC columns
        if (!ws['!cols']) ws['!cols'] = [];

        // Define the column style for AMC columns
        amcColumns.forEach(col => {
            // Convert column index to letter (e.g., 3 -> D)
            const colLetter = XLSX.utils.encode_col(col);

            // Apply background color to all cells in this column
            for (let row = 0; row < wsData.length; row++) {
                const cellRef = colLetter + (row + 1);
                if (!ws[cellRef]) ws[cellRef] = { v: '' };
                if (!ws[cellRef].s) ws[cellRef].s = {};
                ws[cellRef].s.fill = { fgColor: { rgb: "87CEEB" } }; // Sky blue color
                ws[cellRef].s.font = { color: { rgb: "FFFFFF" } }; // White text

                // Add bold to header row
                if (row === 0) {
                    if (!ws[cellRef].s.font) ws[cellRef].s.font = {};
                    ws[cellRef].s.font.bold = true;
                }
            }
        });

        // Create workbook
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Monthly Consumption');

        // Generate filename
        const facilityName = props.facilityInfo ? props.facilityInfo.name.replace(/\s+/g, '_') : 'All_Facilities';
        const startDate = filters.value.start_month.replace('-', '_');
        const endDate = filters.value.end_month.replace('-', '_');
        const filename = `Monthly_Consumption_${facilityName}_${startDate}_to_${endDate}.xlsx`;

        // Export to Excel file
        XLSX.writeFile(wb, filename);
    } catch (error) {
        console.error('Error exporting to Excel:', error);
        Swal.fire({
            title: 'Export Error',
            text: 'There was an error exporting to Excel. Please try again.',
            icon: 'error',
            confirmButtonColor: '#f97316'
        });
    }
}
</script>
