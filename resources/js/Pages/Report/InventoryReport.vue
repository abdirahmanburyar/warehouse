<template>
    <AuthenticatedLayout title="Inventory Reports" description="View Generated Inventory Reports" img="/assets/images/report.png">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventory Reports
            </h2>
        </template>

        <div class="py-5">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Filters -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Warehouse Filter -->
                        <div>
                            <label for="warehouse" class="block text-sm font-medium text-gray-700">Warehouse</label>
                            <select
                                id="warehouse"
                                v-model="filters.warehouseId"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                            >
                                <option value="">All Warehouses</option>
                                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                    {{ warehouse.name }}
                                </option>
                            </select>
                        </div>

                            <!-- Month/Year Filter -->
                            <div>
                                <label for="monthYear" class="block text-sm font-medium text-gray-700">Month & Year</label>
                                <input
                                    type="month"
                                    id="monthYear"
                                    v-model="monthYearInput"
                                    @change="updateFiltersFromMonthInput"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                />
                            </div>

                        <!-- Load Report Button -->
                        <div class="flex items-end space-x-2">
                            <button
                                @click="loadReport"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                                :disabled="loading"
                            >
                                {{ loading ? 'Loading...' : 'Load Report' }}
                            </button>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div v-if="loading" class="flex justify-center my-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
                    </div>

                    <!-- Report Table -->
                    <div v-else-if="reportData.length > 0" class="mt-4">
                        <div class="flex justify-between mb-4">
                            <h3 class="text-lg font-semibold">
                                Monthly Inventory Report: {{ getMonthName(filters.value?.month) }} {{ filters.value?.year }}
                            </h3>
                            <button
                                v-if="$page.props.auth.can.report_view"
                                @click="exportToExcel"
                                class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                :disabled="exportLoading"
                            >
                                {{ exportLoading ? 'Exporting...' : 'Export to Excel' }}
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Beginning Balance
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock Received
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock Issued
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Negative Adjustment
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Positive Adjustment
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Closing Balance
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            UOM
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit Cost
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Value
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in reportData" :key="item.product_id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ item.product_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatNumber(item.beginning_balance) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatNumber(item.stock_received) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatNumber(item.stock_issued) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatNumber(item.negative_adjustment) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatNumber(item.positive_adjustment) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatNumber(item.closing_balance) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ item.uom }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(item.unit_cost) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(item.total_value) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- No Data Message -->
                    <div v-else-if="!loading && hasGenerated" class="text-center py-8">
                        <div class="text-gray-500 text-lg">No report found for the selected criteria</div>
                        <div class="text-gray-400 text-sm mt-2">
                            Reports are generated automatically by scheduled commands. 
                            <br>If you need a report for a specific month, please contact your administrator.
                        </div>
                    </div>

                    <!-- Initial State Message -->
                    <div v-else-if="!loading && !hasGenerated" class="text-center py-8">
                        <p class="text-gray-500">Select filters and click "Load Report" to view existing reports.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import * as XLSX from 'xlsx';
import moment from 'moment';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();

// Data
const warehouses = ref([]);
const reportData = ref([]);
const loading = ref(false);
const exportLoading = ref(false);
const hasGenerated = ref(false);
const monthYearInput = ref('');

// Props
const props = defineProps({
    warehouses: {
        type: Array,
        default: () => []
    },
    currentMonth: {
        type: Number,
        default: () => new Date().getMonth() + 1
    },
    currentYear: {
        type: Number,
        default: () => new Date().getFullYear()
    }
});

// Filters - initialize with default values first, then update with props
const filters = ref({
    warehouseId: '',
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
});

// Update filters with props values after initialization
filters.value.month = props.currentMonth;
filters.value.year = props.currentYear;

// Initialize monthYearInput from props
monthYearInput.value = `${props.currentYear}-${String(props.currentMonth).padStart(2, '0')}`;

// Constants - removed months array and availableYears since we're using month input

// Methods
function updateFiltersFromMonthInput() {
    if (monthYearInput.value && filters.value) {
        const [year, month] = monthYearInput.value.split('-');
        filters.value.year = parseInt(year);
        filters.value.month = parseInt(month);
        console.log('Updated filters from month input:', filters.value);
    }
}

function getMonthName(monthNumber) {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return months[monthNumber - 1] || 'Unknown';
}

function formatNumber(value) {
    return parseFloat(value || 0).toLocaleString();
}

function formatCurrency(value) {
    return `$${parseFloat(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

// Removed storeMonthlyReport function - reports are generated automatically by commands

async function loadReport() {
    if (loading.value) return;
    
    console.log('loadReport called with filters:', filters.value);
    console.log('monthYearInput value:', monthYearInput.value);
    
    loading.value = true;
    
    try {
        // Ensure we have valid month and year values
        if (!filters.value?.month || !filters.value?.year || filters.value.month < 1 || filters.value.month > 12 || filters.value.year < 2000 || filters.value.year > 2100) {
            console.error('Invalid month or year:', { month: filters.value?.month, year: filters.value?.year });
            toast.error('Please select a valid month and year');
            loading.value = false;
            return;
        }
        
        // Format month with leading zero if needed
        const month = String(filters.value?.month || 1).padStart(2, '0');
        const year = filters.value?.year || new Date().getFullYear();
        const monthYear = `${year}-${month}`;
        
        // Calculate previous month for beginning balance using a valid date format
        // Create a date using the specified format to avoid moment.js warnings
        const dateString = `${year}-${month}-01`;
        const prevDate = moment(dateString, 'YYYY-MM-DD').subtract(1, 'month');
        const prevMonthYear = prevDate.format('YYYY-MM');
        
        // Get warehouse filter
        const warehouseId = filters.value?.warehouseId;
        
        // Fetch existing report data from API
        const response = await axios.get(route('reports.inventoryReport.data'), {
            params: {
                month_year: monthYear,
                prev_month_year: prevMonthYear,
                warehouse_id: warehouseId || undefined
            }
        });
        
        if (response.data.success) {
            console.log('Report data received:', response.data);
            console.log('Data count:', response.data.data?.length || 0);
            console.log('Debug info:', response.data.debug);
            
            reportData.value = response.data.data;
            hasGenerated.value = true;
            toast.success(`Report loaded successfully - ${response.data.data?.length || 0} items found`);
        } else {
            console.error('Report loading failed:', response.data);
            toast.error(response.data.message || 'No report found for the selected month. Reports are generated automatically by scheduled commands.');
        }
    } catch (error) {
        console.error('Error loading report:', error);
        console.error('Error details:', {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
            statusText: error.response?.statusText,
            url: error.config?.url,
            method: error.config?.method
        });
        
        let errorMessage = 'Failed to load report. Please try again.';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = `Error: ${error.message}`;
        }
        
        toast.error(errorMessage);
    } finally {
        loading.value = false;
    }
}

function exportToExcel() {
    if (reportData.value.length === 0) return;
    
    exportLoading.value = true;
    
    try {
        // Create a new workbook
        const wb = XLSX.utils.book_new();
        
        // Prepare data for the worksheet
        const wsData = [];
        
        // Add headers
        wsData.push([
            'Item',
            'Beginning Balance',
            'Stock Received',
            'Stock Issued',
            'Negative Adjustment',
            'Positive Adjustment',
            'Closing Balance',
            'UOM',
            'Unit Cost',
            'Total Value'
        ]);
        
        // Add data rows
        reportData.value.forEach(item => {
            wsData.push([
                item.product_name,
                item.beginning_balance,
                item.stock_received,
                item.stock_issued,
                item.negative_adjustment,
                item.positive_adjustment,
                item.closing_balance,
                item.uom,
                item.unit_cost,
                item.total_value
            ]);
        });
        
        // Create worksheet from data
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        
        // Set column widths
        ws['!cols'] = [
            { wch: 30 }, // Item
            { wch: 15 }, // Beginning Balance
            { wch: 15 }, // Stock Received
            { wch: 15 }, // Stock Issued
            { wch: 15 }, // Negative Adjustment
            { wch: 15 }, // Positive Adjustment
            { wch: 15 }, // Closing Balance
            { wch: 10 }, // UOM
            { wch: 15 }, // Unit Cost
            { wch: 15 }  // Total Value
        ];
        
        // Style the header row
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const address = XLSX.utils.encode_cell({ r: 0, c: C });
            if (!ws[address]) continue;
            ws[address].s = {
                font: { bold: true },
                fill: { fgColor: { rgb: "E9E9E9" } }
            };
        }
        
        // Add the worksheet to the workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Inventory Report');
        
        // Generate filename with month and year
        const filename = `inventory_report_${getMonthName(filters.value?.month || 1)}_${filters.value?.year || new Date().getFullYear()}.xlsx`;
        
        // Generate Excel file and trigger download
        XLSX.writeFile(wb, filename);
    } catch (error) {
        console.error('Error exporting report:', error);
    } finally {
        exportLoading.value = false;
    }
}

// Initialize warehouses from props
warehouses.value = props.warehouses;

// Lifecycle hooks
onMounted(async () => {
    // Warehouses are already loaded from props, no need to fetch them
    console.log('InventoryReport mounted with props:', {
        currentMonth: props.currentMonth,
        currentYear: props.currentYear,
        warehouses: props.warehouses.length
    });
    console.log('Initial filters:', filters.value);
});
</script>