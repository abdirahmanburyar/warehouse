<template>
    <AuthenticatedLayout
        title="Orders Management"
        description="Track and manage all facility orders"
        img="/assets/images/tracking.png"
    >
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Orders</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Manage and track facility orders
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search orders..."
                        class="w-64 px-4 py-2 pl-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <svg
                        class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
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
        </div>
        <!-- Orders Table -->
        <div
            class="shadow-sm overflow-hidden mb-[100px] flex justify-between"
        >
            <div class="w-full overflow-x-auto">
                <table
                    class="min-w-full border border-gray-400 divide-y divide-gray-400"
                >
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase border-r border-gray-400 text-black-500"
                            >
                                #SN
                            </th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase border-r border-gray-400 text-black-500"
                            >
                                Order
                            </th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase border-r border-gray-400 text-black-500"
                            >
                                Facility
                            </th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase border-r border-gray-400 text-black-500"
                            >
                                Type
                            </th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase border-r border-gray-400 text-black-500"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-black-500"
                            >
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-400">
                        <tr v-if="filteredOrders.length == 0">
                            <td colspan="6">
                                <span
                                    class="flex items-center justify-center p-6"
                                    >No Data Found</span
                                >
                            </td>
                        </tr>
                        <tr
                            v-for="(order, index) in filteredOrders"
                            :key="order.id"
                            class="hover:bg-gray-50"
                        >
                            <td
                                class="px-6 py-4 border-r border-gray-400 whitespace-nowrap"
                            >
                                <div class="text-sm font-medium text-gray-900">
                                    {{ index + 1 }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 border-r border-gray-400 whitespace-nowrap"
                            >
                                <div class="text-sm font-medium text-gray-900">
                                    <Link
                                        :href="route('orders.show', order.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        {{ order.order_number }}
                                    </Link>
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 border-r border-gray-400 whitespace-nowrap"
                            >
                                <div class="text-sm text-gray-900">
                                    {{ order.facility.name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ order.facility.facility_type }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 border-r border-gray-400 whitespace-nowrap"
                            >
                                <div class="text-sm text-gray-900">
                                    {{ order.order_type }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 border-r border-gray-400 whitespace-nowrap"
                            >
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        getStatusClass(order.status),
                                    ]"
                                >
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ formatDate(order.order_date) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Expected:
                                    {{ formatDate(order.expected_date) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- right sidebar status [pending, approved, in process, dispatched, delivered] -->
            <div class="w-[200px] p-4">
                <div class="flex flex-col gap-2 fade-in">
                    <!-- Pending Orders -->
                    <div class="relative flex items-center h-12">
                        <div class="relative w-12">
                            <Doughnut
                                :data="{
                                    labels: ['Pending', 'Other'],
                                    datasets: [
                                        {
                                            data: [
                                                props.stats?.pending || 0,
                                                (getTotalOrders || 1) -
                                                    (props.stats?.pending || 0),
                                            ],
                                            backgroundColor: [
                                                '#eab308',
                                                '#fef3c7',
                                            ],
                                            borderWidth: 0,
                                        },
                                    ],
                                }"
                                :options="{
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '80%',
                                    plugins: {
                                        legend: { display: false },
                                    },
                                }"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-gray-900"
                                    >{{
                                        getPercentage(props.stats?.pending)
                                    }}%</span
                                >
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="ml-3 text-xl text-gray-600">{{
                                props.stats?.pending || 0
                            }}</span>
                            <span class="ml-3 text-xs text-gray-600"
                                >Pending</span
                            >
                        </div>
                    </div>
                    <!-- Approved Orders -->
                    <div class="relative flex items-center h-12">
                        <div class="relative w-12">
                            <Doughnut
                                :data="{
                                    labels: ['Approved', 'Other'],
                                    datasets: [
                                        {
                                            data: [
                                                props.stats?.approved || 0,
                                                (getTotalOrders || 1) -
                                                    (props.stats?.approved ||
                                                        0),
                                            ],
                                            backgroundColor: [
                                                '#16a34a',
                                                '#dcfce7',
                                            ],
                                            borderWidth: 0,
                                        },
                                    ],
                                }"
                                :options="{
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '80%',
                                    plugins: {
                                        legend: { display: false },
                                    },
                                }"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-gray-900"
                                    >{{
                                        getPercentage(props.stats?.approved)
                                    }}%</span
                                >
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="ml-3 text-xl text-gray-600">{{
                                props.stats?.approved || 0
                            }}</span>
                            <span class="ml-3 text-xs text-gray-600"
                                >Approved</span
                            >
                        </div>
                    </div>

                    <!-- Rejected Orders -->
                    <div class="relative flex items-center h-12">
                        <div class="relative w-12">
                            <Doughnut
                                :data="{
                                    labels: ['Rejected', 'Other'],
                                    datasets: [
                                        {
                                            data: [
                                                props.stats?.rejected || 0,
                                                (getTotalOrders || 1) -
                                                    (props.stats?.rejected ||
                                                        0),
                                            ],
                                            backgroundColor: [
                                                '#dc2626',
                                                '#fee2e2',
                                            ],
                                            borderWidth: 0,
                                        },
                                    ],
                                }"
                                :options="{
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '80%',
                                    plugins: {
                                        legend: { display: false },
                                    },
                                }"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-gray-900"
                                    >{{
                                        getPercentage(props.stats?.rejected)
                                    }}%</span
                                >
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="ml-3 text-xl text-gray-600">{{
                                props.stats?.rejected || 0
                            }}</span>
                            <span class="ml-3 text-xs text-gray-600"
                                >Rejected</span
                            >
                        </div>
                    </div>

                    <!-- In Processing Orders -->
                    <div class="relative flex items-center h-12">
                        <div class="relative w-12">
                            <Doughnut
                                :data="{
                                    labels: ['In Processing', 'Other'],
                                    datasets: [
                                        {
                                            data: [
                                                props.stats?.[
                                                    'in processing'
                                                ] || 0,
                                                (getTotalOrders || 1) -
                                                    (props.stats?.[
                                                        'in processing'
                                                    ] || 0),
                                            ],
                                            backgroundColor: [
                                                '#2563eb',
                                                '#dbeafe',
                                            ],
                                            borderWidth: 0,
                                        },
                                    ],
                                }"
                                :options="{
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '80%',
                                    plugins: {
                                        legend: { display: false },
                                    },
                                }"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-gray-900"
                                    >{{
                                        getPercentage(
                                            props.stats?.["in processing"]
                                        )
                                    }}%</span
                                >
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="ml-3 text-xl text-gray-600">{{
                                props.stats?.["in processing"] || 0
                            }}</span>
                            <span class="ml-3 text-xs text-gray-600"
                                >In Process</span
                            >
                        </div>
                    </div>

                    <!-- Dispatched Orders -->
                    <div class="relative flex items-center h-12">
                        <div class="relative w-12">
                            <Doughnut
                                :data="{
                                    labels: ['Dispatched', 'Other'],
                                    datasets: [
                                        {
                                            data: [
                                                props.stats?.dispatched || 0,
                                                (getTotalOrders || 1) -
                                                    (props.stats?.dispatched ||
                                                        0),
                                            ],
                                            backgroundColor: [
                                                '#9333ea',
                                                '#f3e8ff',
                                            ],
                                            borderWidth: 0,
                                        },
                                    ],
                                }"
                                :options="{
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '80%',
                                    plugins: {
                                        legend: { display: false },
                                    },
                                }"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-gray-900"
                                    >{{
                                        getPercentage(props.stats?.dispatched)
                                    }}%</span
                                >
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="ml-3 text-xl text-gray-600">{{
                                props.stats?.dispatched || 0
                            }}</span>
                            <span class="ml-3 text-xs text-gray-600"
                                >Dispatched</span
                            >
                        </div>
                    </div>

                    <!-- Delivered Orders -->
                    <div class="relative flex items-center h-12">
                        <div class="relative w-12">
                            <Doughnut
                                :data="{
                                    labels: ['Delivered', 'Other'],
                                    datasets: [
                                        {
                                            data: [
                                                props.stats?.delivered || 0,
                                                (getTotalOrders || 1) -
                                                    (props.stats?.delivered ||
                                                        0),
                                            ],
                                            backgroundColor: [
                                                '#4b5563',
                                                '#f3f4f6',
                                            ],
                                            borderWidth: 0,
                                        },
                                    ],
                                }"
                                :options="{
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '80%',
                                    plugins: {
                                        legend: { display: false },
                                    },
                                }"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-gray-900"
                                    >{{
                                        getPercentage(props.stats?.delivered)
                                    }}%</span
                                >
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="ml-3 text-xl text-gray-600">{{
                                props.stats?.delivered || 0
                            }}</span>
                            <span class="ml-3 text-xs text-gray-600"
                                >Delivered</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from "chart.js";
import { Doughnut } from "vue-chartjs";

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    orders: Object,
    stats: Object,
});

const search = ref("");

const filteredOrders = computed(() => {
    if (!search.value) return props.orders.data;

    const searchTerm = search.value.toLowerCase();
    return props.orders.data.filter(
        (order) =>
            order.order_number.toLowerCase().includes(searchTerm) ||
            order.facility.name.toLowerCase().includes(searchTerm) ||
            order.order_type.toLowerCase().includes(searchTerm)
    );
});

function reloadOrders() {
    const query = {};
    router.get(route("orders.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["orders", "stats"],
    });
}

const getTotalOrders = computed(() => {
    if (!props.stats) return 0;
    return Object.values(props.stats).reduce((a, b) => a + b, 0);
});

const getPercentage = (value) => {
    if (!value || !getTotalOrders.value) return 0;
    return Math.round((value / getTotalOrders.value) * 100);
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-blue-100 text-blue-800",
        in_process: "bg-indigo-100 text-indigo-800",
        dispatched: "bg-purple-100 text-purple-800",
        delivered: "bg-green-100 text-green-800",
        completed: "bg-green-100 text-green-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

onMounted(() => {
    // Initialize Echo listener for OrderEvent
    window.Echo.channel("orders").listen(".order-received", (e) => {
        // reload();
        console.log(e);
        reloadOrders();
    });
});
</script>
<style scoped>
@keyframes slideInRight {
    0% {
        opacity: 0;
        transform: translateX(30px);
    }

    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

.fade-in {
    animation: slideInRight 0.5s ease-out;
}
</style>
