<template>
    <AuthenticatedLayout title="Review Your Reports" description="Transforming Data into Impact" img="/assets/images/report.png">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Monthly Inventory Consumption Report
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

                            <!-- Month Filter -->
                            <div>
                                <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                                <select
                                    id="month"
                                    v-model="filters.month"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option v-for="(month, index) in months" :key="index" :value="index + 1">
                                        {{ month }}
                                    </option>
                                </select>
                            </div>

                            <!-- Year Filter -->
                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                                <select
                                    id="year"
                                    v-model="filters.year"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option v-for="year in availableYears" :key="year" :value="year">
                                        {{ year }}
                                    </option>
                                </select>
                            </div>

                            <!-- Generate Report Button -->
                            <div class="flex items-end space-x-2">
                                <button
                                    @click="generateReport"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    :disabled="loading"
                                >
                                    {{ loading ? 'Generating...' : 'Generate Report' }}
                                </button>
                                
                                <!-- Store Monthly Report Button (only visible with proper permission) -->
                                <form v-if="$page.props.auth.can.report_generate" @submit.prevent="storeMonthlyReport">
                                    <input type="hidden" name="month_year" :value="`${filters.year}-${String(filters.month).padStart(2, '0')}`">
                                    <input type="hidden" name="warehouse_id" :value="filters.warehouseId || ''">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        :disabled="storeLoading"
                                    >
                                        {{ storeLoading ? 'Storing...' : 'Store Monthly Report' }}
                                    </button>
                                </form>
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
                                    Monthly Inventory Report: {{ months[filters.month - 1] }} {{ filters.year }}
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
                            <p class="text-gray-500">No data found for the selected period.</p>
                        </div>

                        <!-- Initial State Message -->
                        <div v-else-if="!loading && !hasGenerated" class="text-center py-8">
                            <p class="text-gray-500">Select filters and click "Generate Report" to view data.</p>
                        </div>
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
import { toast } from 'vue-toastification';

// Data
const warehouses = ref([]);
const reportData = ref([]);
const loading = ref(false);
const exportLoading = ref(false);
const storeLoading = ref(false);
const hasGenerated = ref(false);

// Filters
const filters = ref({
    warehouseId: '',
    month: new Date().getMonth() + 1, // Current month (1-12)
    year: new Date().getFullYear(), // Current year
});

// Constants
const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// Computed
const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => currentYear - i);
});

// Methods
function formatNumber(value) {
    return parseFloat(value || 0).toLocaleString();
}

function formatCurrency(value) {
    return `$${parseFloat(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

async function storeMonthlyReport() {
    if (storeLoading.value) return;
    
    storeLoading.value = true;
    
    try {
        // Format month with leading zero if needed
        const month = String(filters.month).padStart(2, '0');
        const year = filters.year;
        const monthYear = `${year}-${month}`;
        
        // Submit request to store monthly report
        await axios.post(route('reports.generateInventoryReport'), {
            month_year: monthYear,
            warehouse_id: filters.warehouseId || undefined
        });
        
        // Show success message
        toast.success('Monthly inventory report has been stored successfully');
        
        // Refresh the report to show the stored data
        await generateReport();
    } catch (error) {
        console.error('Error storing monthly report:', error);
        toast.error(error.response?.data?.message || 'Failed to store monthly report');
    } finally {
        storeLoading.value = false;
    }
}

async function generateReport() {
    if (loading.value) return;
    
    loading.value = true;
    hasGenerated.value = true;
    
    try {
        // Ensure we have valid month and year values
        if (!filters.month || !filters.year) {
            console.error('Invalid month or year');
            loading.value = false;
            return;
        }
        
        // Format month with leading zero if needed
        const month = String(filters.month).padStart(2, '0');
        const year = filters.year;
        const monthYear = `${year}-${month}`;
        
        // Calculate previous month for beginning balance using a valid date format
        // Create a date using the specified format to avoid moment.js warnings
        const dateString = `${year}-${month}-01`;
        const prevDate = moment(dateString, 'YYYY-MM-DD').subtract(1, 'month');
        const prevMonthYear = prevDate.format('YYYY-MM');
        
        // Get warehouse filter
        const warehouseId = filters.warehouseId;
        
        // Fetch data from API
        const response = await axios.get(route('reports.inventoryReport.data'), {
            params: {
                month_year: monthYear,
                prev_month_year: prevMonthYear,
                warehouse_id: warehouseId || undefined
            }
        });
        
        reportData.value = response.data;
    } catch (error) {
        console.error('Error generating report:', error);
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
        const filename = `inventory_report_${months[filters.month - 1]}_${filters.year}.xlsx`;
        
        // Generate Excel file and trigger download
        XLSX.writeFile(wb, filename);
    } catch (error) {
        console.error('Error exporting report:', error);
    } finally {
        exportLoading.value = false;
    }
}

// Lifecycle hooks
onMounted(async () => {
    try {
        // Fetch warehouses
        const response = await axios.get(route('api.warehouses'));
        warehouses.value = response.data;
    } catch (error) {
        console.error('Error fetching warehouses:', error);
    }
});
</script>