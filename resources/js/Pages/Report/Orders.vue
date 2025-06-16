<template>
    <AuthenticatedLayout title="Orders Report" description="Monthly Orders Summary" img="/assets/images/report.png">

        <Head title="Orders Report" />

        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg">
                    <Link :href="route('reports.index')" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                </div>
                <div>                    
                    <h1 class="text-lg font-semibold text-gray-900">
                        Orders Report
                    </h1>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Facility Filter -->
                <div>
                    <label for="facility" class="block text-xs font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Facility
                    </label>
                    <Multiselect id="facility" v-model="facility" :options="facilities" :searchable="true"
                        :create-option="true" placeholder="Select facility" class="w-full" />
                </div>

                <!-- Date Filter -->
                <div>
                    <label for="date_from" class="block text-xs font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6" />
                        </svg>
                        Date From
                    </label>
                    <input type="date" id="date_from" v-model="date_from" class="w-full" />
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6" />
                        </svg>
                        Date To
                    </label>
                    <input type="date" id="date_to" v-model="date_to" class="w-full" />
                </div>

                <!-- Approved Filter -->
                <div>
                    <label for="status" class="block text-xs font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Order Status
                    </label>
                    <select id="status" v-model="status"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="in_process">In Process</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="received">Received</option>
                    </select>
                </div>

                <!-- Per Page Filter -->
                <div>
                    <label for="per_page" class="block text-xs font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Items per Page
                    </label>
                    <select id="per_page" v-model="per_page" @change="props.filters.page = 1"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-700">
                                Order #
                            </th>
                            <th class="px-4 py-2 text-left text-gray-700">
                                Facility
                            </th>
                            <th class="px-4 py-2 text-left text-gray-700">
                                Type
                            </th>
                            <th class="px-4 py-2 text-left text-gray-700">
                                Status
                            </th>
                            <th class="px-4 py-2 text-left text-gray-700">
                                Order Date
                            </th>
                            <th class="px-4 py-2 text-left text-gray-700">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="order in props.orders.data" :key="order.id">
                            <td class="px-4 py-2">{{ order.order_number }}</td>
                            <td class="px-4 py-2">
                                {{ order.facility?.name }}
                            </td>
                            <td class="px-4 py-2">{{ order.order_type }}</td>
                            <td class="px-4 py-2">
                                <span :class="getStatusBadgeClass(order.status)">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                {{ formatDate(order.order_date) }}
                            </td>
                            <td class="px-4 py-2">
                                <button @click="viewOrder(order)" class="text-blue-600 hover:underline">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <TailwindPagination :data="props.orders" :limit="2" @pagination-change-page="getResults" />
            </div>
        </div>

        <transition name="fade">
            <div v-if="selectedOrder" class="fixed inset-0 z-50 bg-black bg-opacity-50">
                <div class="fixed inset-0 bg-white p-8 overflow-y-auto">
                    <div class="max-w-7xl mx-auto">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                Order #{{ selectedOrder.order_number }}
                            </h3>
                            <button @click="selectedOrder = null" class="no-export text-gray-400 hover:text-gray-600"
                                aria-label="Close">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <button @click="downloadModal" class="no-export text-blue-500 hover:text-blue-700 no-print ml-4"
                            title="Download as HTML">
                            ⬇️ Download
                        </button>

                        <!-- Order Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <strong>Facility:</strong>
                                {{ selectedOrder.facility?.name }}
                            </div>
                            <div>
                                <strong>Order Type:</strong>
                                {{ selectedOrder.order_type }}
                            </div>
                            <div>
                                <strong>Status:</strong>
                                {{ selectedOrder.status }}
                            </div>
                            <div>
                                <strong>Order Date:</strong>
                                {{ formatDate(selectedOrder.order_date) }}
                            </div>
                            <div>
                                <strong>Expected Date:</strong>
                                {{ formatDate(selectedOrder.expected_date) }}
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="mb-4">
                            <strong>Note:</strong>
                            <div class="mt-1 text-sm">
                                {{ selectedOrder.note || "—" }}
                            </div>
                        </div>

                        <!-- Approvals -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                            <div>
                                <strong>Approved By:</strong>
                                <div class="mt-1">
                                    {{ selectedOrder.approved_by?.name || "—" }}
                                </div>
                            </div>
                            <div v-if="selectedOrder.status === 'rejected'">
                                <strong>Rejected By:</strong>
                                <div class="mt-1">
                                    {{ selectedOrder.rejected_by?.name || "—" }}
                                </div>
                            </div>
                            <div>
                                <strong>Dispatched By:</strong>
                                <div class="mt-1">
                                    {{
                                        selectedOrder.dispatched_by?.name || "—"
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Allocation Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs border">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="p-2 border border-black w-72">
                                            Item
                                        </th>
                                        <th class="p-2 border border-black w-20">
                                            UOM
                                        </th>
                                        <th class="p-2 border border-black w-20">
                                            Barcode
                                        </th>
                                        <th class="p-2 border border-black w-20">
                                            Batch Number
                                        </th>
                                        <th class="p-2 border border-black w-20">
                                            Expiry Date
                                        </th>
                                        <th class="p-2 border border-black w-20">
                                            Quantity
                                        </th>
                                        <th class="p-2 border border-black w-28">
                                            Warehouse
                                        </th>
                                        <th class="p-2 border border-black w-28">
                                            Location
                                        </th>
                                        <th class="p-2 border border-black w-20">
                                            Unit Cost
                                        </th>
                                        <th class="p-2 border border-black w-20">
                                            Total Cost
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="allocation in selectedOrder.inventory_allocations" :key="allocation.id"
                                        class="border-b">
                                        <td class="p-2 border border-black">
                                            {{ allocation.product }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{ allocation.uom }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{ allocation.barcode }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{ allocation.batch_number }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{
                                                formatDate(
                                                    allocation.expiry_date
                                                )
                                            }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{ allocation.quantity }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{ allocation.warehouse }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{
                                                allocation.location?.location ||
                                                allocation.location?.name
                                            }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{
                                                formatCurrency(
                                                    allocation.unit_cost
                                                )
                                            }}
                                        </td>
                                        <td class="p-2 border border-black">
                                            {{
                                                formatCurrency(
                                                    allocation.total_cost
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
    orders: {
        type: Object,
        required: true,
    },
    filters: Object,
    facilities: {
        type: Array,
        required: true,
    },
});

// Filters
const per_page = ref(props.filters.per_page || "25");
const facility = ref(props.filters.facility || null);
const status = ref(props.filters.status || "");
const date_from = ref(props.filters.date_from || "");
const date_to = ref(props.filters.date_to || "");

// Watch filters and reload data
watch(
    [
        () => per_page.value,
        () => facility.value,
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
    if (facility.value) query.facility = facility.value;
    if (status.value) query.status = status.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;
    if (date_from.value) query.date_from = date_from.value;
    if (date_to.value) query.date_to = date_to.value;

    router.get(route("reports.orders"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["orders"],
    });
}

// Selected Order for Modal
const selectedOrder = ref(null);

function viewOrder(order) {
    selectedOrder.value = {
        id: order.id,
        order_number: order.order_number,
        facility: order.facility,
        order_type: order.order_type,
        status: order.status,
        order_date: order.order_date,
        expected_date: order.expected_date,
        note: order.note,
        approved_by: order.approvedBy,
        rejected_by: order.rejectedBy,
        dispatched_by: order.dispatchedBy,
        // Use inventory_allocations instead of items
        inventory_allocations: order.inventory_allocations.map((alloc) => ({
            id: alloc.id,
            product: alloc.product?.name || "",
            warehouse: alloc.warehouse?.name || "",
            location: alloc.location,
            batch_number: alloc.batch_number,
            expiry_date: alloc.expiry_date,
            quantity: alloc.allocated_quantity,
            uom: alloc.uom || "", // Assuming uom is included in allocation or you can set a default
            barcode: alloc.barcode || "", // Include if available
            unit_cost: alloc.unit_cost,
            total_cost: alloc.total_cost,
        })),
    };
}

function formatCurrency(money) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(money);
}

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return "";
    return moment(dateString).format("DD/MM/YYYY");
};

// Get badge class based on status
const getStatusBadgeClass = (status) => {
    const classes = {
        pending:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800",
        approved:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800",
        rejected:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800",
        in_process:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800",
        dispatched:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800",
        received:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800",
    };
    return (
        classes[status] ||
        "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
    );
};

// Pagination handler
function getResults(page = 1) {
    props.filters.page = page;
}


function downloadModal() {
    if (!selectedOrder.value) return;

    const facilityName = selectedOrder.value.facility?.name || 'Unknown Facility';
    const orderNumber = selectedOrder.value.order_number || 'Unknown Order';

    // Generate a clean filename
    const fileName = `${facilityName} - ${orderNumber}`
        .replace(/[^a-zA-Z0-9-_ ]/g, '')
        .replace(/\s+/g, '_') + '.pdf';

    // 📄 Create jsPDF in landscape mode
    const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a4"
    });

    // Title
    doc.setFontSize(16);
    doc.text(`Order Report`, 14, 15);
    doc.setFontSize(12);
    doc.text(`Order Number: ${selectedOrder.value.order_number}`, 14, 25);
    doc.text(`Facility: ${facilityName}`, 14, 32);
    doc.text(`Order Type: ${selectedOrder.value.order_type}`, 14, 39);
    doc.text(`Status: ${selectedOrder.value.status}`, 14, 46);
    doc.text(`Order Date: ${moment(selectedOrder.value.order_date).format('YYYY-MM-DD')}`, 14, 53);
    doc.text(`Expected Date: ${moment(selectedOrder.value.expected_date).format('YYYY-MM-DD')}`, 14, 60);
    doc.text(`Note: ${selectedOrder.value.note || '—'}`, 14, 67);

    // Approvals
    doc.text(`Approved By: ${selectedOrder.value.approved_by?.name || '—'}`, 14, 74);
    const nextY = selectedOrder.value.status === 'rejected' ? 88 : 81;
    if (selectedOrder.value.status === 'rejected') {
        doc.text(`Rejected By: ${selectedOrder.value.rejected_by?.name || '—'}`, 14, 81);
    }
    doc.text(`Dispatched By: ${selectedOrder.value.dispatched_by?.name || '—'}`, 14, nextY);

    // Inventory Allocations table
    const tableStartY = selectedOrder.value.status === 'rejected' ? 95 : 88;
    const allocations = Array.isArray(selectedOrder.value.inventory_allocations) ? selectedOrder.value.inventory_allocations : [];

    autoTable(doc, {
        startY: tableStartY,
        head: [[
            'Item',
            'UOM',
            'Barcode',
            'Batch #',
            'Expiry Date',
            'Qty',
            'Warehouse',
            'Location',
            'Unit Cost',
            'Total Cost'
        ]],
        body: allocations.map((a) => [
            a.product || '',
            a.uom || '',
            a.barcode || '',
            a.batch_number || '',
            a.expiry_date ? moment(a.expiry_date).format('YYYY-MM-DD') : '',
            a.quantity || '',
            a.warehouse || '',
            a.location?.location || a.location?.name || '',
            a.unit_cost !== undefined ? a.unit_cost.toFixed(2) : '',
            a.total_cost !== undefined ? a.total_cost.toFixed(2) : ''
        ]),
        styles: {
            fontSize: 8,
        },
        headStyles: {
            fillColor: [240, 240, 240],
            textColor: 0,
            fontStyle: 'bold',
        },
        theme: 'grid'
    });

    // Save PDF
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
