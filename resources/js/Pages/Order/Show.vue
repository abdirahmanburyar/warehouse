<template>
 <AuthenticatedLayout title="Orders Management" description="Track and manage all facility orders"
 img="/assets/images/tracking.png">
         <div class="py-6 px-4 sm:px-6 lg:px-8">
            <!-- Order Header -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-3">
                            Order #{{ props.order.order_number }}
                        </h1>
                        <div class="mt-2 text-sm text-gray-600">
                            <p>Facility: {{ props.order.facility?.name }}</p>
                            <p>Order Type: {{ props.order.order_type }}</p>
                            <p>Order Date: {{ formatDate(props.order.order_date) }}</p>
                            <p>Expected by {{ formatDate(props.order.expected_date) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span :class="[
                                'px-3 py-1 text-sm rounded-full',
                                getStatusClass(props.order.status)
                            ]">
                                {{ props.order.status }}
                            </span>
                    </div>
                </div>
            </div>

            <!-- Tabs and Items Table -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex">
                        <button v-for="tab in tabs" 
                            :key="tab.key"
                            @click="currentTab = tab.key"
                            :class="[
                                'px-6 py-3 text-sm font-medium',
                                currentTab === tab.key
                                    ? 'border-b-2 border-blue-500 text-blue-600'
                                    : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                            {{ tab.label }}
                            <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs">
                                {{ getItemCountByStatus(tab.status) }}
                            </span>
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search items..."
                            class="px-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ item.product.name }}</div>
                                        <div class="text-sm text-gray-500">{{ item.product.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.product.sku }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ item.quantity }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            getStatusClass(item.status)
                                        ]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex gap-2">
                                            <button v-for="action in getAvailableActions(item)"
                                                :key="action.action"
                                                @click="handleItemAction(item.id, action.action, action.label)"
                                                :class="[
                                                    'px-3 py-1 rounded-md text-white text-xs',
                                                    action.class
                                                ]">
                                                {{ action.label }}
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
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import axios from 'axios';

const toast = useToast();
const props = defineProps({
    order: Object
});

const currentTab = ref('all');
const search = ref('');

const tabs = [
    { key: 'all', label: 'All Items', status: null },
    { key: 'pending', label: 'Pending', status: 'pending' },
    { key: 'approved', label: 'Approved', status: 'approved' },
    { key: 'in_process', label: 'In Process', status: 'in_process' },
    { key: 'dispatched', label: 'Dispatched', status: 'dispatched' },
    { key: 'delivered', label: 'Delivered', status: 'delivered' }
];

const canApprove = computed(() => props.order.status === 'pending');
const canProcess = computed(() => props.order.status === 'approved');
const canDispatch = computed(() => props.order.status === 'in_process');
const canDeliver = computed(() => props.order.status === 'dispatched');

const filteredItems = computed(() => {
    let items = props.order.items;
    
    // Filter by tab
    if (currentTab.value !== 'all') {
        items = items.filter(item => item.status === currentTab.value);
    }
    
    // Filter by search
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        items = items.filter(item => 
            item.product.name.toLowerCase().includes(searchTerm) ||
            item.product.sku.toLowerCase().includes(searchTerm)
        );
    }
    
    return items;
});

const getItemCountByStatus = (status) => {
    if (!status) return props.order.items.length;
    return props.order.items.filter(item => item.status === status).length;
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        in_process: 'bg-indigo-100 text-indigo-800',
        dispatched: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getAvailableActions = (item) => {
    const actions = [];
    switch (item.status) {
        case 'pending':
            actions.push({ action: 'approved', label: 'Approve', class: 'bg-blue-600 hover:bg-blue-700' });
            break;
        case 'approved':
            actions.push({ action: 'in_process', label: 'Process', class: 'bg-indigo-600 hover:bg-indigo-700' });
            break;
        case 'in_process':
            actions.push({ action: 'dispatched', label: 'Dispatch', class: 'bg-purple-600 hover:bg-purple-700' });
            break;
        case 'dispatched':
            actions.push({ action: 'delivered', label: 'Deliver', class: 'bg-green-600 hover:bg-green-700' });
            break;
    }
    return actions;
};

const handleAction = async (id, status) => {
   
};

function reloadItems(){
    const query = {}
    router.get(route('orders.show', props.order.id), query,{
        preserveState: true,
        preserveScroll: true,
        only: ['order']
    })
}

const handleItemAction = async (id, status, label) => {
    console.log(id, status)
    Swal.fire({
        title: `Are you sure to ${label}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update'
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios.post(route('orders.change-item-status'),{
                item_id: id,
                status
            })
            .then((response) => {
                reloadItems();
                Swal.fire({
                    icon: 'success',
                    title: 'Order successfully ' + status + 'ed',
                    showConfirmButton: false,
                    timer: 1500
                })
            })
            .catch((error) => {
                console.log(error);
                toast.error('Failed to update order status');
            })
        }
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    // Initialize Echo listener for OrderEvent
    window.Echo.channel("orders").listen(".order-received", (e) => {
        // reload();
        console.log(e);
    });
});

</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>