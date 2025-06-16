<template>
    <AuthenticatedLayout
        title="Transfer Report"
        description="Monthly Transfer Summary"
        img="/assets/images/report.png"
    >
        <Head title="Transfer Report" />

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
                        Transfer Report
                    </h1>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Facility Filter -->
                <div>
                    <label
                        for="facility"
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
                        Facility
                    </label>
                    <Multiselect
                        id="facility"
                        v-model="facility"
                        :options="facilities"
                        :searchable="true"
                        :create-option="true"
                        placeholder="Select facility"
                        class="w-full"
                    />
                </div>

                <!-- Warehouse Filter -->
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
                        Warehouse
                    </label>
                    <Multiselect
                        id="warehouse"
                        v-model="warehouse"
                        :options="warehouses"
                        :searchable="true"
                        :create-option="true"
                        placeholder="Select warehouse"
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
                        Transfer Status
                    </label>
                    <select
                        id="status"
                        v-model="status"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors duration-200 text-xs"
                    >
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="in_process">In Process</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="received">Received</option>
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
                <div>
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
                <h2 class="text-lg font-semibold mb-4">Transfer List</h2>

                <table class="min-w-full text-sm text-left border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border">Transfer ID</th>
                            <th class="px-4 py-2 border">From</th>
                            <th class="px-4 py-2 border">To</th>
                            <th class="px-4 py-2 border">Quantity</th>
                            <th class="px-4 py-2 border">Status</th>
                            <th class="px-4 py-2 border">Transfer Date</th>
                            <th class="px-4 py-2 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="transfer in props.transfers.data"
                            :key="transfer.id"
                            class="border-t hover:bg-gray-50"
                        >
                            <td class="px-4 py-2 border">
                                {{ transfer.transferID }}
                            </td>
                            <td class="px-4 py-2 border">
                                {{
                                    transfer.from_warehouse?.name ||
                                    transfer.from_facility?.name ||
                                    "—"
                                }}
                            </td>
                            <td class="px-4 py-2 border">
                                {{
                                    transfer.to_warehouse?.name ||
                                    transfer.to_facility?.name ||
                                    "—"
                                }}
                            </td>
                            <td class="px-4 py-2 border">
                                {{ transfer.quantity }}
                            </td>
                            <td class="px-4 py-2 border capitalize">
                                <span
                                    :class="[
                                        'inline-block px-2 py-0.5 rounded text-xs font-medium',
                                        {
                                            'bg-yellow-100 text-yellow-800':
                                                transfer.status === 'pending',
                                            'bg-green-100 text-green-800':
                                                transfer.status === 'approved',
                                            'bg-red-100 text-red-800':
                                                transfer.status === 'rejected',
                                        },
                                    ]"
                                >
                                    {{ transfer.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border">
                                {{ formatDate(transfer.transfer_date) }}
                            </td>
                            <td class="px-4 py-2 border text-center">
                                <button
                                    class="text-blue-600 hover:underline text-sm"
                                    @click="viewTransfer(transfer)"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <TailwindPagination
                    :data="props.transfers"
                    :limit="2"
                    @pagination-change-page="getResults"
                />
            </div>
        </div>

        <!-- transfer items modal -->
        <transition name="fade">
    <div v-if="selectedTransfer" class="fixed inset-0 z-50 bg-black bg-opacity-50">
      <div class="fixed inset-0 bg-white p-8 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">
              Transfer #{{ selectedTransfer.transferID }}
            </h3>
            <button
              @click="selectedTransfer = null"
              class="no-export text-gray-400 hover:text-gray-600"
              aria-label="Close"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <button
            @click="downloadModal"
            class="no-export text-blue-500 hover:text-blue-700 no-print ml-4 mb-6"
            title="Download as PDF"
          >
            ⬇️ Download
          </button>

          <!-- Transfer Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
            <div>
              <strong>From:</strong>
              {{ selectedTransfer.from_warehouse?.name || selectedTransfer.from_facility?.name || "—" }}
            </div>
            <div>
              <strong>To:</strong>
              {{ selectedTransfer.to_warehouse?.name || selectedTransfer.to_facility?.name || "—" }}
            </div>
            <div>
              <strong>Status:</strong>
              {{ selectedTransfer.status }}
            </div>
            <div>
              <strong>Transfer Date:</strong>
              {{ formatDate(selectedTransfer.transfer_date) }}
            </div>
            <div>
              <strong>Quantity:</strong>
              {{ selectedTransfer.quantity }}
            </div>
          </div>

          <!-- Note -->
          <div class="mb-4">
            <strong>Note:</strong>
            <div class="mt-1 text-sm">{{ selectedTransfer.note || "—" }}</div>
          </div>

          <!-- Approvals -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
            <div>
              <strong>Approved By:</strong>
              <div class="mt-1">{{ selectedTransfer.approved_by?.name || "—" }}</div>
            </div>
            <div v-if="selectedTransfer.status === 'rejected'">
              <strong>Rejected By:</strong>
              <div class="mt-1">{{ selectedTransfer.rejected_by?.name || "—" }}</div>
            </div>
            <div>
              <strong>Dispatched By:</strong>
              <div class="mt-1">{{ selectedTransfer.dispatched_by?.name || "—" }}</div>
            </div>
          </div>

          <!-- Items Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs border">
              <thead class="bg-gray-100">
                <tr>
                  <th class="p-2 border border-black w-72">Item</th>
                  <th class="p-2 border border-black w-20">UOM</th>
                  <th class="p-2 border border-black w-20">Barcode</th>
                  <th class="p-2 border border-black w-20">Batch Number</th>
                  <th class="p-2 border border-black w-20">Expiry Date</th>
                  <th class="p-2 border border-black w-20">Quantity</th>
                  <th class="p-2 border border-black w-28">Received Quantity</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedTransfer.items" :key="item.id" class="border-b">
                  <td class="p-2 border border-black">
                    {{ item.product?.name || "—" }}
                  </td>
                  <td class="p-2 border border-black">{{ item.uom }}</td>
                  <td class="p-2 border border-black">{{ item.barcode }}</td>
                  <td class="p-2 border border-black">{{ item.batch_number }}</td>
                  <td class="p-2 border border-black">{{ formatDate(item.expire_date) }}</td>
                  <td class="p-2 border border-black">{{ item.quantity }}</td>
                  <td class="p-2 border border-black">{{ item.received_quantity }}</td>
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
    transfers: {
        type: Object,
        required: true,
    },
    filters: Object,
    facilities: {
        type: Array,
        required: true,
    },
    warehouses: {
        type: Array,
        required: true,
    },
});

// Filters
const per_page = ref(props.filters.per_page || "25");
const facility = ref(props.filters.facility || null);
const warehouse = ref(props.filters.warehouse || null);
const status = ref(props.filters.status || "");
const date_from = ref(props.filters.date_from || "");
const date_to = ref(props.filters.date_to || "");

// Watch filters and reload data
watch(
    [
        () => per_page.value,
        () => facility.value,
        () => warehouse.value,
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
    if (warehouse.value) query.warehouse = warehouse.value;
    if (status.value) query.status = status.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;
    if (date_from.value) query.date_from = date_from.value;
    if (date_to.value) query.date_to = date_to.value;

    router.get(route("reports.transfers"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["transfers"],
    });
}

// Selected Order for Modal
const selectedTransfer = ref(null);

function viewTransfer(transfer) {
    console.log(transfer);
    selectedTransfer.value = transfer;
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


function downloadModal() {
    if (!selectedTransfer.value) return;

    const fromName = selectedTransfer.value.from_warehouse?.name || selectedTransfer.value.from_facility?.name || 'Unknown From';
    const toName = selectedTransfer.value.to_warehouse?.name || selectedTransfer.value.to_facility?.name || 'Unknown To';
    const transferID = selectedTransfer.value.transferID || 'UnknownTransfer';

    // 📄 Generate a clean filename
    const fileName = `${fromName} to ${toName} - ${transferID}`
        .replace(/[^a-zA-Z0-9-_ ]/g, '')
        .replace(/\s+/g, '_') + '.pdf';

    // 📄 Create jsPDF in landscape mode
    const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a4"
    });

    // Header
    doc.setFontSize(16);
    doc.text(`Transfer Report`, 14, 15);
    doc.setFontSize(12);
    doc.text(`Transfer ID: ${transferID}`, 14, 25);
    doc.text(`From: ${fromName}`, 14, 32);
    doc.text(`To: ${toName}`, 14, 39);
    doc.text(`Status: ${selectedTransfer.value.status}`, 14, 46);
    doc.text(`Transfer Date: ${moment(selectedTransfer.value.transfer_date).format('YYYY-MM-DD')}`, 14, 53);
    doc.text(`Quantity: ${selectedTransfer.value.quantity}`, 14, 60);
    doc.text(`Note: ${selectedTransfer.value.note || '—'}`, 14, 67);

    // Approvals
    doc.text(`Approved By: ${selectedTransfer.value.approved_by?.name || '—'}`, 14, 74);
    const nextY = selectedTransfer.value.status === 'rejected' ? 88 : 81;
    if (selectedTransfer.value.status === 'rejected') {
        doc.text(`Rejected By: ${selectedTransfer.value.rejected_by?.name || '—'}`, 14, 81);
    }
    doc.text(`Dispatched By: ${selectedTransfer.value.dispatched_by?.name || '—'}`, 14, nextY);

    // Items Table
    const tableStartY = selectedTransfer.value.status === 'rejected' ? 95 : 88;
    const items = Array.isArray(selectedTransfer.value.items) ? selectedTransfer.value.items : [];

    autoTable(doc, {
        startY: tableStartY,
        head: [[
            'Item',
            'UOM',
            'Barcode',
            'Batch #',
            'Expiry Date',
            'Quantity',
            'Received Quantity'
        ]],
        body: items.map(item => [
            item.product?.name || '',
            item.uom || '',
            item.barcode || '',
            item.batch_number || '',
            item.expire_date ? moment(item.expire_date).format('YYYY-MM-DD') : '',
            item.quantity || '',
            item.received_quantity || 0
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

    // Save the PDF
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
