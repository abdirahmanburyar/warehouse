<template>
    <AuthenticatedLayout>
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
                    <!-- Ship To Info -->
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
            </div>

            <!-- Right Column: Status & Notes -->
            <div class="space-y-6">
                <!-- Status Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Status Information</h3>
                        <span :class="getStatusClass()">{{ props.po.status }}</span>
                    </div>
                    <div class="space-y-4">
                        <div v-if="props.po.reviewed_by">
                            <p class="text-sm font-medium text-gray-500">
                                Reviewed By
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ props.po.reviewedBy?.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(props.po.reviewed_at) }}
                            </p>
                        </div>
                        <div v-if="props.po.approved_by">
                            <p class="text-sm font-medium text-gray-500">
                                Approved By
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ props.po.approvedBy?.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(props.po.approved_at) }}
                            </p>
                        </div>
                        <div v-if="props.po.rejected_by">
                            <p class="text-sm font-medium text-gray-500">
                                Rejected By
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ props.po.rejectedBy?.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(props.po.rejected_at) }}
                            </p>
                            <p class="mt-2 text-sm text-red-600">
                                {{ props.po.rejection_reason }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div
                    v-if="props.po.notes"
                    class="bg-white rounded-lg shadow p-6"
                >
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Notes
                    </h3>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap" v-html="props.po.notes">
                    </p>
                </div>

                <!-- Documents -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Attached Documents
                    </h3>
                    <div v-if="props.po.documents.length" class="space-y-3">
                        <div
                            v-for="doc in props.po.documents"
                            :key="doc.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150"
                        >
                            <div class="flex items-center space-x-3">
                                <DocumentIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ doc.file_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ formatDate(doc.uploaded_at) }} ·
                                        {{ doc.document_type }}
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="doc.file_path"
                                target="_blank"
                                class="inline-flex items-center px-3 py-1 text-sm text-blue-600 hover:text-blue-800 border border-blue-200 rounded-md hover:bg-blue-50 transition-colors duration-150"
                            >
                                <EyeIcon class="h-4 w-4 mr-1" />
                                View
                            </a>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        No documents attached
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { jsPDF } from "jspdf";
import moment from "moment";
import autoTable from "jspdf-autotable";
import {
    ArrowLeftIcon,
    DocumentArrowDownIcon,
    DocumentIcon,
    EyeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    po: {
        required: true,
        type: Object,
    },
});

const getStatusClass = () => {
    const classes = "px-3 py-1 rounded-full text-sm font-medium";
    switch (props.po.status) {
        case "pending":
            return `${classes} bg-yellow-100 text-yellow-800`;
        case "approved":
            return `${classes} bg-green-100 text-green-800`;
        case "rejected":
            return `${classes} bg-red-100 text-red-800`;
        default:
            return `${classes} bg-gray-100 text-gray-800`;
    }
};

const calculateTotal = () => {
    return props.po.items.reduce((sum, item) => sum + item.total_cost, 0);
};

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY')
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
};

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
        `Original P.O. No: ${props.po.original_po_no || 'N/A'}`
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
            fillColor: [68, 114, 196], // Navy blue color from screenshot
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
        columns: [
            { dataKey: "product" },
            { dataKey: "uom" },
            { dataKey: "quantity" },
            { dataKey: "unitCost" },
            { dataKey: "total" },
        ],
        columnStyles: {
            product: { cellWidth: "auto" },
            uom: { cellWidth: 40, halign: "center" },
            quantity: { cellWidth: 40, halign: "center" },
            unitCost: { cellWidth: 60, halign: "right" },
            total: { cellWidth: 60, halign: "right" },
        },
        // Generate table data with empty rows
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
        tableLineWidth: 0.5,
        tableLineColor: [211, 211, 211], // Light gray for grid lines
        styles: {
            lineColor: [211, 211, 211],
            lineWidth: 0.5,
        },
        foot: [
            [
                {
                    content: "TOTAL",
                    colSpan: 4,
                    styles: { halign: "right", fontStyle: "bold" },
                },
                {
                    content: formatNumber(calculateTotal()),
                    styles: { halign: "right", fontStyle: "bold" },
                },
            ],
        ],
        footStyles: {
            fontSize: 9,
            cellPadding: 4,
        },
    });

    // Notes section
    const finalY = doc.lastAutoTable.finalY || 240;
    let currentY = finalY + 40;

    if (props.po.notes) {
        // Draw notes box
        doc.setDrawColor(68, 114, 196);
        doc.rect(40, currentY, pageWidth - 80, 100);
        doc.setFillColor(68, 114, 196);
        doc.rect(40, currentY, pageWidth - 80, 25, 'F');
        doc.setTextColor(255);
        doc.setFontSize(12);
        doc.text('Notes', 50, currentY + 17);

        // Notes content
        doc.setTextColor(0);
        doc.setFontSize(9);
        const splitNotes = doc.splitTextToSize(props.po.notes, pageWidth - 100);
        doc.text(splitNotes, 50, currentY + 40);
        currentY += 120;
    }

    // Status section
    doc.setFontSize(12);
    currentY += 20;

    // Draw status boxes side by side
    const statusBoxWidth = (pageWidth - 80 - 20) / 2; // 20px gap between boxes
    const statusBoxHeight = 80;

    // Reviewed by box
    if (props.po.reviewed_by) {
        doc.setDrawColor(68, 114, 196);
        doc.rect(40, currentY, statusBoxWidth, statusBoxHeight);
        doc.setFillColor(68, 114, 196);
        doc.rect(40, currentY, statusBoxWidth, 25, 'F');
        doc.setTextColor(255);
        doc.text('Reviewed By', 50, currentY + 17);

        // Reviewer details
        doc.setTextColor(0);
        doc.setFontSize(9);
        doc.text([
            props.po.reviewedBy?.name || 'N/A',
            formatDate(props.po.reviewed_at)
        ], 50, currentY + 45, { lineHeightFactor: 1.5 });
    }

    // Approved by box
    if (props.po.approved_by) {
        const approvalX = pageWidth - 40 - statusBoxWidth;
        doc.setDrawColor(68, 114, 196);
        doc.rect(approvalX, currentY, statusBoxWidth, statusBoxHeight);
        doc.setFillColor(68, 114, 196);
        doc.rect(approvalX, currentY, statusBoxWidth, 25, 'F');
        doc.setTextColor(255);
        doc.text('Approved By', approvalX + 10, currentY + 17);

        // Approver details
        doc.setTextColor(0);
        doc.setFontSize(9);
        doc.text([
            props.po.approvedBy?.name || 'N/A',
            formatDate(props.po.approved_at)
        ], approvalX + 10, currentY + 45, { lineHeightFactor: 1.5 });
    }

    // Save the PDF
    const filename = `purchase_order_${props.po.po_number.replace(
        /[^a-zA-Z0-9]/g,
        "_"
    )}.pdf`;
    doc.save(filename);
};
</script>
