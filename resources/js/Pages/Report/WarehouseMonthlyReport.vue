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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4-8-4m16 0v10l-8 4-8-4V7" />
                            </svg>
                        </div>
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
                                {{ formatNumber(item.positive_adjustment) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-orange-600">
                                {{ formatNumber(item.negative_adjustment) }}
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
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
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
        }
    },
    setup(props) {
        const monthYear = ref(props.filters.month_year || new Date().toISOString().slice(0, 7));
        const perPage = ref(props.filters.per_page || 25);

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

        return {
            monthYear,
            perPage,
            formatMonthYear,
            formatNumber,
            getBalanceColor,
            onPageChange
        };
    }
};
</script>
