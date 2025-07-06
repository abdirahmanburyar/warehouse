<template>
    <Head title="Received Back Orders" />
    <AuthenticatedLayout title="Received Back Orders"
        description="Manage and track received back orders" img="/assets/images/supplies.png">
        <div class="overflow-hidden mb-[80px]">
            <div class="text-gray-900">
                <!-- Search and Filter Row -->
                <div class="flex flex-wrap justify-between w-full px-2 mb-6">
                    <div class="flex-1 mr-2 flex-grow-2">
                        <label for="search" class="text-xs font-medium text-gray-700">Search</label>
                        <input type="text" v-model="search" placeholder="Search by ID, barcode, batch number, product"
                            class="w-[400px] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                    </div>
                    <div class="flex-1 mx-2">
                        <label for="status" class="text-xs font-medium text-gray-700">Status</label>
                        <select v-model="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="flex-1 mx-2">
                        <label for="type" class="text-xs font-medium text-gray-700">Type</label>
                        <select v-model="type"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">All Types</option>
                            <option value="backorder">Back Order</option>
                            <option value="return">Return</option>
                            <option value="damaged">Damaged</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="flex-1 mx-2">
                        <label for="date_from" class="text-xs font-medium text-gray-700">Date From</label>
                        <input type="date" v-model="date_from"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                    </div>
                    <div class="flex-1 mx-2">
                        <label for="date_to" class="text-xs font-medium text-gray-700">Date To</label>
                        <input type="date" v-model="date_to"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                    </div>
                    <div class="ml-2 w-[180px]">
                        <label for="per_page" class="text-xs font-medium text-gray-700">Per Page</label>
                        <select v-model="per_page" @change="filters.page = 1"
                            class="w-full rounded-3xl border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Total Received</p>
                                <p class="text-lg font-bold text-gray-800">{{ stats.total }}</p>
                            </div>
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Pending</p>
                                <p class="text-lg font-bold text-gray-800">{{ stats.pending }}</p>
                            </div>
                            <div class="p-2 bg-yellow-50 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Total Quantity</p>
                                <p class="text-lg font-bold text-gray-800">{{ stats.total_quantity }}</p>
                            </div>
                            <div class="p-2 bg-green-50 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Total Cost</p>
                                <p class="text-lg font-bold text-gray-800">{{ formatCurrency(stats.total_cost) }}</p>
                            </div>
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Received Back Orders Table -->
                <div class="px-2 mb-6">
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Received Back Order Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Received Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Cost
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="receivedBackorder in receivedBackorders.data" :key="receivedBackorder.id"
                                        class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ receivedBackorder.received_backorder_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">{{ receivedBackorder.product?.name }}</div>
                                                <div class="text-gray-500 text-xs">{{ receivedBackorder.product?.productID }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="typeClass(receivedBackorder.type)">
                                                {{ receivedBackorder.type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ receivedBackorder.quantity }} {{ receivedBackorder.uom }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(receivedBackorder.received_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="statusClass(receivedBackorder.status)">
                                                {{ receivedBackorder.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(receivedBackorder.total_cost) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('supplies.received-backorder.show', receivedBackorder.id)"
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </Link>
                                                <Link :href="route('supplies.received-backorder.edit', receivedBackorder.id)"
                                                    class="text-green-600 hover:text-green-900">
                                                    Edit
                                                </Link>
                                                <button v-if="receivedBackorder.status === 'pending'" @click="reviewReceivedBackorder(receivedBackorder)"
                                                    class="text-blue-600 hover:text-blue-900">
                                                    Review
                                                </button>
                                                <button v-if="receivedBackorder.status === 'reviewed'" @click="approveReceivedBackorder(receivedBackorder)"
                                                    class="text-green-600 hover:text-green-900">
                                                    Approve
                                                </button>
                                                <button v-if="receivedBackorder.status === 'pending' || receivedBackorder.status === 'reviewed'" @click="rejectReceivedBackorder(receivedBackorder)"
                                                    class="text-red-600 hover:text-red-900">
                                                    Reject
                                                </button>
                                                <button @click="deleteReceivedBackorder(receivedBackorder)"
                                                    class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            <TailwindPagination
                                :data="receivedBackorders"
                                @pagination-change-page="onPageChange"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from 'sweetalert2';

const props = defineProps({
    receivedBackorders: Object,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const type = ref(props.filters.type || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');
const per_page = ref(props.filters.per_page || 25);

// Watch for filter changes and update the URL
watch([search, status, type, date_from, date_to, per_page], () => {
    router.get(route('supplies.received-backorder.index'), {
        search: search.value,
        status: status.value,
        type: type.value,
        date_from: date_from.value,
        date_to: date_to.value,
        per_page: per_page.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const onPageChange = (page) => {
    router.get(route('supplies.received-backorder.index'), {
        ...props.filters,
        page: page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

const formatCurrency = (amount) => {
    if (!amount) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const statusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'reviewed':
            return 'bg-blue-100 text-blue-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const typeClass = (type) => {
    switch (type) {
        case 'backorder':
            return 'bg-blue-100 text-blue-800';
        case 'return':
            return 'bg-orange-100 text-orange-800';
        case 'damaged':
            return 'bg-red-100 text-red-800';
        case 'expired':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const deleteReceivedBackorder = (receivedBackorder) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete received back order ${receivedBackorder.received_backorder_number}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('supplies.received-backorder.destroy', receivedBackorder.id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Received back order has been deleted.',
                        'success'
                    );
                },
                onError: (errors) => {
                    Swal.fire(
                        'Error!',
                        errors.message || 'Failed to delete received back order.',
                        'error'
                    );
                }
            });
        }
    });
};

const reviewReceivedBackorder = (receivedBackorder) => {
    Swal.fire({
        title: 'Review Received Back Order',
        text: `Do you want to review received back order ${receivedBackorder.received_backorder_number}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, review it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('supplies.received-backorder.review', receivedBackorder.id), {}, {
                onSuccess: () => {
                    Swal.fire(
                        'Reviewed!',
                        'Received back order has been reviewed.',
                        'success'
                    );
                },
                onError: (errors) => {
                    Swal.fire(
                        'Error!',
                        errors.message || 'Failed to review received back order.',
                        'error'
                    );
                }
            });
        }
    });
};

const approveReceivedBackorder = (receivedBackorder) => {
    Swal.fire({
        title: 'Approve Received Back Order',
        text: `Do you want to approve received back order ${receivedBackorder.received_backorder_number}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('supplies.received-backorder.approve', receivedBackorder.id), {}, {
                onSuccess: () => {
                    Swal.fire(
                        'Approved!',
                        'Received back order has been approved.',
                        'success'
                    );
                },
                onError: (errors) => {
                    Swal.fire(
                        'Error!',
                        errors.message || 'Failed to approve received back order.',
                        'error'
                    );
                }
            });
        }
    });
};

const rejectReceivedBackorder = (receivedBackorder) => {
    Swal.fire({
        title: 'Reject Received Back Order',
        input: 'text',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter rejection reason...',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a rejection reason!';
            }
        },
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Reject'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('supplies.received-backorder.reject', receivedBackorder.id), {
                rejection_reason: result.value
            }, {
                onSuccess: () => {
                    Swal.fire(
                        'Rejected!',
                        'Received back order has been rejected.',
                        'success'
                    );
                },
                onError: (errors) => {
                    Swal.fire(
                        'Error!',
                        errors.message || 'Failed to reject received back order.',
                        'error'
                    );
                }
            });
        }
    });
};
</script>
