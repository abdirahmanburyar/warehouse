<template>
    <AuthenticatedLayout title="Physical Count Report" description="Inventory Verification Tool" img="/assets/images/report.png">
       <div class="mb-16">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Physical Count Report</h1>
                        <p class="text-sm text-gray-600">{{ monthYearFormatted }}</p>
                    </div>
                </div>
                
                <div v-if="$page.props.auth.can.report_physical_count_generate" class="flex items-center space-x-3">
                    <Link 
                        :href="route('reports.physicalCountShow')"
                        class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        v-if="$page.props.auth.can.report_physical_count_view"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Reports
                    </Link>
                    <button 
                        type="button" 
                        class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm"
                        @click="handleButtonClick"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Generate Report
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Report Information Card -->
        <div v-if="hasAdjustment" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-lg">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-6a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Physical Count Report</h3>
                        <p class="text-sm text-gray-600">
                            Created on {{ formatDate(props.physicalCountReport.adjustment_date) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Status Card -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Status</p>
                                <div class="mt-1">
                                    <span :class="getItemStatusClass(props.physicalCountReport.status)">
                                        {{ props.physicalCountReport.status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Total Items Card -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Items</p>
                                <p class="text-xl font-semibold text-blue-900 mt-1">
                                    {{ props.physicalCountReport.items ? props.physicalCountReport.items.length : 0 }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>

                    <!-- Adjustment ID Card -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Adjustment ID</p>
                                <p class="text-lg font-semibold text-purple-900 mt-1">
                                    #{{ props.physicalCountReport.id }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                        </div>
                    </div>

                    <!-- Date Card -->
                    <div class="bg-amber-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Report Date</p>
                                <p class="text-sm font-semibold text-amber-900 mt-1">
                                    {{ formatDate(props.physicalCountReport.adjustment_date) }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Review and Approval Information -->
                <div v-if="props.physicalCountReport.reviewed_by || props.physicalCountReport.approved_by" class="mt-6 pt-6 border-t border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Reviewed By -->
                        <div v-if="props.physicalCountReport.reviewed_by" class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-900">Reviewed By</p>
                                <p class="text-sm text-blue-700">
                                    {{ props.physicalCountReport.reviewer ? props.physicalCountReport.reviewer.name : 'N/A' }}
                                    <span v-if="props.physicalCountReport.reviewed_at" class="text-xs text-blue-600 ml-1">
                                        • {{ formatDate(props.physicalCountReport.reviewed_at) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Approved By -->
                        <div v-if="props.physicalCountReport.approved_by" class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-green-900">Approved By</p>
                                <p class="text-sm text-green-700">
                                    {{ props.physicalCountReport.approver ? props.physicalCountReport.approver.name : 'N/A' }}
                                    <span v-if="props.physicalCountReport.approved_at" class="text-xs text-green-600 ml-1">
                                        • {{ formatDate(props.physicalCountReport.approved_at) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="hasAdjustment && props.physicalCountReport.items && props.physicalCountReport.items.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-8 h-8 bg-indigo-100 rounded-lg">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Inventory Items</h3>
                            <p class="text-sm text-gray-600">{{ filteredItems.length }} items found</p>
                        </div>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            v-model="search" 
                            placeholder="Search items..." 
                            class="block w-64 pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-sm placeholder-gray-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System Qty</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Physical Count</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Product Info -->
                            <td class="px-4 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">{{ item.product?.name?.charAt(0) || 'N' }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ item.product?.name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ item.product?.productID || 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Product Details -->
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center text-sm">
                                        <span class="text-gray-500 w-16">UOM:</span>
                                        <span class="text-gray-900">{{ item.uom || 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <span class="text-gray-500 w-16">Batch:</span>
                                        <span class="text-gray-900">{{ item.batch_number || 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <span class="text-gray-500 w-16">Expiry:</span>
                                        <span class="text-gray-900">{{ formatDate(item.expiry_date) }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Location -->
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-gray-900">{{ item.warehouse?.name || 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ item.location?.location || 'N/A' }}</div>
                                </div>
                            </td>

                            <!-- System Quantity -->
                            <td class="px-4 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ item.quantity }}</div>
                            </td>

                            <!-- Physical Count Input -->
                            <td class="px-4 py-4">
                                <input 
                                    type="number" 
                                    v-model="item.physical_count" 
                                    class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm text-center bg-gray-50 focus:bg-white transition-colors duration-200" 
                                    min="0"
                                    @input="item.difference = calculateDifference(item)"
                                />
                            </td>

                            <!-- Difference -->
                            <td class="px-4 py-4">
                                <span :class="getDifferenceClass(item)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ calculateDifference(item) }}
                                </span>
                            </td>

                            <!-- Remarks -->
                            <td class="px-4 py-4">
                                <input 
                                    type="text" 
                                    v-model="item.remarks" 
                                    class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-gray-50 focus:bg-white transition-colors duration-200" 
                                    placeholder="Add remarks"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                <button 
                    v-if="props.physicalCountReport.status === 'pending'" 
                    :disabled="isSubmitting" 
                    @click="submitPhysicalCount" 
                    type="button" 
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
                >
                    <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ isSubmitting ? "Submitting..." : "Submit Report" }}
                </button>

                <button 
                    v-else-if="props.physicalCountReport.status === 'submitted' && $page.props.auth.can.report_physical_count_review" 
                    type="button" 
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                    @click="reviewPhysicalCount"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Review Report
                </button>

                <div v-else-if="props.physicalCountReport.status == 'reviewed' && $page.props.auth.can.report_physical_count_approve" class="flex space-x-3">
                    <button 
                        type="button" 
                        class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-500 border border-transparent rounded-lg hover:from-green-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
                        @click="approvePhysicalCount"
                        :disabled="isApproving"
                    >
                        <svg v-if="isApproving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ isApproving ? "Approving..." : "Approve" }}
                    </button>
                    <button v-if="$page.props.auth.can.report_physical_count_approve" type="button" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-500 border border-transparent rounded-lg hover:from-red-700 hover:to-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
                    @click="rejectPhysicalCount"
                    :disabled="isRejecting"
                    >
                        <svg v-if="isRejecting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ isRejecting ? "Rejecting..." : "Reject" }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="!hasAdjustment" class="bg-white shadow overflow-hidden sm:rounded-lg p-6 text-center mb-[100px]">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-black">No physical count adjustment</h3>
            <p class="mt-1 text-sm text-black">
                Click the Generate button to prepare inventory for physical count adjustment.
            </p>
        </div>
       </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import moment from 'moment';
import Swal from "sweetalert2";
import axios from "axios";

const props = defineProps({
    physicalCountReport: Object,
    currentMonthYear: String
});

const isSubmitting = ref(false);
const search = ref('');

// Computed properties
const hasAdjustment = computed(() => {
    return props.physicalCountReport && Object.keys(props.physicalCountReport).length > 0;
});

const monthYearFormatted = computed(() => {
    return moment(props.currentMonthYear).format('MMMM YYYY');
});

function submitPhysicalCount(){
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to submit the physical count report',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, submit!'
    }).then(async (result) => {
        if (result.isConfirmed) {
             // Show loading alert with counter
             let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Submitting Physical Count Data',
                html: 'Submitting physical count Data... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            await axios.post(route('reports.physical-count.update'), {
                id: props.physicalCountReport.id,
                items: props.physicalCountReport.items
            })
            .then(response => {
                console.log(response);
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
                Swal.fire({
                    title: 'Success!',
                    text: response.data,
                    icon: 'success'
                })
                .then(() => {
                    router.get(route('reports.physicalCount'));
                })
            })
            .catch(error => {
                console.log(error);

                Swal.fire({
                    title: 'Error!',
                    text: error.response.data,
                    icon: 'error'
                });
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
            });
        }
    });
}

function reviewPhysicalCount(){
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to review the physical count report',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, reviewed!'
    }).then(async (result) => {
        if (result.isConfirmed) {
             // Show loading alert with counter
             let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Marking Physical Count Data as Reviewed',
                html: 'Marking physical count data as reviewed... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            await axios.post(route('reports.physical-count.status'), {
                id: props.physicalCountReport.id,
                status: 'reviewed'
            })
            .then(response => {
                console.log(response);
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
                Swal.fire({
                    title: 'Success!',
                    text: response.data,
                    icon: 'success'
                })
                .then(() => {
                    router.get(route('reports.physicalCount'));
                })
            })
            .catch(error => {
                console.log(error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response.data.message,
                    icon: 'error'
                });
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
            });
        }
    });
}

const isApproving = ref(false);

function approvePhysicalCount(){
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to approve the physical count report',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approved!'
    }).then(async (result) => {
        if (result.isConfirmed) {
             // Show loading alert with counter
             isApproving.value = true;
             let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Marking Physical Count Data as Approved',
                html: 'Marking physical count data as approved... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            await axios.post(route('reports.physical-count.approve'), {
                id: props.physicalCountReport.id,
                status: 'approved'
            })
            .then(response => {
                console.log(response);
                isApproving.value = false;
                clearInterval(timerInterval);
                Swal.close();
                Swal.fire({
                    title: 'Success!',
                    text: response.data,
                    icon: 'success'
                })
                .then(() => {
                    router.get(route('reports.physicalCount'));
                })
            })
            .catch(error => {
                console.log(error);

                Swal.fire({
                    title: 'Error!',
                    text: error.response.data,
                    icon: 'error'
                });
                isApproving.value = false;
                clearInterval(timerInterval);
                Swal.close();
            });
        }
    });
}

const isRejecting = ref(false);

function rejectPhysicalCount(){
    Swal.fire({
        title: 'Reject Physical Count Report',
        text: 'Please provide a reason for rejection',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter your reason for rejection...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
            'maxlength': '500',
            'rows': '4'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Reject Report',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a rejection reason!'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            isRejecting.value = true;
            let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Marking Physical Count Data as Rejected',
                html: 'Marking physical count data as rejected... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });

            try {
                await axios.post(route('reports.physical-count.reject'), {
                    id: props.physicalCountReport.id,
                    status: 'rejected',
                    rejection_reason: result.value
                });
                
                clearInterval(timerInterval);
                Swal.close();
                
                await Swal.fire({
                    title: 'Rejected!',
                    text: 'Physical count report has been rejected successfully.',
                    icon: 'success'
                });
                
                router.get(route('reports.physicalCount'));
            } catch (error) {
                console.error(error);
                clearInterval(timerInterval);
                Swal.close();
                
                await Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'An error occurred while rejecting the report.',
                    icon: 'error'
                });
            } finally {
                isRejecting.value = false;
            }
        }
    });
}


const filteredItems = computed(() => {
    if (!hasAdjustment.value || !props.physicalCountReport.items) return [];
    
    if (!search.value) return props.physicalCountReport.items;
    
    const searchTerm = search.value.toLowerCase();
    return props.physicalCountReport.items.filter(item => {
        const productName = item.product ? item.product.name.toLowerCase() : '';
        const batchNumber = item.batch_number ? item.batch_number.toLowerCase() : '';
        
        return productName.includes(searchTerm) || batchNumber.includes(searchTerm);
    });
});

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return moment(dateString).format('DD/MM/YYYY');
};

const calculateDifference = (item) => {
    if (item.physical_count === null || item.quantity === null) return 'N/A';
    return item.physical_count - item.quantity;
};

const getDifferenceClass = (item) => {
    const diff = calculateDifference(item);
    if (diff === 'N/A') return 'bg-gray-100 text-gray-700';
    if (diff < 0) return 'bg-red-100 text-red-700';
    if (diff > 0) return 'bg-green-100 text-green-700';
    return 'bg-gray-100 text-gray-700';
};

const getItemStatusClass = (status) => {
    const statusClasses = {
        'pending': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
        'submitted': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
        'reviewed': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800',
        'approved': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
        'rejected': 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'
    };
    
    return statusClasses[status] || 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
};


const handleButtonClick = () => {
    Swal.fire({
        title: 'Prepare Inventory for Adjustments',
        text: 'Are you sure you want to prepare the warehouse inventory for physical count adjustments?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!'
    }).then( async (result) => {
        if (result.isConfirmed) {
            isSubmitting.value = true;
            
            // Show loading alert with counter
            let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Preparing Inventory Data',
                html: 'Processing inventory data... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            
            await axios.post(route('reports.physicalCountReport'))
                .then(response => {
                    isSubmitting.value = false;
                    clearInterval(timerInterval);
                    Swal.close();
                    
                    console.log(response.data);
                    
                    Swal.fire({
                        title: 'Preparation Complete',
                        text: 'Warehouse inventory has been successfully prepared for adjustments.',
                        icon: 'success'
                    }).then(() => {
                        // Reload the page to show the new data
                        window.location.reload();
                    });
                })
                .catch(error => {
                    isSubmitting.value = false;
                    clearInterval(timerInterval);
                    Swal.close();
                    
                    // Get the error message from the response if available
                    const errorMessage = error.response && error.response.data && error.response.data.message
                        ? error.response.data.message
                        : 'There was an error preparing inventory for adjustments: ' + error.message;
                    
                    Swal.fire({
                        title: 'Cannot Proceed',
                        text: errorMessage,
                        icon: 'warning'
                    });
                    console.error(error);
                });
        }
    });
};
</script>
