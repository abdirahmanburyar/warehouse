<template>
    <Head title="Order Tracking Report" />
    <AuthenticatedLayout title="Order Tracking Report" description="Track order status and inventory allocations">
        <div class="relative bg-white mb-2 text-xs">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center mb-5">
                <div>
                    <Multiselect
                        v-model="filters.facility"
                        :options="facilities"
                        placeholder="Select facilities..."
                        :searchable="true"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :preserve-search="true"
                        class="w-full"
                    />
                </div>
                <div>
                    <select v-model="filters.status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div>
                    <input type="date" v-model="filters.date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <input type="date" v-model="filters.date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full">
                                        <thead style="background-color: #F4F7FB;">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b rounded-tl-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Order Number</th>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Facility</th>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Handled By</th>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Order Date</th>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Expected Date</th>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Status</th>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Order Summary</th>
                                <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b rounded-tr-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Progress</th>
                            </tr>
                        </thead>
                <tbody class="bg-white">
                                                <tr v-for="order in orders.data" :key="order.id" class="border-b">
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    <div class="text-sm font-medium text-gray-900">{{ order.order_number }}</div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    <div class="text-sm text-gray-900">{{ order.facility?.name }}</div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    <div class="text-sm text-gray-900">{{ order.facility?.handledby?.name || 'N/A' }}</div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    <div class="text-sm text-gray-900">{{ formatDate(order.order_date) }}</div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    <div class="text-sm text-gray-900">{{ formatDate(order.expected_date) }}</div>
                                </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                            <span :class="getStatusClass(order.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm">
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Total Items:</span>
                                    <span class="text-gray-900">{{ order.tracking_data.total_items }}</span>
                                </div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Allocated:</span>
                                    <span class="text-gray-900">{{ order.tracking_data.total_allocated }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Received:</span>
                                    <span class="text-gray-900">{{ order.tracking_data.total_received }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap border-b" style="border-bottom: 1px solid #B7C6E6;">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-blue-600 h-2 rounded-full" 
                                    :style="{ width: order.tracking_data.progress_percentage + '%' }"
                                ></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ order.tracking_data.progress_percentage }}% Complete</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 mb-[80px]">
            <TailwindPagination 
                :data="orders" 
                @pagination-change-page="getResults"
            />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    orders: Object,
    facilities: Array,
    filters: Object,
});

const filters = ref({
    facility: props.filters.facility || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    per_page: props.filters.per_page || 25,
});

// Watch for filter changes and automatically request data
watch(filters, (newFilters) => {
    // Remove empty filters from the URL
    const cleanFilters = Object.fromEntries(
        Object.entries(newFilters).filter(([key, value]) => {
            if (Array.isArray(value)) {
                return value.length > 0;
            }
            return value !== '' && value !== null && value !== undefined;
        })
    );
    
    router.get(route('reports.order-tracking'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
    });
}, { deep: true });

const getResults = (page = 1) => {
    // Remove empty filters from the URL
    const cleanFilters = Object.fromEntries(
        Object.entries({ ...filters.value, page }).filter(([key, value]) => {
            if (Array.isArray(value)) {
                return value.length > 0;
            }
            return value !== '' && value !== null && value !== undefined;
        })
    );
    
    router.get(route('reports.order-tracking'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        dispatched: 'bg-blue-100 text-blue-800',
        completed: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};


</script> 