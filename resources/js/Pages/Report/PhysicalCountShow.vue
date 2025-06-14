<template>
    <AuthenticatedLayout title="Physical Count - Report" description="Inventory Verification Tool" img="/assets/images/report.png">
        <Head title="Physical Count Reports" />
        
        <!-- Breadcrumb Navigation -->
        <div class="mb-6">
            <Link 
                :href="route('reports.physicalCount')"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Physical Count
            </Link>
        </div>

        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="flex items-center justify-center w-10 h-10 bg-indigo-100 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Physical Count History</h1>
                    <p class="text-sm text-gray-600">View and manage historical physical count reports</p>
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
                        Filter by Month
                    </label>
                    <input 
                        type="month" 
                        id="month"
                        v-model="month" 
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm"
                    />
                </div>

                <!-- Items Per Page -->
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        Items per Page
                    </label>
                    <select 
                        id="per_page"
                        v-model="per_page" 
                        @change="props.filters.page = 1"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm"
                    >
                        <option value="100">100 per page</option>
                        <option value="200">200 per page</option>
                        <option value="500">500 per page</option>
                    </select>
                </div>

                <!-- Summary Stats -->
                <div class="md:col-span-1 lg:col-span-1">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Reports</p>
                                <p class="text-lg font-semibold text-gray-900">{{ physicalCountReport.total || 0 }}</p>
                            </div>
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports List -->
        <div class="mb-[80px]">
            <div v-if="physicalCountReport.data.length > 0">
                <!-- Reports Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                    <div 
                        v-for="report in physicalCountReport.data" 
                        :key="report.id" 
                        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="p-6">
                            <!-- Report Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">{{ formatDate(report.month_year) }}</h3>
                                        <p class="text-xs text-gray-500">Report ID: {{ report.id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span :class="getStatusClass(report.status)" class="text-xs font-medium">
                                        {{ report.status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Report Details -->
                            <div class="space-y-3 mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Adjustment Date
                                    </span>
                                    <span class="font-medium text-gray-900">{{ formatDateTime(report.adjustment_date) }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        Items Counted
                                    </span>
                                    <span class="font-medium text-gray-900">{{ report.items.length }}</span>
                                </div>

                                <!-- Status-specific Info -->
                                <div v-if="report.reviewer" class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Reviewed by
                                    </span>
                                    <span class="font-medium text-gray-900">{{ report.reviewer.name }}</span>
                                </div>

                                <div v-if="report.approver" class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Approved by
                                    </span>
                                    <span class="font-medium text-gray-900">{{ report.approver.name }}</span>
                                </div>

                                <div v-if="report.rejecter" class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Rejected by
                                    </span>
                                    <span class="font-medium text-gray-900">{{ report.rejecter.name }}</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="pt-4 border-t border-gray-100">
                                <button 
                                    @click="openModal(report)"
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="flex justify-end mt-3">
                    <TailwindPagination
                        :data="props.physicalCountReport"
                        :limit="2"
                        @pagination-change-page="getResult"
                    />
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No Physical Count Reports</h3>
                    <p class="text-gray-500 mb-4">No physical count report data available for the selected criteria.</p>
                    <Link 
                        :href="route('reports.physicalCount')"
                        class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create New Report
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modal for Report Details -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="closeModal"></div>
            
            <!-- Modal container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden" id="reportContent">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Physical Count Report Details</h2>
                                    <p class="text-sm text-gray-600" v-if="selectedReport">{{ formatDate(selectedReport.month_year) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <button
                                    @click="downloadPDF"
                                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-500 border border-transparent rounded-lg hover:from-green-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download PDF
                                </button>
                                <button
                                    @click="closeModal"
                                    class="inline-flex items-center justify-center w-10 h-10 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Content -->
                    <div class="overflow-y-auto max-h-[calc(90vh-80px)]">
                        <div v-if="selectedReport" class="p-6 space-y-6">
                            <!-- Report Status Information -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Reviewer Information -->
                                <div v-if="selectedReport.reviewer" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-2 mb-3">
                                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </div>
                                        <h5 class="font-semibold text-blue-800">Reviewed By</h5>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <p><span class="text-gray-600">Name:</span> <span class="font-medium">{{ selectedReport.reviewer.name }}</span></p>
                                        <p><span class="text-gray-600">Username:</span> <span class="font-medium">{{ selectedReport.reviewer.username }}</span></p>
                                        <p><span class="text-gray-600">Date:</span> <span class="font-medium">{{ formatDateTime(selectedReport.reviewed_at) }}</span></p>
                                    </div>
                                </div>
                                
                                <!-- Approver Information -->
                                <div v-if="selectedReport.approver" class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-2 mb-3">
                                        <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-lg">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h5 class="font-semibold text-green-800">Approved By</h5>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <p><span class="text-gray-600">Name:</span> <span class="font-medium">{{ selectedReport.approver.name }}</span></p>
                                        <p><span class="text-gray-600">Username:</span> <span class="font-medium">{{ selectedReport.approver.username }}</span></p>
                                        <p><span class="text-gray-600">Date:</span> <span class="font-medium">{{ formatDateTime(selectedReport.approved_at) }}</span></p>
                                    </div>
                                </div>

                                <!-- Rejection Information -->
                                <div v-if="selectedReport.rejecter" class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-2 mb-3">
                                        <div class="flex items-center justify-center w-8 h-8 bg-red-100 rounded-lg">
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                        <h5 class="font-semibold text-red-800">Rejected By</h5>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <p><span class="text-gray-600">Name:</span> <span class="font-medium">{{ selectedReport.rejecter.name }}</span></p>
                                        <p><span class="text-gray-600">Username:</span> <span class="font-medium">{{ selectedReport.rejecter.username }}</span></p>
                                        <p><span class="text-gray-600">Date:</span> <span class="font-medium">{{ formatDateTime(selectedReport.rejected_at) }}</span></p>
                                        <p><span class="text-gray-600">Reason:</span> <span class="font-medium">{{ selectedReport.rejection_reason || 'No reason provided' }}</span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Report Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-md font-semibold mb-3">Report Information</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Month/Year</p>
                                        <p class="font-medium">{{ formatDate(selectedReport.month_year) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Adjustment Date</p>
                                        <p class="font-medium">{{ formatDateTime(selectedReport.adjustment_date) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Status</p>
                                        <span :class="getStatusClass(selectedReport.status)">
                                            {{ selectedReport.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <h4 class="text-md font-semibold p-4 bg-gray-50">Physical Count Items</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosage</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch Number</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Physical Count</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Difference</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="item in selectedReport.items" :key="item.id" class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm">
                                                    <div class="font-medium text-gray-900">{{ item.product?.name }}</div>
                                                    <div class="text-gray-500 text-xs">ID: {{ item.product?.productID }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-sm">{{ item.uom || 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm">{{ item.product?.category?.name }}</td>
                                                <td class="px-4 py-3 text-sm">{{ item.product?.dosage?.name }}</td>
                                                <td class="px-4 py-3 text-sm">{{ item.barcode || 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm">{{ item.batch_number }}</td>
                                                <td class="px-4 py-3 text-sm">{{ moment(item.expiry_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-4 py-3 text-sm text-right">{{ item.quantity }}</td>
                                                <td class="px-4 py-3 text-sm text-right">{{ item.physical_count }}</td>
                                                <td class="px-4 py-3 text-sm text-right">
                                                    <span :class="getDifferenceClass(item.difference)">
                                                        {{ item.difference }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm">{{ item.remark || '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
        
<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import html2pdf from 'html2pdf.js';
import moment from 'moment';
import { TailwindPagination } from "laravel-vue-pagination";


const props = defineProps({
    physicalCountReport: Object,
    filters: Object
});

const isModalOpen = ref(false);
const selectedReport = ref(null);

const getStatusClass = (status) => {
    const baseClasses = 'px-2.5 py-1 rounded-full text-xs font-medium';
    const statusClasses = {
        pending: `${baseClasses} bg-yellow-100 text-yellow-800 border border-yellow-200`,
        reviewed: `${baseClasses} bg-blue-100 text-blue-800 border border-blue-200`,
        approved: `${baseClasses} bg-green-100 text-green-800 border border-green-200`,
        rejected: `${baseClasses} bg-red-100 text-red-800 border border-red-200`
    };
    return statusClasses[status] || `${baseClasses} bg-gray-100 text-gray-800 border border-gray-200`;
};

const month = ref(props.filters.month);
const per_page = ref(props.filters.per_page);

watch([
    () => month.value,
    () => per_page.value,
    () => props.filters.page,
], () => {
    reloadPage();
})

function getResult(page = 1){
    props.filters.page = page;
}

function reloadPage(){
    const query = {}
    if(month.value) query.month = month.value;
    if(props.filters.page) query.page = props.filters.page;
    if(per_page.value) query.per_page = per_page.value;
    
    router.get(route('reports.physicalCountShow'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            'physicalCountReport'
        ]
    })
}

const getDifferenceClass = (difference) => {
    const baseClasses = 'px-2 py-1 rounded text-xs font-medium';
    if (difference > 0) {
        return `${baseClasses} bg-green-100 text-green-800`;
    } else if (difference < 0) {
        return `${baseClasses} bg-red-100 text-red-800`;
    } else {
        return `${baseClasses} bg-gray-100 text-gray-600`;
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedReport.value = null;
};

const downloadPDF = async () => {
    // Create a clean version of the report for PDF
    const pdfContent = document.createElement('div');
    pdfContent.innerHTML = `
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="font-size: 24px; color: #1f2937; margin-bottom: 10px;">Physical Count Report</h1>
                <p style="font-size: 16px; color: #4b5563;">${formatDate(selectedReport.value.month_year)}</p>
            </div>

            <!-- Report Info -->
            <div style="margin-bottom: 20px; padding: 15px; background-color: #f3f4f6; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; width: 33%;"><strong>Status:</strong> ${selectedReport.value.status.toUpperCase()}</td>
                        <td style="padding: 8px; width: 33%;"><strong>Adjustment Date:</strong> ${formatDateTime(selectedReport.value.adjustment_date)}</td>
                        <td style="padding: 8px; width: 33%;"><strong>Total Items:</strong> ${selectedReport.value.items.length}</td>
                    </tr>
                </table>
            </div>

            <!-- Approval Info -->
            <div style="margin-bottom: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                ${selectedReport.value.reviewer ? `
                    <div style="padding: 15px; background-color: #dbeafe; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0; color: #1e40af;">Reviewed By</h3>
                        <p style="margin: 5px 0;"><strong>Name:</strong> ${selectedReport.value.reviewer.name}</p>
                        <p style="margin: 5px 0;"><strong>Date:</strong> ${formatDateTime(selectedReport.value.reviewed_at)}</p>
                    </div>
                ` : ''}
                ${selectedReport.value.approver ? `
                    <div style="padding: 15px; background-color: #dcfce7; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0; color: #166534;">Approved By</h3>
                        <p style="margin: 5px 0;"><strong>Name:</strong> ${selectedReport.value.approver.name}</p>
                        <p style="margin: 5px 0;"><strong>Date:</strong> ${formatDateTime(selectedReport.value.approved_at)}</p>
                    </div>
                ` : ''}
                ${selectedReport.value.rejecter ? `
                    <div style="padding: 15px; background-color: #fee2e2; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0; color: #991b1b;">Rejected By</h3>
                        <p style="margin: 5px 0;"><strong>Name:</strong> ${selectedReport.value.rejecter.name}</p>
                        <p style="margin: 5px 0;"><strong>Date:</strong> ${formatDateTime(selectedReport.value.rejected_at)}</p>
                        ${selectedReport.value.rejection_reason ? `<p style="margin: 5px 0;"><strong>Reason:</strong> ${selectedReport.value.rejection_reason}</p>` : ''}
                    </div>
                ` : ''}
            </div>

            <!-- Items Table -->
            <div style="margin-top: 20px;">
                <h2 style="font-size: 18px; color: #1f2937; margin-bottom: 15px;">Physical Count Items</h2>
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #f3f4f6;">
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Item</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">UOM</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Category</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Dosage</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Barcode</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Batch Number</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Expiry Date</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Quantity</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Physical Count</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Difference</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${selectedReport.value.items.map(item => `
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px 8px;">
                                    <div style="font-weight: 500;">${item.product.name}</div>
                                    <div style="color: #6b7280; font-size: 11px;">ID: ${item.product.productID}</div>
                                </td>
                                <td style="padding: 12px 8px;">${item.uom || 'N/A'}</td>
                                <td style="padding: 12px 8px;">${item.product.category?.name}</td>
                                <td style="padding: 12px 8px;">${item.product.dosage?.name}</td>
                                <td style="padding: 12px 8px;">${item.barcode || 'N/A'}</td>
                                <td style="padding: 12px 8px;">${item.batch_number}</td>
                                <td style="padding: 12px 8px;">${formatDate(item.expiry_date)}</td>
                                <td style="padding: 12px 8px; text-align: right;">${item.quantity}</td>
                                <td style="padding: 12px 8px; text-align: right;">${item.physical_count}</td>
                                <td style="padding: 12px 8px; text-align: right;">
                                    <span style="padding: 4px 8px; border-radius: 4px; ${item.difference < 0 ? 'background-color: #fee2e2; color: #991b1b;' : item.difference > 0 ? 'background-color: #dcfce7; color: #166534;' : 'color: #4b5563;'}">
                                        ${item.difference}
                                    </span>
                                </td>
                                <td style="padding: 12px 8px;">${item.remark || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #6b7280;">
                <p>Generated on ${new Date().toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'long' })}</p>
            </div>
        </div>
    `;

    // Configure PDF options
    const options = {
        margin: [15, 15],
        filename: `physical-count-report-${selectedReport.value.month_year}.pdf`,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            logging: false
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'landscape',
            compress: true
        },
        pagebreak: { mode: 'avoid-all' }
    };

    try {
        // Generate PDF
        await html2pdf().set(options).from(pdfContent).save();
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('There was an error generating the PDF. Please try again.');
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return moment(dateString).format('MMMM YYYY');
};

const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return 'N/A';
    return moment(dateTimeString).format('DD/MM/YYYY HH:mm:ss');
};

const openModal = (report) => {
    console.log(report);
    selectedReport.value = report;
    isModalOpen.value = true;
};

</script>