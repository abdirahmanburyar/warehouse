<template>
    <AuthenticatedLayout>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-[100px]">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Monthly Consumption Report</h1>

                <!-- Filters -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                        <select v-model="filters.facility_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <option :value="null">All Facilities</option>
                            <option v-for="facility in facilities" :key="facility.id" :value="facility.id">
                                {{ facility.name }} ({{ facility.facility_type }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                        <select v-model="filters.product_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <option :value="null">All Products</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Month</label>
                            <input type="month" v-model="filters.start_month"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Month</label>
                            <input type="month" v-model="filters.end_month"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                        </div>
                    </div>

                    <div class="md:col-span-3 flex justify-end space-x-4 items-center">
                        <div v-if="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">Loading data...</span>
                        </div>
                        <button @click="clearFilters" :disabled="loading"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear Filters
                        </button>
                        <button @click="exportToExcel" v-if="pivotData.length > 0" :disabled="loading"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export to Excel
                        </button>
                        <button @click="applyFilters" :disabled="loading"
                            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            Generate Report
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
                        <p>Report Period: {{ formatMonth(filters.start_month) }} to {{ formatMonth(filters.end_month) }}</p>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table v-if="pivotData.length > 0" class="min-w-full border-collapse border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SN
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items
                                </th>
                                <!-- Regular month columns and average columns after every 3 months -->
                                <template v-for="(month, index) in months" :key="month">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ formatMonthShort(month) }}-{{ formatYear(month) }}
                                    </th>
                                    <!-- Add average column after every 3 months -->
                                    <th v-if="(index + 1) % 3 === 0 && index > 0" 
                                        class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-sky-500">
                                        AMC
                                    </th>
                                </template>
                                <!-- Add final average if months count is not divisible by 3 -->
                                <th v-if="months.length % 3 !== 0 && months.length > 0" 
                                    class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-sky-500">
                                    AMC
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in pivotData" :key="row.product_id" class="border-b border-gray-200">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200 text-center">
                                        <span>{{ row[month] || 0 }}</span>
                                    </td>
                                    
                                    <!-- Average column after every 3 months -->
                                    <td v-if="(index + 1) % 3 === 0 && index > 0" 
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white border-r border-gray-200 text-center bg-sky-500">
                                        {{ calculateThreeMonthAverage(row, index) }}
                                    </td>
                                </template>
                                
                                <!-- Add final average if months count is not divisible by 3 -->
                                <td v-if="months.length % 3 !== 0 && months.length > 0" 
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white border-r border-gray-200 text-center bg-sky-500">
                                    {{ calculateRemainingMonthsAverage(row) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-else class="text-center py-10 text-gray-500">
                        No consumption data found for the selected filters.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import * as XLSX from 'xlsx';
import Swal from 'sweetalert2';

const props = defineProps({
    pivotData: Array,
    months: Array,
    facilities: Array,
    products: Array,
    facilityInfo: Object,
    filters: Object
});

// Initialize filters with props values or defaults
const filters = ref({
    facility_id: props.filters?.facility_id || null,
    product_id: props.filters?.product_id || null,
    start_month: props.filters?.start_month || '',
    end_month: props.filters?.end_month || ''
});

// Loading state
const loading = ref(false);

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
        }
    });
}

// Clear all filters and reset to default values
function clearFilters() {
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
    
    // Clear the data display if any exists
    if (props.pivotData && props.pivotData.length > 0) {
        router.visit(route('reports.monthlyConsumption'), {
            method: 'get',
            data: {},
            preserveState: false,
            replace: true,
            onSuccess: () => {
                // Show a success message
                Swal.fire({
                    title: 'Filters Cleared',
                    text: 'All filters have been reset to default values',
                    icon: 'success',
                    confirmButtonColor: '#f97316',
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        });
    } else {
        // Show a success message
        Swal.fire({
            title: 'Filters Cleared',
            text: 'All filters have been reset to default values',
            icon: 'success',
            confirmButtonColor: '#f97316',
            timer: 2000,
            timerProgressBar: true
        });
    }
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
