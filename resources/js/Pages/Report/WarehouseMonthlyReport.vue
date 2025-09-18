<template>
    <AuthenticatedLayout title="Warehouse Monthly Report" description="Monthly Inventory Movement Summary" img="/assets/images/report.png">
        <Head title="Warehouse Monthly Report" />
        
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Filters -->
                <div class="flex items-center gap-6">
                    <!-- Month Year Selector -->
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <span>📅</span>
                            <span>Month:</span>
                        </label>
                        <input 
                            type="month" 
                            v-model="month_year"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border-2"
                        >
                        <span class="text-gray-700 font-semibold bg-gray-100 px-3 py-2 rounded-lg">
                            {{ formatMonthYear(month_year) }}
                        </span>
                    </div>
                    
                    <!-- Warehouse Filter -->
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <span>🏢</span>
                            <span>Warehouse:</span>
                        </label>
                        <select 
                            v-model="selectedWarehouse"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm min-w-[250px] px-3 py-2 border-2"
                        >
                            <option value="">All Warehouses</option>
                            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                {{ warehouse.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Report Status -->
                <div class="flex items-center gap-4">
                    <span class="text-gray-600">Status:</span>
                    <span :class="getStatusBadgeClass(props.inventoryReport?.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                        {{ props.inventoryReport?.status?.toUpperCase() ?? 'N/A' }}
                    </span>
                    <span v-if="canEdit" class="text-green-600 text-sm font-medium">
                        ✏️ Editable
                    </span>
                    <span v-else class="text-gray-500 text-sm">
                        🔒 Read-only (Status: {{ props.inventoryReport?.status || 'unknown' }})
                    </span>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button 
                        v-if="hasUnsavedChanges && canEdit"
                        @click="saveAdjustments"
                        :disabled="saving"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2"
                    >
                        <span v-if="!saving">Save Changes</span>
                        <span v-else>Saving...</span>
                    </button>

                    <button 
                        v-if="canSubmit"
                        @click="submitReport"
                        :disabled="processing || hasUnsavedChanges"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Submit for Review
                    </button>

                    <button 
                        v-if="canReview"
                        @click="reviewReport"
                        :disabled="processing"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Mark as Under Review
                    </button>

                    <div v-if="canApprove || canReject" class="flex gap-2">
                        <button 
                            v-if="canApprove"
                            @click="approveReport"
                            :disabled="processing"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                        >
                            Approve Report
                        </button>
                        <button 
                            v-if="canReject"
                            @click="rejectReport"
                            :disabled="processing"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                        >
                            Reject Report
                        </button>
                    </div>

                    <button 
                        @click="exportToExcel"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div v-if="filteredReportData && filteredReportData.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📊</span>
                        <div>
                            <div class="text-sm font-medium text-blue-600">Total Products</div>
                            <div class="text-2xl font-bold text-blue-800">{{ filteredReportData.length }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📥</span>
                        <div>
                            <div class="text-sm font-medium text-green-600">Total Received</div>
                            <div class="text-2xl font-bold text-green-800">{{ formatNumber(filteredReportData.reduce((sum, item) => sum + (item.received_quantity || 0), 0)) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📤</span>
                        <div>
                            <div class="text-sm font-medium text-red-600">Total Issued</div>
                            <div class="text-2xl font-bold text-red-800">{{ formatNumber(filteredReportData.reduce((sum, item) => sum + (item.issued_quantity || 0), 0)) }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📋</span>
                        <div>
                            <div class="text-sm font-medium text-purple-600">Total Closing</div>
                            <div class="text-2xl font-bold text-purple-800">{{ formatNumber(filteredReportData.reduce((sum, item) => sum + calculateClosingBalance(item, filteredReportData.indexOf(item)), 0)) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-[100px]">
            <div v-if="dataLoading" class="p-8 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                <div class="mt-4 text-gray-700">Loading report data...</div>
                <div class="text-sm text-gray-500 mt-2">This may take a few moments for large datasets</div>
            </div>
            <div v-else-if="filteredReportData && filteredReportData.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">
                                <div class="flex items-center gap-2">
                                    <span>🏢</span>
                                    <span>Warehouse</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">
                                <div class="flex items-center gap-2">
                                    <span>📦</span>
                                    <span>Product</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">
                                <div class="flex items-center justify-end gap-2">
                                    <span>📊</span>
                                    <span>Beginning Balance</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">
                                <div class="flex items-center justify-end gap-2">
                                    <span>📥</span>
                                    <span>Stock Received</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">
                                <div class="flex items-center justify-end gap-2">
                                    <span>📤</span>
                                    <span>Stock Issued</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-red-50 border border-gray-300">
                                <div class="flex items-center justify-end gap-2">
                                    <span>📉</span>
                                    <span>Negative Adjustment</span>
                                    <span v-if="canEdit" class="text-red-600" title="Editable">✏️</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50 border border-gray-300">
                                <div class="flex items-center justify-end gap-2">
                                    <span>📈</span>
                                    <span>Positive Adjustment</span>
                                    <span v-if="canEdit" class="text-green-600" title="Editable">✏️</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">
                                <div class="flex items-center justify-end gap-2">
                                    <span>📋</span>
                                    <span>Closing Balance</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(item, index) in filteredReportData" :key="`${item.warehouse_id}-${item.product.id}`" :class="getRowClass(index)">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border border-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🏢</span>
                                    <div>
                                        <div class="font-semibold">{{ item.warehouse?.name || 'Unknown Warehouse' }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ item.warehouse_id || 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border border-gray-300 relative">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📦</span>
                                    <div>
                                        <div class="font-semibold">{{ item.product.name }}</div>
                                        <div v-if="item.product.category" class="text-xs text-gray-500">
                                            {{ item.product.category.name }}
                                        </div>
                                    </div>
                                </div>
                                <span v-if="rowError[index]" class="absolute top-2 right-2 text-red-500 animate-bounce">
                                    ❌
                                </span>
                                <span v-else-if="rowSaving[index]" class="absolute top-2 right-2 text-yellow-500">
                                    <svg class="animate-pulse h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                    </svg>
                                </span>
                                <span v-else-if="rowUpdating[index]" class="absolute top-2 right-2 text-blue-500">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span v-else-if="rowChanged[index]" class="absolute top-2 right-2 text-green-500">
                                    ✓
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right border border-gray-300 transition-all duration-300" :class="getBalanceColor(item.beginning_balance)">
                                <div class="flex items-center justify-end gap-1">
                                    <span class="text-lg">📊</span>
                                    <span class="font-semibold">{{ formatNumber(item.beginning_balance) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 border border-gray-300 transition-all duration-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span class="text-lg">📥</span>
                                    <span class="font-semibold text-green-600">{{ formatNumber(item.received_quantity) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 border border-gray-300 transition-all duration-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span class="text-lg">📤</span>
                                    <span class="font-semibold text-red-600">{{ formatNumber(item.issued_quantity) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right border border-gray-300 transition-all duration-300 bg-red-50">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-lg">📉</span>
                                    <div class="flex flex-col items-end">
                                        <input 
                                            v-if="canEdit"
                                            v-model.number="currentAdjustments[index].negative_adjustment"
                                            @input="updateCalculatedBalance(item, index)"
                                            type="number"
                                            min="0"
                                            step="1"
                                            :class="[
                                                'w-24 px-3 py-2 text-right border rounded-lg focus:ring-2 transition-all duration-200 font-semibold',
                                                rowError[index] ? 'border-red-400 ring-2 ring-red-200 bg-red-100' :
                                                rowSaving[index] ? 'border-yellow-400 ring-2 ring-yellow-200 bg-yellow-100' :
                                                rowUpdating[index] ? 'border-blue-400 ring-2 ring-blue-200 bg-blue-100' : 
                                                'border-red-300 focus:border-red-500 focus:ring-red-500 bg-white'
                                            ]"
                                        />
                                        <span v-else class="text-red-700 font-bold text-lg">
                                            {{ formatNumber(item.negative_adjustment) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right border border-gray-300 transition-all duration-300 bg-green-50">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-lg">📈</span>
                                    <div class="flex flex-col items-end">
                                        <input 
                                            v-if="canEdit"
                                            v-model.number="currentAdjustments[index].positive_adjustment"
                                            @input="updateCalculatedBalance(item, index)"
                                            type="number"
                                            min="0"
                                            step="1"
                                            :class="[
                                                'w-24 px-3 py-2 text-right border rounded-lg focus:ring-2 transition-all duration-200 font-semibold',
                                                rowError[index] ? 'border-red-400 ring-2 ring-red-200 bg-red-100' :
                                                rowSaving[index] ? 'border-yellow-400 ring-2 ring-yellow-200 bg-yellow-100' :
                                                rowUpdating[index] ? 'border-blue-400 ring-2 ring-blue-200 bg-blue-100' : 
                                                'border-green-300 focus:border-green-500 focus:ring-green-500 bg-white'
                                            ]"
                                        />
                                        <span v-else class="text-green-700 font-bold text-lg">
                                            {{ formatNumber(item.positive_adjustment) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right border border-gray-300" :class="getClosingBalanceClass(item, index)">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-lg">📋</span>
                                    <div class="flex flex-col items-end">
                                        <span class="font-bold text-lg" :class="getClosingBalanceTextClass(item, index)">
                                            {{ formatNumber(calculateClosingBalance(item, index)) }}
                                        </span>
                                        <div class="flex items-center gap-1 mt-1">
                                            <span v-if="rowError[index]" class="text-red-500 animate-bounce text-sm">
                                                ❌
                                            </span>
                                            <span v-else-if="rowSaving[index]" class="text-yellow-500 text-sm">
                                                💾
                                            </span>
                                            <span v-else-if="rowUpdating[index]" class="text-blue-500 text-sm">
                                                ⟳
                                            </span>
                                            <span v-else-if="rowChanged[index]" class="text-green-500 text-sm">
                                                ✓
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="p-8 text-center">
                <div v-if="props.error" class="text-red-600">
                    <div class="text-lg font-medium">Error Loading Report</div>
                    <div class="text-sm mt-2">{{ props.error }}</div>
                    <button 
                        @click="reloadData" 
                        class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Retry
                    </button>
                </div>
                <div v-else-if="props.inventoryReport?.status === 'error'" class="text-red-600">
                    <div class="text-lg font-medium">Report Generation Failed</div>
                    <div class="text-sm mt-2">There was an error generating the report data. Please try again.</div>
                    <button 
                        @click="reloadData" 
                        class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Retry
                    </button>
                </div>
                <div v-else class="text-gray-500">
                    <div class="text-lg">No data available</div>
                    <div class="text-sm">Please select a month to view the report</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import moment from 'moment';

const props = defineProps({
    reportData: {
        type: Array,
        default: () => []
    },
    warehouses: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    inventoryReport: {
        type: Object,
        default: () => ({})
    },
    monthYear: {
        type: String,
        default: () => moment().format('YYYY-MM')
    },
    error: {
        type: String,
        default: null
    }
});

const currentAdjustments = ref({});
const originalAdjustments = ref({});
const month_year = ref(props.monthYear || props.filters.month_year);
const selectedWarehouse = ref(props.filters.warehouse_id || '');
const cellLoading = ref({});
const processing = ref(false);
const saving = ref(false);
const dataLoading = ref(false);
const rowUpdating = ref({});
const rowChanged = ref({});
const rowSaving = ref({});
const rowError = ref({});
const saveTimeouts = ref({});

// Initialize adjustments tracking
const initializeAdjustments = () => {
    if (props.reportData && Array.isArray(props.reportData)) {
        const newOriginal = {};
        const newCurrent = {};
        
        props.reportData.forEach((item, index) => {
            // Initialize only the required editable fields
            newOriginal[index] = {
                positive_adjustment: item.positive_adjustment ?? 0,
                negative_adjustment: item.negative_adjustment ?? 0
            };
            
            newCurrent[index] = {
                positive_adjustment: item.positive_adjustment ?? 0,
                negative_adjustment: item.negative_adjustment ?? 0
            };
            
            // Initialize row states
            rowUpdating.value[index] = false;
            rowChanged.value[index] = false;
            rowSaving.value[index] = false;
            rowError.value[index] = false;
        });
        
        originalAdjustments.value = newOriginal;
        currentAdjustments.value = newCurrent;
    }
};

// Initialize cell loading states
onMounted(() => {
    if (props.reportData && props.reportData.length > 0) {
        props.reportData.forEach((item, index) => {
            cellLoading.value[index] = {
                positive_adjustment: false,
                negative_adjustment: false
            };
            rowUpdating.value[index] = false;
            rowChanged.value[index] = false;
            rowSaving.value[index] = false;
            rowError.value[index] = false;
        });
        initializeAdjustments();
    }
});

// Cleanup timeouts on unmount
onUnmounted(() => {
    Object.values(saveTimeouts.value).forEach(timeout => {
        if (timeout) clearTimeout(timeout);
    });
});

watch([
    () => month_year.value,
    () => selectedWarehouse.value,
], () => {
    reloadData();
});

watch(() => props.reportData, (newData) => {
    if (newData && newData.length > 0) {
        initializeAdjustments();
    }
}, { immediate: true });

function reloadData() {
    dataLoading.value = true;
    const query = {};
    if (month_year.value) query.month_year = month_year.value;
    if (selectedWarehouse.value) query.warehouse_id = selectedWarehouse.value;
    router.get(route('reports.warehouseMonthly'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            'reportData',
            'inventoryReport',
            'monthYear',
            'warehouses',
            'error'
        ],
        onFinish: () => {
            dataLoading.value = false;
            // Re-initialize adjustments with new data
            setTimeout(() => {
                if (props.reportData && props.reportData.length > 0) {
                    initializeAdjustments();
                }
            }, 100);
        }
    });
}

// Row state helpers
const getRowClass = (index) => {
    const classes = ['transition-all', 'duration-300', 'ease-in-out'];
    
    if (rowError.value[index]) {
        classes.push('bg-red-50', 'border-red-200', 'shadow-md');
    } else if (rowSaving.value[index]) {
        classes.push('bg-yellow-50', 'border-yellow-200', 'shadow-md');
    } else if (rowUpdating.value[index]) {
        classes.push('bg-blue-50', 'shadow-md', 'transform', 'scale-[1.02]');
    } else if (rowChanged.value[index]) {
        classes.push('bg-green-50', 'border-green-200');
    }
    
    return classes.join(' ');
};

const getClosingBalanceClass = (item, index) => {
    const baseClasses = ['transition-all', 'duration-500', 'ease-in-out'];
    const balanceColor = getBalanceColor(calculateClosingBalance(item, index));
    
    if (rowUpdating.value[index]) {
        baseClasses.push('font-bold', 'text-lg', 'animate-pulse');
    } else if (rowChanged.value[index]) {
        baseClasses.push('font-semibold');
    }
    
    baseClasses.push(balanceColor);
    return baseClasses.join(' ');
};

const getClosingBalanceTextClass = (item, index) => {
    const balance = calculateClosingBalance(item, index);
    
    if (balance < 0) {
        return 'text-red-600';
    } else if (balance === 0) {
        return 'text-yellow-600';
    } else {
        return 'text-green-600';
    }
};

// Computed properties
const filteredReportData = computed(() => {
    if (!props.reportData || !Array.isArray(props.reportData)) {
        return [];
    }
    
    let filtered = props.reportData;
    
    // Apply warehouse filter
    if (selectedWarehouse.value) {
        filtered = filtered.filter(item => item.warehouse_id == selectedWarehouse.value);
    }
    
    return filtered;
});

const groupedReportData = computed(() => {
    if (!filteredReportData.value || !Array.isArray(filteredReportData.value)) {
        return {};
    }
    
    const grouped = {};
    filteredReportData.value.forEach((item, index) => {
        const warehouseId = item.warehouse_id || 'unknown';
        if (!grouped[warehouseId]) {
            grouped[warehouseId] = {
                warehouse: item.warehouse || { name: 'Unknown Warehouse' },
                items: []
            };
        }
        grouped[warehouseId].items.push({
            ...item,
            globalIndex: index
        });
    });
    
    return grouped;
});

const canEdit = computed(() => {
    return props.inventoryReport?.status === 'pending' || props.inventoryReport?.status === 'rejected';
});

const submittedBy = computed(() => {
    return props.inventoryReport?.submitted_by ? props.inventoryReport.submitted_by.name : null;
});

const reviewedBy = computed(() => {
    return props.inventoryReport?.reviewed_by ? props.inventoryReport.reviewed_by.name : null;
});

const approvedBy = computed(() => {
    return props.inventoryReport?.approved_by ? props.inventoryReport.approved_by.name : null;
});

const rejectedBy = computed(() => {
    return props.inventoryReport?.rejected_by ? props.inventoryReport.rejected_by.name : null;
});

const canSubmit = computed(() => {
    return props.inventoryReport?.status === 'pending' || props.inventoryReport?.status === 'rejected';
});

const canReview = computed(() => {
    return props.inventoryReport?.status === 'submitted';
});

const canApprove = computed(() => {
    return props.inventoryReport?.status === 'under_review';
});

const canReject = computed(() => {
    return props.inventoryReport?.status === 'under_review';
});

const hasUnsavedChanges = computed(() => {
    if (!currentAdjustments.value || !originalAdjustments.value) {
        return false;
    }

    return Object.keys(currentAdjustments.value).some(index => {
        const current = currentAdjustments.value[index];
        const original = originalAdjustments.value[index];
        
        // Return false if either current or original is undefined
        if (!current || !original) {
            return false;
        }

        return current.positive_adjustment !== original.positive_adjustment ||
               current.negative_adjustment !== original.negative_adjustment;
    });
});

// Get global index for a warehouse item
const getGlobalIndex = (warehouseId, itemIndex) => {
    const warehouseGroup = groupedReportData.value[warehouseId];
    if (!warehouseGroup || !warehouseGroup.items[itemIndex]) {
        return 0;
    }
    return warehouseGroup.items[itemIndex].globalIndex;
};

// Format month year for display
const formatMonthYear = (dateString) => {
    if (!dateString) return 'Select Month';
    return moment(dateString).format('MMMM YYYY');
};

// Format numbers with commas
const formatNumber = (num) => {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString();
};

// Format currency values
const formatCurrency = (num) => {
    if (num === null || num === undefined) return '$0.00';
    return '$' + Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Get balance color based on value
const getBalanceColor = (balance) => {
    if (balance > 0) return 'text-green-600';
    if (balance < 0) return 'text-red-600';
    return 'text-gray-600';
};

// Get status badge class
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'submitted':
            return 'bg-blue-100 text-blue-800';
        case 'under_review':
            return 'bg-purple-100 text-purple-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Methods for report actions
const saveAdjustments = async () => {
    if (!hasUnsavedChanges.value) return;
    
    saving.value = true;
    try {
        const adjustments = [];
        Object.keys(currentAdjustments.value).forEach(index => {
            const current = currentAdjustments.value[index];
            const original = originalAdjustments.value[index];
            const item = props.reportData[parseInt(index)];
            
            if (current.positive_adjustment !== original.positive_adjustment ||
                current.negative_adjustment !== original.negative_adjustment) {
                adjustments.push({
                    product_id: item.product.id,
                    warehouse_id: item.warehouse_id,
                    positive_adjustment: parseFloat(current.positive_adjustment) || 0,
                    negative_adjustment: parseFloat(current.negative_adjustment) || 0
                });
            }
        });

        await axios.put(route('reports.warehouseMonthly.updateInventoryReportAdjustments'), {
            month_year: month_year.value,
            adjustments
        });

        // Update original adjustments to match current
        originalAdjustments.value = JSON.parse(JSON.stringify(currentAdjustments.value));
        
        // Clear all row states
        Object.keys(rowChanged.value).forEach(index => {
            rowChanged.value[index] = false;
            rowUpdating.value[index] = false;
            rowSaving.value[index] = false;
            rowError.value[index] = false;
        });
        
        // Reload data to get updated calculations
        reloadData();
        
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Adjustments saved successfully',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Error saving adjustments:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to save adjustments'
        });
    } finally {
        saving.value = false;
    }
};

const submitReport = async () => {
    if (hasUnsavedChanges.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Unsaved Changes',
            text: 'Please save your changes before submitting the report.'
        });
        return;
    }

    processing.value = true;
    try {
        await axios.put(route('reports.warehouseMonthly.submit'), {
            month_year: month_year.value
        });
        
        reloadData();
        
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Report submitted successfully',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Error submitting report:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to submit report'
        });
    } finally {
        processing.value = false;
    }
};

const reviewReport = async () => {
    processing.value = true;
    try {
        await axios.put(route('reports.warehouseMonthly.review'), {
            month_year: month_year.value
        });
        
        reloadData();
        
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Report marked as under review',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Error reviewing report:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to review report'
        });
    } finally {
        processing.value = false;
    }
};

const approveReport = async () => {
    processing.value = true;
    try {
        await axios.put(route('reports.warehouseMonthly.approve'), {
            month_year: month_year.value
        });
        
        reloadData();
        
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Report approved successfully',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Error approving report:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to approve report'
        });
    } finally {
        processing.value = false;
    }
};

const rejectReport = async () => {
    processing.value = true;
    try {
        await axios.put(route('reports.warehouseMonthly.reject'), {
            month_year: month_year.value
        });
        
        reloadData();
        
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Report rejected successfully',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Error rejecting report:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to reject report'
        });
    } finally {
        processing.value = false;
    }
};

// Export to Excel using frontend xlsx library
const exportToExcel = async () => {
    try {
        // Import xlsx dynamically
        const XLSX = await import('xlsx');
        
        // Prepare the data for export
        const exportData = props.reportData.map((item, index) => {
            // Calculate closing balance using the same formula as the table
            const currentAdj = currentAdjustments.value[index];
            const positiveAdj = currentAdj ? (parseFloat(currentAdj.positive_adjustment) || 0) : item.positive_adjustment;
            const negativeAdj = currentAdj ? (parseFloat(currentAdj.negative_adjustment) || 0) : item.negative_adjustment;
            const closingBalance = item.beginning_balance + item.received_quantity - item.issued_quantity - negativeAdj + positiveAdj;
            
            return {
                'Warehouse': item.warehouse?.name || 'Unknown Warehouse',
                'Product': item.product.name || 'N/A',
                'Beginning Balance': item.beginning_balance || 0,
                'Stock Received': item.received_quantity || 0,
                'Stock Issued': item.issued_quantity || 0,
                'Negative Adjustment': negativeAdj,
                'Positive Adjustment': positiveAdj,
                'Closing Balance': closingBalance
            };
        });
        
        // Create workbook and worksheet
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.json_to_sheet(exportData);
        
        // Set column widths
        const columnWidths = [
            { wch: 25 }, // Warehouse
            { wch: 30 }, // Product
            { wch: 15 }, // Beginning Balance
            { wch: 15 }, // Stock Received
            { wch: 15 }, // Stock Issued
            { wch: 18 }, // Negative Adjustment
            { wch: 18 }, // Positive Adjustment
            { wch: 15 }  // Closing Balance
        ];
        worksheet['!cols'] = columnWidths;
        
        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Warehouse Monthly Report');
        
        // Generate filename
        const filename = `warehouse_monthly_report_${month_year.value}.xlsx`;
        
        // Download the file
        XLSX.writeFile(workbook, filename);
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Export Successful',
            text: `Report exported as ${filename}`,
            timer: 2000,
            showConfirmButton: false
        });
        
    } catch (error) {
        console.error('Error exporting to Excel:', error);
        Swal.fire({
            icon: 'error',
            title: 'Export Error',
            text: 'Failed to export report to Excel. Please try again.'
        });
    }
};

// Calculate closing balance using the specification formula
const calculateClosingBalance = (item, index) => {
    const currentAdj = currentAdjustments.value[index];
    
    // Use current form values if editing, otherwise use original values
    const positiveAdj = currentAdj ? (parseFloat(currentAdj.positive_adjustment) || 0) : item.positive_adjustment;
    const negativeAdj = currentAdj ? (parseFloat(currentAdj.negative_adjustment) || 0) : item.negative_adjustment;
    
    // Formula: Beginning balance + stock received - stock issued - negative adjustment + positive adjustment
    return item.beginning_balance + item.received_quantity - item.issued_quantity - negativeAdj + positiveAdj;
};

const updateCalculatedBalance = (item, index) => {
    if (currentAdjustments.value[index]) {
        // Clear any previous error state
        rowError.value[index] = false;
        
        // Mark row as updating
        rowUpdating.value[index] = true;
        
        currentAdjustments.value[index].positive_adjustment = parseFloat(currentAdjustments.value[index].positive_adjustment) || 0;
        currentAdjustments.value[index].negative_adjustment = parseFloat(currentAdjustments.value[index].negative_adjustment) || 0;
        
        // Clear previous timeout if exists
        if (saveTimeouts.value[index]) {
            clearTimeout(saveTimeouts.value[index]);
        }
        
        // Debounce auto-save with 1 second delay
        saveTimeouts.value[index] = setTimeout(async () => {
            // Mark as saving
            rowSaving.value[index] = true;
            
            // Auto-save to backend
            try {
                await saveRowToBackend(item, index);
                
                // Success state
                rowChanged.value[index] = true;
                rowSaving.value[index] = false;
                
                // Clear updating state after animation
                setTimeout(() => {
                    rowUpdating.value[index] = false;
                }, 300);
                
                // Clear success state after delay
                setTimeout(() => {
                    rowChanged.value[index] = false;
                }, 2000);
                
            } catch (error) {
                console.error('Error auto-saving row:', error);
                
                // Error state
                rowError.value[index] = true;
                rowSaving.value[index] = false;
                rowUpdating.value[index] = false;
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Auto-save Failed',
                    text: error.response?.data?.message || 'Failed to save changes automatically',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                
                // Clear error state after delay
                setTimeout(() => {
                    rowError.value[index] = false;
                }, 5000);
            }
        }, 1000); // 1 second debounce
    }
};

// Auto-save individual row to backend
const saveRowToBackend = async (item, index) => {
    const current = currentAdjustments.value[index];
    const original = originalAdjustments.value[index];
    
    // Check if there are actual changes
    if (current.positive_adjustment === original.positive_adjustment &&
        current.negative_adjustment === original.negative_adjustment) {
        return; // No changes to save
    }
    
    try {
        await axios.put(route('reports.warehouseMonthly.updateInventoryReportAdjustments'), {
            month_year: month_year.value,
            adjustments: [{
                product_id: item.product.id,
                warehouse_id: item.warehouse_id,
                positive_adjustment: parseFloat(current.positive_adjustment) || 0,
                negative_adjustment: parseFloat(current.negative_adjustment) || 0
            }]
        });
        
        // Update original values to match current (mark as saved)
        originalAdjustments.value[index] = {
            positive_adjustment: current.positive_adjustment,
            negative_adjustment: current.negative_adjustment
        };
        
        return true;
    } catch (error) {
        throw error;
    }
};


</script>
