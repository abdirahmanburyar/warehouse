<template>
    <Head title="Transfer Analysis Report" />
    <AuthenticatedLayout
        title="Transfer Analysis Report"
        description="Comprehensive analysis of transfers by type and reasons"
        img="/assets/images/report.png"
    >
        <h2 class="text-xl font-semibold mb-4">
            Transfer Analysis Report
        </h2>

        <!-- Filter Bar (moved to top, horizontal) -->
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6 flex flex-col md:flex-row md:items-end md:space-x-4 space-y-3 md:space-y-0">
            <!-- Transfer Type Filter -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Transfer Type
                </label>
                <select
                    v-model="filters.transfer_type"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                >
                    <option value="">All Types</option>
                    <option value="Facility to Facility">Facility to Facility</option>
                    <option value="Warehouse to Warehouse">Warehouse to Warehouse</option>
                    <option value="Warehouse to Facility">Warehouse to Facility</option>
                    <option value="Facility to Warehouse">Facility to Warehouse</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Reason Filter -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Transfer Reason
                </label>
                                        <select
                            v-model="filters.reason"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                        >
                            <option value="">All Reasons</option>
                            <option v-for="reason in reasons" :key="reason.id" :value="reason.name">
                                {{ reason.name }}
                            </option>
                        </select>
            </div>

            <!-- Date From Filter -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6"></path>
                    </svg>
                    Date From
                </label>
                <input
                    type="date"
                    v-model="filters.date_from"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                />
            </div>

            <!-- Date To Filter -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6"></path>
                    </svg>
                    Date To
                </label>
                <input
                    type="date"
                    v-model="filters.date_to"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                />
            </div>

            <!-- Report Type Toggle -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Report Type
                </label>
                <div class="flex space-x-4 mt-1">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="reportType"
                            value="type"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        />
                        <span class="ml-2 text-sm text-gray-700">By Transfer Type</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="reportType"
                            value="reason"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        />
                        <span class="ml-2 text-sm text-gray-700">By Transfer Reason</span>
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2 md:ml-auto">
                <button
                    type="button"
                    @click="resetFilters"
                    class="inline-flex justify-center items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Reset Filters
                </button>
                <button
                    type="button"
                    @click="exportToExcel"
                    class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :disabled="exporting || !currentData.length"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-2"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ exportButtonText }}
                </button>
            </div>
        </div>

        <!-- Content Section -->
        <div class="flex-[0.8]">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-600">Total Transfers</p>
                            <p class="text-2xl font-bold text-blue-900">{{ totalTransfers }}</p>
                        </div>
                    </div>
                </div>
                    
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600">Total Quantity</p>
                            <p class="text-2xl font-bold text-green-900">{{ totalQuantity.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
                    
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-600">Total Value</p>
                            <p class="text-2xl font-bold text-purple-900">${{ totalValue.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
                    
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-100 rounded-lg">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-orange-600">{{ reportType === 'type' ? 'Transfer Types' : 'Unique Reasons' }}</p>
                            <p class="text-2xl font-bold text-orange-900">{{ currentData.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Title -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ reportType === 'type' ? 'Transfer Type Analysis' : 'Transfer Reasons Analysis' }}
                </h3>
                <p class="text-sm text-gray-500">
                    {{ reportType === 'type' ? 'Analysis of transfers grouped by type (Facility to Facility, Warehouse to Warehouse, etc.)' : 'Analysis of transfers grouped by reason (Emergency, Regular Supply, etc.)' }}
                </p>
            </div>

            <!-- Data Table -->
            <div class="overflow-auto bg-white rounded-lg shadow-sm border border-gray-200">
                <div
                    v-if="loading"
                    class="flex justify-center items-center p-12"
                >
                    <div class="flex flex-col items-center space-y-3">
                        <div class="animate-spin rounded-full h-10 w-10 border-3 border-blue-500 border-t-transparent"></div>
                        <p class="text-sm text-gray-500">Loading {{ reportType === 'type' ? 'transfer types' : 'transfer reasons' }}...</p>
                    </div>
                </div>

                <div
                    v-else-if="!currentData.length"
                    class="text-center p-12"
                >
                    <div class="flex flex-col items-center space-y-3">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <p class="text-gray-500 font-medium">No {{ reportType === 'type' ? 'transfer types' : 'transfer reasons' }} found matching the criteria.</p>
                    </div>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    {{ reportType === 'type' ? 'Transfer Type' : 'Reason' }}
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Count
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Total Quantity
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Total Value
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Percentage
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr
                                v-for="(item, index) in currentData"
                                :key="index"
                                class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ reportType === 'type' ? item.type : item.reason }}</div>
                                            <div class="text-sm text-gray-500">{{ reportType === 'type' ? 'Transfer category' : 'Transfer reason' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-gray-900">{{ item.count.toLocaleString() }}</div>
                                    <div class="text-sm text-gray-500">transfers</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-gray-900">{{ item.total_quantity.toLocaleString() }}</div>
                                    <div class="text-sm text-gray-500">units</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-gray-900">${{ item.total_value.toLocaleString() }}</div>
                                    <div class="text-sm text-gray-500">USD</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div 
                                                class="bg-blue-600 h-2 rounded-full" 
                                                :style="{ width: getPercentage(item.count) + '%' }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ getPercentage(item.count).toFixed(1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { router, Head } from "@inertiajs/vue3";
import moment from "moment";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import * as XLSX from "xlsx";

// Loading states
const loading = ref(false);
const exporting = ref(false);

const props = defineProps({
    transferTypes: Object,
    transferReasons: Object,
    reasons: Array,
    filters: Object,
});

// Form data for filters
const filters = ref({
    transfer_type: props.filters.transfer_type || "",
    reason: props.filters.reason || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

// Report type state
const reportType = ref('type');

// Computed properties
const transferTypes = computed(() => {
    if (!props.transferTypes) return [];
    return Object.values(props.transferTypes);
});

const transferReasons = computed(() => {
    if (!props.transferReasons) return [];
    return Object.values(props.transferReasons);
});

const currentData = computed(() => {
    return reportType.value === 'type' ? transferTypes.value : transferReasons.value;
});

const totalTransfers = computed(() => {
    return currentData.value.reduce((sum, item) => sum + item.count, 0);
});

const totalQuantity = computed(() => {
    return currentData.value.reduce((sum, item) => sum + item.total_quantity, 0);
});

const totalValue = computed(() => {
    return currentData.value.reduce((sum, item) => sum + item.total_value, 0);
});

const reasons = computed(() => {
    return props.reasons || [];
});

// Debounced fetch function
let fetchTimeout = null;

const debouncedFetchData = () => {
    if (fetchTimeout) {
        clearTimeout(fetchTimeout);
    }
    
    fetchTimeout = setTimeout(() => {
        fetchData();
    }, 300);
};

// Fetch data based on filters
const fetchData = () => {
    loading.value = true;

    // Only include non-empty filters in the request
    const cleanFilters = {};
    Object.keys(filters.value).forEach(key => {
        if (filters.value[key] && filters.value[key] !== '') {
            cleanFilters[key] = filters.value[key];
        }
    });

    const routeName = reportType.value === 'type' ? 'reports.transfer-type' : 'reports.transfer-reasons';

    router.visit(route(routeName), {
        data: cleanFilters,
        preserveState: true,
        onSuccess: () => {
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        transfer_type: "",
        reason: "",
        date_from: "",
        date_to: "",
    };
};

// Helper functions
const getPercentage = (count) => {
    if (totalTransfers.value === 0) return 0;
    return (count / totalTransfers.value) * 100;
};

// Export to Excel
const exportToExcel = () => {
    loading.value = true;

    // Prepare the data for export
    const exportData = currentData.value.map((item, index) => ({
        SN: index + 1,
        [reportType.value === 'type' ? 'Transfer Type' : 'Reason']: reportType.value === 'type' ? item.type : item.reason,
        "Count": item.count,
        "Total Quantity": item.total_quantity,
        "Total Value": item.total_value,
        "Percentage": getPercentage(item.count).toFixed(1) + "%",
    }));

    // Create summary data
    const summaryData = [
        [`Transfer ${reportType.value === 'type' ? 'Type' : 'Reasons'} Analysis Report`, "", "", "", "", ""],
        ["Generated on:", formatDateTime(new Date()), "", "", "", ""],
        ["", "", "", "", "", ""],
        [`Total ${reportType.value === 'type' ? 'Transfer Types' : 'Unique Reasons'}:`, currentData.value.length, "", "", "", ""],
        ["Total Transfers:", totalTransfers.value, "", "", "", ""],
        ["Total Quantity:", totalQuantity.value.toLocaleString(), "", "", "", ""],
        ["Total Value:", "$" + totalValue.value.toLocaleString(), "", "", "", ""],
        ["", "", "", "", "", ""],
        ["", "", "", "", "", ""],
    ];

    // Add headers row
    const headers = [
        "SN",
        reportType.value === 'type' ? 'Transfer Type' : 'Reason',
        "Count",
        "Total Quantity",
        "Total Value",
        "Percentage"
    ];
    summaryData.push(headers);

    // Create worksheet
    const worksheet = XLSX.utils.aoa_to_sheet(summaryData);

    // Append the actual data
    XLSX.utils.sheet_add_json(worksheet, exportData, {
        origin: summaryData.length,
        skipHeader: true,
        header: headers,
    });

    // Style the headers
    const headerRowIndex = summaryData.length - 1;
    const headerRange = XLSX.utils.decode_range(worksheet["!ref"]);
    for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
        const cellRef = XLSX.utils.encode_cell({ r: 0, c: col });
        if (!worksheet[cellRef]) worksheet[cellRef] = {};
        worksheet[cellRef].s = { font: { bold: true, sz: 14 } };

        const headerCellRef = XLSX.utils.encode_cell({ r: headerRowIndex, c: col });
        if (!worksheet[headerCellRef]) worksheet[headerCellRef] = {};
        worksheet[headerCellRef].s = { font: { bold: true } };
    }

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, reportType.value === 'type' ? "Transfer Types" : "Transfer Reasons");

    // Generate file name
    const fileName = `transfer_${reportType.value}_analysis_${new Date().toISOString().split('T')[0]}.xlsx`;

    // Save the file
    XLSX.writeFile(workbook, fileName);
    loading.value = false;
};

const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return "N/A";
    return moment(dateTimeString).format("DD/MM/YYYY HH:mm:ss");
};

// Computed property for export button text
const exportButtonText = computed(() => {
    return exporting.value ? "Exporting..." : "Export to Excel";
});

// Watchers for automatic filtering
watch(filters, () => {
    debouncedFetchData();
}, { deep: true });

watch(reportType, () => {
    fetchData();
});

// Lifecycle hooks
onMounted(() => {
    // Auto-fetch data on mount
    fetchData();
});
</script> 