<template>
    <AuthenticatedLayout
        title="Optimize Your Transfers"
        description="Moving Supplies, Bridging needs"
        img="/assets/images/transfer.png"
    >
        <!-- Header Section -->
        <div class="flex flex-col space-y-6">
            <!-- Buttons First -->
            <div class="flex items-center justify-end">
                <!-- New Transfer -->
                <button
                    @click="router.visit(route('transfers.create'))"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    New Transfer
                </button>
            </div>

            <!-- Filters Section -->
            <div class="mb-4">
                <div class="grid grid-cols-4 gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <input
                            type="text"
                            v-model="search"
                            class="pl-10 pr-4 py-2 border border-gray-300 w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search a Transfer"
                        />
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
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
                    </div>

                    <!-- warehouse or facility selection -->
                    <div>
                        <Multiselect
                            v-model="transfer_type"
                            :options="facilityType"
                            :searchable="true"
                            :allow-empty="true"
                            :show-labels="false"
                            placeholder="All Transfer Type"
                            class=""
                        >
                        </Multiselect>
                    </div>

                    <!-- Facility Selector -->
                    <div>
                        <Multiselect
                            v-model="facility"
                            :options="props.facilities"
                            :searchable="true"
                            :allow-empty="true"
                            :show-labels="false"
                            placeholder="All Facilities"
                            class=""
                        >
                        </Multiselect>
                    </div>

                    <!-- Warehouse Selector -->
                    <div>
                        <Multiselect
                            v-model="warehouse"
                            :options="props.warehouses"
                            :close-on-select="true"
                            :clear-on-select="false"
                            :preserve-search="true"
                            placeholder="All Warehouses"
                            class=""
                            :preselect-first="false"
                        >
                        </Multiselect>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div>
                        <label for="date_from">From Date</label>
                        <input
                            type="date"
                            v-model="date_from"
                            class="border border-gray-300 w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="From Date"
                        />
                    </div>

                    <!-- To Date -->
                    <div>
                        <label for="date_to">To Date</label>
                        <input
                            type="date"
                            v-model="date_to"
                            class="border border-gray-300 w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="To Date"
                        />
                    </div>
                </div>

                <div class="flex justify-end mt-2">
                    <select
                        class="rounded-3xl"
                        name="per_page"
                        id="per_page"
                        @change="props.filters.page = 1"
                        v-model="per_page"
                    >
                        <option value="2">2 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.value"
                        @click="currentTab = tab.value"
                        class="whitespace-nowrap py-4 px-3 border-b-4 font-bold text-xs"
                        :class="[
                            currentTab === tab.value
                                ? 'border-green-500 text-green-600'
                                : 'border-transparent text-black hover:text-gray-700 hover:border-gray-300',
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-12 gap-1 mb-[40px]">
            <!-- Table Section (9 cols) -->
            <div class="md:col-span-9 sm:col-span-12">
                <div class="max-w-full overflow-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th
                                    scope="col"
                                    class="px-2 py-1 border border-black text-left text-xs font-medium text-[#6C75B8] capitalize tracking-wider"
                                >
                                    Transfer ID
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-1 border border-black text-left text-xs font-medium text-[#6C75B8] capitalize tracking-wider"
                                >
                                    Date
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-1 border border-black text-left text-xs font-medium text-[#6C75B8] capitalize tracking-wider"
                                >
                                    From
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-1 border border-black text-left text-xs font-medium text-[#6C75B8] capitalize tracking-wider"
                                >
                                    To
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-1 border border-black text-left text-xs font-medium text-[#6C75B8] capitalize tracking-wider"
                                >
                                    Transfer Type
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-1 border border-black text-left text-xs font-medium text-[#6C75B8] capitalize tracking-wider"
                                >
                                    Items
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-1 border border-black text-left text-xs font-medium text-[#6C75B8] capitalize tracking-wider"
                                >
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="">
                            <tr v-if="props.transfers.data.length === 0">
                                <td
                                    colspan="7"
                                    class="text-center text-black py-4 border border-black"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center"
                                    >
                                        <svg
                                            class="w-12 h-12 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                            ></path>
                                        </svg>
                                        <p class="mt-2 text-xs font-medium">
                                            No transfer data available
                                        </p>
                                        <p class="mt-1 text-xs">
                                            Create a new transfer or adjust your
                                            filters to see results
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="transfer in props.transfers.data"
                                :key="transfer.id"
                                class="hover:bg-gray-50 border-b border border-black"
                            >
                                <td
                                    class="px-2 py-1 whitespace-nowrap text-xs font-medium text-black"
                                >
                                    <Link
                                        :href="
                                            route('transfers.show', transfer.id)
                                        "
                                        class="hover:underline text-blue-500"
                                    >
                                        {{ transfer.transferID }}
                                    </Link>
                                </td>
                                <td
                                    class="px-2 py-1 whitespace-nowrap text-xs text-black border border-black"
                                >
                                    {{
                                        new Date(
                                            transfer.transfer_date
                                        ).toLocaleDateString()
                                    }}
                                </td>

                                <td
                                    class="px-2 py-1 whitespace-nowrap text-xs text-black border border-black"
                                >
                                    {{
                                        transfer.from_warehouse?.name ||
                                        transfer.from_facility?.name
                                    }}
                                </td>
                                <td
                                    class="px-2 py-1 whitespace-nowrap text-xs text-black border border-black"
                                >
                                    {{
                                        transfer.to_warehouse?.name ||
                                        transfer.to_facility?.name
                                    }}
                                </td>
                                <td
                                    class="px-2 py-1 whitespace-nowrap text-xs text-black border border-black"
                                >
                                    {{ transfer.transfer_type }}
                                </td>
                                <td
                                    class="px-2 py-1 whitespace-nowrap text-xs text-black border border-black"
                                >
                                    {{ transfer.items_count }}
                                </td>
                                <td class="px-2 py-1 text-xs text-black border border-black">
                                    <div class="flex items-center gap-2">
                                        <!-- Status Progress Icons - Only show actions taken -->
                                        <div class="flex items-center gap-2">
                                            <!-- Show status progression up to current status - icons with labels -->
                                            <!-- Always show pending as it's the initial state -->
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <img
                                                    src="/assets/images/pending.png"
                                                    class="w-8 h-8"
                                                    alt="Pending"
                                                    title="Pending"
                                                />
                                            </div>

                                            <!-- Show approved if status is approved or further -->
                                            <template
                                                v-if="
                                                    [
                                                        'approved',
                                                        'in_process',
                                                        'dispatched',
                                                        'transferred',
                                                        'delivered',
                                                        'received',
                                                    ].includes(
                                                        transfer.status?.toLowerCase()
                                                    )
                                                "
                                            >
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <img
                                                        src="/assets/images/approved.png"
                                                        class="w-8 h-8"
                                                        alt="Approved"
                                                        title="Approved"
                                                    />
                                                </div>
                                            </template>

                                            <!-- Show processed if status is in_process or further -->
                                            <template
                                                v-if="
                                                    [
                                                        'in_process',
                                                        'dispatched',
                                                        'transferred',
                                                        'delivered',
                                                        'received',
                                                    ].includes(
                                                        transfer.status?.toLowerCase()
                                                    )
                                                "
                                            >
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <img
                                                        src="/assets/images/inprocess.png"
                                                        class="w-8 h-8"
                                                        alt="Processed"
                                                        title="Processed"
                                                    />
                                                </div>
                                            </template>

                                            <!-- Show dispatched if status is dispatched or further -->
                                            <template
                                                v-if="
                                                    [
                                                        'dispatched',
                                                        'transferred',
                                                        'delivered',
                                                        'received',
                                                    ].includes(
                                                        transfer.status?.toLowerCase()
                                                    )
                                                "
                                            >
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <img
                                                        src="/assets/images/dispatch.png"
                                                        class="w-8 h-8"
                                                        alt="Dispatched"
                                                        title="Dispatched"
                                                    />
                                                </div>
                                            </template>
                                            <!-- Show received if status is received -->
                                            <template
                                                v-if="
                                                    ['received'].includes(
                                                        transfer.status?.toLowerCase()
                                                    )
                                                "
                                            >
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <img
                                                        src="/assets/images/received.png"
                                                        class="w-8 h-8"
                                                        alt="Received"
                                                        title="Received"
                                                    />
                                                </div>
                                            </template>

                                            <!-- Show rejected if status is rejected (special case) -->
                                            <template
                                                v-if="
                                                    transfer.status?.toLowerCase() ===
                                                    'rejected'
                                                "
                                            >
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <img
                                                        src="/assets/images/rejected.png"
                                                        class="w-8 h-8"
                                                        alt="Rejected"
                                                        title="Rejected"
                                                    />
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 flex justify-end items-center">
                    <TailwindPagination
                        :data="props.transfers"
                        :limit="2"
                        @pagination-change-page="getResults"
                    />
                </div>
            </div>

            <!-- Statistics Section (3 cols) -->
            <div class="md:col-span-3 sm:col-span-12">
                <div class="bg-white mb-4">
                    <h3 class="text-xs text-black mb-4">Transfer Statistics</h3>
                    <div class="flex justify-between gap-1">
                        <!-- Pending -->
                        <div class="flex flex-col items-center">
                            <div
                                class="h-[250px] w-8 bg-amber-50 relative overflow-hidden shadow-md rounded-2xl"
                            >
                                <div
                                    class="absolute top-0 inset-x-0 flex justify-center pt-2"
                                >
                                    <img
                                        src="/assets/images/pending_small.png"
                                        class="h-8 w-8 object-contain"
                                        alt="Pending"
                                    />
                                </div>
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-amber-500 to-amber-400 transition-all duration-500"
                                    :style="{
                                        height:
                                            props.statistics.pending
                                                .percentage + '%',
                                    }"
                                >
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-black font-bold text-xs tracking-wide"
                                    >
                                        {{
                                            props.statistics.pending.percentage
                                        }}%
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <span
                                    class="font-medium text-gray-900"
                                    style="font-size: 10px; font-weight: bold"
                                    >Pending</span
                                >
                            </div>
                        </div>

                         <!-- Reviewed -->
                         <div class="flex flex-col items-center">
                            <div
                                class="h-[250px] w-8 bg-blue-50 relative overflow-hidden shadow-md rounded-2xl"
                            >
                                <div
                                    class="absolute top-0 inset-x-0 flex justify-center pt-2"
                                >
                                    <img
                                        src="/assets/images/review.png"
                                        class="h-8 w-6 object-contain"
                                        alt="Reviewed"
                                    />
                                </div>
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-blue-600 to-blue-400 transition-all duration-500"
                                    :style="{
                                        height:
                                            props.statistics.reviewed
                                                .percentage + '%',
                                    }"
                                >
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-black font-bold text-xs tracking-wide"
                                    >
                                        {{
                                            props.statistics.reviewed
                                                .percentage
                                        }}%
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <span
                                    class="font-medium text-gray-900"
                                    style="font-size: 10px; font-weight: bold"
                                    >Reviewed</span
                                >
                            </div>
                        </div>

                        <!-- Approved -->
                        <div class="flex flex-col items-center">
                            <div
                                class="h-[250px] w-8 bg-blue-50 relative overflow-hidden shadow-md rounded-2xl"
                            >
                                <div
                                    class="absolute top-0 inset-x-0 flex justify-center pt-2"
                                >
                                    <img
                                        src="/assets/images/approved_small.png"
                                        class="h-8 w-8 object-contain"
                                        alt="Approved"
                                    />
                                </div>
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-blue-600 to-blue-400 transition-all duration-500"
                                    :style="{
                                        height:
                                            props.statistics.approved
                                                .percentage + '%',
                                    }"
                                >
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-black font-bold text-xs tracking-wide"
                                    >
                                        {{
                                            props.statistics.approved
                                                .percentage
                                        }}%
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <span
                                    class="font-medium text-gray-900"
                                    style="font-size: 10px; font-weight: bold"
                                    >Approved</span
                                >
                            </div>
                        </div>

                        <!-- In Process -->
                        <div class="flex flex-col items-center">
                            <div
                                class="h-[250px] w-8 bg-slate-50 relative overflow-hidden shadow-md rounded-2xl"
                            >
                                <div
                                    class="absolute top-0 inset-x-0 flex justify-center pt-2"
                                >
                                    <img
                                        src="/assets/images/inprocess.png"
                                        class="h-8 w-8 object-contain"
                                        alt="In Process"
                                    />
                                </div>
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-600 to-slate-400 transition-all duration-500"
                                    :style="{
                                        height:
                                            props.statistics.in_process
                                                .percentage + '%',
                                    }"
                                >
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-black font-bold text-xs tracking-wide"
                                    >
                                        {{
                                            props.statistics.in_process
                                                .percentage
                                        }}%
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <span
                                    class="font-medium text-gray-900"
                                    style="font-size: 10px; font-weight: bold"
                                    >In Process</span
                                >
                            </div>
                        </div>

                        <!-- Dispatched -->
                        <div class="flex flex-col items-center">
                            <div
                                class="h-[250px] w-8 bg-purple-50 relative overflow-hidden shadow-md rounded-2xl"
                            >
                                <div
                                    class="absolute top-0 inset-x-0 flex justify-center pt-2"
                                >
                                    <img
                                        src="/assets/images/dispatch.png"
                                        class="h-8 w-8 object-contain"
                                        alt="Dispatched"
                                    />
                                </div>
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-purple-600 to-purple-400 transition-all duration-500"
                                    :style="{
                                        height:
                                            (props.statistics.dispatched
                                                ?.percentage || 0) + '%',
                                    }"
                                >
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-black font-bold text-xs tracking-wide"
                                    >
                                        {{
                                            props.statistics.dispatched
                                                ?.percentage || 0
                                        }}%
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <span
                                    class="font-medium text-gray-900"
                                    style="font-size: 10px; font-weight: bold"
                                    >Dispatched</span
                                >
                            </div>
                        </div>

                        <!-- Received -->
                        <div class="flex flex-col items-center">
                            <div
                                class="h-[250px] w-8 bg-emerald-50 relative overflow-hidden shadow-md rounded-2xl"
                            >
                                <div
                                    class="absolute top-0 inset-x-0 flex justify-center pt-2"
                                >
                                    <img
                                        src="/assets/images/received.png"
                                        class="h-8 w-8 object-contain"
                                        alt="Received"
                                    />
                                </div>
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-emerald-600 to-emerald-400 transition-all duration-500"
                                    :style="{
                                        height:
                                            (props.statistics.received
                                                ?.percentage || 0) + '%',
                                    }"
                                >
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-black font-bold text-xs tracking-wide"
                                    >
                                        {{
                                            props.statistics.received
                                                ?.percentage || 0
                                        }}%
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <span
                                    class="font-medium text-gray-900"
                                    style="font-size: 10px; font-weight: bold"
                                    >Received</span
                                >
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router, Link } from "@inertiajs/vue3";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { TailwindPagination } from "laravel-vue-pagination";

const props = defineProps({
    transfers: {
        type: Object,
    },
    statistics: {
        type: Object,
        default: () => ({
            approved: { count: 0, percentage: 0 },
            pending: { count: 0, percentage: 0 },
            in_process: { count: 0, percentage: 0 },
            transferred: { count: 0, percentage: 0 },
            rejected: { count: 0, percentage: 0 },
        }),
    },
    facilities: {
        type: Array,
        default: () => [],
    },
    warehouses: {
        type: Array,
        default: () => [],
    },
    filters: Object,
});

const currentTab = ref("all");
const facilityType = [
    "Warehouse to Warehouse",
    "Facility to Facility",
    "Facility to Warehouse",
    "Warehouse to Facility",
];
// Status configuration
const statusTabs = [
    { value: "all", label: "All Transfers", color: "blue" },
    { value: "pending", label: "Pending", color: "yellow" },
    { value: "reviewed", label: "Reviewed", color: "green" },
    { value: "approved", label: "Approved", color: "green" },
    { value: "in_process", label: "In Process", color: "blue" },
    { value: "dispatched", label: "Dispatched", color: "purple" },
    { value: "received", label: "Received", color: "gray" },
    { value: "rejected", label: "Rejected", color: "red" },
];

const search = ref(props.filters.search);
const per_page = ref(props.filters.per_page || 25);
const facility = ref(props.filters.facility);
const warehouse = ref(props.filters.warehouse);
const date_from = ref(props.filters.date_from);
const date_to = ref(props.filters.date_to);
const transfer_type = ref(props.filters.transfer_type);

watch(
    [
        () => search.value,
        () => per_page.value,
        () => facility.value,
        () => props.filters.page,
        () => currentTab.value,
        () => warehouse.value,
        () => date_from.value,
        () => date_to.value,
        () => transfer_type.value,
    ],
    () => {
        reloadTransfer();
    }
);

function getResults(page = 1) {
    props.filters.page = page;
}

function reloadTransfer() {
    const query = {};
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;
    if (currentTab.value) query.tab = currentTab.value;
    if (facility.value) query.facility = facility.value;
    if (warehouse.value) query.warehouse = warehouse.value;
    if (date_from.value) query.date_from = date_from.value;
    if (date_to.value) query.date_to = date_to.value;
    if (transfer_type.value) query.transfer_type = transfer_type.value;

    router.get(route("transfers.index"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["transfers"],
    });
}
</script>
