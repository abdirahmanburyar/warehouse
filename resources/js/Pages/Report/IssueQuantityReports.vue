<template>
    <Head title="Issue Quantities Report" />
    <AuthenticatedLayout
        title="Issue Quantities Report"
        description="Track all issued inventory"
        img="/assets/images/report.png"
    >
        <h2 class="text-xl font-semibold mb-4">
            Monthly Issue Quantities Report
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

                <!-- Upload Excel Button -->
                <button class="ml-2 px-4 py-2 bg-green-600 text-white rounded" @click="showUploadModal = true">
                  Upload Excel
                </button>

                <!-- Export Button (Only visible with export permission) -->
                <button
                    v-if="$page.props.auth.can.report_view"
                    type="button"
                    @click="exportToExcel"
                    class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :disabled="exportLoading"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 mr-2"
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
                <div class="overflow-auto bg-white rounded-lg shadow-sm border border-gray-200">
                    <div
                        v-if="loading"
                        class="flex justify-center items-center p-12"
                    >
                        <div class="flex flex-col items-center space-y-3">
                            <div class="animate-spin rounded-full h-10 w-10 border-3 border-indigo-500 border-t-transparent"></div>
                            <p class="text-sm text-gray-500">Loading reports...</p>
                        </div>
                    </div>

                    <div
                        v-else-if="!dataFetched"
                        class="text-center p-12"
                    >
                        <div class="flex flex-col items-center space-y-3">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">Please select filters and click Apply to view data</p>
                        </div>
                    </div>

                    <div
                        v-else-if="issueQuantityReports.data.length === 0"
                        class="text-center p-12"
                    >
                        <div class="flex flex-col items-center space-y-3">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">No monthly reports found matching the criteria.</p>
                        </div>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gradient-to-r from-red-50 to-orange-50 border-b border-gray-200">
                                <tr>
                                    <th
                                        v-for="report in issueQuantityReports.data"
                                        :key="`header-${report.id}`"
                                        class="px-6 py-4 text-center font-semibold text-gray-700 uppercase tracking-wider text-sm"
                                    >
                                        <div class="flex flex-col items-center space-y-1">
                                            <span class="font-bold text-red-600">{{ formatMonthShort(report.month_year) }}</span>
                                            <span class="text-xs text-gray-500 font-normal">{{ formatMonth(report.month_year) }}</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <!-- Quantities Row -->
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td
                                        v-for="report in issueQuantityReports.data"
                                        :key="`qty-${report.id}`"
                                        class="px-6 py-6 text-center"
                                    >
                                        <div class="flex flex-col items-center space-y-3">
                                            <span class="text-2xl font-bold text-gray-900">{{ report.total_quantity.toLocaleString() }}</span>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Items Issued</span>
                                            <button
                                                @click="viewReportItems(report)"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 border border-red-200 rounded-md text-sm font-medium text-red-700 hover:bg-red-100 hover:border-red-300 transition-colors duration-150"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Details
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="issueQuantityReports.data.length > 0" class="mt-4">
            <TailwindPagination
                :data="issueQuantityReports"
                @pagination-change-page="getResult"
            />
        </div>

        <!-- Modal for viewing report items -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl h-full max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                Issue Quantities for {{ formatMonth(currentReport?.month_year) }}
                            </h3>
                            <p class="text-sm text-gray-500">Detailed breakdown of issued quantities</p>
                        </div>
                    </div>
                    <button
                        @click="showModal = false"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-150"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
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

                <!-- Modal Content -->
                <div class="px-6 py-4 overflow-auto flex-grow">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-600">Total Items</p>
                                    <p class="text-2xl font-bold text-blue-900">{{ currentReportItems.length || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-600">Total Quantity</p>
                                    <p class="text-2xl font-bold text-red-900">{{ currentReport?.total_quantity?.toLocaleString() || 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-purple-600">Actions</p>
                                    <button
                                        v-if="$page.props.auth.can.report_view"
                                        @click="exportReportItems(currentReport.id)"
                                        class="text-sm font-medium text-purple-700 hover:text-purple-800 underline"
                                        :disabled="exportLoading"
                                    >
                                        {{ exportLoading ? 'Exporting...' : 'Download Excel' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Item Name
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Category
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Dosage Form
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Batch Number
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Expiry Date
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Unit Cost
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                            Total Cost
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr
                                        v-for="(item, index) in currentReportItems"
                                        :key="item.id"
                                        class="hover:bg-gray-50 transition-colors duration-150"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-800 text-sm font-semibold rounded-full">
                                                {{ index + 1 }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ item.product?.name || "N/A" }}</div>
                                                    <div class="text-sm text-gray-500">Product ID: {{ item.product?.id || "N/A" }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ item.product?.category?.name || "N/A" }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ item.product?.dosage?.name || "N/A" }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-lg font-bold text-gray-900">{{ item.quantity.toLocaleString() }}</span>
                                                <span class="ml-2 text-sm text-gray-500">units</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ item.batch_number || 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ item.expiry_date ? moment(item.expiry_date).format('DD/MM/YYYY') : 'N/A' }}</div>
                                            <div v-if="item.expiry_date" class="text-xs text-gray-500">
                                                {{ getExpiryStatus(item.expiry_date) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ item.unit_cost ? `$${parseFloat(item.unit_cost).toFixed(2)}` : 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ item.total_cost ? `$${parseFloat(item.total_cost).toFixed(2)}` : 'N/A' }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button
                        @click="showModal = false"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Excel Modal (TailwindCSS Dialog) -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
                <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Upload Issue Quantity Excel</h3>
                <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>
                <form @submit.prevent="submitUpload">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Month/Year</label>
                    <input v-model="uploadMonthYear" type="month" required class="border rounded px-2 py-1 w-full" name="month_year" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Excel File</label>
                    <input @change="handleFileChange" type="file" accept=".xlsx,.xls" required class="border rounded px-2 py-1 w-full" />
                </div>
                <div class="flex justify-end">
                    <button type="button" :disabled="isUploading" class="mr-2 px-3 py-1 rounded bg-gray-200" @click="showUploadModal = false">Cancel</button>
                    <button type="submit" :disabled="isUploading" class="px-3 py-1 rounded bg-indigo-600 text-white">{{ isUploading ? 'Uploading...' : 'Upload'}}</button>
                </div>
                </form>
            </div>
        </div>
        
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { router, usePage } from '@inertiajs/vue3';
import { TailwindPagination } from 'laravel-vue-pagination';
import moment from 'moment';
import * as XLSX from 'xlsx';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const toast = useToast();

// Props from controller
const props = defineProps({
    issueQuantityReports: Object,
    warehouses: Array,
    products: Array,
    filters: Object,
});

// State variables
const loading = ref(false);
const exportLoading = ref(false);
const showModal = ref(false);
const currentReport = ref(null);
const currentReportItems = ref([]);
const dataFetched = ref(false);
const showUploadModal = ref(false);
const uploadMonthYear = ref('');
const excelFile = ref(null);

// Methods
const isUploading = ref(false);
function handleFileChange(e){
    console.log(e.target.files[0]);
    excelFile.value = e.target.files[0];
}
const submitUpload = async () => {
    console.log(excelFile.value);
    const formData = new FormData();
    formData.append('month_year', uploadMonthYear.value);
    formData.append('file', excelFile.value);
    isUploading.value = true;
    await axios.post(route('reports.issue-quantity.upload'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(response => {
        isUploading.value = false;
        showUploadModal.value = false;
        Swal.fire('Success', response.data, 'success')
            .then(() => {
                router.reload();
                uploadMonthYear.value = '';
                excelFile.value = null;
            });
    })
    .catch(error => {
        isUploading.value = false;
        console.log(error);
        toast.error(error.response.data);
    });
};

// Date filter variables
const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];
const selectedYears = ref([]);
const selectedMonths = ref([]);
const yearMonthSelections = ref([]);

// Computed properties
const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => currentYear - i);
});

const dateFilters = computed(() => {
    const filters = [];
    
    // Use yearMonthSelections if available
    if (yearMonthSelections.value.length > 0) {
        yearMonthSelections.value.forEach(selection => {
            // Ensure month is padded with leading zero if needed
            const paddedMonth = String(selection.month).padStart(2, '0');
            filters.push(`${selection.year}-${paddedMonth}`);
        });
    }
    // If both years and months are selected, combine them (fallback)
    else if (selectedYears.value.length > 0 && selectedMonths.value.length > 0) {
        selectedYears.value.forEach(year => {
            selectedMonths.value.forEach(month => {
                // Ensure month is padded with leading zero if needed
                const paddedMonth = String(month + 1).padStart(2, '0');
                filters.push(`${year}-${paddedMonth}`);
            });
        });
    } 
    // If only years are selected (no specific months), add just the years
    else if (selectedYears.value.length > 0) {
        selectedYears.value.forEach(year => {
            filters.push(year.toString());
        });
    }
    
    return filters;
});

const exportButtonText = computed(() => {
    return exportLoading.value ? 'Exporting...' : 'Export to Excel';
});

const selectedReport = computed(() => {
    return currentReport.value;
});

// Methods
function toggleYearMonths(year, event) {
    // We only need to handle the unchecking case here
    // When a year is checked, we don't need to do anything special
    if (!event.target.checked) {
        // When a year is unchecked, remove all its months
        deselectAllMonthsForYear(year);
    }
}

function selectAllMonthsForYear(year) {
    // First, remove any existing selections for this year to avoid duplicates
    yearMonthSelections.value = yearMonthSelections.value.filter(selection => 
        selection.year !== year
    );
    
    // Add all months for this year
    for (let i = 0; i < 12; i++) {
        // Add the year-month combination to yearMonthSelections
        yearMonthSelections.value.push({
            year: year,
            month: i + 1 // Convert to 1-indexed for the API
        });
    }
    
    // Update the selectedMonths array (will be handled by the watcher as well)
    for (let i = 0; i < 12; i++) {
        if (!selectedMonths.value.includes(i)) {
            selectedMonths.value.push(i);
        }
    }
}

function deselectAllMonthsForYear(year) {
    // Remove all selections for this year from yearMonthSelections
    yearMonthSelections.value = yearMonthSelections.value.filter(selection => 
        selection.year !== year
    );
    
    // Recalculate which months should remain selected based on remaining selections
    const remainingMonths = new Set();
    yearMonthSelections.value.forEach(selection => {
        remainingMonths.add(selection.month - 1); // Convert to 0-indexed
    });
    
    selectedMonths.value = Array.from(remainingMonths);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return moment(dateString).format('DD/MM/YYYY');
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    return moment(dateTimeString).format('DD/MM/YYYY HH:mm:ss');
}

function formatMonth(monthYear) {
    if (!monthYear) return 'N/A';
    return moment(monthYear).format('MMMM YYYY');
}

function getExpiryStatus(expiryDate) {
    if (!expiryDate) return "N/A";
    const today = moment();
    const expiry = moment(expiryDate);
    const daysUntilExpiry = expiry.diff(today, 'days');
    
    if (daysUntilExpiry < 0) {
        return 'Expired';
    } else if (daysUntilExpiry <= 30) {
        return 'Expiring Soon';
    } else if (daysUntilExpiry <= 90) {
        return 'Expiring in 3 months';
    } else {
        return 'Valid';
    }
}

function formatMonthShort(monthYear) {
    if (!monthYear) return 'N/A';
    return moment(monthYear).format('MMM-YY');
}

function fetchData() {
    loading.value = true;
    
    router.get(
        route('reports.issueQuantityReports'),
        { date_filters: dateFilters.value },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
                dataFetched.value = true;
            },
            onError: () => {
                loading.value = false;
            },
        }
    );
}

function getResult(page) {
    loading.value = true;
    
    router.get(
        route('reports.issueQuantityReports', { page }),
        { date_filters: dateFilters.value },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
            },
            onError: () => {
                loading.value = false;
            },
        }
    );
}

function viewReportItems(report) {
    // Set the current report
    currentReport.value = report;
    
    // Use the items that are already included in the report object
    if (report.items && Array.isArray(report.items)) {
        currentReportItems.value = report.items;
    } else {
        // If for some reason items aren't included in the report object, initialize as empty array
        currentReportItems.value = [];
    }
    
    // Show the modal
    showModal.value = true;
}

function exportToExcel() {
    exportLoading.value = true;
    
    try {
        // Create a new workbook
        const wb = XLSX.utils.book_new();
        
        // Prepare data for the main summary sheet
        const summaryData = [];
        
        // Add headers
        summaryData.push(['Month', 'Total Quantity', 'Total Value']);
        
        // Add data rows
        if (props.issueQuantityReports && props.issueQuantityReports.data) {
            props.issueQuantityReports.data.forEach(report => {
                summaryData.push([
                    formatMonth(report.month_year),
                    report.total_quantity,
                    report.total_value ? `$${report.total_value.toFixed(2)}` : '$0.00'
                ]);
            });
        }
        
        // Create worksheet from data
        const ws = XLSX.utils.aoa_to_sheet(summaryData);
        
        // Set column widths
        ws['!cols'] = [
            { wch: 15 }, // Month
            { wch: 15 }, // Total Quantity
            { wch: 15 }  // Total Value
        ];
        
        // Add the worksheet to the workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Summary');
        
        // Generate Excel file and trigger download
        XLSX.writeFile(wb, `issue_quantities_report_${new Date().toISOString().split('T')[0]}.xlsx`);
    } catch (error) {
        console.error('Error exporting report:', error);
    } finally {
        exportLoading.value = false;
    }
}

function exportReportItems(reportId) {
    exportLoading.value = true;
    
    try {
        // Create a new workbook
        const wb = XLSX.utils.book_new();
        
        // Prepare data for the items sheet
        const itemsData = [];
        
        // Add headers - match the table columns exactly
        itemsData.push([
            '#', 
            'Item', 
            'Category', 
            'Dosage Form', 
            'Quantity', 
            'Batch Number',
            'Expiry Date',
            'Expiry Status',
            'Unit Cost',
            'Total Cost'
        ]);
        
        // Add data rows - match the table data structure
        if (currentReportItems.value && currentReportItems.value.length > 0) {
            currentReportItems.value.forEach((item, index) => {
                itemsData.push([
                    index + 1,
                    item.product?.name || 'N/A',
                    item.product?.category?.name || 'N/A',
                    item.product?.dosage?.name || 'N/A',
                    item.quantity,
                    item.batch_number || 'N/A',
                    item.expiry_date ? moment(item.expiry_date).format('DD/MM/YYYY') : 'N/A',
                    getExpiryStatus(item.expiry_date),
                    item.unit_cost ? parseFloat(item.unit_cost).toFixed(2) : 'N/A',
                    item.total_cost ? parseFloat(item.total_cost).toFixed(2) : 'N/A'
                ]);
            });
        }
        
        // Create worksheet from data
        const ws = XLSX.utils.aoa_to_sheet(itemsData);
        
        // Set column widths to match table structure
        ws['!cols'] = [
            { wch: 5 },  // #
            { wch: 30 }, // Item
            { wch: 20 }, // Category
            { wch: 20 }, // Dosage Form
            { wch: 10 }, // Quantity
            { wch: 15 }, // Batch Number
            { wch: 15 }, // Expiry Date
            { wch: 15 }, // Expiry Status
            { wch: 12 }, // Unit Cost
            { wch: 12 }  // Total Cost
        ];
        
        // Style the header row
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const address = XLSX.utils.encode_cell({ r: 0, c: C });
            if (!ws[address]) continue;
            ws[address].s = {
                font: { bold: true },
                fill: { fgColor: { rgb: "E9E9E9" } }
            };
        }
        
        // Add the worksheet to the workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Items');
        
        // Get the report month/year for the filename
        const reportName = currentReport.value ? 
            formatMonth(currentReport.value.month_year).replace(' ', '_') : 
            reportId;
        
        // Generate Excel file and trigger download
        XLSX.writeFile(wb, `issue_quantities_items_${reportName}.xlsx`);
    } catch (error) {
        console.error('Error exporting report items:', error);
    } finally {
        exportLoading.value = false;
    }
}

// Watch for changes in yearMonthSelections
watch(yearMonthSelections, (newSelections) => {
    // Update selectedYears and selectedMonths based on yearMonthSelections
    const years = new Set();
    const months = new Set();
    
    newSelections.forEach(selection => {
        years.add(selection.year);
        months.add(selection.month - 1); // Convert to 0-indexed for consistency
    });
    
    selectedYears.value = Array.from(years);
    selectedMonths.value = Array.from(months);
});

// Initialize from URL params
onMounted(() => {
    // Check if we have filters from the URL
    if (props.filters && props.filters.month) {
        const [year, month] = props.filters.month.split('-');
        if (year && !isNaN(parseInt(year))) {
            const yearInt = parseInt(year);
            selectedYears.value.push(yearInt);
            
            if (month && !isNaN(parseInt(month))) {
                const monthInt = parseInt(month);
                selectedMonths.value.push(monthInt - 1); // Convert to 0-indexed
                
                // Also add to yearMonthSelections
                yearMonthSelections.value.push({
                    year: yearInt,
                    month: monthInt
                });
            }
        }
    }
});
</script>
