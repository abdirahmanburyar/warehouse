<template>
    <AuthenticatedLayout>
        <div class="flex justify-end space-x-4 mb-4 px-4">
            <Link :href="route('supplies.index')" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <ArrowLeftIcon class="-ml-1 mr-2 h-5 w-5 text-gray-500" />
                Back to List
            </Link>
            <!-- v-if="$page.props.auth.can.supply_download" -->
            <button @click="downloadPdf" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <DocumentArrowDownIcon class="-ml-1 mr-2 h-5 w-5" />
                Download PDF
            </button>
        </div>
        <div class="max-w-5xl mx-auto bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#4472C4] tracking-wide">PURCHASE ORDER</h1>
            </div>

            <!-- Company and PO Info -->
            <div class="grid grid-cols-2 gap-8 mb-8">
                <!-- Company Info -->
                <div>
                    <h2 class="text-lg font-bold">{{ props.po.supplier.warehouse?.name || 'Warehouse Name' }}</h2>
                    <p class="text-sm text-gray-600">{{ props.po.supplier.warehouse?.address || 'Warehouse Address' }}</p>
                    <p class="text-sm text-gray-600">Phone: {{ props.po.supplier.warehouse?.phone || 'N/A' }}</p>
                </div>
                <!-- PO Info -->
                <div class="text-right">
                    <div class="inline-grid grid-cols-2 gap-x-4 text-sm">
                        <span class="text-right font-medium">DATE:</span>
                        <span class="text-left">{{ formatDate(props.po.po_date) }}</span>
                        <span class="text-right font-medium">PO #:</span>
                        <span class="text-left">{{ props.po.po_number }}</span>
                    </div>
                </div>
            </div>

                        <!-- Vendor and Ship To -->
            <div class="grid grid-cols-2 gap-8 mb-8">
                <!-- Vendor Info -->
                <div class="border rounded">
                    <div class="bg-[#4472C4] text-white px-4 py-2 font-bold">VENDOR</div>
                    <div class="p-4 space-y-1">
                        <p class="font-medium">{{ props.po.supplier.name }}</p>
                        <p class="text-sm">{{ props.po.supplier.contact_person }}</p>
                        <p class="text-sm">{{ props.po.supplier.address || 'N/A' }}</p>
                        <p class="text-sm">Phone: {{ props.po.supplier.phone }}</p>
                        <p class="text-sm">Email: {{ props.po.supplier.email }}</p>
                    </div>
                </div>
                <!-- Ship To Info -->
                <div class="border rounded">
                    <div class="bg-[#4472C4] text-white px-4 py-2 font-bold">SHIP TO</div>
                    <div class="p-4 space-y-1">
                        <p class="font-medium">{{ props.po.supplier.warehouse?.name || 'Warehouse Name' }}</p>
                        <p class="text-sm">{{ props.po.supplier.warehouse?.address || 'Warehouse Address' }}</p>
                        <p class="text-sm">Phone: {{ props.po.supplier.warehouse?.phone || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="border rounded mb-8">
                <div class="grid grid-cols-4 text-sm">
                    <div class="border-r p-2">
                        <div class="bg-[#4472C4] text-white px-2 py-1 font-bold mb-1">REQUISITIONER</div>
                        <p>{{ props.po.created_by?.name || 'N/A' }}</p>
                    </div>
                    <div class="border-r p-2">
                        <div class="bg-[#4472C4] text-white px-2 py-1 font-bold mb-1">SHIP VIA</div>
                        <p>Standard Delivery</p>
                    </div>
                    <div class="border-r p-2">
                        <div class="bg-[#4472C4] text-white px-2 py-1 font-bold mb-1">F.O.B.</div>
                        <p>Destination</p>
                    </div>
                    <div class="p-2">
                        <div class="bg-[#4472C4] text-white px-2 py-1 font-bold mb-1">SHIPPING TERMS</div>
                        <p>N/A</p>
                    </div>
                </div>
            </div>

                    <!-- Items Table -->
                    <div class="border rounded mb-8">
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th class="bg-[#4472C4] text-white p-2 border-r font-bold text-left w-32">ITEM #</th>
                                    <th class="bg-[#4472C4] text-white p-2 border-r font-bold text-left">DESCRIPTION</th>
                                    <th class="bg-[#4472C4] text-white p-2 border-r font-bold text-center w-24">QTY</th>
                                    <th class="bg-[#4472C4] text-white p-2 border-r font-bold text-right w-32">UNIT PRICE</th>
                                    <th class="bg-[#4472C4] text-white p-2 font-bold text-right w-32">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in props.po.items" :key="item.id" class="border-b">
                                    <td class="p-2 border-r">{{ item.product.sku || 'N/A' }}</td>
                                    <td class="p-2 border-r">{{ item.product.name }}</td>
                                    <td class="p-2 border-r text-center">{{ item.quantity }} {{ item.uom }}</td>
                                    <td class="p-2 border-r text-right">${{ formatNumber(item.unit_cost) }}</td>
                                    <td class="p-2 text-right">${{ formatNumber(item.total_cost) }}</td>
                                </tr>
                                <!-- Empty rows to match template -->
                                <tr v-for="n in Math.max(0, 10 - props.po.items.length)" :key="n" class="border-b">
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
                                    <div class="flex justify-between">
                                        <span class="font-medium">SUBTOTAL</span>
                                        <span>${{ formatNumber(calculateTotal()) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">TAX</span>
                                        <span>-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">SHIPPING</span>
                                        <span>-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">OTHER</span>
                                        <span>-</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t font-bold">
                                        <span>TOTAL</span>
                                        <span>${{ formatNumber(calculateTotal()) }}</span>
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
                                <p class="text-sm font-medium text-gray-500">Reviewed By</p>
                                <p class="mt-1 text-sm text-gray-900">{{ props.po.reviewedBy?.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(props.po.reviewed_at) }}</p>
                            </div>
                            <div v-if="props.po.approved_by">
                                <p class="text-sm font-medium text-gray-500">Approved By</p>
                                <p class="mt-1 text-sm text-gray-900">{{ props.po.approvedBy?.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(props.po.approved_at) }}</p>
                            </div>
                            <div v-if="props.po.rejected_by">
                                <p class="text-sm font-medium text-gray-500">Rejected By</p>
                                <p class="mt-1 text-sm text-gray-900">{{ props.po.rejectedBy?.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(props.po.rejected_at) }}</p>
                                <p class="mt-2 text-sm text-red-600">{{ props.po.rejection_reason }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="props.po.notes" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ props.po.notes }}</p>
                    </div>

                    <!-- Documents -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Attached Documents</h3>
                        <div v-if="props.po.documents.length" class="space-y-3">
                            <div v-for="doc in props.po.documents" :key="doc.id" 
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150">
                                <div class="flex items-center space-x-3">
                                    <DocumentIcon class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ doc.file_name }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(doc.uploaded_at) }} · {{ doc.document_type }}</p>
                                    </div>
                                </div>
                                <a :href="doc.file_path" target="_blank" 
                                    class="inline-flex items-center px-3 py-1 text-sm text-blue-600 hover:text-blue-800 border border-blue-200 rounded-md hover:bg-blue-50 transition-colors duration-150">
                                    <EyeIcon class="h-4 w-4 mr-1" />
                                    View
                                </a>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">No documents attached</div>
                    </div>
                <!-- Comments/Notes Section -->
                <div class="grid grid-cols-2 gap-8 mb-8">
                    <div class="border rounded col-span-2">
                        <div class="bg-gray-200 px-4 py-2 font-bold text-gray-700">Comments or Special Instructions</div>
                        <div class="p-4 min-h-[120px] whitespace-pre-wrap text-sm">
                            {{ props.po.notes || 'No special instructions' }}
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-sm text-gray-600 text-center pb-4 border-t pt-4">
                    <p>If you have any questions about this purchase order, please contact:</p>
                    <p class="font-medium">{{ props.po.created_by?.name || 'N/A' }} • {{ props.po.created_by?.email || 'N/A' }}</p>
                </div>

                <!-- Documents Section -->
                <div v-if="props.po.documents?.length" class="border-t pt-4">
                    <h3 class="text-sm font-medium mb-2">Attached Documents</h3>
                    <div class="space-y-2">
                        <div v-for="doc in props.po.documents" :key="doc.id" 
                            class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm">
                            <div class="flex items-center space-x-2">
                                <DocumentIcon class="h-4 w-4 text-gray-400" />
                                <span>{{ doc.file_name }}</span>
                            </div>
                            <a :href="doc.file_path" target="_blank" 
                                class="text-blue-600 hover:text-blue-800">
                                <EyeIcon class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';
import { ArrowLeftIcon, DocumentArrowDownIcon, DocumentIcon, EyeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    po: {
        required: true,
        type: Object
    }
});

const getStatusClass = () => {
    const classes = 'px-3 py-1 rounded-full text-sm font-medium';
    switch (props.po.status) {
        case 'pending':
            return `${classes} bg-yellow-100 text-yellow-800`;
        case 'approved':
            return `${classes} bg-green-100 text-green-800`;
        case 'rejected':
            return `${classes} bg-red-100 text-red-800`;
        default:
            return `${classes} bg-gray-100 text-gray-800`;
    }
};

const calculateTotal = () => {
    return props.po.items.reduce((sum, item) => sum + item.total_cost, 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

const downloadPdf = async () => {
    // Initialize jsPDF
    const doc = new jsPDF('p', 'pt', 'a4');
    const pageWidth = doc.internal.pageSize.width;
    
    // Header
    doc.setFontSize(18);
    doc.text('Purchase Order', pageWidth / 2, 40, { align: 'center' });
    doc.setFontSize(10);
    doc.text(`PO Number: ${props.po.po_number}`, pageWidth / 2, 60, { align: 'center' });
    
    // Two columns: Supplier and Order Info
    const leftCol = 40;
    const rightCol = pageWidth - 250;
    let yPos = 100;
    
    // Supplier Information
    doc.setFontSize(12);
    doc.text('Supplier Information', leftCol, yPos);
    yPos += 25;
    doc.setFontSize(9);
    doc.text([
        `Name: ${props.po.supplier.name}`,
        `Contact: ${props.po.supplier.contact_person}`,
        `Email: ${props.po.supplier.email}`,
        `Phone: ${props.po.supplier.phone}`
    ], leftCol, yPos);
    
    // Order Information (right column)
    doc.setFontSize(12);
    doc.text('Order Information', rightCol, 100);
    doc.setFontSize(9);
    doc.text([
        `Date: ${formatDate(props.po.po_date)}`,
        `Status: ${props.po.status.toUpperCase()}`,
        `Original PO: ${props.po.original_po_no || 'N/A'}`
    ], rightCol, 125);
    
    // Items Table
    await autoTable(doc, {
        startY: 180,
        theme: 'plain',
        styles: {
            fontSize: 9,
            cellPadding: 4,
            lineColor: [211, 211, 211],
            lineWidth: 0.5
        },
        headStyles: {
            fillColor: [68, 114, 196],  // Navy blue color from screenshot
            textColor: 255,
            fontSize: 9,
            fontStyle: 'bold',
            valign: 'middle',
            cellPadding: { top: 4, right: 4, bottom: 4, left: 4 },
            minCellHeight: 30
        },
        head: [
            [
                { content: 'Item Description', styles: { halign: 'left' } },
                { content: 'Quantity', styles: { halign: 'center' } },
                { content: 'Unit Cost', styles: { halign: 'center' } },
                { content: 'Total Cost', styles: { halign: 'right' } }
            ]
        ],
        columns: [
            { dataKey: 'product' },
            { dataKey: 'quantity' },
            { dataKey: 'unitCost' },
            { dataKey: 'total' }
        ],
        columnStyles: {
            product: { cellWidth: 'auto' },
            quantity: { cellWidth: 40, halign: 'center' },
            unitCost: { cellWidth: 60, halign: 'right' },
            total: { cellWidth: 60, halign: 'right' }
        },
        // Generate table data with empty rows
        body: [
            // Real data row
            {
                product: props.po.items[0]?.product.name || '',
                quantity: props.po.items[0]?.quantity || '',
                unitCost: formatNumber(props.po.items[0]?.unit_cost || 0),
                total: formatNumber(props.po.items[0]?.total_cost || 0)
            },
            // Empty rows
            ...[...Array(9)].map(() => ({
                product: '',
                quantity: '',
                unitCost: '',
                total: ''
            }))
        ],
        tableLineWidth: 0.5,
        tableLineColor: [211, 211, 211], // Light gray for grid lines
        styles: {
            lineColor: [211, 211, 211],
            lineWidth: 0.5
        },
        foot: [[
            { content: 'SUBTOTAL', colSpan: 4, styles: { halign: 'right', fontStyle: 'bold' } },
            { content: formatNumber(calculateTotal()), styles: { halign: 'right', fontStyle: 'bold' } }
        ]],
        footStyles: { 
            fontSize: 9,
            cellPadding: 4,
            fontStyle: 'bold'
        }
    });
    
    // Notes section
    const finalY = doc.lastAutoTable.finalY || 180;
    let currentY = finalY + 40;

    if (props.po.notes) {
        doc.setFontSize(12);
        doc.text('Notes', 40, currentY);
        doc.setFontSize(9);
        const splitNotes = doc.splitTextToSize(props.po.notes, pageWidth - 80);
        doc.text(splitNotes, 40, currentY + 20);
        currentY += splitNotes.length * 12 + 40;
    }

    // Approval section
    doc.setFontSize(12);
    doc.text('Approvals', 40, currentY);
    doc.setFontSize(9);
    
    // Draw approval boxes
    const boxWidth = 200;
    const boxHeight = 60;
    const padding = 20;
    
    // Reviewed by
    if (props.po.reviewed_by) {
        currentY += 20;
        doc.rect(40, currentY, boxWidth, boxHeight);
        doc.text('Reviewed by:', 50, currentY + 15);
        doc.text(props.po.reviewed_by.name, 50, currentY + 35);
        doc.text(formatDate(props.po.reviewed_at), 50, currentY + 50);
    }

    // Approved by
    if (props.po.approved_by) {
        const approvalX = props.po.reviewed_by ? 40 + boxWidth + padding : 40;
        const approvalY = props.po.reviewed_by ? currentY : currentY + 20;
        doc.rect(approvalX, approvalY, boxWidth, boxHeight);
        doc.text('Approved by:', approvalX + 10, approvalY + 15);
        doc.text(props.po.approved_by.name, approvalX + 10, approvalY + 35);
        doc.text(formatDate(props.po.approved_at), approvalX + 10, approvalY + 50);
    }
    
    // Save the PDF
    const filename = `purchase_order_${props.po.po_number.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
    doc.save(filename);
};
</script>