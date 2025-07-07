<template>
    <AuthenticatedLayout :title="props.po.po_number" description="Purchase Order Details"
        img="/assets/images/orders.png">

        <Head>
            <title>{{ props.po.po_number }}</title>
        </Head>
        <div class="flex justify-between items-center mb-6 px-4 print:hidden">
            <div class="flex items-center space-x-4">
                <Link :href="route('supplies.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                    <ArrowLeftIcon class="-ml-1 mr-2 h-5 w-5 text-gray-500" />
                    Back to List
                </Link>
                <span class="text-xl font-bold text-indigo-700">PO #{{ props.po.po_number }}</span>
            </div>
            <button @click="handlePrint"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                <PrinterIcon class="-ml-1 mr-2 h-5 w-5" />
                Print
            </button>
        </div>
        <div id="printable-content" class="bg-white print:m-0 print:p-0">
            <div class="p-4">
                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Supplier Card -->
                    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500 uppercase mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Supplier
                        </div>
                        <div class="font-medium space-y-1">
                            <p class="font-semibold text-gray-900">{{ props.po.supplier.name }}</p>
                            <p class="text-sm text-gray-600">{{ props.po.supplier.contact_person }}</p>
                            <p class="text-sm text-gray-600">{{ props.po.supplier.address || "N/A" }}</p>
                            <p class="text-sm text-gray-600">Phone: {{ props.po.supplier.phone }}</p>
                            <p class="text-sm text-gray-600">Email: {{ props.po.supplier.email }}</p>
                        </div>
                    </div>
                    <!-- PO Info Card -->
                    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500 uppercase mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a8 8 0 11-16 0 8 8 0 0116 0z"></path></svg>
                            Purchase Order
                        </div>
                        <div class="font-medium space-y-1">
                            <p class="font-semibold text-gray-900">P.O. No: {{ props.po.po_number || "N/A" }}</p>
                            <p class="text-sm text-gray-600">P.O. Date: {{ formatDate(props.po.po_date) || "N/A" }}</p>
                            <p class="text-sm text-gray-600">Original P.O. No: {{ props.po.original_po_no || "N/A" }}</p>
                            <p class="text-sm text-gray-600">Status: <span :class="getStatusClass()">{{ props.po.status }}</span></p>
                        </div>
                    </div>
                    <!-- Approval Info Card -->
                    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500 uppercase mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Approval Information
                        </div>
                        <div class="font-medium space-y-1">
                            <template v-if="props.po.reviewed_by">
                                <p class="text-sm text-gray-600">Reviewed By: <span class="font-semibold text-gray-900">{{ props.po.reviewed_by.name }}</span></p>
                                <p class="text-xs text-gray-500">Reviewed At: {{ formatDate(props.po.reviewed_at) }}</p>
                            </template>
                            <template v-if="props.po.approved_by">
                                <p class="text-sm text-gray-600">Approved By: <span class="font-semibold text-gray-900">{{ props.po.approved_by.name }}</span></p>
                                <p class="text-xs text-gray-500">Approved At: {{ formatDate(props.po.approved_at) }}</p>
                            </template>
                            <template v-if="props.po.rejected_by">
                                <p class="text-sm text-gray-600">Rejected By: <span class="font-semibold text-gray-900">{{ props.po.rejected_by.name }}</span></p>
                                <p class="text-xs text-gray-500">Rejected At: {{ formatDate(props.po.rejected_at) }}</p>
                                <p class="text-xs text-red-600">Reason: {{ props.po.rejection_reason }}</p>
                            </template>
                            <p v-if="!props.po.reviewed_by && !props.po.approved_by && !props.po.rejected_by" class="text-sm text-gray-400 italic">
                                No approval information available
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
                    <table class="w-full text-sm">
                        <thead class="bg-indigo-50">
                            <tr>
                                <th class="text-left p-2 text-xs font-bold text-gray-700">ITEM DESCRIPTION</th>
                                <th class="p-2 text-xs font-bold text-gray-700">UoM</th>
                                <th class="p-2 text-xs font-bold text-gray-700">QTY</th>
                                <th class="p-2 text-xs font-bold text-gray-700">UNIT COST</th>
                                <th class="p-2 text-xs font-bold text-gray-700">TOTAL COST</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in props.po.items" :key="item.id" class="border-b hover:bg-indigo-50/30">
                                <td class="p-2 border-r">{{ item.product.name }}</td>
                                <td class="p-2 border-r text-center">{{ item.uom }}</td>
                                <td class="p-2 border-r text-center">{{ item.quantity }}</td>
                                <td class="p-2 border-r text-right">${{ formatNumber(item.unit_cost) }}</td>
                                <td class="p-2 text-right">${{ formatNumber(item.total_cost) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="p-2 text-right font-bold text-indigo-700">
                                    ${{ formatNumber(calculateTotal()) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes -->
                <div v-if="props.po.notes" class="bg-white rounded-xl p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Additional Notes
                    </h3>
                    <div class="prose prose-sm max-w-none">
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ props.po.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Documents -->
        <div class="bg-white rounded-xl p-6 mb-[80px]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Attached Documents</h3>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ props.po.documents.length }} document(s)</span>
                    <button @click="$refs.fileInput.click()" :disabled="isUploading"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                        <DocumentPlusIcon v-if="!isUploading" class="h-5 w-5 mr-2" />
                        {{ isUploading ? 'Uploading...' : 'Upload Document' }}
                    </button>
                    <input type="file" ref="fileInput" @change="handleFileUpload" accept="application/pdf" class="hidden" />
                </div>
            </div>
            <div v-if="props.po.documents.length" class="space-y-3">
                <div v-for="doc in props.po.documents" :key="doc.id"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150">
                    <div class="flex items-center space-x-3">
                        <DocumentIcon class="h-5 w-5 text-gray-400" />
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ doc.file_name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(doc.created_at) }}
                                <span class="text-gray-400">·</span>
                                <span class="text-gray-500">Uploaded by {{ doc.uploader?.name }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a :href="doc.file_path" target="_blank"
                            class="inline-flex items-center px-3 py-1 text-sm text-blue-600 hover:text-blue-800 border border-blue-200 rounded-md hover:bg-blue-50 transition-colors duration-150">
                            <EyeIcon class="h-4 w-4 mr-1" />
                            View
                        </a>
                    </div>
                </div>
            </div>
            <div v-else class="text-sm text-gray-500 text-center p-8">
                <DocumentIcon class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                <p>No documents attached</p>
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
    const classes = "px-3 py-1 rounded-full text-sm font-medium";
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

const calculateTotal = () => {
    return props.po.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return moment(date).format("DD/MM/YYYY HH:mm");
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number || 0);
};

const handlePrint = () => {
    const printContent = document.getElementById('printable-content');
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContent.innerHTML;
    window.print();
    document.body.innerHTML = originalContents;

    // Reinitialize Vue app after restoring content
    window.location.reload();
};

const handleFileUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return
    console.log(props.po.id);

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
            console.log(response);
            isUploading.value = false
            toast.success('Document uploaded successfully')
            router.get(route('supplies.po-show', props.po.id), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['po']
            })
            fileInput.value.value = '' // Clear the file input
        })
        .catch(error => {
            console.log(error);
            isUploading.value = false
            toast.error(error.response?.data || 'Failed to upload document')
        });
}

</script>

<style>
@media print {
    @page {
        size: landscape;
        margin: 0.5cm;
    }
}
</style>
