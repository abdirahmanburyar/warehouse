<template>
    <Head title="LMIS Monthly Report" />
    <AuthenticatedLayout
        title="LMIS Monthly Report"
        description="LMIS Monthly Report"
        img="/assets/images/report.png"
    >
        <div class="flex flex-col lg:flex-row gap-6 p-1 overflow-auto">
            <!-- Filter Section (300px width) -->
            <div
                class="w-full lg:w-[300px] bg-white rounded-lg shadow p-1 h-fit"
            >
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Filters</h2>
                    <button
                        @click="clearFilters"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        Clear All
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">
                            Select Facilities by District
                        </h3>
                        <div
                            v-for="(
                                facilities, districtName
                            ) in facilitiesGrouped"
                            :key="districtName"
                            class="mb-2"
                        >
                            <!-- District header with toggle -->
                            <button
                                @click="toggleDistrict(districtName)"
                                class="w-full text-left px-4 py-3 bg-blue-600 text-white font-bold rounded-t hover:bg-blue-700 flex justify-between items-center"
                            >
                                {{ districtName }}
                                <svg
                                    :class="{
                                        'transform rotate-180':
                                            isOpen(districtName),
                                    }"
                                    class="w-5 h-5 transition-transform duration-200"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </button>

                            <transition name="collapse">
                                <div
                                    v-show="isOpen(districtName)"
                                    class="p-2 bg-white rounded-b"
                                >
                                    <!-- Search Bar -->
                                    <div class="relative mb-3">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <svg
                                                class="h-4 w-4 text-gray-400"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                />
                                            </svg>
                                        </div>
                                        <input
                                            type="text"
                                            v-model="
                                                searchQueries[districtName]
                                            "
                                            :placeholder="`Search ${districtName} facilities...`"
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>

                                    <div class="text-xs text-gray-500 mb-2">
                                        {{
                                            filteredFacilities(
                                                facilities,
                                                districtName
                                            ).length
                                        }}
                                        facilities found
                                    </div>

                                    <div
                                        class="flex flex-col gap-2 max-h-80 overflow-y-auto pr-2"
                                    >
                                        <div
                                            v-for="facility in filteredFacilities(
                                                facilities,
                                                districtName
                                            )"
                                            :key="facility.id"
                                            class="flex items-center space-x-2 border rounded p-2 shadow-sm hover:bg-gray-50 w-full"
                                        >
                                            <input
                                                type="radio"
                                                :id="`facility-${facility.id}`"
                                                v-model="selectedFacility"
                                                :value="facility.id"
                                                class="form-radio text-blue-600"
                                                name="facility-selection"
                                            />
                                            <label
                                                :for="`facility-${facility.id}`"
                                                class="text-sm"
                                            >
                                                {{ facility.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col mt-4">
                <h1>LMIS Monthly Report</h1>
                <div>
                    <div class="flex flex-col">
                        <label for="month_year">Month Year</label>
                        <input
                            type="month"
                            id="month_year"
                            v-model="month_year"
                            class="w-[300px]"
                        />
                    </div>
                </div>
                <div class="flex gap-2 mt-2">
                    <button
                        :disabled="isLoading"
                        @click="getReport"
                        class="bg-blue-500 text-white px-4 py-2 rounded flex-1"
                    >
                        <span v-if="!isLoading">Get Report</span>
                        <span v-else>Please wait...</span>
                    </button>
                    <button
                        v-if="report"
                        @click="exportToExcel"
                        class="bg-green-600 text-white px-4 py-2 rounded flex items-center gap-1"
                        :disabled="isLoading"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </button>
                    <button
                        v-if="report"
                        @click="exportToPdf"
                        class="bg-red-600 text-white px-4 py-2 rounded flex items-center gap-1"
                        :disabled="isLoading"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>
        </div>
        <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <span class="ml-3 text-gray-600">Loading report...</span>
        </div>
        <div v-else-if="!report" class="text-center py-12">
            <div class="max-w-md mx-auto p-6 bg-white rounded-lg shadow-sm">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    {{ props.filters.facility && month_year ? 'No report found' : 'No report selected' }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    <template v-if="props.filters.facility && month_year">
                        No report found for the selected facility and month/year.
                    </template>
                    <template v-else>
                        Select a facility and date range to view the report.
                    </template>
                </p>
            </div>
        </div>
        <div v-else class="mb-[80px]">
            <div class="mb-2">
                <!-- Facility Info Section -->
                <div
                    class="bg-white border border-gray-200 rounded p-6 shadow-sm"
                >
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Facility Information
                    </h2>
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm"
                    >
                        <div>
                            <span class="font-medium text-gray-600">Name:</span>
                            {{ props.report?.facility?.name }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-600"
                                >District:</span
                            >
                            {{ props.report?.facility?.district }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-600"
                                >Facility Type:</span
                            >
                            {{ props.report?.facility?.facility_type }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-600"
                                >Cold Storage:</span
                            >
                            {{
                                props.report?.facility?.has_cold_storage ? "Yes" : "No"
                            }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-600"
                                >Active:</span
                            >
                            {{ props.report?.facility?.is_active ? "Yes" : "No" }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-600"
                                >Phone:</span
                            >
                            {{ props.report?.facility?.phone || "—" }}
                        </div>
                        <div>
                            <span class="font-medium text-gray-600"
                                >Email:</span
                            >
                            {{ props.report?.facility?.email || "—" }}
                        </div>
                        <div class="col-span-full">
                            <span class="font-medium text-gray-600"
                                >Address:</span
                            >
                            {{ props.report?.facility?.address || "—" }}
                        </div>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Report Period:</span>
                        {{ props.report?.report_period }}
                    </div>
                </div>


                <!-- Approval Timeline -->
                <div
                    class="bg-white border border-gray-200 rounded p-6 shadow-sm"
                >
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Approval Timeline
                    </h2>
                    <ol
                        class="relative border-l border-gray-300 pl-4 space-y-6 text-sm text-gray-700"
                    >
                    <li v-if="props.report?.approved_by" class="ml-2">
                            <div
                                class="absolute -left-1.5 top-0.5 w-3 h-3 bg-green-500 rounded-full border border-white"
                            ></div>
                            <p class="font-medium">Approved</p>
                            <p class="text-xs">
                                {{ props.report?.approved_by?.name }} on
                                {{ formatDate(props.report?.approved_at) }}
                            </p>
                        </li>
                        <li v-if="props.report?.reviewed_by" class="ml-2">
                            <div
                                class="absolute -left-1.5 top-0.5 w-3 h-3 bg-yellow-500 rounded-full border border-white"
                            ></div>
                            <p class="font-medium">Reviewed</p>
                            <p class="text-xs">
                                {{ props.report?.reviewed_by?.name }} on
                                {{ formatDate(props.report?.reviewed_at) }}
                            </p>
                        </li>
                        <li class="ml-2">
                            <div
                                class="absolute -left-1.5 top-0.5 w-3 h-3 bg-blue-500 rounded-full border border-white"
                            ></div>
                            <p class="font-medium">Submitted</p>
                            <p class="text-xs">
                                {{ props.report?.submitted_by?.name }} on
                                {{ formatDate(props.report?.submitted_at) }}
                            </p>
                        </li>

                        <li v-if="props.report?.rejected_by" class="ml-2">
                            <div
                                class="absolute -left-1.5 top-0.5 w-3 h-3 bg-red-500 rounded-full border border-white"
                            ></div>
                            <p class="font-medium">Rejected</p>
                            <p class="text-xs">
                                {{ props.report?.rejected_by?.name }} on
                                {{ formatDate(props.report?.rejected_at) }}
                            </p>
                        </li>
                    </ol>
                </div>

                <!-- Action Buttons for Review/Approve/Reject -->
                <div v-if="canShowActions" class="bg-white border border-gray-200 rounded p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Actions</h2>
                    <div class="flex gap-3">
                        <!-- Review Button -->
                        <button 
                            v-if="canReview"
                            @click="reviewReport"
                            :disabled="isProcessing"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isProcessing ? 'Processing...' : 'Review Report' }}
                        </button>

                        <!-- Approve Button -->
                        <button 
                            v-if="canApprove"
                            @click="approveReport"
                            :disabled="isProcessing"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isProcessing ? 'Processing...' : 'Approve Report' }}
                        </button>

                        <!-- Reject Button -->
                        <button 
                            v-if="canReject"
                            @click="rejectReport"
                            :disabled="isProcessing"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            {{ isProcessing ? 'Processing...' : 'Reject Report' }}
                        </button>
                    </div>
                    
                    <!-- Workflow Timeline -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Approval Workflow</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <!-- Draft Stage (Current Status) -->
                            <div v-if="props.report?.status === 'draft'" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-semibold text-gray-800">Draft</span>
                                </div>
                                <div class="text-xs text-gray-700">
                                    <div>Created on {{ formatDateTime(props.report?.created_at) }}</div>
                                    <div class="text-gray-500">Pending submission</div>
                                </div>
                            </div>

                            <!-- Submitted Stage -->
                            <div v-if="props.report?.submitted_at" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-semibold text-blue-800">Submitted</span>
                                </div>
                                <div class="text-xs text-blue-700">
                                    <div>On {{ formatDateTime(props.report?.submitted_at) }}</div>
                                    <div v-if="props.report?.submitted_by">By {{ props.report?.submitted_by?.name || 'Unknown' }}</div>
                                </div>
                            </div>

                            <!-- Reviewed Stage -->
                            <div v-if="props.report?.reviewed_at" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-semibold text-yellow-800">Reviewed</span>
                                </div>
                                <div class="text-xs text-yellow-700">
                                    <div>On {{ formatDateTime(props.report?.reviewed_at) }}</div>
                                    <div v-if="props.report?.reviewed_by">By {{ props.report?.reviewed_by?.name || 'Unknown' }}</div>
                                </div>
                            </div>

                            <!-- Approved Stage -->
                            <div v-if="props.report?.approved_at" class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="font-semibold text-green-800">Approved</span>
                                </div>
                                <div class="text-xs text-green-700">
                                    <div>On {{ formatDateTime(props.report?.approved_at) }}</div>
                                    <div v-if="props.report?.approved_by">By {{ props.report?.approved_by?.name || 'Unknown' }}</div>
                                </div>
                            </div>

                            <!-- Rejected Stage -->
                            <div v-if="props.report?.rejected_at" class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-semibold text-red-800">Rejected</span>
                                </div>
                                <div class="text-xs text-red-700">
                                    <div>On {{ formatDateTime(props.report?.rejected_at) }}</div>
                                    <div v-if="props.report?.rejected_by">By {{ props.report?.rejected_by?.name || 'Unknown' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Status Badge -->
                        <div class="mt-4 flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-600">Current Status:</span>
                            <span :class="getStatusClass(props.report?.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                {{ getStatusLabel(props.report?.status) }}
                            </span>
                        </div>
                    </div>
                </div>


            </div>
            <!-- Report Items -->
            <div class="mb-2 overflow-x-auto">
                <table
                    class="min-w-full bg-white border border-gray-200 text-xs"
                >
                    <thead
                        class="bg-gray-100 text-left text-gray-600 capitalize tracking-wider"
                    >
                        <tr>
                            <th class="px-4 py-2">Item</th>
                            <th class="px-4 py-2">Opening Balance</th>
                            <th class="px-4 py-2">Stock Received</th>
                            <th class="px-4 py-2">Stock Issued</th>
                            <th class="px-4 py-2">+ Adjustments</th>
                            <th class="px-4 py-2">– Adjustments</th>
                            <th class="px-4 py-2">Closing Balance</th>
                            <th class="px-4 py-2">Stockout Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in props.report?.items"
                            :key="item.id"
                            class="border-t hover:bg-gray-50"
                        >
                            <td class="px-4 py-2">
                                {{ item.product?.name }}
                            </td>
                            <td class="px-4 py-2">
                                {{ item.opening_balance }}
                            </td>
                            <td class="px-4 py-2">
                                {{ item.stock_received }}
                            </td>
                            <td class="px-4 py-2">
                                {{ item.stock_issued }}
                            </td>
                            <td class="px-4 py-2">
                                {{ item.positive_adjustments }}
                            </td>
                            <td class="px-4 py-2">
                                {{ item.negative_adjustments }}
                            </td>
                            <td class="px-4 py-2">
                                {{ item.closing_balance }}
                            </td>
                            <td class="px-4 py-2">
                                {{ item.stockout_days }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Comments -->
            <div
                v-if="props.report?.comments"
                :class="props.report?.status === 'rejected' 
                    ? 'mt-6 p-4 bg-red-50 border border-red-200 rounded-lg' 
                    : 'mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg'"
            >
                <div class="flex items-center gap-2 mb-2">
                    <svg v-if="props.report?.status === 'rejected'" class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <h3 :class="props.report?.status === 'rejected' 
                        ? 'font-semibold text-red-800' 
                        : 'font-semibold text-gray-800'">
                        {{ props.report?.status === 'rejected' ? 'Rejection Reason' : 'Comments' }}
                    </h3>
                </div>
                <p :class="props.report?.status === 'rejected' 
                    ? 'text-sm text-red-700' 
                    : 'text-sm text-gray-700'">{{ props.report?.comments }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, computed } from "vue";
import moment from "moment";
import * as XLSX from 'xlsx';
import { jsPDF } from 'jspdf';
import 'jspdf-autotable';
import autoTable from 'jspdf-autotable';
import Swal from 'sweetalert2';
import axios from 'axios';


const props = defineProps({
    facilitiesGrouped: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            facility: null,
            month_year: "", // Default to current year-month
        }),
    },
    report: Object,
});

// Initialize search queries when component mounts
onMounted(() => {
    initializeSearchQueries(Object.keys(props.facilitiesGrouped));
});
const selectedFacility = ref(props.filters.facility);
const isLoading = ref(false);
const searchQueries = ref({}); // Store search queries for each district

// Track open/closed districts with a Set for O(1) toggle
const openDistricts = ref(new Set());

// Review/Approve/Reject functionality
const isProcessing = ref(false);

// Initialize search queries for each district
const initializeSearchQueries = (districts) => {
    districts.forEach((district) => {
        searchQueries.value[district] = "";
    });
};

// Filter facilities based on search query
const filteredFacilities = (facilities, districtName) => {
    const query = (searchQueries.value[districtName] || "").toLowerCase();
    if (!query) return facilities;
    return facilities.filter((facility) =>
        facility.name.toLowerCase().includes(query)
    );
};

const formatDate = (dateStr) => {
    if (!dateStr) return "—";
    return moment(dateStr).format("DD/MM/YYYY");
};

const formatPeriod = (periodStr) => {
    if (!periodStr) return "—";
    const [year, month] = periodStr.split("-");
    const date = new Date(`${year}-${month}-01`);
    return date.toLocaleDateString(undefined, {
        year: "numeric",
        month: "long",
    });
};

// Clear selected facility
const clearFilters = () => {
    selectedFacility.value = null;
    month_year.value = "";
    router.get(
        route("reports.lmis-monthly"),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ["report"],
        }
    );
};

function toggleDistrict(district) {
    if (openDistricts.value.has(district)) {
        openDistricts.value.delete(district);
    } else {
        openDistricts.value.add(district);
    }
    // Trigger reactivity
    openDistricts.value = new Set(openDistricts.value);
}

function isOpen(district) {
    return openDistricts.value.has(district);
}

const month_year = ref(props.filters.month_year);

const exportToExcel = () => {
    if (!props.report) return;
    
    // Prepare data for Excel
    const data = [
        ['Item', 'Opening Balance', 'Stock Received', 'Stock Issued', '+ Adjustments', '– Adjustments', 'Closing Balance', 'Stockout Days'],
        ...props.report.items.map(item => [
            item.product?.name || '—',
            item.opening_balance,
            item.stock_received,
            item.stock_issued,
            item.positive_adjustments,
            item.negative_adjustments,
            item.closing_balance,
            item.stockout_days
        ])
    ];

    // Create workbook and worksheet
    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'LMIS Monthly Report');
    
    // Generate Excel file
    XLSX.writeFile(wb, `LMIS_Report_${props.report.facility?.name || 'Facility'}_${props.report.report_period || 'Period'}.xlsx`);
};

// export tot pdf
const exportToPdf = () => {
    if (!props.report) return;

    const report = props.report;
    const facility = report.facility || {};

    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: 'a4'
    });

    const title = `LMIS Monthly Report - ${facility.name || 'Unknown Facility'}`;
    const subtitle = `Report Period: ${report.report_period || '—'}`;

    // Header
    doc.setFontSize(18).setFont('helvetica', 'bold').text(title, 20, 20);
    doc.setFontSize(14).setFont('helvetica', 'normal').text(subtitle, 20, 28);

    // Facility Metadata
    const metadata = [
        `Facility Type: ${facility.facility_type || '—'}`,
        `District: ${facility.district || '—'}`,
        `Region: ${facility.region || '—'}`,
        `Phone: ${facility.phone || '—'}`,
        `Email: ${facility.email || '—'}`,
        `Cold Storage: ${facility.has_cold_storage ? 'Yes' : 'No'}`,
        `Active: ${facility.is_active ? 'Yes' : 'No'}`
    ];

    const reportInfo = [
        report.submitted_at ? `Submitted: ${moment(report.submitted_at).format('YYYY-MM-DD HH:mm')} by ${report.submitted_by?.name || '—'}` : '',
        report.reviewed_at ? `Reviewed: ${moment(report.reviewed_at).format('YYYY-MM-DD HH:mm')} by ${report.reviewed_by?.name || '—'}` : '',
        report.approved_at ? `Approved: ${moment(report.approved_at).format('YYYY-MM-DD HH:mm')} by ${report.approved_by?.name || '—'}` : '',
        report.rejected_at ? `Rejected: ${moment(report.rejected_at).format('YYYY-MM-DD HH:mm')} by ${report.rejected_by?.name || '—'}` : ''
    ].filter(Boolean);

    const yStart = 40;
    let y = yStart;

    // Section Headers
    doc.setFontSize(11).setFont('helvetica', 'bold');
    doc.text('Facility Details', 20, y);
    doc.text('Report Status', 120, y);

    y += 6;
    doc.setFont('helvetica', 'normal');

    metadata.forEach((line, index) => {
        doc.text(line, 20, y + index * 5);
    });

    reportInfo.forEach((line, index) => {
        doc.text(line, 120, y + index * 5);
    });

    // Table Data
    const tableColumn = [
        'Item',
        'Opening Balance',
        'Stock Received',
        'Stock Issued',
        '+Adj',
        '-Adj',
        'Closing Balance',
        'Stockout Days'
    ];

    const tableRows = (report.items || []).map(item => [
        item.product?.name || '—',
        item.opening_balance ?? '0.00',
        item.stock_received ?? '0.00',
        item.stock_issued ?? '0.00',
        item.positive_adjustments ?? '0.00',
        item.negative_adjustments ?? '0.00',
        item.closing_balance ?? '0.00',
        item.stockout_days ?? '0'
    ]);

    // Render Table
    autoTable(doc, {
        head: [tableColumn],
        body: tableRows,
        startY: y + (Math.max(metadata.length, reportInfo.length) * 5) + 10,
        theme: 'grid',
        headStyles: {
            fillColor: [41, 128, 185],
            textColor: 255,
            fontStyle: 'bold'
        },
        styles: {
            fontSize: 8,
            cellPadding: 2,
            lineWidth: 0.1
        }
    });

    // Page Numbers
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.text(
            `Page ${i} of ${pageCount}`,
            doc.internal.pageSize.getWidth() - 20,
            doc.internal.pageSize.getHeight() - 10
        );
    }

    // Save PDF
    const safeName = (facility.name || 'Facility').replace(/\s+/g, '_');
    const fileName = `LMIS_Report_${safeName}_${report.report_period || 'Period'}.pdf`;
    doc.save(fileName);
};


const getReport = () => {
    try {
        // Validate required fields
        if (!selectedFacility.value) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please select a facility',
                icon: 'warning'
            });
            return;
        }

        if (!month_year.value) {
            Swal.fire({
                title: 'Validation Error', 
                text: 'Please select a month and year',
                icon: 'warning'
            });
            return;
        }

        isLoading.value = true;

        router.get(
            route("reports.lmis-monthly"),
            {
                facility: selectedFacility.value,
                month_year: month_year.value,
            },
            {
                preserveScroll: true,
                preserveState: true,
                only: ["report"],
                onSuccess: () => {
                    console.log("Report data loaded successfully");
                },
                onError: (errors) => {
                    console.error("Failed to load report:", errors);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load report. Please try again.',
                        icon: 'error'
                    });
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            }
        );
    } catch (error) {
        console.error("Unexpected error:", error);
        Swal.fire({
            title: 'Error!',
            text: 'An unexpected error occurred. Please try again.',
            icon: 'error'
        });
        isLoading.value = false;
    }
};

// Computed properties for action buttons
const canShowActions = computed(() => {
    console.log('Checking canShowActions - Report status:', props.report?.status);
    return props.report && props.report.status && ['submitted', 'reviewed'].includes(props.report.status);
});

const canReview = computed(() => {
    console.log('Checking canReview - Report status:', props.report?.status);
    return props.report && props.report.status === 'submitted';
});

const canApprove = computed(() => {
    console.log('Checking canApprove - Report status:', props.report?.status);
    return props.report && props.report.status === 'reviewed';
});

const canReject = computed(() => {
    console.log('Checking canReject - Report status:', props.report?.status);
    return props.report && props.report.status === 'reviewed';
});

// Status helper functions
const getStatusLabel = (status) => {
    const statusLabels = {
        'draft': 'Draft',
        'submitted': 'Submitted',
        'reviewed': 'Reviewed',
        'approved': 'Approved',
        'rejected': 'Rejected'
    };
    return statusLabels[status] || status;
};

const getStatusClass = (status) => {
    const statusClasses = {
        'draft': 'bg-gray-100 text-gray-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'reviewed': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

// Format datetime for workflow display
const formatDateTime = (datetime) => {
    if (!datetime) return '';
    return moment(datetime).format('DD/MM/YYYY HH:mm');
};

// Action functions
const reviewReport = async () => {
    if (!props.report) return;
    
    // Show confirmation dialog
    Swal.fire({
        title: 'Review Report',
        text: 'Are you sure you want to mark this report for review?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Review it!',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isProcessing.value = true;
            
            try {
                const requestData = {
                    report_period: props.report.report_period,
                    facility_id: parseInt(props.report.facility_id),
                };
                
                console.log('Sending review request with data:', requestData);
                console.log('Report object:', props.report);
                
                const response = await axios.put(route('reports.lmis-monthly.review'), requestData);

                // Refresh the page to show updated status
                router.reload({ only: ['report'] });
                
                Swal.fire({
                    title: 'Success!',
                    text: response.data.message || 'Report has been marked as reviewed.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error('Review error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'An error occurred while reviewing the report.',
                    icon: 'error'
                });
            } finally {
                isProcessing.value = false;
            }
        }
    });
};

const approveReport = async () => {
    if (!props.report) return;
    
    // Show confirmation dialog
    Swal.fire({
        title: 'Approve Report',
        text: 'Are you sure you want to approve this report? This action cannot be undone.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Approve it!',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isProcessing.value = true;
            
            try {
                const requestData = {
                    report_period: props.report.report_period,
                    facility_id: parseInt(props.report.facility_id),
                };
                
                console.log('Sending approve request with data:', requestData);
                
                const response = await axios.put(route('reports.lmis-monthly.approve'), requestData);

                // Refresh the page to show updated status
                router.reload({ only: ['report'] });
                
                Swal.fire({
                    title: 'Approved!',
                    text: response.data.message || 'Report has been approved successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error('Approve error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'An error occurred while approving the report.',
                    icon: 'error'
                });
            } finally {
                isProcessing.value = false;
            }
        }
    });
};

const rejectReport = async () => {
    if (!props.report) return;
    
    // Show confirmation dialog with input for reason
    Swal.fire({
        title: 'Reject Report',
        text: 'Are you sure you want to reject this report?',
        input: 'textarea',
        inputLabel: 'Reason for rejection (optional)',
        inputPlaceholder: 'Enter reason for rejection...',
        inputAttributes: {
            'aria-label': 'Reason for rejection'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Reject it!',
        cancelButtonText: 'Cancel',
        preConfirm: (reason) => {
            return reason || ''; // Allow empty reason
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            isProcessing.value = true;
            
            try {
                const requestData = {
                    report_period: props.report.report_period,
                    facility_id: parseInt(props.report.facility_id),
                    reason: result.value || '',
                };
                
                console.log('Sending reject request with data:', requestData);
                
                const response = await axios.put(route('reports.lmis-monthly.reject'), requestData);

                // Refresh the page to show updated status
                router.reload({ only: ['report'] });
                
                Swal.fire({
                    title: 'Rejected!',
                    text: response.data.message || 'Report has been rejected.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error('Reject error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'An error occurred while rejecting the report.',
                    icon: 'error'
                });
            } finally {
                isProcessing.value = false;
            }
        }
    });
};
</script>

<style scoped>
.collapse-enter-active,
.collapse-leave-active {
    transition: all 0.3s ease;
}
.collapse-enter-from,
.collapse-leave-to {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
}
.collapse-enter-to,
.collapse-leave-from {
    max-height: 1000px;
    opacity: 1;
}
</style>
