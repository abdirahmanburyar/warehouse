<template>
    <AuthenticatedLayout title="Warehouse Monthly Report" description="Monthly Inventory Movement Summary" img="/assets/images/report.png">
        <Head title="Warehouse Monthly Report" />
        
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Month Year Selector -->
                <div class="flex items-center gap-4">
                    <input 
                        type="month" 
                        v-model="month_year"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <span class="text-gray-700 font-medium">{{ formatMonthYear(month_year) }}</span>
                </div>

                <!-- Report Status -->
                <div class="flex items-center gap-4">
                    <span class="text-gray-600">Status:</span>
                    <span :class="getStatusBadgeClass(props.inventoryReport?.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                        {{ props.inventoryReport?.status?.toUpperCase() ?? 'N/A' }}
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

        <!-- Report Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-[100px]">
            <div v-if="dataLoading" class="p-8 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                <div class="mt-4 text-gray-700">Loading report data...</div>
                <div class="text-sm text-gray-500 mt-2">This may take a few moments for large datasets</div>
            </div>
            <div v-else-if="props.reportData && props.reportData.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Beginning Balance</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received Quantity</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Quantity</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Other Quantity Out</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Positive Adjustment</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Negative Adjustment</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Closing Balance</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Average Monthly Consumption</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Months of Stock</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity in Pipeline</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in props.reportData" :key="item.product.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ item.product.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ item.uom || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ item.batch_number || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ item.expiry_date ? formatDate(item.expiry_date) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right" :class="getBalanceColor(item.beginning_balance)">
                                {{ formatNumber(item.beginning_balance) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.received_quantity) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.issued_quantity) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.other_quantity_out) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.positive_adjustment) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.negative_adjustment) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right" :class="getBalanceColor(item.closing_balance)">
                                {{ formatNumber(item.closing_balance) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right" :class="getBalanceColor(item.total_closing_balance)">
                                {{ formatNumber(item.total_closing_balance) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.average_monthly_consumption) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.months_of_stock) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatNumber(item.quantity_in_pipeline) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatCurrency(item.unit_cost) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ formatCurrency(item.total_cost) }}
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
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import moment from 'moment';

const props = defineProps({
    reportData: {
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
const cellLoading = ref({});
const processing = ref(false);
const saving = ref(false);
const dataLoading = ref(false);

// Initialize cell loading states
onMounted(() => {
    if (props.reportData && props.reportData.length > 0) {
        props.reportData.forEach(item => {
            cellLoading.value[item.product.id] = {
                positive_adjustment: false,
                negative_adjustment: false,
                months_of_stock: false
            };
        });
        initializeAdjustments();
    }
});

watch([
    () => month_year.value,
], () => {
    reloadData();
});

function reloadData(){
    dataLoading.value = true;
    const query = {}
    if(month_year.value) query.month_year = month_year.value
    router.get(route('reports.warehouseMonthly'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            'reportData',
            'inventoryReport',
            'monthYear',
            'error'
        ],
        onFinish: () => {
            dataLoading.value = false;
        }
    })
}

// Initialize adjustments tracking
const initializeAdjustments = () => {
    if (props.reportData && Array.isArray(props.reportData)) {
        const newOriginal = {};
        const newCurrent = {};
        
        props.reportData.forEach(item => {
            if (!item.product?.id) return; // Skip if no product id
            
            // Initialize with default values if any field is undefined
            newOriginal[item.product.id] = {
                positive_adjustment: item.positive_adjustment ?? 0,
                negative_adjustment: item.negative_adjustment ?? 0,
                months_of_stock: item.months_of_stock?.toString() ?? '0'  // Convert to string
            };
            
            newCurrent[item.product.id] = {
                positive_adjustment: item.positive_adjustment ?? 0,
                negative_adjustment: item.negative_adjustment ?? 0,
                months_of_stock: item.months_of_stock?.toString() ?? '0'  // Convert to string
            };
        });
        
        originalAdjustments.value = newOriginal;
        currentAdjustments.value = newCurrent;
    }
};

// format dates
const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
};

// Computed properties
const canEdit = computed(() => {
    return props.inventoryReport?.status === 'generated' || props.inventoryReport?.status === 'rejected';
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
    return props.inventoryReport?.status === 'generated' || props.inventoryReport?.status === 'rejected';
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

    return Object.keys(currentAdjustments.value).some(id => {
        const current = currentAdjustments.value[id];
        const original = originalAdjustments.value[id];
        
        // Return false if either current or original is undefined
        if (!current || !original) {
            return false;
        }

        return current.positive_adjustment !== original.positive_adjustment ||
               current.negative_adjustment !== original.negative_adjustment ||
               current.months_of_stock !== original.months_of_stock;
    });
});

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
        case 'generated':
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
        Object.keys(currentAdjustments.value).forEach(productId => {
            const current = currentAdjustments.value[productId];
            const original = originalAdjustments.value[productId];
            
            if (current.positive_adjustment !== original.positive_adjustment ||
                current.negative_adjustment !== original.negative_adjustment ||
                current.months_of_stock !== original.months_of_stock) {
                adjustments.push({
                    product_id: parseInt(productId),
                    positive_adjustment: parseFloat(current.positive_adjustment) || 0,
                    negative_adjustment: parseFloat(current.negative_adjustment) || 0,
                    months_of_stock: current.months_of_stock
                });
            }
        });

        await axios.post(route('reports.warehouseMonthly.updateInventoryReportAdjustments'), {
            month_year: month_year.value,
            adjustments
        });

        // Update original adjustments to match current
        originalAdjustments.value = JSON.parse(JSON.stringify(currentAdjustments.value));
        
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
        await axios.post(route('reports.warehouseMonthly.submit'), {
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
        await axios.post(route('reports.warehouseMonthly.review'), {
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
        await axios.post(route('reports.warehouseMonthly.approve'), {
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
        await axios.post(route('reports.warehouseMonthly.reject'), {
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

// Export to Excel
const exportToExcel = () => {
    window.location.href = route('reports.warehouseMonthly.exportToExcel', { monthYear: month_year.value });
};
</script>
