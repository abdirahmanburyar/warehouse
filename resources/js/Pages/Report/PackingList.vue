<template>
    <AuthenticatedLayout
        title="Packing List Report"
        description="Packing List Summary"
        img="/assets/images/report.png"
    >
        <Head title="Packing List Report" />

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
                        Packing List Report
                    </h1>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label
                        for="search"
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
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                        </svg>
                        Search
                    </label>
                    <input
                        type="text"
                        id="search"
                        v-model="search"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                <!-- Supplier Filter -->
                <div>
                    <label
                        for="supplier"
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
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                        </svg>
                        Supplier
                    </label>
                    <Multiselect
                        id="supplier"
                        v-model="supplier"
                        :options="props.suppliers"
                        :searchable="true"
                        :create-option="true"
                        placeholder="Select supplier"
                        class="w-full"
                    />
                </div>

                <!-- Status Filter -->
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
                        Status
                    </label>
                    <select
                        id="status"
                        v-model="status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">All</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
            </div>

            <!-- Date Range Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
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
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        From Date
                    </label>
                    <input
                        type="date"
                        id="date_from"
                        v-model="date_from"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        To Date
                    </label>
                    <input
                        type="date"
                        id="date_to"
                        v-model="date_to"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="flex justify-end items-center mb-3">
                <div class="w-[200px]">
                    <label
                        for="per_page"
                        class="block text-xs font-medium text-gray-700 mb-2"
                    >
                        Per Page
                    </label>
                    <select
                        id="per_page"
                        v-model="per_page"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        @change="props.filters.page = 1"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Packing List #
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Purchase Order
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Total Items
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Total Cost
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="pl in props.packingLists.data" :key="pl.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ pl.packing_list_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ pl.purchase_order?.supplier?.name || "—" }} -
                                {{ pl.purchase_order?.po_number || "—" }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="statusBadgeClass(pl.status)">
                                    {{ pl.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ formatDate(pl.pk_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ pl.items.length }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${{ calculateTotal(pl.items) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    @click="viewDetails(pl)"
                                    class="text-blue-600 hover:text-blue-800 mr-2"
                                    title="View Details"
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
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="downloadPackingList(pl)"
                                    class="text-green-600 hover:text-green-800"
                                    title="Download PDF"
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
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                        />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <TailwindPagination
                    :data="props.packingLists"
                    :limit="2"
                    @pagination-change-page="getResults"
                />
            </div>
        </div>

        <!-- Packing List Details Modal -->
        <transition name="fade">
            <div
                v-if="selectedPackingList"
                class="fixed inset-0 z-50 bg-black bg-opacity-50"
            >
                <div class="fixed inset-0 bg-white p-8 overflow-y-auto">
                    <div class="max-w-7xl mx-auto">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                Packing List #{{ selectedPackingList.packing_list_number }}
                            </h3>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="downloadPackingList(selectedPackingList)"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    title="Download PDF"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0L8 16m4-4v12" />
                                    </svg>
                                    Download PDF
                                </button>
                                <button
                                    @click="selectedPackingList = null"
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
                        </div>

                        <!-- Packing List Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left Column -->
                            <div>
                                <h4 class="font-semibold mb-2">
                                    Packing List Details
                                </h4>
                                <p>
                                    <strong>Packing List #:</strong>
                                    {{
                                        selectedPackingList.packing_list_number ||
                                        "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Purchase Order:</strong>
                                    {{
                                        selectedPackingList.purchase_order
                                            ?.po_number || "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    {{
                                        selectedPackingList.status.toUpperCase()
                                    }}
                                </p>
                                <p>
                                    <strong>Ref No.</strong>
                                    {{ selectedPackingList.ref_no || "—" }}
                                </p>
                                <p v-if="selectedPackingList.confirmed_by">
                                    <strong>Confirmed By:</strong>
                                    {{
                                        selectedPackingList.confirmed_by
                                            ?.name || "—"
                                    }}
                                    at
                                    {{
                                        formatDate(
                                            selectedPackingList.confirmed_at
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Reviewed By:</strong>
                                    {{
                                        selectedPackingList.reviewed_by?.name ||
                                        "—"
                                    }}
                                    at
                                    {{
                                        formatDate(
                                            selectedPackingList.reviewed_at
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Approved By:</strong>
                                    {{
                                        selectedPackingList.approved_by?.name ||
                                        "—"
                                    }}
                                    at
                                    {{
                                        formatDate(
                                            selectedPackingList.approved_at
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Total Cost:</strong>
                                    ${{
                                        calculateTotal(
                                            selectedPackingList.items
                                        )
                                    }}
                                </p>
                            </div>

                            <!-- Right Column -->
                            <div>
                                <h4 class="font-semibold mb-2">
                                    Supplier Information
                                </h4>
                                <p>
                                    <strong>Supplier:</strong>
                                    {{
                                        selectedPackingList.purchase_order
                                            ?.supplier?.name || "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Contact Person:</strong>
                                    {{
                                        selectedPackingList.purchase_order
                                            ?.supplier?.contact_person || "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Phone:</strong>
                                    {{
                                        selectedPackingList.purchase_order
                                            ?.supplier?.phone || "—"
                                    }}
                                </p>
                                <p>
                                    <strong>Email:</strong>
                                    {{
                                        selectedPackingList.purchase_order
                                            ?.supplier?.email || "—"
                                    }}
                                </p>
                            </div>
                        </div>

                        <h4 class="font-medium mb-2 mt-2">Received Items</h4>

                        <!-- Items Table -->
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Item
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            UOM
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Batch No
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Expiry Date
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Quantity
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Unit Cost
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Total Cost
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Storage Location
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="item in selectedPackingList.items"
                                        :key="item.id"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ item.product?.name || "—" }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ item.uom || "—" }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ item.batch_number || "—" }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(item.expire_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ${{
                                                item.unit_cost?.toFixed(2) ||
                                                "0.00"
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ${{
                                                item.total_cost?.toFixed(2) ||
                                                "0.00"
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span
                                                    >WH:
                                                    {{
                                                        item.warehouse?.name ||
                                                        "—"
                                                    }}</span
                                                >
                                                <span
                                                    >Loc:
                                                    {{
                                                        item.location
                                                            ?.location || "—"
                                                    }}</span
                                                >
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Notes Section -->
                        <div>
                            <h4 class="font-semibold mb-2">Notes</h4>
                            <p>
                                {{ selectedPackingList.notes || "—" }}
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
import { ref, watch, onMounted } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { TailwindPagination } from "laravel-vue-pagination";
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';
import moment from 'moment';

const props = defineProps({
    packingLists: Object,
    suppliers: Array,
    filters: Object,
});

// Filters
const search = ref(props.filters.search || null);
const status = ref(props.filters.status || "");
const date_from = ref(props.filters.date_from || "");
const date_to = ref(props.filters.date_to || "");
const per_page = ref(props.filters.per_page || 25);
const supplier = ref(props.filters.supplier);

watch(
    [
        () => search.value,
        () => status.value,
        () => date_from.value,
        () => date_to.value,
        () => per_page.value,
        () => supplier.value,
        () => props.filters.page,
    ],
    () => {
        reloadPage();
    }
);

function reloadPage() {
    const query = {};
    if (search.value) query.search = search.value;
    if (status.value) query.status = status.value;
    if (date_from.value) query.date_from = date_from.value;
    if (date_to.value) query.date_to = date_to.value;
    if (per_page.value) query.per_page = per_page.value;
    if (supplier.value) query.supplier = supplier.value;
    router.get(route("reports.packing-list"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["packingLists"],
    });
}

// Selected Packing List for Modal
const selectedPackingList = ref(null);

// Helper Functions
const formatDate = (dateString) => {
    if (!dateString) return "—";
    return new Date(dateString).toLocaleDateString();
};

const calculateTotal = (items) => {
    return items
        .reduce((total, item) => total + (item.total_cost || 0), 0)
        .toFixed(2);
};

const statusBadgeClass = (status) => {
    const classes = {
        pending:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800",
        confirmed:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800",
        reviewed:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800",
        approved:
            "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800",
    };
    return classes[status] || classes.pending;
};

// Modal Functions
const viewDetails = (pl) => {
    selectedPackingList.value = pl;
};

// Pagination Handler
const getResults = (page = 1) => {
    props.filters.page = page;
};

// Download Function
const downloadPackingList = (pl) => {
    if (!pl) return;

    console.log(pl);

    const fileName = `packing_list_${pl.packing_list_number || 'unknown'}_${moment().format('YYYYMMDD')}.pdf`
        .toLowerCase()
        .replace(/[^a-z0-9.-]/g, '_');

    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: 'a4',
    });

    doc.setProperties({
        title: `Packing List #${pl.packing_list_number || ''}`,
        subject: 'Packing List',
        author: 'Warehouse Management System',
        creator: 'Warehouse Management System',
    });

    const pageWidth = 297; // A4 landscape
    const pageHeight = 210;
    const margin = 10;
    const columnGap = 20;
    const contentWidth = pageWidth - (2 * margin);
    const columnWidth = (contentWidth - columnGap) / 2;
    let yPos = 20;

    const checkNewPage = (extraSpace = 10) => {
        if (yPos + extraSpace > pageHeight - margin) {
            doc.addPage();
            yPos = margin;
        }
    };

    // Header
    doc.setFontSize(18);
    doc.setFont(undefined, 'bold');
    doc.text(`PACKING LIST #${pl.packing_list_number || ''}`, margin, yPos);
    yPos += 10;

    doc.setFontSize(10);
    doc.setTextColor(100);
    doc.setFont(undefined, 'normal');
    yPos += 8;

    // Left Column
    doc.setFontSize(11);
    doc.setFont(undefined, 'bold');
    doc.text('Packing List Details', margin, yPos);
    yPos += 6;
    doc.setFont(undefined, 'normal');

    doc.text(`Packing List #: ${pl.packing_list_number || '—'}`, margin, yPos); yPos += 6;
    doc.text(`Status: ${pl.status.toUpperCase()}`, margin, yPos); yPos += 6;
    doc.text(`Purchase Order: ${pl.purchase_order?.po_number || '—'}`, margin, yPos); yPos += 6;
    doc.text(`Ref No: ${pl.ref_no || '—'}`, margin, yPos); yPos += 10;

    const dates = [
        { label: 'Confirmed:', date: pl.confirmed_at, user: pl.confirmed_by },
        { label: 'Reviewed:', date: pl.reviewed_at, user: pl.reviewed_by },
        { label: 'Approved:', date: pl.approved_at, user: pl.approved_by },
    ];

    dates.forEach((item) => {
        if (item.date) {
            let dateText = `${item.label}`;
            if (item.user?.name) {
                dateText += ` by ${item.user.name}`;
            }
            dateText += ` ${moment(item.date).format('DD/MM/YYYY HH:mm')}`;
            checkNewPage(6);
            doc.text(dateText, margin, yPos);
            yPos += 6;
        }
    });

    // Right Column
    let yRight = 40;
    const rightColX = margin + columnWidth + columnGap;
    doc.setFont(undefined, 'bold');
    doc.text('Supplier Information', rightColX, yRight);
    yRight += 6;
    doc.setFont(undefined, 'normal');

    if (pl.purchase_order?.supplier) {
        doc.text(`Name: ${pl.purchase_order.supplier.name || '—'}`, rightColX, yRight); yRight += 6;
        doc.text(`Contact: ${pl.purchase_order.supplier.contact_person || '—'}`, rightColX, yRight); yRight += 6;
        doc.text(`Phone: ${pl.purchase_order.supplier.phone || '—'}`, rightColX, yRight); yRight += 6;
        doc.text(`Email: ${pl.purchase_order.supplier.email || '—'}`, rightColX, yRight); yRight += 6;
    }

    // Items Table
    yPos = Math.max(yPos, yRight) + 10;
    doc.setFont(undefined, 'bold');
    doc.setFontSize(12);
    doc.text('Packed Items', margin, yPos);
    yPos += 8;

    // Calculate column widths based on content and available space
    const tableColumn = [
        { header: 'Item', dataKey: 'item', width: 60 },
        { header: 'Batch', dataKey: 'batch', width: 30 },
        { header: 'Expiry', dataKey: 'expire_date', width: 25 },
        { header: 'UOM', dataKey: 'uom', width: 20 },
        { header: 'Qty', dataKey: 'qty', width: 15 },
        { header: 'Unit Cost', dataKey: 'unit_cost', width: 25 },
        { header: 'Total Cost', dataKey: 'total_cost', width: 25 },
        { header: 'Storage', dataKey: 'location', width: 40 }
    ];

    // Adjust widths to fit page
    const totalWidth = tableColumn.reduce((sum, col) => sum + col.width, 0);
    const scaleFactor = (pageWidth - 2 * margin) / totalWidth;
    
    // Apply scaling to fit page width
    tableColumn.forEach(col => {
        col.width = col.width * scaleFactor;
    });

    const tableData = pl.items.map(item => ({
        item: item.product?.name || '—',
        batch: item.batch_number || '—',
        expire_date: item.expire_date ? moment(item.expire_date).format('DD/MM/YYYY') : '—',
        uom: item.uom || '—',
        qty: item.quantity || '0',
        unit_cost: `$${parseFloat(item.unit_cost || 0).toFixed(2)}`,
        total_cost: `$${parseFloat(item.total_cost || 0).toFixed(2)}`,
        location: `WH: ${item.warehouse?.name || '—'}\nLC: ${item.location?.location || '—'}`,
    }));

    // Prepare column styles with calculated widths
    const columnStyles = {};
    tableColumn.forEach((col, index) => {
        columnStyles[index] = {
            cellWidth: col.width,
            fontSize: 8,
            cellPadding: 2,
            overflow: 'linebreak',
            halign: col.dataKey.includes('cost') || col.dataKey === 'qty' ? 'right' : 'left'
        };
        if (index === 0) columnStyles[0].fontStyle = 'bold';
        if (index === 4) columnStyles[4].halign = 'center';
    });

    autoTable(doc, {
        startY: yPos,
        head: [tableColumn.map(col => col.header)],
        body: tableData.map(row => tableColumn.map(col => row[col.dataKey])),
        margin: { left: margin, right: margin },
        tableWidth: 'wrap',
        headStyles: {
            fillColor: [59, 130, 246],
            textColor: 255,
            fontSize: 8,
            cellPadding: 3,
            lineWidth: 0.1,
        },
        bodyStyles: {
            fontSize: 8,
            cellPadding: 2,
            lineWidth: 0.1,
            lineColor: [220, 220, 220],
        },
        styles: {
            fontSize: 8,
            cellPadding: 2,
            lineWidth: 0.1,
            overflow: 'linebreak',
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245],
        },
        columnStyles: columnStyles,
        didDrawPage: function(data) {
            doc.setFontSize(8);
            doc.text(`Page ${doc.internal.getNumberOfPages()}`, pageWidth - margin - 10, pageHeight - 5);
        },
        showHead: 'everyPage',
        theme: 'grid',
    });

    const finalY = doc.lastAutoTable.finalY || yPos + 10;

    // Total Row
    doc.setFont(undefined, 'bold');
    doc.setFontSize(10);
    checkNewPage(10);
    doc.text(`TOTAL: $${calculateTotal(pl.items)}`, pageWidth - margin - 60, finalY + 10, { align: 'right' });

    // Notes Section
    if (pl.notes) {
        let notesY = finalY + 20;
        if (notesY > pageHeight - 30) {
            doc.addPage();
            notesY = margin;
        }
        doc.setFont(undefined, 'bold');
        doc.text('Notes:', margin, notesY);
        doc.setFont(undefined, 'normal');
        const splitNotes = doc.splitTextToSize(pl.notes, pageWidth - 2 * margin - 10);
        doc.text(splitNotes, margin + 6, notesY + 5);
    }

    doc.save(fileName);
};

</script>
