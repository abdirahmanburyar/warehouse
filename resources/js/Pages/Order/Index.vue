<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';
import axios from 'axios';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

// State for bulk actions
const selectedOrders = ref([]);

const props = defineProps({
    orders: Object,
    filters: Object,
    facilities: Array,
    stats: Object
});

// Compute total orders
const totalOrders = computed(() => {
    return props.stats.pending + props.stats.approved + props.stats.in_process +
        props.stats.dispatched + props.stats.delivered;
});

// Status configuration
const statusTabs = [
    { value: null, label: 'All Orders', color: 'blue' },
    { value: 'pending', label: 'Pending', color: 'yellow' },
    { value: 'approved', label: 'Approved', color: 'green' },
    { value: 'in_process', label: 'In Process', color: 'blue' },
    { value: 'dispatched', label: 'Dispatched', color: 'purple' },
    { value: 'delivered', label: 'Delivered', color: 'indigo' },
    { value: 'received', label: 'Received', color: 'gray' }
];

// Filter states
const search = ref('');
const currentStatus = ref(props.filters.currentStatus || null);
const facility = ref(props.filters?.facility || null);

const changeStatus = (orderId, newStatus) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to change the order status to ${newStatus}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('orders.change-status'), {
                order_id: orderId,
                status: newStatus
            })
                .then(response => {
                    Swal.fire(
                        'Updated!',
                        'Order status has been updated.',
                        'success'
                    ).then(() => {
                        reloadOrder();
                    });
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        error.response?.data || 'Failed to update order status',
                        'error'
                    );
                });
        }
    });
};

const bulkChangeStatus = async (newStatus) => {
    try {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to change the status of ${selectedOrders.value.length} orders to ${newStatus}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!'
        });

        if (result.isConfirmed) {
            await axios.post(route('orders.bulk-change-status'), {
                order_ids: selectedOrders.value,
                status: newStatus
            });

            await Swal.fire(
                'Updated!',
                'Orders have been updated successfully.',
                'success'
            );

            selectedOrders.value = [];
            reloadOrder();
        }
    } catch (error) {
        Swal.fire(
            'Error!',
            error.response?.data || 'Failed to update orders',
            'error'
        );
    }
};

const toggleAllSelection = () => {
    if (selectedOrders.value.length === props.orders.data.length) {
        selectedOrders.value = [];
    } else {
        selectedOrders.value = props.orders.data.map(order => order.id);
    }
};

const clearSelection = () => {
    selectedOrders.value = [];
};

const getBulkStatusActions = () => {
    // Get unique current statuses of selected orders
    const currentStatuses = [...new Set(props.orders.data
        .filter(order => selectedOrders.value.includes(order.id))
        .map(order => order.status))];

    // Define possible next statuses based on current status
    const statusFlow = {
        pending: { next: 'approved', color: 'green', label: 'Approve' },
        approved: { next: 'in_process', color: 'blue', label: 'Start Processing' },
        in_process: { next: 'dispatched', color: 'purple', label: 'Dispatch' },
        dispatched: { next: 'delivered', color: 'indigo', label: 'Mark Delivered' }
    };

    // Get possible next statuses
    const possibleNextStatuses = currentStatuses
        .map(status => statusFlow[status]?.next)
        .filter(Boolean);

    // Return actions for common next statuses
    return Object.entries(statusFlow)
        .filter(([_, action]) => possibleNextStatuses.includes(action.next))
        .map(([_, action]) => ({
            ...action,
            status: action.next,
            icon: `/images/status/${action.next.replace('_', '-')}.svg`
        }));
};

const selectedFacility = ref(null);

function handleSelect(selected) {
    selectedFacility.value = selected;
    facility.value = selected.id;
}

const getStatusActions = (order) => {
    const actions = [];

    switch (order.status) {
        case 'pending':
            actions.push({ label: 'Approve', status: 'approved', color: 'green', icon: '/assets/images/approved.png' });
            break;
        case 'approved':
            actions.push({ label: 'Process', status: 'in_process', color: 'blue', icon: '/assets/images/inprocess.png' });
            break;
        case 'in_process':
            actions.push({ label: 'Dispatch', status: 'dispatched', color: 'purple', icon: '/assets/images/dispatch.png' });
            break;
        case 'dispatched':
            actions.push({ label: 'Deliver', status: 'delivered', color: 'indigo', icon: '/assets/images/delivery.png' });
            break;
    }

    return actions
};

function reloadOrder() {
    const query = {}
    if (search.value) query.search = search.value;
    if (facility.value) query.facility = facility.value;
    if (currentStatus.value) query.currentStatus = currentStatus.value;
    console.log(query);
    router.get(route('orders.index'), query, {
        preserveScroll: false,
        preserveState: false,
        only: ["orders", 'filters','stats']
    })
}

// Watch for filter changes
watch([
    () => search.value,
    () => currentStatus.value,
    () => facility.value
], () => {
    reloadOrder();
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>

    <Head title="All Orders" />
    <AuthenticatedLayout title="All Orders">
        <!-- Filters Section -->
        <div class="bg-white shadow rounded-lg mb-6 p-4">
            <div class="flex flex-col md:flex-row gap-4 items-start md:items-center mb-4">
                <!-- Search -->
                <div class="relative w-full md:w-64">
                    <input type="text" v-model="search" placeholder="Search orders..."
                        class="w-full px-4 py-2 pl-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Facility Filter -->
                <div class="w-full md:w-64">
                    <Multiselect v-model="selectedFacility" :options="props.facilities" :searchable="true"
                        :close-on-select="true" :show-labels="false" :allow-empty="true"
                        placeholder="Select Sub-location" track-by="id" label="name" @select="handleSelect">
                    </Multiselect>
                </div>
            </div>
            <!-- Status Tabs -->
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="-mb-px flex space-x-8">
                    <button v-for="tab in statusTabs" :key="tab.value" @click="currentStatus = tab.value"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" :class="[
                            currentStatus === tab.value ?
                                `border-${tab.color}-500 text-${tab.color}-600` :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]">
                        {{ tab.label }}
                        <span v-if="props.orders.meta?.counts && props.orders.meta.counts[tab.value || 'all']"
                            class="ml-2 px-2 py-0.5 text-xs rounded-full"
                            :class="`bg-${tab.color}-100 text-${tab.color}-800`">
                            {{ props.orders.meta.counts[tab.value || 'all'] }}
                        </span>
                    </button>
                </nav>
            </div>
        </div>
        <div class="fixed top-0 left-0 right-0 bg-white z-50 border-b border-gray-200 px-4 py-2">
            <!-- Bulk Actions Panel -->
            <div v-if="selectedOrders?.length > 0" class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700">{{ selectedOrders.length }} orders selected</span>
                    <div class="flex gap-2">
                        <button v-for="action in getBulkStatusActions()" :key="action.status"
                            @click="bulkChangeStatus(action.status)"
                            :class="[`text-${action.color}-700 bg-${action.color}-50 hover:bg-${action.color}-100`,
                                'px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150']">
                            <img :src="action.icon" class="w-5 h-5 inline mr-1" :alt="action.label" />
                            {{ action.label }}
                        </button>
                    </div>
                </div>
                <button @click="clearSelection" class="text-gray-600 hover:text-gray-800">
                    <span class="sr-only">Clear selection</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 mt-16">
            <!-- Orders Table -->
            <div class="lg:col-span-10">
                <div class="bg-white rounded-lg overflow-auto">
                    <div class="shadow overflow-x-auto border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-4 px-6 py-3">
                                        <input type="checkbox" 
                                            :checked="selectedOrders?.length === orders.data.length"
                                            @change="toggleAllSelection"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Facility
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="orders.data?.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No orders found
                                    </td>
                                </tr>
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                    <td class="w-4 px-6 py-4">
                                        <input type="checkbox" 
                                            v-model="selectedOrders"
                                            :value="order.id"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><Link :href="route('orders.show', order.id)">{{ order.order_number }}</Link></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ order.facility?.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ order.order_type }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ formatDate(order.created_at) }}</div>
                                        <div class="text-xs text-gray-500">Expected: {{ formatDate(order.expected_date) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <!-- Status Progress Icons -->
                                            <div class="flex items-center gap-1">
                                                <img src="/assets/images/pending.svg" class="w-12 h-12" alt="pending"
                                                    :class="{'opacity-50': !['pending', 'in_process', 'dispatched', 'delivered'].includes(order.status)}" />
                                                
                                                <img src="/assets/images/approved.png" class="w-12 h-12" alt="Approved"
                                                    :class="{'opacity-50': !['approved', 'in_process', 'dispatched', 'delivered'].includes(order.status)}" />
                                                <img src="/assets/images/inprocess.png" class="w-12 h-12" alt="In Process"
                                                    :class="{'opacity-50': !['in_process', 'dispatched', 'delivered'].includes(order.status)}" />
                                                <img src="/assets/images/dispatch.png" class="w-12 h-12" alt="Dispatched"
                                                    :class="{'opacity-50': !['dispatched', 'delivered'].includes(order.status)}" />
                                                <img src="/assets/images/delivery.png" class="w-12 h-12" alt="Delivered"
                                                    :class="{'opacity-50': order.status !== 'delivered'}" />
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <template v-for="action in getStatusActions(order)" :key="action.status">
                                            <button @click="changeStatus(order.id, action.status)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md transition-colors duration-150"
                                                :class="[
                                                    `text-${action.color}-700 bg-${action.color}-50 hover:bg-${action.color}-100`,
                                                    'font-medium cursor-pointer'
                                                ]">
                                                <img :src="action.icon" class="w-12 h-12" :alt="action.label" />
                                            </button>
                                        </template>
                                        <span v-if="order.status == 'delivered'">Waiting to be received</span>
                                        <span v-if="order.status == 'received'">successfully received</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Status Statistics -->
            <div class="lg:col-span-1">
                <div class="sticky top-4 sticky:mt-5">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Order Statistics</h3>
                    <div class="space-y-8">
                        <!-- Pending -->
                        <div class="relative">
                            <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="4" />
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#eab308" stroke-width="4"
                                        :stroke-dasharray="`${(stats.pending / totalOrders) * 125.6} 125.6`" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-base font-bold text-yellow-600">{{ Math.round((stats.pending / totalOrders) * 100) }}%</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ stats.pending }}</div>
                                <div class="text-base text-gray-600">Pending</div>
                            </div>
                        </div>
                    </div>
                    </div>

                    <!-- Approved -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="4" />
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#22c55e" stroke-width="4"
                                        :stroke-dasharray="`${(stats.approved / totalOrders) * 125.6} 125.6`" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-base font-bold text-green-600">{{ Math.round((stats.approved / totalOrders) * 100) }}%</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ stats.approved }}</div>
                                <div class="text-base text-gray-600">Approved</div>
                            </div>
                        </div>
                    </div>

                    <!-- In Process -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="4" />
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#3b82f6" stroke-width="4"
                                        :stroke-dasharray="`${(stats.in_process / totalOrders) * 125.6} 125.6`" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-base font-bold text-blue-600">{{ Math.round((stats.in_process / totalOrders) * 100) }}%</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ stats.in_process }}</div>
                                <div class="text-base text-gray-600">In Process</div>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatched -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="4" />
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#8b5cf6" stroke-width="4"
                                        :stroke-dasharray="`${(stats.dispatched / totalOrders) * 125.6} 125.6`" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-base font-bold text-purple-600">{{ Math.round((stats.dispatched / totalOrders) * 100) }}%</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ stats.dispatched }}</div>
                                <div class="text-base text-gray-600">Dispatched</div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivered -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="4" />
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#6366f1" stroke-width="4"
                                        :stroke-dasharray="`${(stats.delivered / totalOrders) * 125.6} 125.6`" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-base font-bold text-indigo-600">{{ Math.round((stats.delivered / totalOrders) * 100) }}%</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ stats.delivered }}</div>
                                <div class="text-base text-gray-600">Delivered</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
