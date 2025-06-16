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

                {{ props.purchaseOrders}}

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

// Selected Order for Modal
const selectedPurchaseOrder = ref(null);

function viewPurchaseOrder(purchaseOrder) {
    console.log(purchaseOrder);
    selectedPurchaseOrder.value = purchaseOrder;
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
    console.log(selectedPurchaseOrder.value);
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
