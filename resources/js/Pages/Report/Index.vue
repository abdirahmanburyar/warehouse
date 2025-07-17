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
                                    <span>Warehouse Inventory Report</span>
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

                    <!-- Products Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Products Reports</h3>
                                    <p class="text-sm text-gray-600">Product management & analytics</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Comprehensive product reports including active/inactive status, eligibility, categories, and dosage forms analysis.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.products.active-inactive')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <span>Active & Inactive Product Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.products.eligibility')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <span>Product Eligibility Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.products.categories')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <span>Product Category Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.products.dosage-forms')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <span>Product Dosage Forms Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Expiry Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Expiry Reports</h3>
                                    <p class="text-sm text-gray-600">Expiration tracking & alerts</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Monitor product expiration dates with comprehensive tracking for expired, expiring within 6 months, and expiring within 1 year.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.products.expiry-tracking')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-red-50 rounded-lg transition-colors">
                                    <span>Expiry Tracking Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Procurement Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Procurement</h3>
                                    <p class="text-sm text-gray-600">Purchase & supply chain</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Comprehensive procurement reports including purchase orders, packing lists, backorders, lead time analysis, and demand forecasting.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.procurement.purchase-orders')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-teal-50 rounded-lg transition-colors">
                                    <span>Purchase Orders Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.procurement.packing-list')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-teal-50 rounded-lg transition-colors">
                                    <span>Packing List Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.procurement.backorder')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-teal-50 rounded-lg transition-colors">
                                    <span>Backorder Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.procurement.lead-time-analysis')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-teal-50 rounded-lg transition-colors">
                                    <span>Lead Time Analysis Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.procurement.demand-forecasting')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-teal-50 rounded-lg transition-colors">
                                    <span>Demand Forecasting Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Liquidation & Disposal Reports Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Liquidation & Disposal</h3>
                                    <p class="text-sm text-gray-600">Item disposal tracking</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">
                                Track product liquidations and item disposals with comprehensive reporting on status, approvals, and financial impact.
                            </p>
                            
                            <div class="space-y-2">
                                <Link :href="route('reports.liquidation-disposal.liquidation')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                    <span>Liquidation Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.liquidation-disposal.disposal')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                    <span>Disposal Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Facilities Reports Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Facilities Card (grouped links) -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden col-span-1">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l9-4 9 4" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Facilities</h3>
                                    <p class="text-sm text-gray-600">Facilities-related reports</p>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Access all facilities-related reports including list, monthly consumption, and compliance.</p>
                            <div class="space-y-2">
                                <Link :href="route('reports.facilities-list')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-cyan-50 rounded-lg transition-colors">
                                    <span>Facilities List Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.lmis-monthly-consumption')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-cyan-50 rounded-lg transition-colors">
                                    <span>LMIS Monthly Consumption Report</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link :href="route('reports.facility-compliance')"
                                    class="flex items-center justify-between p-3 text-sm text-gray-700 hover:bg-cyan-50 rounded-lg transition-colors">
                                    <span>Facility Compliance Report</span>
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