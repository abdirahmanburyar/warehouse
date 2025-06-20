<template>
    <Head title="Received Quantities Report" />
    <AuthenticatedLayout
        title="Received Quantities Report"
        description="Track all received inventory"
        img="/assets/images/report.png"
    >
        <!-- <div class="mb-[100px]"> -->
            <h2 class="text-xl font-semibold mb-4">
                Monthly Received Quantities Report
            </h2>

            <!-- Main Content with adjusted split -->
            <div class="flex">
                <!-- Filter Section -->
                <div class="flex-[0.1] pr-4">
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h3 class="text-lg font-medium mb-3">Filters</h3>

                        <!-- Years with nested months -->
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Filter by Year & Month</label
                            >
                            <div
                                class="max-h-80 overflow-y-auto border border-gray-300 rounded-md p-2"
                            >
                                <div
                                    v-for="year in availableYears"
                                    :key="year"
                                    class="mb-3"
                                >
                                    <div class="flex items-center mb-1">
                                        <input
                                            type="checkbox"
                                            :id="`year-${year}`"
                                            :value="year"
                                            v-model="selectedYears"
                                            @change="
                                                toggleYearMonths(year, $event)
                                            "
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        />
                                        <label
                                            :for="`year-${year}`"
                                            class="ml-2 block text-sm font-medium text-gray-900"
                                            >{{ year }}</label
                                        >
                                    </div>

                                    <!-- Months for this year -->
                                    <div
                                        v-if="selectedYears.includes(year)"
                                        class="ml-6 mt-1 border-l-2 border-gray-200 pl-2"
                                    >
                                        <div class="mb-1 flex justify-end">
                                            <button
                                                type="button"
                                                @click="
                                                    selectAllMonthsForYear(year)
                                                "
                                                class="text-xs text-indigo-600 hover:text-indigo-800"
                                            >
                                                Select All
                                            </button>
                                            <span class="mx-1">|</span>
                                            <button
                                                type="button"
                                                @click="
                                                    deselectAllMonthsForYear(
                                                        year
                                                    )
                                                "
                                                class="text-xs text-indigo-600 hover:text-indigo-800"
                                            >
                                                Clear
                                            </button>
                                        </div>
                                        <div class="flex flex-col gap-y-1">
                                            <div
                                                v-for="(month, index) in months"
                                                :key="`${year}-${index}`"
                                                class="flex items-center"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :id="`year-${year}-month-${index}`"
                                                    :value="{
                                                        year: year,
                                                        month: index + 1,
                                                    }"
                                                    v-model="
                                                        yearMonthSelections
                                                    "
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                />
                                                <label
                                                    :for="`year-${year}-month-${index}`"
                                                    class="ml-2 block text-sm text-gray-900 truncate"
                                                    >{{ month }}</label
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Button -->
                    <button
                        type="button"
                        @click="fetchData"
                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :disabled="loading"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-2"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                            />
                        </svg>
                        Apply Filter
                    </button>

                    <!-- Export Button (Only visible with export permission) -->
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
                    <!-- Monthly Reports Table - Column-Based Design -->
                    <div class="overflow-auto bg-white p-4 rounded-md shadow-sm">
                    <div
                        v-if="loading"
                        class="flex justify-center items-center p-8"
                    >
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"
                        ></div>
                    </div>

                    <div
                        v-else-if="!dataFetched"
                        class="text-center p-8 text-gray-500"
                    >
                        Please select filters and click Apply to view data
                    </div>

                    <table
                        v-else
                        class="min-w-full border-collapse border border-gray-300"
                    >
                        <thead>
                            <tr>
                                <template
                                    v-if="receivedQuantities.data.length === 0"
                                >
                                    <th
                                        class="border border-gray-300 p-2 text-center font-medium"
                                    >
                                        No Data Available
                                    </th>
                                </template>
                                <template v-else>
                                    <th
                                        v-for="report in receivedQuantities.data"
                                        :key="`header-${report.id}`"
                                        class="border border-gray-300 p-2 text-center font-medium"
                                    >
                                        {{
                                            formatMonthShort(report.month_year)
                                        }}
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="receivedQuantities.data.length === 0">
                                <td
                                    class="border border-gray-300 p-4 text-center text-gray-500"
                                >
                                    No monthly reports found matching the
                                    criteria.
                                </td>
                            </tr>
                            <template v-else>
                                <!-- Quantities Row -->
                                <tr>
                                    <td
                                        v-for="report in receivedQuantities.data"
                                        :key="`qty-${report.id}`"
                                        class="border border-gray-300 p-2 text-center"
                                    >
                                        {{
                                            report.total_quantity.toLocaleString()
                                        }}
                                    </td>
                                </tr>
                                <!-- Actions Row -->
                                <tr>
                                    <td
                                        v-for="report in receivedQuantities.data"
                                        :key="`action-${report.id}`"
                                        class="border border-gray-300 p-2 text-center"
                                    >
                                        <button
                                            v-if="
                                                $page.props.auth.can.report_view
                                            "
                                            @click="viewReportItems(report)"
                                            class="text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            view
                                        </button>
                                        <template
                                            v-if="
                                                $page.props.auth.can.report_view
                                            "
                                            >/</template
                                        >
                                        <button
                                            @click="downloadReportItems(report)"
                                            class="text-green-600 hover:text-green-800 font-medium"
                                        >
                                            download
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-end">
            <TailwindPagination
                :data="receivedQuantities"
                @pagination-change-page="getResult"
            />
        </div>

        <!-- Modal for viewing report items -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div
                class="bg-white rounded-lg shadow-lg w-full h-full max-h-screen overflow-hidden flex flex-col"
            >
                <div
                    class="px-6 py-4 border-b border-gray-200 flex justify-between items-center"
                >
                    <h3 class="text-lg font-medium text-gray-900">
                        Received Items for
                        {{ formatMonthYear(selectedReport?.month_year) }}
                    </h3>
                    <button
                        @click="showModal = false"
                        class="text-gray-400 hover:text-gray-500"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
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
                <div class="px-6 py-4 overflow-auto flex-grow">
                    <div class="flex justify-between mb-4">
                        <div>
                            <span class="font-semibold">Total Items:</span>
                            {{ selectedReport?.items?.length || 0 }}
                        </div>
                        <div>
                            <span class="font-semibold">Total Quantity:</span>
                            {{
                                selectedReport?.total_quantity?.toLocaleString() ||
                                0
                            }}
                        </div>
                        <button
                            @click="downloadReportItems(selectedReport)"
                            class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Download Excel
                        </button>
                    </div>
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-black">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider border border-black"
                                >
                                    #
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider border border-black"
                                >
                                    Item
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider border border-black"
                                >
                                    Category
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider border border-black"
                                >
                                    Dosage
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider border border-black"
                                >
                                    Quantity
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, i) in selectedReport?.items"
                                :key="item.id"
                                class="hover:bg-gray-50 border border-black"
                            >
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 border border-black"
                                >
                                    {{ i + 1 }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 border border-black"
                                >
                                    {{ item.product?.name || "N/A" }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 border border-black"
                                >
                                    {{ item.product?.category?.name || "N/A" }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 border border-black"
                                >
                                    {{ item.product?.dosage?.name || "N/A" }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 border border-black"
                                >
                                    {{ item.quantity.toLocaleString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 text-right">
                    <button
                        @click="showModal = false"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Close
                    </button>
                </div>
            </div>
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

// Loading state for export
const loading = ref(false);
const showModal = ref(false);
const selectedReport = ref(null);

const props = defineProps({
    receivedQuantities: Object,
    warehouses: Array,
    products: Array,
    filters: Object,
});

// Extract warehouses and products from props for easier access
const warehouses = ref(props.warehouses || []);
const products = ref(props.products || []);

// Form data for filters
const filters = ref({
    per_page: props.filters.per_page || "10",
});

// Track if data has been fetched
const dataFetched = ref(false);

// Arrays for selected years and year-month combinations
const selectedYears = ref([]);
const yearMonthSelections = ref([]);

// Helper functions for year-month selections
const toggleYearMonths = (year, event) => {
    // When a year is deselected, remove all its month selections
    if (!event.target.checked) {
        yearMonthSelections.value = yearMonthSelections.value.filter(
            (item) => item.year !== year
        );
    }
};

const selectAllMonthsForYear = (year) => {
    // First remove any existing selections for this year
    yearMonthSelections.value = yearMonthSelections.value.filter(
        (item) => item.year !== year
    );

    // Then add all months for this year
    for (let i = 1; i <= 12; i++) {
        yearMonthSelections.value.push({ year, month: i });
    }
};

const deselectAllMonthsForYear = (year) => {
    // Remove all month selections for this year
    yearMonthSelections.value = yearMonthSelections.value.filter(
        (item) => item.year !== year
    );
};

// Computed property for the receivedQuantities
const receivedQuantities = computed(() => props.receivedQuantities);

// Generate available years (current year + 5 years)
const currentYear = new Date().getFullYear();
const availableYears = computed(() => {
    const years = [];
    for (let i = currentYear - 5; i <= currentYear + 5; i++) {
        years.push(i);
    }
    return years;
});

// Month names
const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
];

// Reset filters to default values
const resetFilters = () => {
    filters.value = {
        year: new Date().getFullYear(),
        month: "",
        per_page: "10",
    };
};

// Fetch data based on filters
const fetchData = () => {
    loading.value = true;

    // Prepare filter data
    const filterData = { ...filters.value };

    // Create an array to store formatted date filters
    const dateFilters = [];

    // Process selected years and year-month combinations
    if (selectedYears.value.length > 0) {
        // Process specific year-month selections
        if (yearMonthSelections.value.length > 0) {
            // Add each specific year-month combination
            yearMonthSelections.value.forEach((selection) => {
                const monthStr = selection.month.toString().padStart(2, "0");
                dateFilters.push(`${selection.year}-${monthStr}`);
            });
        } else {
            // No specific months selected, add each selected year
            selectedYears.value.forEach((year) => {
                dateFilters.push(year.toString());
            });
        }
    }

    // Add the date filters to the request
    if (dateFilters.length > 0) {
        filterData.date_filters = dateFilters;
    }

    router.visit(route("reports.receivedQuantities"), {
        data: filterData,
        preserveState: true,
        onSuccess: () => {
            loading.value = false;
            dataFetched.value = true;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

// Get results for pagination
const getResult = (page = 1) => {
    const params = { ...filters.value, page };

    // Create an array to store formatted date filters
    const dateFilters = [];

    // Process selected years and year-month combinations
    if (selectedYears.value.length > 0) {
        // Process specific year-month selections
        if (yearMonthSelections.value.length > 0) {
            // Add each specific year-month combination
            yearMonthSelections.value.forEach((selection) => {
                const monthStr = selection.month.toString().padStart(2, "0");
                dateFilters.push(`${selection.year}-${monthStr}`);
            });
        } else {
            // No specific months selected, add each selected year
            selectedYears.value.forEach((year) => {
                dateFilters.push(year.toString());
            });
        }
    }

    // Add the date filters to the request
    if (dateFilters.length > 0) {
        params.date_filters = dateFilters;
    }

    router.visit(route("reports.receivedQuantities"), {
        data: params,
        preserveState: true,
    });
};

// Methods for viewing and exporting report items
const viewReportItems = (report) => {
    selectedReport.value = report;
    showModal.value = true;
};

const downloadReportItems = (report) => {
    loading.value = true;

    // Prepare the data for export
    const exportData = report.items.map((item, index) => ({
        SN: index + 1,
        Item: item.product ? item.product.name : "N/A",
        Category: item.product?.category?.name || "N/A",
        "Dosage Form": item.product?.dosage?.name || "N/A",
        Quantity: item.quantity
    }));

    // Calculate total quantity
    const totalQty = exportData.reduce(
        (sum, item) => sum + Number(item["Quantity"] || 0),
        0
    );

    // Create summary data
    const summaryData = [
        [
            "Monthly Received Quantities Report - Detailed Items",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        ],
        [
            "Generated on:",
            formatDateTime(new Date()),
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        ],
        ["", "", "", "", "", "", "", "", "", "", ""],
        [
            "Month/Year:",
            formatMonthYear(report.month_year),
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        ],
        [
            "Total Quantity Received:",
            totalQty.toLocaleString(),
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        ],
        [
            "Generated By:",
            report.user ? report.user.name : "System",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        ],
        [
            "Generated At:",
            formatDateTime(report.created_at),
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        ],
        ["", "", "", "", "", "", "", "", "", "", ""],
        ["", "", "", "", "", "", "", "", "", "", ""],
    ];

    // Add headers row
    const headers = [
        "SN",
        "Item",
        "Category",
        "Dosage Form",
        "Quantity"
    ];
    summaryData.push(headers);

    // Create worksheet with summary data first
    const worksheet = XLSX.utils.aoa_to_sheet(summaryData);

    // Append the actual data starting after the summary (skipHeader=true to avoid duplicate headers)
    XLSX.utils.sheet_add_json(worksheet, exportData, {
        origin: summaryData.length,
        skipHeader: true,
        header: headers,
    });

    // Style the headers (make them bold)
    const headerRowIndex = summaryData.length - 1;
    const headerRange = XLSX.utils.decode_range(worksheet["!ref"]);
    for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
        const cellRef = XLSX.utils.encode_cell({ r: 0, c: col }); // Title row
        if (!worksheet[cellRef]) worksheet[cellRef] = {};
        worksheet[cellRef].s = { font: { bold: true, sz: 14 } };

        const headerCellRef = XLSX.utils.encode_cell({
            r: headerRowIndex,
            c: col,
        }); // Headers row
        if (!worksheet[headerCellRef]) worksheet[headerCellRef] = {};
        worksheet[headerCellRef].s = { font: { bold: true } };
    }

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Detailed Items");

    // Generate file name with month/year
    const fileName = `monthly_received_items_${report.month_year}.xlsx`;

    // Save the file
    XLSX.writeFile(workbook, fileName);
    loading.value = false;
};

// Method to export all monthly reports
const exportToExcel = () => {
    // Get all data (not just current page) by removing pagination
    const exportFilters = { ...filters.value };

    // Create an array to store formatted date filters
    const dateFilters = [];

    // Process selected years and year-month combinations
    if (selectedYears.value.length > 0) {
        // Process specific year-month selections
        if (yearMonthSelections.value.length > 0) {
            // Add each specific year-month combination
            yearMonthSelections.value.forEach((selection) => {
                const monthStr = selection.month.toString().padStart(2, "0");
                dateFilters.push(`${selection.year}-${monthStr}`);
            });
        } else {
            // No specific months selected, add each selected year
            selectedYears.value.forEach((year) => {
                dateFilters.push(year.toString());
            });
        }
    }

    // Add the date filters to the request
    if (dateFilters.length > 0) {
        exportFilters.date_filters = dateFilters;
    }

    // Add a large per_page value to get all data
    exportFilters.per_page = 10000;

    // Set loading state
    loading.value = true;

    // Fetch all data for export
    router.visit(route("reports.receivedQuantities"), {
        data: exportFilters,
        only: ["receivedQuantities"],
        preserveState: true,
        onSuccess: (page) => {
            // Prepare the data for export
            const exportData = page.props.receivedQuantities.data.map(
                (item, index) => ({
                    SN: index + 1,
                    "Month/Year": formatMonthYear(item.month_year),
                    "Total Quantity": item.total_quantity,
                    "Generated By": item.user ? item.user.name : "System",
                    "Generated At": formatDateTime(item.created_at),
                })
            );

            // Create summary data
            const summaryData = [
                ["Monthly Received Quantities Reports", "", "", "", ""],
                ["Generated on:", formatDateTime(new Date()), "", "", ""],
                ["", "", "", "", ""],
            ];

            // Add filter information if applicable
            if (selectedYears.value.length > 0) {
                // Add selected years
                summaryData.push([
                    "Selected Years:",
                    selectedYears.value.join(", "),
                    "",
                    "",
                    "",
                ]);

                // Add selected year-month combinations if any
                if (yearMonthSelections.value.length > 0) {
                    // Group by year for better readability
                    const yearMonthMap = {};

                    yearMonthSelections.value.forEach((selection) => {
                        if (!yearMonthMap[selection.year]) {
                            yearMonthMap[selection.year] = [];
                        }
                        yearMonthMap[selection.year].push(
                            months[selection.month - 1]
                        );
                    });

                    // Add each year's selected months
                    Object.keys(yearMonthMap).forEach((year) => {
                        summaryData.push([
                            `${year} Months:`,
                            yearMonthMap[year].join(", "),
                            "",
                            "",
                            "",
                        ]);
                    });
                }

                summaryData.push(["", "", "", "", ""]);
            }

            // Add empty row before data
            summaryData.push(["", "", "", "", ""]);
            summaryData.push(["", "", "", "", ""]);

            // Add headers row
            const headers = [
                "SN",
                "Month/Year",
                "Total Quantity",
                "Generated By",
                "Generated At",
            ];
            summaryData.push(headers);

            // Create worksheet with summary data first
            const worksheet = XLSX.utils.aoa_to_sheet(summaryData);

            // Append the actual data starting after the summary (skipHeader=true to avoid duplicate headers)
            XLSX.utils.sheet_add_json(worksheet, exportData, {
                origin: summaryData.length,
                skipHeader: true,
                header: headers,
            });

            // Style the headers (make them bold)
            const headerRowIndex = summaryData.length - 1;
            const headerRange = XLSX.utils.decode_range(worksheet["!ref"]);
            for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
                const cellRef = XLSX.utils.encode_cell({ r: 0, c: col }); // Title row
                if (!worksheet[cellRef]) worksheet[cellRef] = {};
                worksheet[cellRef].s = { font: { bold: true, sz: 14 } };

                const headerCellRef = XLSX.utils.encode_cell({
                    r: headerRowIndex,
                    c: col,
                }); // Headers row
                if (!worksheet[headerCellRef]) worksheet[headerCellRef] = {};
                worksheet[headerCellRef].s = { font: { bold: true } };
            }

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(
                workbook,
                worksheet,
                "Monthly Reports"
            );

            // Generate file name with current date and applied filters
            const date = new Date().toISOString().split("T")[0];
            let fileName = `monthly_received_reports_${date}`;

            // Add year information if years are selected
            if (selectedYears.value.length > 0) {
                if (selectedYears.value.length === 1) {
                    fileName += `_year_${selectedYears.value[0]}`;
                } else {
                    fileName += `_years_${selectedYears.value.length}`;
                }
            }

            // Add month information if specific year-month combinations are selected
            if (yearMonthSelections.value.length > 0) {
                // Count unique months
                const uniqueMonths = new Set(
                    yearMonthSelections.value.map((ym) => ym.month)
                );

                if (uniqueMonths.size === 1) {
                    const monthNum = [...uniqueMonths][0];
                    const monthName = months[monthNum - 1].substring(0, 3);
                    fileName += `_month_${monthName}`;
                } else {
                    fileName += `_months_${uniqueMonths.size}`;
                }
            }

            fileName += ".xlsx";

            // Save the file
            XLSX.writeFile(workbook, fileName);
            loading.value = false;
        },
        onError: (errors) => {
            console.error("Error exporting data:", errors);
            loading.value = false;
        },
    });
};

// Formatting helpers
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return moment(dateString).format("DD/MM/YYYY");
};

const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return "N/A";
    return moment(dateTimeString).format("DD/MM/YYYY HH:mm:ss");
};

const formatReceivedAt = (dateTimeString) => {
    if (!dateTimeString) return "N/A";
    return moment(dateTimeString).format("DD/MM/YYYY");
};

const formatMonthYear = (monthYearString) => {
    if (!monthYearString) return "N/A";
    return moment(monthYearString).format("MMMM YYYY");
};

const formatMonthShort = (monthYearString) => {
    if (!monthYearString) return "N/A";
    return moment(monthYearString).format("MMM-YY");
};

// Computed property for export button text
const exportButtonText = computed(() => {
    return loading.value ? "Exporting..." : "Export to Excel";
});

// Calculate total quantity received
const totalQuantity = computed(() => {
    if (!props.receivedQuantities || !props.receivedQuantities.data) return 0;
    return props.receivedQuantities.data
        .reduce((sum, item) => sum + Number(item.total_quantity || 0), 0)
        .toLocaleString();
});

// Lifecycle hooks
onMounted(() => {
    // Initialize with dataFetched as false - user must apply filter first
    dataFetched.value = false;

    // Set current year as the default selected year
    const currentYear = new Date().getFullYear();
    selectedYears.value = [currentYear];

    // Set current month as default selected month for current year
    const currentMonth = new Date().getMonth() + 1; // JavaScript months are 0-indexed
    yearMonthSelections.value = [{ year: currentYear, month: currentMonth }];
});
</script>
