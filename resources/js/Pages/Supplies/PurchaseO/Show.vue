<template>
    <AuthenticatedLayout :title="props.po.po_number" description="Purchase Order Details" img="/assets/images/orders.png">
        <Head>
            <title>{{ props.po.po_number }}</title>
        </Head>
        <div class="flex justify-end space-x-4 mb-4 px-4">
            <Link
                :href="route('supplies.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
                <ArrowLeftIcon class="-ml-1 mr-2 h-5 w-5 text-gray-500" />
                Back to List
            </Link>
            <!-- v-if="$page.props.auth.can.supply_download" -->
            <button
                @click="downloadPdf"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            >
                <DocumentArrowDownIcon class="-ml-1 mr-2 h-5 w-5" />
                Download PDF
            </button>
        </div>
        <div class="bg-white mb-[100px]">
            <div class="p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-[#4472C4] tracking-wide">
                        PURCHASE ORDER
                    </h1>
                </div>

                <!-- Vendor and Ship To -->
                <div class="grid grid-cols-2 gap-8 mb-8">
                    <!-- Vendor Info -->
                    <div class="border rounded">
                        <div
                            class="bg-[#4472C4] text-white px-4 py-2 font-bold"
                        >
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
                        <div
                            class="bg-[#4472C4] text-white px-4 py-2 font-bold"
                        >
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
                </div>

                <!-- Items Table -->
                <div class="border rounded mb-8">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="bg-[#4472C4] text-white p-2 border-r font-bold text-left"
                                >
                                ITEM DESCRIPTION
                                </th>
                                <th
                                    class="bg-[#4472C4] text-white p-2 border-r font-bold text-center w-24"
                                >
                                    UoM
                                </th>
                                <th
                                    class="bg-[#4472C4] text-white p-2 border-r font-bold text-center w-24"
                                >
                                QTY
                                </th>
                                <th
                                    class="bg-[#4472C4] text-white p-2 border-r font-bold text-right w-32"
                                >
                                    UNIT COST
                                </th>
                                <th
                                    class="bg-[#4472C4] text-white p-2 font-bold text-right w-32"
                                >
                                    TOTAL COST
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in props.po.items"
                                :key="item.id"
                                class="border-b"
                            >
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
                            <tr
                                v-for="n in Math.max(
                                    0,
                                    10 - props.po.items.length
                                )"
                                :key="n"
                                class="border-b"
                            >
                                <td class="p-2 border-r">&nbsp;</td>
                                <td class="p-2 border-r">&nbsp;</td>
                                <td class="p-2 border-r">&nbsp;</td>
                                <td class="p-2 border-r">&nbsp;</td>
                                <td class="p-2">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="border-t">
                        <div class="flex justify-end p-4 space-x-8">
                            <div class="w-48 space-y-2">
                                <div
                                    class="flex justify-between pt-2 border-t font-bold"
                                >
                                    <span>TOTAL:</span>
                                    <span
                                        >${{
                                            formatNumber(calculateTotal())
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Purchase Order Timeline</h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <!-- Created -->
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                <DocumentIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex-1 flex-cols space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Created by <span class="font-medium text-gray-900">{{ props.po.creator?.name || 'System' }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.created_at">{{ formatDate(props.po.created_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Reviewed -->
                            <li v-if="props.po.reviewed_by">
                                <div class="relative pb-8">
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <EyeIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Reviewed by <span class="font-medium text-gray-900">{{ props.po.reviewed_by.name }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.reviewed_at">{{ formatDate(props.po.reviewed_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Approved -->
                            <li v-if="props.po.approved_by">
                                <div class="relative pb-8">
                                    <span v-if="props.po.rejected_by" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <CheckIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Approved by <span class="font-medium text-gray-900">{{ props.po.approved_by.name }}</span></p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.approved_at">{{ formatDate(props.po.approved_at) }}</time>
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
                                            <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <XMarkIcon class="h-5 w-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">Rejected by <span class="font-medium text-gray-900">{{ props.po.rejected_by.name }}</span></p>
                                                <p class="mt-2 text-sm text-red-600">{{ props.po.rejection_reason }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time :datetime="props.po.rejected_at">{{ formatDate(props.po.rejected_at) }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="props.po.notes" class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                    <div class="prose prose-sm max-w-none">
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ props.po.notes }}</p>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Attached Documents</h3>
                        <span class="text-sm text-gray-500">{{ props.po.documents.length }} document(s)</span>
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
                    <div v-else class="text-sm text-gray-500 text-center py-8">
                        <DocumentIcon class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                        <p>No documents attached</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { jsPDF } from "jspdf";
import moment from "moment";
import autoTable from "jspdf-autotable";
import { useToast } from "vue-toastification";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import {
    ArrowLeftIcon,
    DocumentArrowDownIcon,
    DocumentIcon,
    EyeIcon,
    CheckIcon,
    XMarkIcon,
    ArrowDownTrayIcon,
    ArrowUpTrayIcon,
} from "@heroicons/vue/24/outline";

const SpinnerIcon = {
    template: `
        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    `
};

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

const toast = useToast();
const showApprovalModal = ref(false);
const showRejectModal = ref(false);
const rejectionReason = ref('');
const document = ref(null);


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

async function approvePurchaseOrder() {
    try {
        await axios.post(route('supplies.approve', props.po.id));
        showApprovalModal.value = false;
        toast.success('Purchase order approved successfully');
        router.reload();
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to approve purchase order');
    }
}

async function rejectPurchaseOrder() {
    if (!rejectionReason.value.trim()) return;

    try {
        await axios.post(route('supplies.reject', props.po.id), {
            reason: rejectionReason.value
        });
        showRejectModal.value = false;
        rejectionReason.value = '';
        toast.success('Purchase order rejected successfully');
        router.reload();
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to reject purchase order');
    }
}

const downloadPdf = async () => {
    // Initialize jsPDF
    const doc = new jsPDF("p", "pt", "a4");
    const pageWidth = doc.internal.pageSize.width;

    // Header
    doc.setFontSize(18);
    doc.text("Purchase Order", pageWidth / 2, 40, { align: "center" });
    doc.setFontSize(10);
    doc.text(`PO Number: ${props.po.po_number}`, pageWidth / 2, 60, {
        align: "center",
    });

    // Two columns: Supplier and PO Info
    const leftCol = 40;
    const rightCol = pageWidth - 250;
    let yPos = 100;

    // Draw boxes for both columns
    const boxWidth = (pageWidth - 80) / 2 - 10;
    const boxHeight = 120;
    
    // Supplier Box
    doc.setDrawColor(68, 114, 196);
    doc.rect(leftCol, yPos, boxWidth, boxHeight);
    doc.setFillColor(68, 114, 196);
    doc.rect(leftCol, yPos, boxWidth, 25, 'F');
    doc.setTextColor(255);
    doc.setFontSize(12);
    doc.text('Supplier', leftCol + 10, yPos + 17);
    
    // Supplier Information
    doc.setTextColor(0);
    doc.setFontSize(9);
    yPos += 35;
    doc.text([
        `${props.po.supplier.name}`,
        `${props.po.supplier.contact_person}`,
        `${props.po.supplier.address || 'N/A'}`,
        `Phone: ${props.po.supplier.phone}`,
        `Email: ${props.po.supplier.email}`
    ], leftCol + 10, yPos, { lineHeightFactor: 1.5 });

    // PO Box
    yPos = 100; // Reset yPos for right column
    doc.setDrawColor(68, 114, 196);
    doc.rect(rightCol, yPos, boxWidth, boxHeight);
    doc.setFillColor(68, 114, 196);
    doc.rect(rightCol, yPos, boxWidth, 25, 'F');
    doc.setTextColor(255);
    doc.setFontSize(12);
    doc.text('Purchase Order', rightCol + 10, yPos + 17);

    // PO Information
    doc.setTextColor(0);
    doc.setFontSize(9);
    yPos += 35;
    doc.text([
        `P.O. No: ${props.po.po_number}`,
        `P.O. Date: ${formatDate(props.po.po_date)}`,
        `Original P.O. No: ${props.po.original_po_no || 'N/A'}`,
        `Status: ${props.po.status.toUpperCase()}`
    ], rightCol + 10, yPos, { lineHeightFactor: 1.5 });

    // Items Table
    await autoTable(doc, {
        startY: 240,
        theme: "plain",
        styles: {
            fontSize: 9,
            cellPadding: 4,
            lineColor: [211, 211, 211],
            lineWidth: 0.5,
        },
        headStyles: {
            fillColor: [68, 114, 196],
            textColor: 255,
            fontSize: 9,
            fontStyle: "bold",
            valign: "middle",
            cellPadding: { top: 4, right: 4, bottom: 4, left: 4 },
            minCellHeight: 30,
        },
        head: [
            [
                { content: "ITEM DESCRIPTION", styles: { halign: "left" } },
                { content: "UoM", styles: { halign: "center" } },
                { content: "QTY", styles: { halign: "center" } },
                { content: "UNIT COST", styles: { halign: "right" } },
                { content: "TOTAL COST", styles: { halign: "right" } },
            ],
        ],
        body: [
            ...props.po.items.map(item => ({
                product: item.product.name,
                uom: item.uom || "N/A",
                quantity: item.quantity,
                unitCost: formatNumber(item.unit_cost),
                total: formatNumber(item.total_cost),
            })),
            // Empty rows to make it at least 10 rows
            ...[...Array(Math.max(0, 10 - props.po.items.length))].map(() => ({
                product: "",
                uom: "",
                quantity: "",
                unitCost: "",
                total: "",
            })),
        ],
        foot: [
            [
                { content: "TOTAL", colSpan: 4, styles: { halign: "right", fontStyle: "bold" } },
                { content: formatNumber(calculateTotal()), styles: { halign: "right", fontStyle: "bold" } },
            ],
        ],
    });

    // Add approval information
    yPos = doc.lastAutoTable.finalY + 30;
    doc.setFontSize(10);
    doc.setFont(undefined, 'bold');
    doc.text("Approval Information", 40, yPos);
    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);
    yPos += 20;

    const approvalInfo = [
        `Created by: ${props.po.creator?.name || 'System'} at ${formatDate(props.po.created_at)}`,
        props.po.reviewed_by ? `Reviewed by: ${props.po.reviewed_by.name} at ${formatDate(props.po.reviewed_at)}` : null,
        props.po.approved_by ? `Approved by: ${props.po.approved_by.name} at ${formatDate(props.po.approved_at)}` : null,
        props.po.rejected_by ? `Rejected by: ${props.po.rejected_by.name} at ${formatDate(props.po.rejected_at)}` : null,
    ].filter(Boolean);

    doc.text(approvalInfo, 40, yPos, { lineHeightFactor: 1.5 });

    // Notes section if exists
    if (props.po.notes) {
        yPos = doc.lastAutoTable.finalY + 100;
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text("Notes", 40, yPos);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(9);
        yPos += 20;
        doc.text(props.po.notes, 40, yPos);
    }

    // Save the PDF
    doc.save(`${props.po.po_number}.pdf`);
};
</script>
