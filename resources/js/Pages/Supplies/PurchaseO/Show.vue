<template>
    <AuthenticatedLayout :title="props.po.po_number" description="Purchase Order Details"
        img="/assets/images/orders.png">

        <Head>
            <title>{{ props.po.po_number }}</title>
        </Head>
        <div class="flex justify-end space-x-4 mb-4 px-4 print:hidden">
            <Link :href="route('supplies.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <ArrowLeftIcon class="-ml-1 mr-2 h-5 w-5 text-gray-500" />
            Back to List
            </Link>
            <!-- v-if="$page.props.auth.can.supply_download" -->
            <button @click="handlePrint"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <PrinterIcon class="-ml-1 mr-2 h-5 w-5" />
                Print
            </button>
        </div>
        <div id="printable-content" class="bg-white print:m-0 print:p-0">
            <div class=" print:p-4">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-sm font-bold text-[#4472C4] tracking-wide print:text-black">
                        PURCHASE ORDER
                    </h1>
                </div>

                <!-- Vendor and Ship To -->
                <div class="grid grid-cols-3 gap-8 mb-2">
                    <!-- Vendor Info -->
                    <div class="border rounded">
                        <div class="bg-[#4472C4] text-white px-4 py-2 font-bold">
                            Supplier
                        </div>
                        <div class="p-4 font-medium space-y-1">
                            <p class="font-medium">
                                {{ props.po.supplier.name }}
                            </p>
                            <p class="text-sm">
                                {{ props.po.supplier.contact_person }}
                            </p>
                            <p class="text-sm">
                                {{ props.po.supplier.address || "N/A" }}
                            </p>
                            <p class="text-sm">
                                Phone: {{ props.po.supplier.phone }}
                            </p>
                            <p class="text-sm">
                                Email: {{ props.po.supplier.email }}
                            </p>
                        </div>
                    </div>
                    <!-- Purchase Order Info -->
                    <div class="border rounded">
                        <div class="bg-[#4472C4] text-white px-4 py-2 font-bold">
                            Purchase Order
                        </div>
                        <div class="p-4 font-medium space-y-1">
                            <p class="font-medium">
                                P.O. No: {{
                                    props.po.po_number ||
                                    "N/A"
                                }}
                            </p>
                            <p class="font-medium text-sm">
                                P.O. Date: {{
                                    formatDate(props.po.po_date) ||
                                    "N/A"
                                }}
                            </p>
                            <p class="font-medium text-sm">
                                Original P.O. No: {{
                                    props.po.original_po_no ||
                                    "N/A"
                                }}
                            </p>
                            <p class="font-medium text-sm">
                                Status:
                                <span :class="getStatusClass()">{{ props.po.status }}</span>
                            </p>
                        </div>
                    </div>
                    <!-- Approval Info -->
                    <div class="border rounded">
                        <div class="bg-[#4472C4] text-white px-4 py-2 font-bold">
                            Approval Information
                        </div>
                        <div class="p-4 font-medium space-y-1">
                            <template v-if="props.po.reviewed_by">
                                <p class="text-sm">
                                    Reviewed By: {{ props.po.reviewed_by.name }}
                                </p>
                                <p class="text-sm">
                                    Reviewed At: {{ formatDate(props.po.reviewed_at) }}
                                </p>
                            </template>
                            <template v-if="props.po.approved_by">
                                <p class="text-sm">
                                    Approved By: {{ props.po.approved_by.name }}
                                </p>
                                <p class="text-sm">
                                    Approved At: {{ formatDate(props.po.approved_at) }}
                                </p>
                            </template>
                            <template v-if="props.po.rejected_by">
                                <p class="text-sm">
                                    Rejected By: {{ props.po.rejected_by.name }}
                                </p>
                                <p class="text-sm">
                                    Rejected At: {{ formatDate(props.po.rejected_at) }}
                                </p>
                                <p class="text-sm text-red-600">
                                    Reason: {{ props.po.rejection_reason }}
                                </p>
                            </template>
                            <p v-if="!props.po.reviewed_by && !props.po.approved_by && !props.po.rejected_by" class="text-sm text-gray-500 italic">
                                No approval information available
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="border rounded mb-2 print:break-inside-avoid">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="bg-[#4472C4] text-white text-left p-1 text-xs border border-gray-300 font-bold">
                                    ITEM DESCRIPTION
                                </th>
                                <th class="bg-[#4472C4] text-white p-1 text-xs border border-gray-300 font-bold">
                                    UoM
                                </th>
                                <th class="bg-[#4472C4] text-white p-1 text-xs border border-gray-300 font-bold">
                                    QTY
                                </th>
                                <th class="bg-[#4472C4] text-white p-1 text-xs border border-gray-300 font-bold">
                                    UNIT COST
                                </th>
                                <th class="bg-[#4472C4] text-white p-1 text-xs border border-gray-300 font-bold">
                                    TOTAL COST
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in props.po.items" :key="item.id" class="border-b">
                                <td class="p-2 border-r">
                                    {{ item.product.name }}
                                </td>
                                <td class="p-2 border-r text-center">
                                    {{ item.uom }}
                                </td>
                                <td class="p-2 border-r text-center">
                                    {{ item.quantity }}
                                </td>
                                <td class="p-2 border-r text-right">
                                    ${{ formatNumber(item.unit_cost) }}
                                </td>
                                <td class="p-2 text-right">
                                    ${{ formatNumber(item.total_cost) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="p-2 text-right">
                                    Total Cost: ${{ formatNumber(calculateTotal()) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-lg shadow p-6 mb-6 print:hidden">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Purchase Order Timeline</h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <!-- Created -->
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                                        aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                <DocumentIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex-1 flex-cols space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Created by <span
                                                        class="font-medium text-gray-900">{{ props.po.creator?.name ||
                                                        'System' }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.created_at">{{ formatDate(props.po.created_at)
                                                    }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Reviewed -->
                            <li v-if="props.po.reviewed_by">
                                <div class="relative pb-8">
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                                        aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <EyeIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Reviewed by <span
                                                        class="font-medium text-gray-900">{{ props.po.reviewed_by.name
                                                        }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.reviewed_at">{{
                                                    formatDate(props.po.reviewed_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Approved -->
                            <li v-if="props.po.approved_by">
                                <div class="relative pb-8">
                                    <span v-if="props.po.rejected_by"
                                        class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                                        aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <CheckIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Approved by <span
                                                        class="font-medium text-gray-900">{{ props.po.approved_by.name
                                                        }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.approved_at">{{
                                                    formatDate(props.po.approved_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Rejected -->
                            <li v-if="props.po.rejected_by">
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <XMarkIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Rejected by <span
                                                        class="font-medium text-gray-900">{{ props.po.rejected_by.name
                                                        }}</span></p>
                                                <p class="mt-2 text-sm text-red-600">{{ props.po.rejection_reason }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.rejected_at">{{
                                                    formatDate(props.po.rejected_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="props.po.notes" class="bg-white rounded-lg shadow p-6 mb-1 print:break-inside-avoid">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                    <div class="prose prose-sm max-w-none">
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ props.po.notes }}</p>
                    </div>
                </div>

            </div>
        </div>
        <!-- Documents -->
        <div class="bg-white rounded-lg shadow p-8 mb-[80px]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Attached Documents</h3>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ props.po.documents.length }} document(s)</span>
                    <button @click="$refs.fileInput.click()" :disabled="isUploading"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                        <DocumentPlusIcon v-if="!isUploading" class="h-5 w-5 mr-2" />
                        {{ isUploading ? 'Uploading...' : 'Upload Document' }}
                    </button>
                    <input type="file" ref="fileInput" @change="handleFileUpload" accept="application/pdf"
                        class="hidden" />
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
