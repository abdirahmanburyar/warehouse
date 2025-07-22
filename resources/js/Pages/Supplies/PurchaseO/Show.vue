<template>
    <AuthenticatedLayout :title="props.po.po_number" description="Purchase Order Details"
        img="/assets/images/orders.png">
        <Head>
            <title>{{ props.po.po_number }}</title>
        </Head>
        
        <!-- Header Controls (Print Hidden) -->
        <div class="flex justify-between items-center mb-8 px-6 print:hidden">
            <div class="flex items-center space-x-4">
                <Link :href="route('supplies.index')"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-all duration-200 hover:shadow-md">
                    <ArrowLeftIcon class="-ml-1 mr-3 h-5 w-5 text-gray-500" />
                    Back to Purchase Orders
                </Link>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                    <span class="text-2xl font-bold text-gray-900">Purchase Order</span>
                    <span class="text-2xl font-bold text-blue-600">#{{ props.po.po_number }}</span>
                </div>
            </div>
            <button @click="handlePrint"
                class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all duration-200 hover:shadow-md">
                <PrinterIcon class="-ml-1 mr-3 h-5 w-5" />
                Print Purchase Order
            </button>
        </div>

        <!-- Printable Content -->
        <div id="printable-content" class="bg-white print:m-0 print:p-0 print:shadow-none">
            <!-- Professional Header -->
            <div class="border-b-4 border-blue-600 bg-gradient-to-r from-blue-50 to-indigo-50 p-8 print:p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-2">PURCHASE ORDER</h1>
                        <div class="flex items-center space-x-4">
                            <span class="text-lg font-semibold text-blue-600">PO #{{ props.po.po_number }}</span>
                            <span :class="getStatusBadgeClass()" class="px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wide">
                                {{ props.po.status }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600 mb-1">Date Issued</div>
                        <div class="text-lg font-semibold text-gray-900">{{ formatDate(props.po.po_date) }}</div>
                        <div v-if="props.po.original_po_no" class="text-sm text-gray-600 mt-2">
                            Original PO: {{ props.po.original_po_no }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 print:p-6">
                <!-- Company & Supplier Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 print:grid-cols-2 print:gap-6">
                    <!-- Supplier Information -->
                    <div class="bg-gray-50 rounded-lg p-6 border-l-4 border-blue-600 print:bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            VENDOR INFORMATION
                        </h3>
                        <div class="space-y-3">
                            <div class="font-bold text-xl text-gray-900">{{ props.po.supplier.name }}</div>
                            <div v-if="props.po.supplier.contact_person" class="text-gray-700">
                                <span class="font-medium">Contact:</span> {{ props.po.supplier.contact_person }}
                            </div>
                            <div v-if="props.po.supplier.address" class="text-gray-700">
                                <span class="font-medium">Address:</span> {{ props.po.supplier.address }}
                            </div>
                            <div class="text-gray-700">
                                <span class="font-medium">Phone:</span> {{ props.po.supplier.phone }}
                            </div>
                            <div class="text-gray-700">
                                <span class="font-medium">Email:</span> {{ props.po.supplier.email }}
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Order Details -->
                    <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-indigo-600 print:bg-blue-50">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a8 8 0 11-16 0 8 8 0 0116 0z"></path>
                            </svg>
                            ORDER DETAILS
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-600">PO Number</div>
                                <div class="font-bold text-gray-900">{{ props.po.po_number }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-600">Order Date</div>
                                <div class="font-bold text-gray-900">{{ formatDate(props.po.po_date) }}</div>
                            </div>
                            <div v-if="props.po.original_po_no">
                                <div class="text-sm font-medium text-gray-600">Original PO</div>
                                <div class="font-bold text-gray-900">{{ props.po.original_po_no }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-600">Status</div>
                                <div :class="getStatusClass()" class="inline-block">{{ props.po.status }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 capitalize tracking-wide">ORDER ITEMS</h3>                    
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-300">
                                <th class="text-left p-4 font-bold uppercase tracking-wider text-sm text-gray-800 border-r border-gray-300">
                                    Item Description
                                </th>
                                <th class="p-4 font-bold uppercase tracking-wider text-sm text-center text-gray-800 border-r border-gray-300">
                                    UoM
                                </th>
                                <th class="p-4 font-bold uppercase tracking-wider text-sm text-center text-gray-800 border-r border-gray-300">
                                    Quantity
                                </th>
                                <th class="p-4 font-bold uppercase tracking-wider text-sm text-right text-gray-800 border-r border-gray-300">
                                    Unit Cost
                                </th>
                                <th class="p-4 font-bold uppercase tracking-wider text-sm text-right text-gray-800">
                                    Total Cost
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(item, index) in props.po.items" :key="item.id" 
                                :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                                class="border-b border-gray-200 hover:bg-blue-50 transition-colors duration-150">
                                <td class="p-4 font-medium text-gray-900 border-r border-gray-200">
                                    {{ item.product.name }}
                                </td>
                                <td class="p-4 text-center text-gray-700 border-r border-gray-200 font-medium">
                                    {{ item.uom }}
                                </td>
                                <td class="p-4 text-center font-bold text-gray-900 border-r border-gray-200">
                                    {{ formatNumber(item.quantity, 0) }}
                                </td>
                                <td class="p-4 text-right font-semibold text-gray-900 border-r border-gray-200">
                                    ${{ formatNumber(item.unit_cost) }}
                                </td>
                                <td class="p-4 text-right font-bold text-blue-600">
                                    ${{ formatNumber(item.total_cost) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="p-5 text-right font-bold text-lg uppercase tracking-wider border-r border-blue-500">
                                    TOTAL AMOUNT:
                                </td>
                                <td class="p-5 text-right font-bold text-2xl">
                                    ${{ formatNumber(calculateTotal()) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Approval Information -->
                <div v-if="hasApprovalInfo()" class="bg-green-50 rounded-lg p-6 mb-8 border-l-4 border-green-600">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        APPROVAL INFORMATION
                    </h3>
                    <div class="flex justify-between items-start">
                        <div v-if="props.po.reviewed_by" class="flex-1">
                            <div class="text-sm font-medium text-gray-600">Reviewed By</div>
                            <div class="font-bold text-gray-900">{{ props.po.reviewed_by.name }}</div>
                            <div class="text-xs text-gray-500">{{ formatDate(props.po.reviewed_at) }}</div>
                        </div>
                        <div v-if="props.po.approved_by" class="flex-1">
                            <div class="text-sm font-medium text-gray-600">Approved By</div>
                            <div class="font-bold text-gray-900">{{ props.po.approved_by.name }}</div>
                            <div class="text-xs text-gray-500">{{ formatDate(props.po.approved_at) }}</div>
                        </div>
                        <div v-if="props.po.rejected_by" class="flex-1">
                            <div class="text-sm font-medium text-gray-600">Rejected By</div>
                            <div class="font-bold text-gray-900">{{ props.po.rejected_by.name }}</div>
                            <div class="text-xs text-gray-500">{{ formatDate(props.po.rejected_at) }}</div>
                            <div class="text-xs text-red-600 mt-1">Reason: {{ props.po.rejection_reason }}</div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div v-if="props.po.notes" class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">ADDITIONAL NOTES</h3>
                    <div class="bg-yellow-50 rounded-lg p-6 border-l-4 border-yellow-400">
                        <p class="text-gray-800 whitespace-pre-wrap leading-relaxed">{{ props.po.notes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Section (Print Hidden) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 print:hidden">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Attached Documents</h3>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                        {{ props.po.documents.length }} document(s)
                    </span>
                    <button @click="$refs.fileInput.click()" :disabled="isUploading"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-all duration-200">
                        <DocumentPlusIcon v-if="!isUploading" class="h-5 w-5 mr-2" />
                        {{ isUploading ? 'Uploading...' : 'Upload Document' }}
                    </button>
                    <input type="file" ref="fileInput" @change="handleFileUpload" accept="application/pdf" class="hidden" />
                </div>
            </div>
            
            <div v-if="props.po.documents.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="doc in props.po.documents" :key="doc.id"
                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <DocumentIcon class="h-8 w-8 text-red-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ doc.file_name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(doc.created_at) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                by {{ doc.uploader?.name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a :href="doc.file_path" target="_blank"
                            class="inline-flex items-center px-3 py-1 text-sm text-blue-600 hover:text-blue-800 border border-blue-200 rounded-md hover:bg-blue-50 transition-colors duration-150">
                            <EyeIcon class="h-4 w-4 mr-1" />
                            View
                        </a>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12">
                <DocumentIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                <p class="text-lg font-medium text-gray-900 mb-2">No documents attached</p>
                <p class="text-sm text-gray-500">Upload PDF documents related to this purchase order</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import moment from "moment";
import { ref } from "vue";
import { useToast } from "vue-toastification";
import axios from "axios";

const toast = useToast();
const isUploading = ref(false);
const fileInput = ref(null);

import {
    ArrowLeftIcon,
    PrinterIcon,
    DocumentIcon,
    EyeIcon,
    CheckIcon,
    XMarkIcon,
    DocumentPlusIcon
} from "@heroicons/vue/24/outline";

const props = defineProps({
    po: {
        type: Object,
        required: true
    },
    can: {
        type: Object,
        default: () => ({})
    }
});

const getStatusClass = () => {
    const classes = "px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide";
    switch (props.po.status) {
        case "pending":
            return `${classes} bg-yellow-100 text-yellow-800`;
        case "reviewed":
            return `${classes} bg-blue-100 text-blue-800`;
        case "approved":
            return `${classes} bg-green-100 text-green-800`;
        case "rejected":
            return `${classes} bg-red-100 text-red-800`;
        case "completed":
            return `${classes} bg-gray-100 text-gray-800`;
        default:
            return `${classes} bg-gray-100 text-gray-800`;
    }
};

const getStatusBadgeClass = () => {
    const baseClasses = "px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wide";
    switch (props.po.status) {
        case "pending":
            return `${baseClasses} bg-yellow-500 text-white`;
        case "reviewed":
            return `${baseClasses} bg-blue-500 text-white`;
        case "approved":
            return `${baseClasses} bg-green-500 text-white`;
        case "rejected":
            return `${baseClasses} bg-red-500 text-white`;
        case "completed":
            return `${baseClasses} bg-gray-500 text-white`;
        default:
            return `${baseClasses} bg-gray-500 text-white`;
    }
};

const hasApprovalInfo = () => {
    return props.po.reviewed_by || props.po.approved_by || props.po.rejected_by;
};

const calculateTotal = () => {
    return props.po.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return moment(date).format("DD/MM/YYYY");
};

const formatNumber = (number, decimals = 2) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(number || 0);
};

const handlePrint = () => {
    const printContent = document.getElementById('printable-content');
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContent.innerHTML;
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
};

const handleFileUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    if (file.type !== 'application/pdf') {
        toast.error('Please select a PDF file')
        return
    }

    const formData = new FormData()
    formData.append('document', file)
    isUploading.value = true

    await axios.post(route('supplies.uploadDocument', props.po.id),
        formData,
        {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }
    )
        .then(response => {
            isUploading.value = false
            toast.success('Document uploaded successfully')
            router.get(route('supplies.po-show', props.po.id), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['po']
            })
            fileInput.value.value = ''
        })
        .catch(error => {
            isUploading.value = false
            toast.error(error.response?.data || 'Failed to upload document')
        });
}
</script>

<style>
@media print {
    @page {
        size: A4;
        margin: 0.5in;
    }
    
    body {
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .print\:hidden {
        display: none !important;
    }
    
    .print\:m-0 {
        margin: 0 !important;
    }
    
    .print\:p-0 {
        padding: 0 !important;
    }
    
    .print\:p-6 {
        padding: 1.5rem !important;
    }
    
    .print\:shadow-none {
        box-shadow: none !important;
    }
    
    .print\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
    
    .print\:gap-6 {
        gap: 1.5rem !important;
    }
    
    .print\:bg-gray-50 {
        background-color: #f9fafb !important;
    }
    
    .print\:bg-blue-50 {
        background-color: #eff6ff !important;
    }
    
    /* Ensure table borders print correctly */
    table, th, td {
        border-collapse: collapse !important;
    }
    
    th, td {
        border: 1px solid #d1d5db !important;
    }
    
    thead tr {
        background-color: #f3f4f6 !important;
    }

}
</style>
