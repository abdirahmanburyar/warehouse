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
                    <p class="text-sm text-gray-600">Monthly inventory movement summary with balances and transactions</p>
                </div>
            </div>

            <!-- Report Status -->
            <div v-if="inventoryReport" class="mb-6 p-4 bg-gray-50 rounded-lg border">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">Report Status</h3>
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
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
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
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            Submit for Review
                        </button>

                        <!-- Review Button -->
                        <button 
                            v-if="canReview && inventoryReport.status === 'submitted'"
                            @click="reviewReport"
                            :disabled="processing"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50"
                        >
                            Start Review
                        </button>

                        <!-- Approve Button -->
                        <button 
                            v-if="canApprove"
                            @click="approveReport"
                            :disabled="processing"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            Approve
                        </button>

                        <!-- Reject Button -->
                        <button 
                            v-if="canReject"
                            @click="rejectReport"
                            :disabled="processing"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Month Filter -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Report Month
                    </label>
                    <input 
                        type="month" 
                        id="month"
                        v-model="monthYear" 
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm"
                    />
                </div>

                <!-- Items Per Page -->
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        Items per page
                    </label>
                    <select 
                        id="per_page"
                        v-model="perPage" 
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm"
                    >
                        <option value="10">10 items</option>
                        <option value="25">25 items</option>
                        <option value="50">50 items</option>
                        <option value="100">100 items</option>
                    </select>
                </div>

                <!-- Summary Card -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Products</p>
                            <p class="text-lg font-bold text-blue-900">{{ reportData?.data?.length || 0 }}</p>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash.success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-green-800 text-sm font-medium">{{ $page.props.flash.success }}</p>
                </div>
            </div>

            <div v-if="$page.props.flash.error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="text-red-800 text-sm font-medium">{{ $page.props.flash.error }}</p>
                </div>
            </div>

            <!-- Report Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-[80px]">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-900">Inventory Movement Report</h2>
                    <p class="text-xs text-gray-600 mt-1">{{ formatMonthYear(monthYear) }}</p>
                </div>

                <div v-if="reportData?.data?.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Beginning Balance
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock Received
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock Issued
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Positive Adj.
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Negative Adj.
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Closing Balance
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unit Cost
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Cost
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Months of Stock
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in reportData.data" :key="item.product.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">
                                                    {{ item.product.name.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ item.product.name }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ item.product.category?.name || 'Uncategorized' }} • 
                                                {{ item.product.dosage?.name || 'No dosage' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                    {{ formatNumber(item.beginning_balance) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-green-600">
                                    {{ formatNumber(item.received_quantity) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-red-600">
                                    {{ formatNumber(item.issued_quantity) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-blue-600">
                                    <input 
                                        v-if="canEdit"
                                        type="number" 
                                        v-model="currentAdjustments[item.product.id].positive_adjustment" 
                                        @input="updateAdjustment(item.product.id, 'positive_adjustment', $event.target.value)"
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm"
                                    />
                                    <span v-else>{{ formatNumber(item.positive_adjustment) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-orange-600">
                                    <input 
                                        v-if="canEdit"
                                        type="number" 
                                        v-model="currentAdjustments[item.product.id].negative_adjustment" 
                                        @input="updateAdjustment(item.product.id, 'negative_adjustment', $event.target.value)"
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm"
                                    />
                                    <span v-else>{{ formatNumber(item.negative_adjustment) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold" :class="getBalanceColor(item.closing_balance)">
                                    {{ formatNumber(item.closing_balance) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                    {{ formatNumber(item.unit_cost) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                    {{ formatNumber(item.total_cost) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                    {{ formatNumber(item.months_of_stock) }}
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
                    <h3 class="text-sm font-medium text-gray-900 mb-2">No data available</h3>
                    <p class="text-xs text-gray-500 mb-4">No inventory movements found for the selected month.</p>
                </div>

                <!-- Pagination -->
                <div v-if="reportData?.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            Showing {{ reportData.from || 0 }} to {{ reportData.to || 0 }} of {{ reportData.total || 0 }} results
                        </div>
                        <TailwindPagination 
                            :data="reportData" 
                            @pagination-change-page="onPageChange"
                            :limit="2"
                            class="flex space-x-1"
                        />
                    </div>
                </div>
            </div>
        </div>
        </AuthenticatedLayout>
    </template>

    <script>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import { ref, watch, onMounted, computed } from 'vue';
    import { TailwindPagination } from 'laravel-vue-pagination';

    export default {
        name: 'WarehouseMonthlyReport',
        components: {
            AuthenticatedLayout,
            Head,
            Link,
            TailwindPagination,
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
            const perPage = ref(props.filters.per_page || 25);
            const saving = ref(false);
            const processing = ref(false);
            const originalAdjustments = ref({});
            const currentAdjustments = ref({});

            // Initialize adjustments tracking
            const initializeAdjustments = () => {
                if (props.reportData?.data) {
                    props.reportData.data.forEach(item => {
                        originalAdjustments.value[item.product.id] = {
                            positive_adjustment: item.positive_adjustment,
                            negative_adjustment: item.negative_adjustment
                        };
                        currentAdjustments.value[item.product.id] = {
                            positive_adjustment: item.positive_adjustment,
                            negative_adjustment: item.negative_adjustment
                        };
                    });
                }
            };

            // Computed properties
            const canEdit = computed(() => {
                return props.inventoryReport?.status === 'draft' || props.inventoryReport?.status === 'rejected';
            });

            const canSubmit = computed(() => {
                return props.inventoryReport?.status === 'draft' || props.inventoryReport?.status === 'rejected';
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
                           current.negative_adjustment !== original.negative_adjustment;
                });
            });

            // Format month year for display
            const formatMonthYear = (monthYear) => {
                if (!monthYear) return '';
                const date = new Date(monthYear + '-01');
                return date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long' 
                });
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
                    case 'draft':
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
                    case 'draft':
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
            const updateAdjustment = (itemId, field, value) => {
                if (!currentAdjustments.value[itemId]) {
                    currentAdjustments.value[itemId] = {
                        positive_adjustment: 0,
                        negative_adjustment: 0
                    };
                }
                currentAdjustments.value[itemId][field] = parseFloat(value) || 0;
            };

            // Save adjustments
            const saveAdjustments = async () => {
                saving.value = true;
                try {
                    const changedItems = Object.keys(currentAdjustments.value).filter(id => {
                        const current = currentAdjustments.value[id];
                        const original = originalAdjustments.value[id];
                        return current.positive_adjustment !== original.positive_adjustment ||
                               current.negative_adjustment !== original.negative_adjustment;
                    }).map(id => ({
                        id: parseInt(id),
                        positive_adjustment: currentAdjustments.value[id].positive_adjustment,
                        negative_adjustment: currentAdjustments.value[id].negative_adjustment
                    }));

                    await router.put(route('reports.warehouseMonthly.updateAdjustments'), {
                        month_year: monthYear.value,
                        adjustments: changedItems
                    }, {
                        preserveState: false,
                        onSuccess: () => {
                            // Update original values
                            initializeAdjustments();
                        }
                    });
                } catch (error) {
                    console.error('Error saving adjustments:', error);
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
                const reason = prompt('Please provide a reason for rejection:');
                if (!reason) {
                    return;
                }
                
                processing.value = true;
                try {
                    await router.put(route('reports.warehouseMonthly.reject'), {
                        month_year: monthYear.value,
                        rejection_reason: reason
                    }, {
                        preserveState: false
                    });
                } catch (error) {
                    console.error('Error rejecting report:', error);
                } finally {
                    processing.value = false;
                }
            };

            // Reload page with new parameters
            const reloadPage = () => {
                const params = new URLSearchParams();
                if (monthYear.value) params.set('month_year', monthYear.value);
                if (perPage.value) params.set('per_page', perPage.value);
                params.set('load_data', '1'); // Always load data when filters change
                
                router.get(route('reports.warehouseMonthly'), Object.fromEntries(params), {
                    preserveState: true,
                    preserveScroll: true
                });
            };

            // Handle pagination change
            const onPageChange = (page) => {
                const params = new URLSearchParams();
                if (monthYear.value) params.set('month_year', monthYear.value);
                if (perPage.value) params.set('per_page', perPage.value);
                params.set('load_data', '1'); // Always load data for pagination
                params.set('page', page);
                
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
                    if (perPage.value) params.set('per_page', perPage.value);
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

            watch(perPage, () => {
                reloadPage();
            });

            // Watch for data changes to reinitialize adjustments
            watch(() => props.reportData?.data, () => {
                initializeAdjustments();
            }, { deep: true });

            return {
                monthYear,
                perPage,
                formatMonthYear,
                formatNumber,
                getBalanceColor,
                onPageChange,
                canEdit,
                canSubmit,
                canReview,
                canApprove,
                canReject,
                saving,
                processing,
                hasUnsavedChanges,
                getStatusBadgeClass,
                getStatusText,
                updateAdjustment,
                currentAdjustments,
                saveAdjustments,
                submitReport,
                reviewReport,
                approveReport,
                rejectReport
            };
        }
    };
    </script>
