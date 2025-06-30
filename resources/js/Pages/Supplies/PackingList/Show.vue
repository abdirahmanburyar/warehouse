<template>
    <AuthenticatedLayout :title="props.packingList.packing_list_number" description="Packing List Details" img="/assets/images/orders.png">
        <Head>
            <title>{{ props.packingList.packing_list_number }}</title>
        </Head>
        
        <div class="flex justify-end space-x-4 mb-4 px-4 no-print">
            <Link
                :href="route('supplies.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
                <ArrowLeftIcon class="-ml-1 mr-2 h-5 w-5 text-gray-500" />
                Back to List
            </Link>
            <button
                @click="handlePrint"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            >
                <PrinterIcon class="-ml-1 mr-2 h-5 w-5" />
                Print
            </button>
        </div>

        <div id="printable-content" class="bg-white mb-[100px]">
            <div class="p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-sm font-bold text-[#4472C4] tracking-wide">
                        PACKING LIST
                    </h1>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-3 gap-8 mb-8">
                    <!-- Basic Info -->
                    <div class="border rounded">
                        <div class="bg-[#4472C4] text-white px-4 py-2 text-xs font-bold">
                            Packing List Information
                        </div>
                        <div class="p-4 font-medium space-y-1">
                            <p class="font-medium text-xs">
                                Packing List No: {{ props.packingList.packing_list_number }}
                            </p>
                            <p class="text-xs">
                                Reference No: {{ props.packingList.ref_no }}
                            </p>
                            <p class="text-xs">
                                Date: {{ formatDate(props.packingList.pk_date) }}
                            </p>
                            <p class="text-xs">
                                Status: <span :class="[getStatusClass(), 'status-badge']">{{ props.packingList.status }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Purchase Order Info -->
                    <div class="border rounded">
                        <div class="bg-[#4472C4] text-white px-4 py-2 text-xs font-bold">
                            Purchase Order Information
                        </div>
                        <div class="p-4 font-medium space-y-1">
                            <p class="text-xs">
                                PO Number: {{ props.packingList.purchase_order.po_number }}
                            </p>
                            <p class="text-xs">
                                Original PO No: {{ props.packingList.purchase_order.original_po_no }}
                            </p>
                            <p class="text-xs">
                                PO Date: {{ formatDate(props.packingList.purchase_order.po_date) }}
                            </p>
                            <p class="text-xs">
                                Supplier: {{ props.packingList.purchase_order.supplier.name }}
                            </p>
                            <p class="text-xs">
                                Contact Person: {{ props.packingList.purchase_order.supplier.contact_person }}
                            </p>
                            <p class="text-xs">
                                Phone: {{ props.packingList.purchase_order.supplier.phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Approval Info -->
                    <div class="border rounded">
                        <div class="bg-[#4472C4] text-white px-4 py-2 text-xs font-bold">
                            Approval Information
                        </div>
                        <div class="p-4 font-medium space-y-1">
                            <template v-if="props.packingList.confirmed_by">
                                <p class="text-xs">
                                    Confirmed By: {{ props.packingList.confirmed_by.name }}
                                </p>
                                <p class="text-xs">
                                    Confirmed At: {{ formatDate(props.packingList.confirmed_at) }}
                                </p>
                            </template>
                            <template v-if="props.packingList.reviewed_by">
                                <p class="text-xs">
                                    Reviewed By: {{ props.packingList.reviewed_by.name }}
                                </p>
                                <p class="text-xs">
                                    Reviewed At: {{ formatDate(props.packingList.reviewed_at) }}
                                </p>
                            </template>
                            <template v-if="props.packingList.approved_by">
                                <p class="text-xs">
                                    Approved By: {{ props.packingList.approved_by.name }}
                                </p>
                                <p class="text-xs">
                                    Approved At: {{ formatDate(props.packingList.approved_at) }}
                                </p>
                            </template>
                            <p v-if="!props.packingList.confirmed_by && !props.packingList.reviewed_by && !props.packingList.approved_by" class="text-xs text-gray-500 italic">
                                No approval information available
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="border rounded mb-8">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-left w-[35%]">
                                    Item
                                </th>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-center w-20">
                                    Category
                                </th>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-center w-20">
                                    Dosage Form
                                </th>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-center w-16">
                                    UOM
                                </th>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-center w-24">
                                    Barcode
                                </th>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-center w-24">
                                    Batch No.
                                </th>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-center w-20">
                                    Quantity
                                </th>
                                <th class="bg-[#4472C4] text-white border-r font-bold text-right w-24">
                                    Unit Cost
                                </th>
                                <th class="bg-[#4472C4] text-white font-bold text-right w-24">
                                    Total Cost
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in props.packingList.items" :key="item.id" class="border-b">
                                <td class="p-2 border-r">{{ item.product.name }}</td>
                                <td class="p-2 border-r text-center">{{ item.product?.category?.name }}</td>
                                <td class="p-2 border-r text-center">{{ item.product?.dosage?.name }}</td>
                                <td class="p-2 border-r text-center">{{ item.uom }}</td>
                                <td class="p-2 border-r text-center">{{ item.barcode }}</td>
                                <td class="p-2 border-r text-center">{{ item.batch_number }}</td>
                                <td class="p-2 border-r text-center">{{ item.quantity }}</td>
                                <td class="p-2 border-r text-right">${{ formatNumber(item.unit_cost) }}</td>
                                <td class="p-2 text-right">${{ formatNumber(item.total_cost) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t">
                                <td colspan="8" class="p-2 text-right font-bold">Total Cost:</td>
                                <td class="p-2 text-right font-bold">
                                    ${{ formatNumber(calculateTotalCost()) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-lg shadow p-6 mb-6 timeline">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Packing List Timeline</h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">

                            <!-- Confirmed -->
                            <li v-if="props.packingList.confirmed_by">
                                <div class="relative pb-8">
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <CheckCircleIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Confirmed by <span class="font-medium text-gray-900">{{ props.packingList.confirmed_by.name }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.packingList.confirmed_at">{{ formatDate(props.packingList.confirmed_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Reviewed -->
                            <li v-if="props.packingList.reviewed_by">
                                <div class="relative pb-8">
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                                <EyeIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Reviewed by <span class="font-medium text-gray-900">{{ props.packingList.reviewed_by.name }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.packingList.reviewed_at">{{ formatDate(props.packingList.reviewed_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Approved -->
                            <li v-if="props.packingList.approved_by">
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <CheckIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Approved by <span class="font-medium text-gray-900">{{ props.packingList.approved_by.name }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.packingList.approved_at">{{ formatDate(props.packingList.approved_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents section (not included in PDF) -->
        <div class="bg-white rounded-lg shadow p-6 mb-[100px]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Attached Documents</h3>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ props.packingList.documents.length }} document(s)</span>
                    <button
                        @click="$refs.fileInput.click()"
                        :disabled="isUploading"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                        <DocumentPlusIcon v-if="!isUploading" class="h-5 w-5 mr-2" />
                        <SpinnerIcon v-else class="animate-spin h-5 w-5 mr-2" />
                        {{ isUploading ? 'Uploading...' : 'Upload Document' }}
                    </button>
                    <input
                        type="file"
                        ref="fileInput"
                        @change="handleFileUpload"
                        accept="application/pdf"
                        class="hidden"
                    />
                </div>
            </div>
            <div v-if="props.packingList.documents.length" class="space-y-3">
                <div v-for="doc in props.packingList.documents" :key="doc.id"
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
            <div v-else class="text-sm text-gray-500 text-center py-8">
                <DocumentIcon class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                <p>No documents attached</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import moment from 'moment'
import { 
    ArrowLeftIcon, 
    PrinterIcon,
    DocumentIcon,
    DocumentPlusIcon,
    CheckCircleIcon,
    EyeIcon,
    CheckIcon
} from '@heroicons/vue/24/outline'
import SpinnerIcon from '@/Components/SpinnerIcon.vue'
import { useToast } from 'vue-toastification';
import axios from 'axios';

const props = defineProps({
    packingList: {
        type: Object,
        required: true
    },
    can: {
        type: Object,
        default: () => ({})
    }
})

const toast = useToast()
const printContent = ref(null)
const fileInput = ref(null)
const isUploading = ref(false)

const canUpload = computed(() => props.can.upload_documents)

const handlePrint = () => {
    const printContent = document.getElementById('printable-content');
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = printContent.innerHTML;
    window.print();
    document.body.innerHTML = originalContents;
    
    // Reinitialize Vue app after restoring content
    window.location.reload();
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return moment(date).format('DD/MM/YYYY HH:mm');
}

const formatNumber = (number) => {
    return Number(number).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}

const calculateTotalCost = () => {
    return props.packingList.items.reduce((total, item) => total + Number(item.total_cost), 0);
};

const getStatusClass = () => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        reviewed: 'bg-indigo-100 text-indigo-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    }
    return `${classes[props.packingList.status] || ''} px-2 py-1 rounded-full text-xs font-medium`
}

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

    try {
        const response = await axios.post(
            `/supplies/packing-list/${props.packingList.id}/upload-document`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        )

        if (response.status === 200) {
            // Get the uploaded document details from response
            const uploadedDoc = response.data.document
            
            // Add the new document to the list
            props.packingList.documents.push({
                id: uploadedDoc.id,
                file_name: uploadedDoc.file_name,
                file_path: uploadedDoc.file_path,
                created_at: uploadedDoc.created_at,
                uploader: {
                    name: uploadedDoc.uploader.name
                }
            })

            toast.success('Document uploaded successfully')
            fileInput.value.value = '' // Clear the file input
        }
    } catch (error) {
        toast.error(error.response?.data || 'Failed to upload document')
    } finally {
        isUploading.value = false
    }
}
</script>

<style>
.status-badge {
    @apply px-2 py-1 text-xs font-medium rounded-full;
}

@media print {
    @page {
        size: landscape;
        margin: 0.5cm;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
    
    .no-print, 
    .timeline,
    button,
    a {
        display: none !important;
    }
    
    #printable-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    table {
        page-break-inside: avoid;
        width: 100% !important;
    }
    
    * {
        print-color-adjust: exact !important;
        -webkit-print-color-adjust: exact !important;
    }
}
</style>