<template>
    <AuthenticatedLayout>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Packing List Details
                </h2>
                <div class="flex items-center space-x-3">
                    <button
                        @click="printPage"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 print:hidden"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    <Link
                        :href="route('supplies.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 print:hidden"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back
                    </Link>
                </div>
            </div>

        <div class="">
            <div class="">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:shadow-none print:rounded-none">
                    <div class="p-8 text-gray-900 print:p-4">
                        <!-- Header Section -->
                        <div class="text-center mb-8 border-b-2 border-gray-200 pb-6 print:mb-4 print:pb-4">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2 print:text-2xl">PACKING LIST</h1>
                            <div class="text-lg text-gray-600 print:text-base">{{ packing_list.packing_list_number }}</div>
                            <div class="text-sm text-gray-500 mt-2 print:text-xs">Generated on {{ formatDate(packing_list.created_at) }}</div>
                        </div>

                        <!-- Packing List Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 print:gap-4 print:mb-4">
                            <!-- Left Column -->
                            <div class="space-y-4 print:space-y-3">
                                <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">Packing List Details</h3>
                                    <div class="space-y-2 print:space-y-1">
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Packing List No:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.packing_list_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Reference No:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.ref_no || 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Packing Date:</span>
                                            <span class="text-gray-900 print:text-sm">{{ formatDate(packing_list.pk_date) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Status:</span>
                                            <span :class="getStatusClasses(packing_list.status)" class="px-2 py-1 text-xs font-semibold rounded-full print:bg-transparent print:border print:border-gray-300">
                                                {{ packing_list.status.toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">Purchase Order Details</h3>
                                    <div class="space-y-2 print:space-y-1">
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">PO Number:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.po_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Original PO:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.original_po_no }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">PO Date:</span>
                                            <span class="text-gray-900 print:text-sm">{{ formatDate(packing_list.purchase_order.po_date) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Total Amount:</span>
                                            <span class="text-gray-900 print:text-sm">${{ formatNumber(packing_list.purchase_order.total_amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4 print:space-y-3">
                                <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">Supplier Information</h3>
                                    <div class="space-y-2 print:space-y-1">
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Supplier Name:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.supplier.name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Contact Person:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.supplier.contact_person }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Email:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.supplier.email }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Phone:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.supplier.phone }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700 print:text-sm">Address:</span>
                                            <span class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.supplier.address }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">Approval Workflow</h3>
                                    <div class="space-y-3 print:space-y-2">
                                        <div v-if="packing_list.confirmed_by" class="flex items-center space-x-3">
                                            <div class="w-3 h-3 bg-green-500 rounded-full print:bg-transparent print:border print:border-gray-300"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 print:text-xs">Confirmed by {{ packing_list.confirmed_by.name }}</div>
                                                <div class="text-xs text-gray-500 print:text-xs">{{ formatDateTime(packing_list.confirmed_at) }}</div>
                                            </div>
                                        </div>
                                        <div v-if="packing_list.reviewed_by" class="flex items-center space-x-3">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full print:bg-transparent print:border print:border-gray-300"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 print:text-xs">Reviewed by {{ packing_list.reviewed_by.name }}</div>
                                                <div class="text-xs text-gray-500 print:text-xs">{{ formatDateTime(packing_list.reviewed_at) }}</div>
                                            </div>
                                        </div>
                                        <div v-if="packing_list.approved_by" class="flex items-center space-x-3">
                                            <div class="w-3 h-3 bg-green-500 rounded-full print:bg-transparent print:border print:border-gray-300"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 print:text-xs">Approved by {{ packing_list.approved_by.name }}</div>
                                                <div class="text-xs text-gray-500 print:text-xs">{{ formatDateTime(packing_list.approved_at) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 print:gap-4 print:mb-4">
                            <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2 print:text-base">Total Items</h4>
                                <p class="text-2xl font-bold text-gray-700 print:text-xl">{{ packing_list.items.length }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2 print:text-base">Total Quantity</h4>
                                <p class="text-2xl font-bold text-gray-700 print:text-xl">{{ formatNumber(getTotalQuantity()) }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2 print:text-base">Total Value</h4>
                                <p class="text-2xl font-bold text-gray-700 print:text-xl">${{ formatNumber(getTotalValue()) }}</p>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-8 print:mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4 print:text-lg print:mb-2">Packed Items</h3>
                            <div class="overflow-x-auto print:overflow-visible">
                                <table class="min-w-full bg-white border border-gray-300 print:text-xs">
                                    <thead class="bg-gray-100 print:bg-transparent print:border-b print:border-gray-300">
                                        <tr>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">#</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Product Name</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Category</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">UOM</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Barcode</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Batch Number</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Location</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Quantity</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Unit Cost</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Total Cost</th>
                                            <th class="px-4 py-3 border border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider print:px-2 print:py-2">Expiry Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <tr v-for="(item, index) in packing_list.items" :key="item.id" class="hover:bg-gray-50 print:hover:bg-transparent">
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ index + 1 }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ item.product.name }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ item.product.category?.name || 'N/A' }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ item.uom }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ item.barcode }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ item.batch_number }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ item.location }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ formatNumber(item.quantity) }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">${{ formatNumber(item.unit_cost) }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 font-medium print:px-2 print:py-2 print:text-xs">${{ formatNumber(item.total_cost) }}</td>
                                            <td class="px-4 py-3 border border-gray-300 text-sm text-gray-900 print:px-2 print:py-2 print:text-xs">{{ formatDate(item.expire_date) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div v-if="packing_list.purchase_order.notes" class="mb-8 print:mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">Notes</h3>
                            <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:border print:border-gray-300">
                                <p class="text-gray-900 print:text-sm">{{ packing_list.purchase_order.notes }}</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center text-sm text-gray-500 border-t border-gray-200 pt-6 print:pt-4 print:text-xs">
                            <p>This document was generated on {{ formatDateTime(new Date()) }}</p>
                            <p class="mt-1">Packing List ID: {{ packing_list.id }}</p>
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
import moment from 'moment';

const props = defineProps({
    packing_list: {
        type: Object,
        required: true
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return moment(dateString).format('DD/MM/YYYY');
};

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    return moment(dateString).format('DD/MM/YYYY HH:mm');
};

const formatNumber = (number) => {
    return Number(number || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getStatusClasses = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        reviewed: 'bg-indigo-100 text-indigo-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getTotalQuantity = () => {
    return props.packing_list.items.reduce((total, item) => total + Number(item.quantity || 0), 0);
};

const getTotalValue = () => {
    return props.packing_list.items.reduce((total, item) => total + Number(item.total_cost || 0), 0);
};

const printPage = () => {
    // Get the main content div
    const contentDiv = document.querySelector('.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg');
    
    if (contentDiv) {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        
        const printStyles = `
            @page { size: landscape; margin: 1.5cm; }
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; font-size: 12px; }
            .print-hidden { display: none !important; }
            .print-bg-transparent { background: transparent !important; }
            .print-border { border: 1px solid #000 !important; }
            .print-border-gray-300 { border-color: #d1d5db !important; }
            .print-text-xs { font-size: 0.75rem !important; }
            .print-text-base { font-size: 1rem !important; }
            .print-text-lg { font-size: 1.125rem !important; }
            .print-text-xl { font-size: 1.25rem !important; }
            .print-text-2xl { font-size: 1.5rem !important; }
            .print-p-4 { padding: 1rem !important; }
            .print-mb-4 { margin-bottom: 1rem !important; }
            .print-gap-4 { gap: 1rem !important; }
            .print-px-2 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
            .print-py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
            .print-shadow-none { box-shadow: none !important; }
            .print-rounded-none { border-radius: 0 !important; }
            .print-overflow-visible { overflow: visible !important; }
        `;
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Packing List - ${props.packing_list.packing_list_number}</title>
                <style>${printStyles}</style>
            </head>
            <body>
                ${contentDiv.outerHTML}
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
        
        // Wait for content to load then print
        printWindow.onload = function() {
            printWindow.print();
            printWindow.close();
        };
    } else {
        // Fallback to regular print if content not found
        window.print();
    }
};
</script>

<style scoped>
@media print {
    @page {
        size: landscape;
        margin: 1.5cm;
    }
    
    /* Hide navigation elements when printing */
    :deep(nav),
    :deep(.sidebar),
    :deep(.navbar),
    :deep([data-sidebar]),
    :deep(.bg-gray-900),
    :deep(.bg-white.border-b),
    :deep(.flex.items-center.justify-between.border-b),
    :deep(.min-h-screen.bg-gray-100) {
        display: none !important;
    }
    
    /* Ensure content takes full width when printing */
    :deep(.max-w-7xl) {
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    :deep(.py-12) {
        padding: 0 !important;
    }
    
    :deep(.sm\\:px-6) {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    :deep(.lg\\:px-8) {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
}
</style>