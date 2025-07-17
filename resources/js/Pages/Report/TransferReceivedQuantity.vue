<template>
    <Head title="Transfer Received Quantity Report" />
    <AuthenticatedLayout
        title="Transfer Received Quantity Report"
        description="Track all received transfer quantities"
        img="/assets/images/report.png"
    >
        <h2 class="text-xl font-semibold mb-4">
            Transfer Received Quantity Report
        </h2>

        <!-- Main Content with adjusted split -->
        <div class="flex">
            <!-- Filter Section -->
            <div class="flex-[0.1] pr-4">
                <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                    <h3 class="text-lg font-medium mb-3">Filters</h3>

                    <!-- Facility Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            To Facility
                        </label>
                        <Multiselect
                            v-model="facility"
                            :options="facilities"
                            :searchable="true"
                            :create-option="true"
                            placeholder="Select facility"
                            class="w-full"
                        />
                    </div>

                    <!-- Warehouse Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            To Warehouse
                        </label>
                        <Multiselect
                            v-model="warehouse"
                            :options="warehouses"
                            :searchable="true"
                            :create-option="true"
                            placeholder="Select warehouse"
                            class="w-full"
                        />
                    </div>

                    <!-- Date Filters -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6"></path>
                            </svg>
                            Date From
                        </label>
                        <input
                            type="date"
                            v-model="date_from"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:bg-white transition-colors duration-200 text-xs"
                        />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8l-5-5m5 5l5 5m6 0H12m0 0h3m0 0h3m0-3H9m0-3H6m0-6H9m0-6H6"></path>
                            </svg>
                            Date To
                        </label>
                        <input
                            type="date"
                            v-model="date_to"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:bg-white transition-colors duration-200 text-xs"
                        />
                    </div>

                    <!-- Per Page Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Items per Page
                        </label>
                        <select
                            v-model="per_page"
                            @change="filters.page = 1"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:bg-white transition-colors duration-200 text-xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Reset Filters Button -->
                <button
                    type="button"
                    @click="resetFilters"
                    class="w-full mt-3 inline-flex justify-center items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Reset Filters
                </button>

                <!-- Export Button -->
                <button
                    type="button"
                    @click="exportToExcel"
                    class="w-full mt-3 inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :disabled="loading || !receivedQuantities.data.length"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-2"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ exportButtonText }}
                </button>
            </div>
            
            <!-- Content Section -->
            <div class="flex-[0.8]">
                <!-- Transfers Table -->
                <div class="overflow-auto bg-white rounded-lg shadow-sm border border-gray-200">
                    <div
                        v-if="loading"
                        class="flex justify-center items-center p-12"
                    >
                        <div class="flex flex-col items-center space-y-3">
                            <div class="animate-spin rounded-full h-10 w-10 border-3 border-green-500 border-t-transparent"></div>
                            <p class="text-sm text-gray-500">Loading transfers...</p>
                        </div>
                    </div>

                    <div
                        v-else-if="!receivedQuantities.data.length"
                        class="text-center p-12"
                    >
                        <div class="flex flex-col items-center space-y-3">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">No received quantities found matching the criteria.</p>
                        </div>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Transfer ID
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        From
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        To
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Received Date
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Received By
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr
                                    v-for="receivedQuantity in receivedQuantities.data"
                                    :key="receivedQuantity.id"
                                    class="hover:bg-gray-50 transition-colors duration-150"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">#{{ receivedQuantity.transfer?.id || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ receivedQuantity.transfer?.ref_no || 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ receivedQuantity.product?.name || 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ receivedQuantity.product?.category?.name || 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ receivedQuantity.transfer?.from_facility?.name || receivedQuantity.transfer?.from_warehouse?.name || 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ receivedQuantity.transfer?.from_facility ? 'Facility' : receivedQuantity.transfer?.from_warehouse ? 'Warehouse' : '' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ receivedQuantity.transfer?.to_facility?.name || receivedQuantity.transfer?.to_warehouse?.name || receivedQuantity.warehouse?.name || 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ receivedQuantity.transfer?.to_facility ? 'Facility' : receivedQuantity.transfer?.to_warehouse ? 'Warehouse' : receivedQuantity.warehouse ? 'Warehouse' : '' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ receivedQuantity.quantity.toLocaleString() }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ receivedQuantity.uom || 'units' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ formatDate(receivedQuantity.received_at) }}</div>
                                        <div class="text-sm text-gray-500">{{ formatTime(receivedQuantity.received_at) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ receivedQuantity.receiver?.name || 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">Received</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-end">
            <TailwindPagination
                :data="transfers"
                @pagination-change-page="getResult"
            />
        </div>


    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { router, Head } from "@inertiajs/vue3";
import moment from "moment";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { TailwindPagination } from "laravel-vue-pagination";
import * as XLSX from "xlsx";

// Loading state
const loading = ref(false);

const props = defineProps({
    receivedQuantities: Object,
    facilities: Array,
    warehouses: Array,
    filters: Object,
});

// Form data for filters
const filters = ref({
    facility: props.filters.facility || "",
    warehouse: props.filters.warehouse || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    per_page: props.filters.per_page || "25",
});

// Computed properties
const receivedQuantities = computed(() => props.receivedQuantities);
const facilities = computed(() => props.facilities);
const warehouses = computed(() => props.warehouses);

// Debounced fetch function
let fetchTimeout = null;

const debouncedFetchData = () => {
    if (fetchTimeout) {
        clearTimeout(fetchTimeout);
    }
    
    fetchTimeout = setTimeout(() => {
        fetchData();
    }, 300);
};

// Fetch data based on filters
const fetchData = () => {
    loading.value = true;

    router.visit(route("reports.transfer-received-quantity"), {
        data: filters.value,
        preserveState: true,
        onSuccess: () => {
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

// Get results for pagination
const getResult = (page = 1) => {
    const params = { ...filters.value, page };
    
    router.visit(route("reports.transfer-received-quantity"), {
        data: params,
        preserveState: true,
    });
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        facility: "",
        warehouse: "",
        date_from: "",
        date_to: "",
        per_page: "25",
    };
};

// Helper functions
const getTotalQuantity = (receivedQuantities) => {
    if (!receivedQuantities || !receivedQuantities.data) return 0;
    return receivedQuantities.data.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return moment(dateString).format("DD/MM/YYYY");
};

const formatTime = (dateString) => {
    if (!dateString) return "N/A";
    return moment(dateString).format("HH:mm");
};

// Export to Excel
const exportToExcel = () => {
    loading.value = true;

    // Prepare the data for export
    const exportData = receivedQuantities.value.data.map((receivedQuantity, index) => ({
        SN: index + 1,
        "Transfer ID": receivedQuantity.transfer?.id || "N/A",
        "Reference No": receivedQuantity.transfer?.ref_no || "N/A",
        "Product": receivedQuantity.product?.name || "N/A",
        "Category": receivedQuantity.product?.category?.name || "N/A",
        "From": receivedQuantity.transfer?.from_facility?.name || receivedQuantity.transfer?.from_warehouse?.name || "N/A",
        "To": receivedQuantity.transfer?.to_facility?.name || receivedQuantity.transfer?.to_warehouse?.name || receivedQuantity.warehouse?.name || "N/A",
        "Quantity": receivedQuantity.quantity,
        "UOM": receivedQuantity.uom || "units",
        "Received Date": formatDate(receivedQuantity.received_at),
        "Received By": receivedQuantity.receiver?.name || "N/A",
    }));

    // Create summary data
    const summaryData = [
        ["Transfer Received Quantity Report", "", "", "", "", "", "", "", "", ""],
        ["Generated on:", formatDateTime(new Date()), "", "", "", "", "", "", "", ""],
        ["", "", "", "", "", "", "", "", "", ""],
        ["Total Records:", receivedQuantities.value.data.length, "", "", "", "", "", "", "", ""],
        ["Total Quantity Received:", getTotalQuantity(receivedQuantities.value).toLocaleString(), "", "", "", "", "", "", "", ""],
        ["", "", "", "", "", "", "", "", "", ""],
        ["", "", "", "", "", "", "", "", "", ""],
    ];

    // Add headers row
    const headers = [
        "SN",
        "Transfer ID",
        "Reference No",
        "Product",
        "Category",
        "From",
        "To",
        "Quantity",
        "UOM",
        "Received Date",
        "Received By"
    ];
    summaryData.push(headers);

    // Create worksheet
    const worksheet = XLSX.utils.aoa_to_sheet(summaryData);

    // Append the actual data
    XLSX.utils.sheet_add_json(worksheet, exportData, {
        origin: summaryData.length,
        skipHeader: true,
        header: headers,
    });

    // Style the headers
    const headerRowIndex = summaryData.length - 1;
    const headerRange = XLSX.utils.decode_range(worksheet["!ref"]);
    for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
        const cellRef = XLSX.utils.encode_cell({ r: 0, c: col });
        if (!worksheet[cellRef]) worksheet[cellRef] = {};
        worksheet[cellRef].s = { font: { bold: true, sz: 14 } };

        const headerCellRef = XLSX.utils.encode_cell({ r: headerRowIndex, c: col });
        if (!worksheet[headerCellRef]) worksheet[headerCellRef] = {};
        worksheet[headerCellRef].s = { font: { bold: true } };
    }

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Transfer Received Quantities");

    // Generate file name
    const fileName = `transfer_received_quantities_${new Date().toISOString().split('T')[0]}.xlsx`;

    // Save the file
    XLSX.writeFile(workbook, fileName);
    loading.value = false;
};

const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return "N/A";
    return moment(dateTimeString).format("DD/MM/YYYY HH:mm:ss");
};

// Computed property for export button text
const exportButtonText = computed(() => {
    return loading.value ? "Exporting..." : "Export to Excel";
});

// Watchers for automatic filtering
watch(filters, () => {
    debouncedFetchData();
}, { deep: true });

// Lifecycle hooks
onMounted(() => {
    // Auto-fetch data on mount
    fetchData();
});
</script> 