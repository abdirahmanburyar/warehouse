<template>
    <AuthenticatedLayout
        title="Purchase Order Report"
        description="Purchase Order Summary"
        img="/assets/images/report.png"
    >
        <Head title="Purchase Order Report" />

        <!-- Header Section -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6"
        >
            <div class="flex items-center space-x-3 mb-6">
                <div
                    class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg"
                >
                    <Link
                        :href="route('reports.index')"
                        class="text-blue-600 hover:text-blue-800"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                    </Link>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">
                        Purchase Order Report
                    </h1>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Facility Filter -->
                <div>
                    <label
                        for="warehouse"
                        class="block text-xs font-medium text-gray-700 mb-2"
                    >
                        <svg
                            class="w-4 h-4 inline mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                        </svg>
                        Supplier
                    </label>
                    <Multiselect
                        id="warehouse"
                        v-model="supplier"
                        :options="props.suppliers"
                        :searchable="true"
                        :create-option="true"
                        placeholder="Select supplier"
                        class="w-full"
                    />
                </div>

                <!-- Approved Filter -->
                <div>
                    <label
                        for="status"
                        class="block text-xs font-medium text-gray-700 mb-2"
                    >
                        <svg
                            class="w-4 h-4 inline mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Purchase Order Status
                    </label>
                    <select
                        id="status"
                        v-model="status"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                    >
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="rejected">Rejected</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label
                        for="date_from"
                        class="block text-xs font-medium text-gray-700 mb-2"
                    >
                        <svg
                            class="w-4 h-4 inline mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6"
                            />
                        </svg>
                        Date From
                    </label>
                    <input
                        type="date"
                        id="date_from"
                        v-model="date_from"
                        class="w-full"
                    />
                </div>
                <div>
                    <label
                        for="date_to"
                        class="block text-xs font-medium text-gray-700 mb-2"
                    >
                        <svg
                            class="w-4 h-4 inline mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6"
                            />
                        </svg>
                        Date To
                    </label>
                    <input
                        type="date"
                        id="date_to"
                        v-model="date_to"
                        class="w-full"
                    />
                </div>

                <!-- Per Page Filter -->
            </div>
            <div class="flex justify-end items-center mb-3">
                <div class="w-[200px]">
                    <label
                        for="per_page"
                        class="block text-xs font-medium text-gray-700 mb-2"
                    >
                        <svg
                            class="w-4 h-4 inline mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                            />
                        </svg>
                        Items per Page
                    </label>
                    <select
                        id="per_page"
                        v-model="per_page"
                        @change="props.filters.page = 1"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Transfers Table -->

            <div class="overflow-x-auto bg-white rounded shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Purchase Order List</h2>

                <div class="overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100 text-gray-700 text-sm">
                            <tr>
                                <th class="px-4 py-3 text-left">PO #</th>
                                <th class="px-4 py-3 text-left">Supplier</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Items</th>
                                <th class="px-4 py-3 text-left">Total ($)</th>
                                <th class="px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 text-sm text-gray-800"
                        >
                            <tr
                                v-for="po in props.purchaseOrders.data"
                                :key="po.id"
                            >
                                <td class="px-4 py-3">{{ po.po_number }}</td>
                                <td class="px-4 py-3">
                                    {{ po.supplier.name }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatDate(po.po_date) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="statusBadgeClass(po.status)"
                                        class="inline-block px-2 py-1 text-xs rounded-full"
                                    >
                                        {{ po.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ po.items.length }}</td>
                                <td class="px-4 py-3">
                                    {{ calculateTotal(po.items) }}
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        class="text-sm px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 transition"
                                        @click="viewDetails(po)"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <TailwindPagination
                    :data="props.purchaseOrders"
                    :limit="2"
                    @pagination-change-page="getResults"
                />
            </div>
        </div>

        <!-- purchaser order items modal -->

        <transition name="fade">
            <div
                v-if="selectedPurchaseOrder"
                class="fixed inset-0 z-50 bg-black bg-opacity-50"
            >
                <div class="fixed inset-0 bg-white p-8 overflow-y-auto">
                    <div class="max-w-7xl mx-auto">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                Purchase Order #{{
                                    selectedPurchaseOrder.po_number
                                }}
                            </h3>
                            <button
                                @click="selectedPurchaseOrder = null"
                                class="text-gray-400 hover:text-gray-500"
                                title="Close"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Download Button -->
                        <button
                            @click="downloadPurchaseOrder"
                            class="text-blue-500 hover:text-blue-700 mb-6"
                            title="Download as PDF"
                        >
                            ⬇️ Download
                        </button>

                        <!-- Supplier and Purchase Order Info -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-sm"
                        >
                            <!-- Purchase Order Info -->
                            <div>
                                <h4 class="font-semibold mb-2">
                                    Purchase Order Details
                                </h4>
                                <p>
                                    <strong>PO Number:</strong>
                                    {{ selectedPurchaseOrder.po_number }}
                                </p>
                                <p>
                                    <strong>PO Date:</strong>
                                    {{
                                        formatDate(
                                            selectedPurchaseOrder.po_date
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    {{ selectedPurchaseOrder.status }}
                                </p>
                                <p>
                                    <strong>Created By:</strong>
                                    {{
                                        selectedPurchaseOrder.creator?.name ||
                                        "—"
                                    }} at {{ formatDate(selectedPurchaseOrder.created_at) }}
                                </p>
                                <p>
                                    <strong>Reviewed By:</strong>
                                    {{
                                        selectedPurchaseOrder.reviewed_by
                                            ?.name || "—"
                                    }} at {{ formatDate(selectedPurchaseOrder.reviewed_at) }}
                                </p>
                                <p>
                                    <strong>Approved By:</strong>
                                    {{
                                        selectedPurchaseOrder.approved_by
                                            ?.name || "—"
                                    }} at {{ formatDate(selectedPurchaseOrder.approved_at) }}
                                </p>
                                <p v-if="selectedPurchaseOrder.rejected_by">
                                    <strong>Rejected By:</strong>
                                    {{
                                        selectedPurchaseOrder.rejected_by
                                            ?.name || "—"
                                    }} at {{ formatDate(selectedPurchaseOrder.rejected_at) }}
                                </p>
                                <p>
                                    <strong>Total Cost:</strong> ${{
                                        selectedPurchaseOrder.total_cost?.toFixed(2) || "0.00"
                                    }}
                                </p>
                                <p>
                                    <strong>Total Amount:</strong> ${{
                                        selectedPurchaseOrder.items.reduce(
                                            (total, item) => total + item.total_cost,
                                            0
                                        )?.toFixed(2) || "0.00"
                                    }}
                                </p>
                            </div>

                            <!-- Supplier Info -->
                            <div>
                                <h4 class="font-semibold mb-2">
                                    Supplier Details
                                </h4>
                                <p>
                                    <strong>Name:</strong>
                                    {{
                                        selectedPurchaseOrder.supplier?.name ||
                                        "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Contact Person:</strong>
                                    {{
                                        selectedPurchaseOrder.supplier
                                            ?.contact_person || "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Email:</strong>
                                    {{
                                        selectedPurchaseOrder.supplier?.email ||
                                        "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Phone:</strong>
                                    {{
                                        selectedPurchaseOrder.supplier?.phone ||
                                        "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Address:</strong>
                                    {{
                                        selectedPurchaseOrder.supplier
                                            ?.address || "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    {{
                                        selectedPurchaseOrder.supplier
                                            ?.status || "—"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="overflow-x-auto mb-6">
                            <table
                                class="min-w-full text-left text-xs border border-gray-300"
                            >
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th
                                            class="p-2 border border-gray-300 w-72"
                                        >
                                            Item
                                        </th>
                                        <th
                                            class="p-2 border border-gray-300 w-36"
                                        >
                                            Category
                                        </th>
                                        <th
                                            class="p-2 border border-gray-300 w-20"
                                        >
                                            UOM
                                        </th>
                                        <th
                                            class="p-2 border border-gray-300 w-20"
                                        >
                                            Quantity
                                        </th>
                                        <th
                                            class="p-2 border border-gray-300 w-28"
                                        >
                                            Unit Cost ($)
                                        </th>
                                        <th
                                            class="p-2 border border-gray-300 w-28"
                                        >
                                            Total Cost ($)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in selectedPurchaseOrder.items"
                                        :key="item.id"
                                        class="border-b border-gray-300"
                                    >
                                        <td class="p-2 border border-gray-300">
                                            {{ item.product?.name || "—" }}
                                        </td>
                                        <td class="p-2 border border-gray-300">
                                            {{
                                                item.product?.category?.name ||
                                                "—"
                                            }}
                                        </td>
                                        <td class="p-2 border border-gray-300">
                                            {{ item.uom }}
                                        </td>
                                        <td class="p-2 border border-gray-300">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="p-2 border border-gray-300">
                                            ${{ item.unit_cost.toFixed(2) }}
                                        </td>
                                        <td class="p-2 border border-gray-300">
                                            ${{ item.total_cost.toFixed(2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Notes -->
                        <div>
                            <h4 class="font-semibold mb-1">Notes</h4>
                            <p class="text-sm">
                                {{ selectedPurchaseOrder.notes || "—" }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { TailwindPagination } from "laravel-vue-pagination";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { jsPDF } from "jspdf";
import autoTable from "jspdf-autotable";
import moment from "moment";

const props = defineProps({
    purchaseOrders: {
        type: Object,
        required: true,
    },
    filters: Object,
    suppliers: {
        type: Array,
        required: true,
    },
});

// Filters
const per_page = ref(props.filters.per_page || "25");
const supplier = ref(props.filters.supplier || null);
const status = ref(props.filters.status || "");
const date_from = ref(props.filters.date_from || "");
const date_to = ref(props.filters.date_to || "");

// Watch filters and reload data
watch(
    [
        () => per_page.value,
        () => supplier.value,
        () => status.value,
        () => date_from.value,
        () => date_to.value,
        () => props.filters.page,
    ],
    () => {
        reloadPage();
    }
);

function reloadPage() {
    const query = {};
    if (supplier.value) query.supplier = supplier.value;
    if (status.value) query.status = status.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;
    if (date_from.value) query.date_from = date_from.value;
    if (date_to.value) query.date_to = date_to.value;

    router.get(route("reports.purchase-orders"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["purchaseOrders"],
    });
}

function calculateTotal(items) {
    return items
        .reduce((total, item) => total + Number(item.total_cost), 0)
        .toFixed(2);
}

function statusBadgeClass(status) {
    switch (status) {
        case "approved":
            return "bg-green-100 text-green-700";
        case "pending":
            return "bg-yellow-100 text-yellow-700";
        case "rejected":
            return "bg-red-100 text-red-700";
        default:
            return "bg-gray-100 text-gray-600";
    }
}

// Selected Order for Modal
const selectedPurchaseOrder = ref(null);

function viewDetails(po) {
    selectedPurchaseOrder.value = po;
}

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return "";
    return moment(dateString).format("DD/MM/YYYY");
};

// Pagination handler
function getResults(page = 1) {
    props.filters.page = page;
}

function downloadPurchaseOrder() {
    if (!selectedPurchaseOrder) return;

    // Ensure we have the correct data structure
    const supplierName =
        selectedPurchaseOrder.value.supplier?.name || "Unknown Supplier";
    const poNumber = selectedPurchaseOrder.value.po_number || "UnknownPO";

    // Clean filename
    const fileName =
        `${supplierName} - ${poNumber}`
            .replace(/[^a-zA-Z0-9-_ ]/g, "")
            .replace(/\s+/g, "_")
            .toLowerCase() + ".pdf"; // Convert to lowercase for consistency

    const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a4",
    });

    // Add header with PO number
    doc.setFontSize(20);
    doc.text(`Purchase Order #${poNumber}`, 14, 15);

    // Create two-column layout
    const columnWidth = 100;
    const leftMargin = 14;
    const rightMargin = leftMargin + columnWidth + 20;
    const sectionStartY = 30;

    // Left Column - Purchase Order Details
    doc.setFontSize(12);
    doc.text("Purchase Order Details", leftMargin, sectionStartY);
    doc.setFontSize(10);
    doc.text(`PO Number: ${selectedPurchaseOrder.value.po_number}`, leftMargin, sectionStartY + 14);
    doc.text(`PO Date: ${moment(selectedPurchaseOrder.value.po_date).format("YYYY-MM-DD")}`, leftMargin, sectionStartY + 21);
    doc.text(`Status: ${selectedPurchaseOrder.value.status.toUpperCase()}`, leftMargin, sectionStartY + 28);

    // Actions with dates
    doc.setFontSize(10);
    doc.text(
        `Created By: ${selectedPurchaseOrder.value.creator?.name || "—"} at ${moment(selectedPurchaseOrder.value.created_at).format("YYYY-MM-DD HH:mm")}`,
        leftMargin,
        sectionStartY + 38
    );
    
    doc.text(
        `Reviewed By: ${selectedPurchaseOrder.value.reviewed_by?.name || "—"} at ${moment(selectedPurchaseOrder.value.reviewed_at).format("YYYY-MM-DD HH:mm")}`,
        leftMargin,
        sectionStartY + 45
    );
    
    doc.text(
        `Approved By: ${selectedPurchaseOrder.value.approved_by?.name || "—"} at ${moment(selectedPurchaseOrder.value.approved_at).format("YYYY-MM-DD HH:mm")}`,
        leftMargin,
        sectionStartY + 52
    );
    
    // Only show rejected if exists
    if (selectedPurchaseOrder.value.rejected_by) {
        doc.text(
            `Rejected By: ${selectedPurchaseOrder.value.rejected_by?.name || "—"} at ${moment(selectedPurchaseOrder.value.rejected_at).format("YYYY-MM-DD HH:mm")}`,
            leftMargin,
            sectionStartY + 59
        );
    }
    
    // Total Cost
    doc.setFontSize(10);
    doc.text(`Total Cost: $${selectedPurchaseOrder.value.items.reduce(
        (total, item) => total + item.total_cost,
        0
    )?.toFixed(2) || "0.00"}`, leftMargin, sectionStartY + 72);

    // Right Column - Supplier Details
    doc.setFontSize(12);
    doc.text("Supplier Details", rightMargin, sectionStartY);
    doc.setFontSize(10);
    doc.text(`Name: ${supplierName}`, rightMargin, sectionStartY + 7);
    doc.text(
        `Contact Person: ${
            selectedPurchaseOrder.value.supplier?.contact_person || "—"
        }`,
        rightMargin,
        sectionStartY + 17
    );
    doc.text(
        `Email: ${selectedPurchaseOrder.value.supplier?.email || "—"}`,
        rightMargin,
        sectionStartY + 24
    );
    doc.text(
        `Phone: ${selectedPurchaseOrder.value.supplier?.phone || "—"}`,
        rightMargin,
        sectionStartY + 31
    );
    doc.text(
        `Address: ${selectedPurchaseOrder.value.supplier?.address || "—"}`,
        rightMargin,
        sectionStartY + 38
    );
    doc.text(
        `Status: ${selectedPurchaseOrder.value.supplier?.status || "—"}`,
        rightMargin,
        sectionStartY + 45
    );

    // Items table
    const items = Array.isArray(selectedPurchaseOrder.value.items)
        ? selectedPurchaseOrder.value.items
        : [];

    const tableStartY = sectionStartY + 85; // Adjusted to avoid overlap
    const tableMargin = 14; // Same as left margin
    const tableWidth = 297 - tableMargin * 2; // Full width minus margins

    // Add table header
    doc.setFontSize(12);
    doc.text("Items", tableMargin, tableStartY);

    // Items table
    autoTable(doc, {
        startY: tableStartY + 10,
        head: [
            [
                "Item",
                "Category",
                "UOM",
                "Quantity",
                "Unit Cost ($)",
                "Total Cost ($)",
            ],
        ],
        body: items.map((item) => [
            item.product?.name || "",
            item.product?.category?.name || "",
            item.uom || "",
            item.quantity || "",
            item.unit_cost?.toFixed(2) || "0.00",
            item.total_cost?.toFixed(2) || "0.00",
        ]),
        styles: { fontSize: 8 },
        theme: "grid",
        margin: { left: 14, right: 14 },
    });

    // Add notes section
    const notesStartY = tableStartY + 150; // Position after table
    doc.setFontSize(12);
    doc.text("Notes", leftMargin, notesStartY);

    const notes = selectedPurchaseOrder.value.notes || "—";
    doc.setFontSize(10);
    doc.text(notes, leftMargin, notesStartY + 10, {
        align: "left",
        maxWidth: tableWidth,
    });

    // Add footer with page number
    const totalPages = doc.internal.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.text(`Page ${i} of ${totalPages}`, leftMargin, 280);
    }

    doc.save(fileName);
}
</script>

<style setup>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
