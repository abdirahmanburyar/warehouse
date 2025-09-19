<template>
    <AuthenticatedLayout title="Review Your Reports" description="Facilities - Monthly Consumptions"
        img="/assets/images/report.png">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-[100px]">
            <div class=" bg-white border-b border-gray-200">
                <div class="flex justify-between mb-4">
                    <h1 class="text-xs text-gray-900">Monthly Consumption Report</h1>
                    <div class="flex space-x-2">
                        <button 
                            @click="exportToExcel" 
                            v-if="pivotData.length > 0" 
                            :disabled="loading"
                            class="px-2 py-1 bg-green-600 text-white text-[11px] font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export to Excel
                        </button>
                        
                        <!-- Download Template Button -->
                        <div class="relative group">
                            <button 
                                @click="downloadTemplate"
                                :disabled="!props.filters.facility_id"
                                class="px-2 py-1 text-[11px] font-medium rounded focus:outline-none focus:ring-1 focus:ring-offset-1 flex items-center gap-1 transition-all duration-200"
                                :class="props.filters.facility_id 
                                    ? 'bg-purple-600 text-white hover:bg-purple-700 focus:ring-purple-500 cursor-pointer' 
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed opacity-50'"
                                :title="!props.filters.facility_id ? 'Please select a facility first' : 'Download template with eligible products'"
                            >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Template
                        </button>
                        </div>
                        
                        <!-- Excel Upload Button -->
                        <button 
                            @click="openUploadModal"
                            class="px-2 py-1 bg-blue-600 text-white text-[11px] font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 flex items-center gap-1"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Upload Excel
                        </button>
                    </div>
                </div>

                <!-- We'll move the product filter to the item column header -->

                <!-- Filters in a single row -->
                <div class="mb-6 flex flex-wrap items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                        <Multiselect v-model="props.filters.facility_id"
                            :options="[{ id: null, name: 'All Facilities' }, ...facilities]" :searchable="true"
                            :close-on-select="true" :show-labels="false" :allow-empty="true"
                            class="text-xs mt-1"
                            placeholder="Select Facility" track-by="id" label="name">
                            <template v-slot:option="{ option }">
                                <div>
                                    <span>{{ option.name }} {{ option.id ? `(${option.facility_type})` : '' }}</span>
                                </div>
                            </template>
                        </Multiselect>
                    </div>

                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Month</label>
                        <input type="month" v-model="props.filters.start_month"
                            class="text-xs mt-1 block w-full rounded-md border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    </div>

                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Month</label>
                        <input type="month" v-model="props.filters.end_month"
                            class="text-xs mt-1 block w-full rounded-md border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    </div>                    
                </div>

                <div class="flex justify-end items-center gap-2 mb-4">
                    <button 
                        @click="clearFilters"
                        class="px-2 py-1 bg-gray-200 text-gray-700 text-[11px] font-medium rounded hover:bg-gray-300 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-gray-500"
                    >
                        Clear
                    </button>
                    <button 
                        @click="applyFilters" 
                        :disabled="loading"
                        class="px-2 py-1 bg-blue-600 text-white text-[11px] font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                    >
                        <span v-if="loading">
                            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        {{ loading ? 'Getting Report' : 'Get Report' }}
                    </button>
                </div>
                <!-- Facility Information -->
                <div v-if="facilityInfo && !loading" class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-xl font-semibold mb-3 text-center">Facility Information</h2>

                    <div class="flex flex-col md:flex-row justify-between max-w-4xl mx-auto mb-4 text-sm">
                        <div class="flex-1 p-3">
                            <table class="w-full">
                                <tbody>
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
                                </tbody>
                            </table>
                        </div>

                        <div class="flex-1 p-3 border-t md:border-t-0 md:border-l border-gray-200">
                            <table class="w-full" v-if="facilityInfo.user">
                                <tbody>
                                    <tr>
                                        <td class="font-medium pr-2 py-1 align-top">Manager:</td>
                                        <td>{{ facilityInfo.user.name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-medium pr-2 py-1 align-top">Email:</td>
                                        <td>{{ facilityInfo.user.email }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="text-gray-500 italic py-2">No manager assigned</p>
                        </div>
                    </div>

                    <div class="text-center text-sm text-gray-600">
                        <p>Report Period: {{ formatMonth(props.filters.start_month) }} to {{ formatMonth(props.filters.end_month) }}
                        </p>
                    </div>
                </div>
                <!-- Data Table -->

                <div class="overflow-x-auto">
                    <table v-if="pivotTableData.length > 0" class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SN
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex flex-col">
                                        <span>Items Description</span>
                                        <div class="mt-1">
                                            <div class="flex">
                                                <input v-model="productSearch" type="text" placeholder="Filter items..."
                                                    class="block w-full text-xs font-normal rounded-md border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                                                <button v-if="productSearch" @click="productSearch = ''"
                                                    class="ml-1 px-2 text-xs text-gray-500 hover:text-gray-700 focus:outline-none">
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <!-- Month columns -->
                                <th v-for="month in sortedMonths" :key="month"
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ formatMonthShort(month) }}-{{ formatYear(month) }}
                                </th>
                                <!-- Single AMC column at far right - Uses exact 70% deviation screening formula -->
                                <th class="px-4 py-2 text-center text-xs font-medium text-white uppercase tracking-wider bg-sky-500 relative group">
                                    AMC
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                        AMC: Finds 3 consistent months (≤70% deviation) and calculates their average
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(row, index) in filteredPivotTableData" :key="index" class="border-b border-gray-200">
                                <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-gray-900 border-r border-gray-200 text-center">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 border-r border-gray-200">
                                    {{ row.product_name }}
                                </td>
                                <!-- Month columns values -->
                                <td v-for="month in sortedMonths" :key="month"
                                    class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 border-r border-gray-200 text-center">
                                    {{ row[month] || 0 }}
                                </td>
                                <!-- Single AMC value -->
                                <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-white border-r border-gray-200 text-center bg-sky-500">
                                    {{ getBackendAMC(row) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

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
                    <p class="mt-2 text-sm text-gray-600">
                        <strong>Note:</strong> The Excel file should contain "Item Description" in the first column and month-year columns (e.g., Jan-25, Feb-25) for consumption data.
                    </p>
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

const emit = defineEmits(['update:filters']);

const props = defineProps({
    pivotData: Array,
    months: Array,
    facilities: Array,
    products: Array,
    yearMonths: Array,
    filters: Object,
    amcByProduct: Object
});

// For product search filtering
const productSearch = ref('');

// Extract unique months from the data
const months = computed(() => {
    if (!props.pivotData || props.pivotData.length === 0) return [];
    
    const uniqueMonths = new Set();
    props.pivotData.forEach(report => {
        uniqueMonths.add(report.month_year);
    });
    
    return Array.from(uniqueMonths);
});

// Sort months chronologically
const sortedMonths = computed(() => {
    return [...months.value].sort((a, b) => {
        // Parse dates and sort chronologically
        const dateA = new Date(a + '-01'); // Add day to make it a valid date
        const dateB = new Date(b + '-01');
        return dateA - dateB; // Ascending order (oldest first)
    });
});

// No grouping now; we render all months then a single AMC column

// Transform data into pivot table format
const pivotTableData = computed(() => {
    if (!props.pivotData || props.pivotData.length === 0) return [];
    
    // Create a map of product IDs to their data
    const productMap = new Map();
    
    // Process each report
    props.pivotData.forEach(report => {
        const monthYear = report.month_year;
        
        // Process each item in the report
        report.items.forEach(item => {
            const productId = item.product_id;
            const productName = item.product.name;
            const quantity = item.quantity;
            const uom = item.uom || item.product.uom || 'N/A';
            const batchNumber = item.batch_number || 'N/A';
            const barcode = item.barcode || item.product.barcode || 'N/A';
            const expiryDate = item.expiry_date || 'N/A';
            
            // Get or create product entry
            if (!productMap.has(productId)) {
                productMap.set(productId, {
                    product_id: productId,
                    product_name: productName,
                    uom: uom,
                    batch_number: batchNumber,
                    barcode: barcode,
                    expiry_date: expiryDate
                });
            }
            
            // Add month data
            const productData = productMap.get(productId);
            productData[monthYear] = quantity;
        });
    }); 
    
    // Convert map to array
    return Array.from(productMap.values());
});

// Filter pivot table data by product name
const filteredPivotTableData = computed(() => {
    if (!pivotTableData.value.length) return [];
    
    // Debug: Log all products in pivot data
    console.log('All products in pivotTableData:', pivotTableData.value.map(row => row.product_name));
    
    if (productSearch.value) {
        const searchTerm = productSearch.value.toLowerCase();
        const filtered = pivotTableData.value.filter(row => {
            return row.product_name.toLowerCase().includes(searchTerm);
        });
        console.log('Filtered products:', filtered.map(row => row.product_name));
        return filtered;
    }
    
    console.log('Using all pivot table data (no search filter)');
    return pivotTableData.value;
});

// Extract facility information from the pivot data
const facilityInfo = computed(() => {
    if (!props.pivotData || props.pivotData.length === 0) return {};
    
    // Get facility from the first report (all reports should have the same facility)
    const firstReport = props.pivotData[0];
    return firstReport.facility || {};
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
    if (!props.filters.facility_id) {
        Swal.fire({
            title: 'Missing Information',
            text: 'Please select a facility',
            icon: 'warning',
            confirmButtonColor: '#f97316'
        });
        return;
    }
    if (!props.filters.start_month) {
        Swal.fire({
            title: 'Missing Information',
            text: 'Please select a start month',
            icon: 'warning',
            confirmButtonColor: '#f97316'
        });
        return;
    }
    if (!props.filters.end_month) {
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
        facility_id: props.filters.facility_id,
        product_id: props.filters.product_id,
        start_month: props.filters.start_month,
        end_month: props.filters.end_month,
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

    // Create default filter values
    const defaultFilters = {
        facility_id: null,
        product_id: null,
        start_month: new Date().getFullYear() + '-01', // January of current year
        end_month: new Date().getFullYear() + '-12',   // December of current year
        is_submitted: false
    };
    
    // Emit event to update filters in parent component
    emit('update:filters', defaultFilters);

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
    if (!monthStr) return '';
    try {
        // Handle both YYYY-MM format and full date format
        let year, month;
        if (monthStr.length > 7) {
            // Full date format (e.g., 2025-05-15)
            const date = new Date(monthStr);
            return date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        } else {
            // YYYY-MM format
            [year, month] = monthStr.split('-');
            const date = new Date(parseInt(year), parseInt(month) - 1);
            return date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        }
    } catch (error) {
        console.error('Error formatting month:', error, monthStr);
        return monthStr; // Return original string if formatting fails
    }
}

// Format month short for display
function formatMonthShort(monthStr) {
    if (!monthStr) return '';
    try {
        // Handle both YYYY-MM format and full date format
        let year, month;
        if (monthStr.length > 7) {
            // Full date format (e.g., 2025-05-15)
            const date = new Date(monthStr);
            return date.toLocaleDateString('en-GB', { month: 'short' });
        } else {
            // YYYY-MM format
            [year, month] = monthStr.split('-');
            const date = new Date(parseInt(year), parseInt(month) - 1);
            return date.toLocaleDateString('en-GB', { month: 'short' });
        }
    } catch (error) {
        console.error('Error formatting month short:', error, monthStr);
        return monthStr; // Return original string if formatting fails
    }
}

// Format year for display
function formatYear(monthStr) {
    if (!monthStr) return '';
    try {
        // Handle both YYYY-MM format and full date format
        if (monthStr.length > 7) {
            // Full date format (e.g., 2025-05-15)
            const date = new Date(monthStr);
            return date.getFullYear().toString();
        } else {
            // YYYY-MM format
            const [year] = monthStr.split('-');
            return year;
        }
    } catch (error) {
        console.error('Error formatting year:', error, monthStr);
        return ''; // Return empty string if formatting fails
    }
}

// Format expiry date to DD/MM/YYYY
function formatExpiryDate(dateStr) {
    if (!dateStr || dateStr === 'N/A') return 'N/A';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr; // Return original if invalid
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch (e) {
        return dateStr; // Return original on error
    }
}

// Open the upload modal
function openUploadModal() {
    showUploadModal.value = true;

    // Set the selected facility in the modal
    if (props.filters.facility_id) {
        // Find the facility object that matches the ID
        // Handle both cases: when facility_id is an object or when it's just an ID
        const facilityId = typeof props.filters.facility_id === 'object' ? 
            props.filters.facility_id.id : props.filters.facility_id;
            
        const selectedFacility = props.facilities.find(f => f.id === facilityId);
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

    // Set uploading state
    uploading.value = true;

    // Show uploading indicator
    Swal.fire({
        title: 'Uploading...',
        text: 'Processing your consumption data file...',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

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
            showUploadModal.value = false;
            selectedFile.value = null;

            // Store the facility object before resetting modalFacilityId
            const uploadedFacility = modalFacilityId.value;
            modalFacilityId.value = null;
            
            // Set date range from January to December of current year
            const currentYear = new Date().getFullYear();
            const newFilters = {
                facility_id: uploadedFacility.id,
                start_month: `${currentYear}-01`, // January
                end_month: `${currentYear}-12`    // December
            };
            
            // Emit event to update filters in parent component
            emit('update:filters', newFilters);
            
            // Show success message
            Swal.fire({
                title: 'Upload Successful!',
                text: response.data?.message || 'Consumption data has been imported successfully.',
                icon: 'success',
                confirmButtonColor: '#f97316',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                // Refresh report data with explicit facility_id
                applyFilters();
            });
        })
        .catch((error) => {
            uploading.value = false;
            console.log(error);
            
            // Show error message
            Swal.fire({
                title: 'Upload Failed',
                text: error.response?.data?.message || 'An error occurred while uploading the file.',
                icon: 'error',
                confirmButtonColor: '#f97316'
            });
        });
}

// Download template with current year months
async function downloadTemplate() {
    // Check if a facility is selected (this should be disabled by the button state, but double-check)
    if (!props.filters.facility_id) {
        return; // Button should be disabled, so this shouldn't happen
    }

    try {
        // Show loading indicator
        Swal.fire({
            title: 'Generating Template...',
            text: 'Fetching eligible products for the selected facility...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Get the facility ID
        const facilityId = typeof props.filters.facility_id === 'object' ? 
            props.filters.facility_id.id : props.filters.facility_id;

        // Fetch eligible products for the facility
        const response = await axios.get(route('reports.template-products'), {
            params: {
                facility_id: facilityId
            }
        });

        const eligibleProducts = response.data.products || [];
        
        // Create template data
        const templateData = [];
        
        // Get current year
        const currentYear = new Date().getFullYear();
        
        // Create header row with Item Description and month columns for current year
        const headerRow = ['Item Description'];
        
        // Add all 12 months for current year
        const months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];
        
        months.forEach(month => {
            headerRow.push(`${month}-${currentYear.toString().slice(-2)}`); // e.g., Jan-25
        });
        
        templateData.push(headerRow);
        
        // Add eligible products or sample products if none found
        if (eligibleProducts.length > 0) {
            eligibleProducts.forEach(product => {
                const row = [product.name];
                // Add empty cells for each month
                months.forEach(() => {
                    row.push(''); // Empty cells for data entry
                });
                templateData.push(row);
            });
        } else {
            // Fallback to sample products if no eligible products found
            const sampleProducts = [
                'Albendazole Tab, 400mg',
                'Amoxicillin Caps, 500mg',
                'Paracetamol Tab, 500mg',
                'ORS Sachet (Low Osmolarity, 2004 WHO Formula), 20.5g/1L',
                'Cotrimoxazole Tab, 960mg'
            ];
            
            sampleProducts.forEach(product => {
                const row = [product];
                // Add empty cells for each month
                months.forEach(() => {
                    row.push(''); // Empty cells for data entry
                });
                templateData.push(row);
            });
        }
        
        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(templateData);
        
        // Set column widths
        const colWidths = [{ wch: 50 }]; // Item Description column width
        months.forEach(() => {
            colWidths.push({ wch: 12 }); // Month columns width
        });
        ws['!cols'] = colWidths;
        
        // Style the header row
        const headerRange = XLSX.utils.decode_range(ws['!ref']);
        for (let col = 0; col <= headerRange.e.c; col++) {
            const cellRef = XLSX.utils.encode_cell({ r: 0, c: col });
            if (!ws[cellRef]) continue;
            
            if (!ws[cellRef].s) ws[cellRef].s = {};
            ws[cellRef].s.fill = { fgColor: { rgb: "4F46E5" } }; // Blue background
            ws[cellRef].s.font = { color: { rgb: "FFFFFF" }, bold: true }; // White bold text
            ws[cellRef].s.alignment = { horizontal: "center" };
        }
        
        // Create workbook
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Monthly Consumption Template');
        
        // Get facility name for filename
        const selectedFacility = props.facilities.find(f => f.id === facilityId);
        const facilityName = selectedFacility ? selectedFacility.name.replace(/\s+/g, '_') : 'Facility';
        
        // Generate filename
        const filename = `Monthly_Consumption_Template_${facilityName}_${currentYear}.xlsx`;
        
        // Download the file
        XLSX.writeFile(wb, filename);
        
        // Show success message
        Swal.fire({
            title: 'Template Downloaded!',
            text: `Template for ${selectedFacility?.name || 'the selected facility'} (${eligibleProducts.length} eligible products) has been downloaded successfully.`,
            icon: 'success',
            confirmButtonColor: '#f97316',
            timer: 3000,
            timerProgressBar: true
        });
        
    } catch (error) {
        console.error('Error creating template:', error);
        
        Swal.fire({
            title: 'Download Error',
            text: error.response?.data?.message || 'There was an error creating the template file.',
            icon: 'error',
            confirmButtonColor: '#f97316'
        });
    }
}

/**
 * Get AMC from backend calculation
 * 
 * Uses the backend AMC calculation service that implements the exact 70% deviation screening formula
 */
function getBackendAMC(row) {
    // Get the product ID from the row
    const productId = row.product_id;
    
    // Return backend-calculated AMC if available
    if (props.amcByProduct && props.amcByProduct[productId]) {
        return props.amcByProduct[productId].amc || 0;
    }
    
    return 0;
}

// Calculate average monthly consumption for a product (legacy function for compatibility)
function calculateAverage(row) {
    // This function now uses the backend AMC calculation
    return getBackendAMC(row);
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

        // Add facility information if available
        if (facilityInfo.value && Object.keys(facilityInfo.value).length > 0) {
            // Title row
            wsData.push(['Facility Information']);

            // Facility details
            wsData.push(['Name', facilityInfo.value.name]);
            wsData.push(['Type', facilityInfo.value.facility_type]);
            wsData.push(['Email', facilityInfo.value.email || 'N/A']);
            wsData.push(['Phone', facilityInfo.value.phone || 'N/A']);
            wsData.push(['Address', facilityInfo.value.address || 'N/A']);

            // Manager details
            if (facilityInfo.value.user) {
                wsData.push(['Manager', facilityInfo.value.user.name]);
                wsData.push(['Manager Email', facilityInfo.value.user.email]);
            } else {
                wsData.push(['Manager', 'Not Assigned']);
            }

            // Report period
            wsData.push(['Report Period',
                `${formatMonth(props.filters.start_month)} to ${formatMonth(props.filters.end_month)}`]);

            // Empty row as separator
            wsData.push([]);
        }

        // Prepare header row: months + single AMC column
        const excelHeaderRow = ['SN', 'ITEMS DESCRIPTION'];
        sortedMonths.value.forEach((month) => {
            const monthDate = new Date(month);
            const monthStr = monthDate.toLocaleString('en-US', { month: 'short' }).toUpperCase();
            const yearStr = monthDate.getFullYear();
            excelHeaderRow.push(`${monthStr}-${yearStr}`);
        });
        excelHeaderRow.push('AMC');

        // Add the header row to the worksheet
        wsData.push(excelHeaderRow);

        // Add data rows with single AMC
        let rowNumber = 1;
        filteredPivotTableData.value.forEach(row => {
            const dataRow = [
                rowNumber++,
                row.product_name || 'N/A'
            ];
            // Add month values in order
            sortedMonths.value.forEach((month) => {
                dataRow.push(row[month] || 0);
            });
            // Add AMC: Uses backend calculation with 70% deviation screening
            const amcValue = getBackendAMC(row);
            dataRow.push(amcValue);

            wsData.push(dataRow);
        });

        // Create worksheet with error handling
        let ws;
        try {
            ws = XLSX.utils.aoa_to_sheet(wsData);
        } catch (error) {
            console.error('Error creating worksheet:', error);
            throw new Error('Failed to create Excel worksheet: ' + error.message);
        }

        // Style the single AMC column (last column)
        if (!ws['!cols']) ws['!cols'] = [];
        const amcColIndex = 2 + sortedMonths.value.length; // SN, ITEM, months, then AMC
        const colLetter = XLSX.utils.encode_col(amcColIndex);
        for (let row = 0; row < wsData.length; row++) {
            const cellRef = colLetter + (row + 1);
            if (!ws[cellRef]) ws[cellRef] = { v: '' };
            if (!ws[cellRef].s) ws[cellRef].s = {};
            ws[cellRef].s.fill = { fgColor: { rgb: '87CEEB' } };
            ws[cellRef].s.font = { color: { rgb: 'FFFFFF' }, bold: row === 0 };
        }

        // Create workbook
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Monthly Consumption');

        // Generate filename
        const facilityName = facilityInfo.value && facilityInfo.value.name ? 
            facilityInfo.value.name.replace(/\s+/g, '_') : 'All_Facilities';
        
        // Handle different date formats safely
        let startDate = 'Unknown';
        let endDate = 'Unknown';
        
        try {
            startDate = props.filters.start_month ? props.filters.start_month.replace(/[-:]/g, '_') : 'Unknown';
            endDate = props.filters.end_month ? props.filters.end_month.replace(/[-:]/g, '_') : 'Unknown';
        } catch (error) {
            console.error('Error formatting dates for filename:', error);
        }
        
        const filename = `Monthly_Consumption_${facilityName}_${startDate}_to_${endDate}.xlsx`;

        // Export to Excel file
        XLSX.writeFile(wb, filename);
    } catch (error) {
        console.error('Error exporting to Excel:', error);
        
        // Provide more detailed error message
        let errorMessage = 'There was an error exporting to Excel.';
        
        if (error.message) {
            errorMessage += ' Details: ' + error.message;
        }
        
        // Show error with more details
        Swal.fire({
            title: 'Export Error',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#f97316'
        });
    }
}
</script>
