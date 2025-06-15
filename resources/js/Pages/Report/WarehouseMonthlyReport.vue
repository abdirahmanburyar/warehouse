<template>
    <AuthenticatedLayout title="Warehouse Monthly Report" description="Monthly Inventory Movement Summary" img="/assets/images/report.png">
        <Head title="Warehouse Monthly Report" />
        
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Warehouse Monthly Inventory Report</h1>
                    <p class="text-xs text-gray-600">Monthly inventory movement summary with balances and transactions</p>
                </div>
            </div>

            <!-- Report Status -->
            <div v-if="inventoryReport" class="mb-6 p-4 bg-gray-50 rounded-lg border">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-900 mb-1">Report Status</h3>
                        <span :class="getStatusBadgeClass(inventoryReport.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                            {{ getStatusText(inventoryReport.status) }}
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <!-- Save Adjustments Button -->
                        <button 
                            v-if="canEdit && hasUnsavedChanges"
                            @click="saveAdjustments"
                            :disabled="saving"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ saving ? 'Saving...' : 'Save Adjustments' }}
                        </button>

                        <!-- Submit Button -->
                        <button 
                            v-if="canSubmit"
                            @click="submitReport"
                            :disabled="processing"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            Submit for Review
                        </button>

                        <!-- Review Button -->
                        <button 
                            v-if="canReview && inventoryReport.status === 'submitted'"
                            @click="reviewReport"
                            :disabled="processing"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50"
                        >
                            Start Review
                        </button>

                        <!-- Approve Button -->
                        <button 
                            v-if="canApprove"
                            @click="approveReport"
                            :disabled="processing"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            Approve
                        </button>

                        <!-- Reject Button -->
                        <button 
                            v-if="canReject"
                            @click="rejectReport"
                            :disabled="processing"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex justify-between mb-[30px]">
                <!-- Month Filter -->
                <div class="w-[200px]">
                    <label for="month" class="block text-xs font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Report Month
                    </label>
                    <input 
                        type="month" 
                        id="month"
                        v-model="monthYear" 
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                    />
                </div>



            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash.success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-green-800 text-xs font-medium">{{ $page.props.flash.success }}</p>
                </div>
            </div>

            <div v-if="$page.props.flash.error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="text-red-800 text-xs font-medium">{{ $page.props.flash.error }}</p>
                </div>
            </div>

            <!-- Report Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-[80px]">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-xs font-semibold text-gray-900">Items</h2>
                    <p class="text-xs text-gray-600 mt-1">{{ formatMonthYear(monthYear) }}</p>
                </div>

                <div v-if="reportData?.length > 0" class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="p-2 text-left text-xs text-black capitalize tracking-wider">
                                    Item
                                </th>
                                <th scope="col" class="p-2 text-left text-xs text-black capitalize tracking-wider">
                                    UoM
                                </th>
                                <th scope="col" class="p-2 text-left text-xs text-black capitalize tracking-wider">
                                    Expiry Date
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Beginning Balance
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Stock Received
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Stock Issued
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Positive Adj.
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Negative Adj.
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Closing Balance
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Unit Cost
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Total Cost
                                </th>
                                <th scope="col" class="p-2 text-right text-xs text-black capitalize tracking-wider">
                                    Months of Stock
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in reportData" :key="item.product.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="p-2">
                                    <div class="flex items-center space-x-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium text-gray-900 truncate">
                                                {{ item.product.name }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ item.product.category?.name || 'Uncategorized' }} • 
                                                {{ item.product.dosage?.name || 'No dosage' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                   {{ item.uom }}
                                </td>
                                <td>
                                   {{ formatDate(item.expiry_date) }}
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-gray-900">
                                    {{ formatNumber(item.beginning_balance) }}
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-green-600">
                                    {{ formatNumber(item.received_quantity) }}
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-red-600">
                                    {{ formatNumber(item.issued_quantity) }}
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-blue-600">
                                    <input 
                                        v-if="canEdit"
                                        type="number" 
                                        :value="getCurrentAdjustment(item.product.id, 'positive_adjustment')"
                                        @input="updateAdjustment(item.product.id, 'positive_adjustment', $event.target.value)"
                                        @keydown.enter="saveAdjustments"
                                        class="w-[150px] bg-gray-50 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs"
                                        :disabled="!canEdit"
                                    />
                                    <span v-else>{{ formatNumber(item.positive_adjustment) }}</span>
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-orange-600">
                                    <input 
                                        v-if="canEdit"
                                        type="number" 
                                        :value="getCurrentAdjustment(item.product.id, 'negative_adjustment')"
                                        @input="updateAdjustment(item.product.id, 'negative_adjustment', $event.target.value)"
                                        @keydown.enter="saveAdjustments"
                                        class="w-[150px] bg-gray-50 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs"
                                        :disabled="!canEdit"
                                    />
                                    <span v-else>{{ formatNumber(item.negative_adjustment) }}</span>
                                </td>
                                <td class="p-2 text-right text-xs font-bold" :class="item.closing_balance > 0 ? 'text-green-600' : item.closing_balance < 0 ? 'text-red-600' : 'text-gray-600'">
                                    {{ formatNumber(item.closing_balance) }}
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-gray-900">
                                    {{ formatNumber(item.unit_cost) }}
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-gray-900">
                                    {{ formatNumber(item.total_cost) }}
                                </td>
                                <td class="p-2 text-right text-xs font-medium text-gray-900">
                                    <input 
                                        v-if="canEdit"
                                        type="text" 
                                        :value="getCurrentAdjustment(item.product.id, 'months_of_stock')"
                                        @input="updateAdjustment(item.product.id, 'months_of_stock', $event.target.value)"
                                        @keydown.enter="saveAdjustments"
                                        class="w-[150px] bg-gray-50 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs"
                                        :disabled="!canEdit"
                                    />
                                    <span v-else class="text-xs">{{ formatNumber(item.months_of_stock) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="flex justify-center mb-4">
                        <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xs font-medium text-gray-900 mb-2">No data available</h3>
                    <p class="text-xs text-gray-500 mb-4">No inventory movements found for the selected month.</p>
                </div>

            </div>
        </div>
        </AuthenticatedLayout>
    </template>

    <script>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import Swal from 'sweetalert2';
    import axios from 'axios';
    import { ref, watch, onMounted, computed } from 'vue';

    import moment from 'moment';


    export default {
        name: 'WarehouseMonthlyReport',
        components: {
            AuthenticatedLayout,
            Head,
            Link,

            moment
        },
        props: {
            reportData: {
                type: Object,
                default: () => ({})
            },
            filters: {
                type: Object,
                default: () => ({})
            },
            inventoryReport: {
                type: Object,
                default: () => ({})
            }
        },
        setup(props) {
            const monthYear = ref(props.filters.month_year || new Date().toISOString().slice(0, 7));
            const saving = ref(false);
            const processing = ref(false);
            const originalAdjustments = ref({});
            const currentAdjustments = ref({});

            // Safe getter for adjustment values
            const getCurrentAdjustment = (productId, field) => {
                if (!currentAdjustments.value[productId]) {
                    return 0; // Return default value if not initialized
                }
                return currentAdjustments.value[productId][field] || 0;
            };

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
                return Object.keys(currentAdjustments.value).some(id => {
                    const current = currentAdjustments.value[id];
                    const original = originalAdjustments.value[id];
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

            // Get status text
            const getStatusText = (status) => {
                switch (status) {
                    case 'generated':
                        return 'Draft';
                    case 'submitted':
                        return 'Submitted for Review';
                    case 'under_review':
                        return 'Under Review';
                    case 'approved':
                        return 'Approved';
                    case 'rejected':
                        return 'Rejected';
                    default:
                        return 'Unknown';
                }
            };

            // Update adjustment value
            const updateAdjustment = (productId, field, value) => {
                console.log('updateAdjustment called:', { productId, field, value, type: typeof value });
                
                // Initialize adjustments if they don't exist
                if (!currentAdjustments.value[productId]) {
                    currentAdjustments.value[productId] = {
                        positive_adjustment: 0,
                        negative_adjustment: 0,
                        months_of_stock: '0'  // Initialize as string
                    };
                }
                
                if (field === 'months_of_stock') {
                    console.log('Updating months_of_stock - before:', {
                        oldValue: currentAdjustments.value[productId][field],
                        newValue: value,
                        type: typeof value
                    });
                    // Keep as string for months_of_stock
                    currentAdjustments.value[productId][field] = value.toString();
                    console.log('Updating months_of_stock - after:', {
                        newValue: currentAdjustments.value[productId][field],
                        type: typeof currentAdjustments.value[productId][field]
                    });
                } else {
                    // Parse as float for numerical fields
                    currentAdjustments.value[productId][field] = parseFloat(value) || 0;
                }
            };

            // Save adjustments
            const saveAdjustments = async () => {
                saving.value = true;
                console.log('Saving adjustments...', JSON.parse(JSON.stringify(currentAdjustments.value)));
                try {
                    const changedItems = Object.keys(currentAdjustments.value).filter(id => {
                        const current = currentAdjustments.value[id];
                        const original = originalAdjustments.value[id];
                        return current.positive_adjustment !== original.positive_adjustment ||
                               current.negative_adjustment !== original.negative_adjustment ||
                               current.months_of_stock !== original.months_of_stock;
                    }).map(id => ({
                        product_id: parseInt(id),
                        positive_adjustment: currentAdjustments.value[id].positive_adjustment,
                        negative_adjustment: currentAdjustments.value[id].negative_adjustment,
                        months_of_stock: currentAdjustments.value[id].months_of_stock
                    }));

                    const payload = {
                        month_year: monthYear.value,
                        adjustments: changedItems
                    };
                    
                    console.log('Sending payload to server:', JSON.parse(JSON.stringify(payload)));
                    
                    const response = await axios.put(route('reports.warehouseMonthly.updateAdjustments'), payload);
                    
                    console.log('Server response:', response);

                    if (response.status === 200) {
                        // Update the local reportData with the new values
                        reportData.value = reportData.value.map(item => {
                            const adjustment = currentAdjustments.value[item.product.id];
                            if (adjustment) {
                                return {
                                    ...item,
                                    positive_adjustment: adjustment.positive_adjustment,
                                    negative_adjustment: adjustment.negative_adjustment,
                                    months_of_stock: adjustment.months_of_stock,
                                    // Recalculate closing balance locally
                                    closing_balance: item.beginning_balance + 
                                                  item.received_quantity - 
                                                  item.issued_quantity + 
                                                  adjustment.positive_adjustment - 
                                                  adjustment.negative_adjustment,
                                    // Recalculate total cost locally
                                    total_cost: (item.beginning_balance + 
                                               item.received_quantity - 
                                               item.issued_quantity + 
                                               adjustment.positive_adjustment - 
                                               adjustment.negative_adjustment) * 
                                               item.unit_cost
                                };
                            }
                            return item;
                        });

                        // Update original adjustments to match current ones
                        originalAdjustments.value = JSON.parse(JSON.stringify(currentAdjustments.value));
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Adjustments saved successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error('Error saving adjustments:', error);
                    console.error('Error response:', error.response?.data);
                    
                    // Close loading indicator
                    Swal.close();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.response?.data?.message || 'Failed to save adjustments',
                        showConfirmButton: true
                    });
                } finally {
                    saving.value = false;
                }
            };

            // Submit report
            const submitReport = async () => {
                processing.value = true;
                try {
                    await router.put(route('reports.warehouseMonthly.submit'), {
                        month_year: monthYear.value
                    }, {
                        preserveState: false
                    });
                } catch (error) {
                    console.error('Error submitting report:', error);
                } finally {
                    processing.value = false;
                }
            };

            // Review report
            const reviewReport = async () => {
                processing.value = true;
                try {
                    await router.put(route('reports.warehouseMonthly.review'), {
                        month_year: monthYear.value
                    }, {
                        preserveState: false
                    });
                } catch (error) {
                    console.error('Error reviewing report:', error);
                } finally {
                    processing.value = false;
                }
            };

            // Approve report
            const approveReport = async () => {
                if (!confirm('Are you sure you want to approve this report?')) {
                    return;
                }
                
                processing.value = true;
                try {
                    await router.put(route('reports.warehouseMonthly.approve'), {
                        month_year: monthYear.value
                    }, {
                        preserveState: false
                    });
                } catch (error) {
                    console.error('Error approving report:', error);
                } finally {
                    processing.value = false;
                }
            };

            // Reject report
            const rejectReport = async () => {
                const { value: reason } = await Swal.fire({
                    title: 'Reject Report',
                    text: 'Please provide a reason for rejection:',
                    input: 'textarea',
                    inputPlaceholder: 'Rejection reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    cancelButtonText: 'Cancel',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Please provide a reason for rejection';
                        }
                    }
                });

                if (reason) {
                    try {
                        const response = await axios.put(route('reports.warehouseMonthly.reject'), {
                            month_year: monthYear.value,
                            rejection_reason: reason
                        });

                        if (response.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Report rejected successfully',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            reloadPage();
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.response?.data?.message || 'Failed to reject report',
                            showConfirmButton: true
                        });
                    }
                }
            };

            // Reload page with new parameters
            const reloadPage = () => {
                const params = new URLSearchParams();
                if (monthYear.value) params.set('month_year', monthYear.value);
                params.set('load_data', '1'); // Always load data when filters change
                
                router.get(route('reports.warehouseMonthly'), Object.fromEntries(params), {
                    preserveState: true,
                    preserveScroll: true
                });
            };

            // Load data on mount if we don't have any
            onMounted(() => {
                initializeAdjustments();
                
                if (!props.reportData?.data || props.reportData.data.length === 0) {
                    const params = new URLSearchParams();
                    if (monthYear.value) params.set('month_year', monthYear.value);
                    params.set('load_data', '1');
                    
                    router.get(route('reports.warehouseMonthly'), Object.fromEntries(params), {
                        preserveState: true,
                        preserveScroll: true
                    });
                }
            });

            // Watch for filter changes
            watch([monthYear], () => {
                reloadPage();
            });



            // Watch for data changes to reinitialize adjustments
            // Load data on mount if we don't have any
            onMounted(() => {
                initializeAdjustments();
            });

            return {
                monthYear,
                saving,
                processing,
                currentAdjustments,
                originalAdjustments,
                initializeAdjustments,
                updateAdjustment,
                saveAdjustments,
                submitReport,
                reviewReport,
                approveReport,
                rejectReport,
                formatNumber,
                formatMonthYear,
                formatDate,
                getStatusBadgeClass,
                getStatusText,
                hasUnsavedChanges,
                canEdit,
                canSubmit,
                canReview,
                canApprove,
                canReject,
                reloadPage,
                getCurrentAdjustment,
                formatDate
            };
        }
    };
    </script>
