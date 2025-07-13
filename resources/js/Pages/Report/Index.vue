<template>
    <Head title="Reports" />
    <AuthenticatedLayout title="Reports Dashboard" description="Comprehensive warehouse analytics and insights"
        img="/assets/images/report.png">
        
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Reports Center</h1>
                    <p class="text-gray-600">Generate comprehensive reports for inventory management, transfers, orders, and LMIS compliance</p>
                </div>

                <!-- Reports Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    
                    <!-- Inventory Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Inventory Reports</h3>
                                    <p class="text-sm text-gray-600">Stock management & analytics</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Comprehensive inventory reports including received quantities, issued quantities, physical counts, and monthly summaries.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.receivedQuantities')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    <span>QTY Received Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.issueQuantityReports')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    <span>QTY Issued Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link v-if="$page.props.auth.can.report_physical_count_view" :href="route('reports.physicalCount')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    <span>Physical Count Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.warehouseMonthly')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    <span>Warehouse Monthly Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.monthly-consumption')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    <span>Facility Monthly Consumption</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Transfer Reports</h3>
                                    <p class="text-sm text-gray-600">Stock movement tracking</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Monitor stock transfers between warehouses and facilities with detailed status tracking and history.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.transfers')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-purple-50 rounded-lg transition-colors">
                                    <span>Transfer Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Orders Reports</h3>
                                    <p class="text-sm text-gray-600">Purchase & fulfillment</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Track purchase orders, order fulfillment, and packing lists with comprehensive reporting.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.order-tracking')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-orange-50 rounded-lg transition-colors">
                                    <span>Order Tracking Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.order-fulfillment')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-orange-50 rounded-lg transition-colors">
                                    <span>Order Fulfillment & Efficiency Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- LMIS Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">LMIS Reports</h3>
                                    <p class="text-sm text-gray-600">Compliance & monitoring</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Generate LMIS compliance reports for regulatory requirements and health system monitoring.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.lmis-monthly')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-green-50 rounded-lg transition-colors">
                                    <span>LMIS Monthly Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>