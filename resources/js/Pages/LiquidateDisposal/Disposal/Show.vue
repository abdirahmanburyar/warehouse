<template>
    <Head title="Disposal Details" />
    <AuthenticatedLayout
        title="Disposal Details"
        description="View disposal information and items"
        img="/assets/images/orders.png"
    >
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-red-600 to-pink-700 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Disposal {{ disposal.disposal_id }}</h1>
                        <p class="text-red-100">View disposal details and items</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('liquidate-disposal.index')"
                        class="inline-flex items-center px-4 py-2 bg-white text-red-600 rounded-lg font-semibold hover:bg-red-50 transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </Link>
                </div>
            </div>
        </div>

        <!-- Disposal Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Disposal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500">Disposal ID</p>
                    <p class="text-lg font-semibold text-gray-900">{{ disposal.disposal_id }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Source</p>
                    <p class="text-lg font-semibold text-gray-900 capitalize">{{ disposal.source?.replace('_', ' ') || 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <span :class="getStatusClasses(disposal.status)" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full">
                        {{ disposal.status }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Disposed By</p>
                    <p class="text-lg font-semibold text-gray-900">{{ disposal.disposedBy?.name || 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Disposal Date</p>
                    <p class="text-lg font-semibold text-gray-900">{{ formatDate(disposal.disposed_at) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-lg font-semibold text-gray-900">{{ disposal.items?.length || 0 }} items</p>
                </div>
            </div>

            <!-- Approval Information -->
            <div v-if="disposal.reviewed_at || disposal.approved_at || disposal.rejected_at" class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-md font-semibold text-gray-900 mb-3">Approval Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-if="disposal.reviewed_at">
                        <p class="text-sm font-medium text-gray-500">Reviewed By</p>
                        <p class="text-sm text-gray-900">{{ disposal.reviewedBy?.name || 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(disposal.reviewed_at) }}</p>
                    </div>
                    <div v-if="disposal.approved_at">
                        <p class="text-sm font-medium text-gray-500">Approved By</p>
                        <p class="text-sm text-gray-900">{{ disposal.approvedBy?.name || 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(disposal.approved_at) }}</p>
                    </div>
                    <div v-if="disposal.rejected_at">
                        <p class="text-sm font-medium text-gray-500">Rejected By</p>
                        <p class="text-sm text-gray-900">{{ disposal.rejectedBy?.name || 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(disposal.rejected_at) }}</p>
                        <p v-if="disposal.rejection_reason" class="text-xs text-red-600 mt-1">{{ disposal.rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disposal Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-[80px]">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Disposal Items</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-sm">
                    <thead style="background-color: #F4F7FB;">
                        <tr>
                            <th class="text-left text-xs font-bold uppercase rounded-tl-lg px-6 py-4" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">
                                Product
                            </th>
                            <th class="text-left text-xs font-bold uppercase px-6 py-4" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">
                                Quantity
                            </th>
                            <th class="text-left text-xs font-bold uppercase px-6 py-4" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">
                                Unit Cost
                            </th>
                            <th class="text-left text-xs font-bold uppercase px-6 py-4" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">
                                Total Cost
                            </th>
                            <th class="text-left text-xs font-bold uppercase px-6 py-4" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">
                                Type
                            </th>
                            <th class="text-left text-xs font-bold uppercase px-6 py-4" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">
                                Location
                            </th>
                            <th class="text-left text-xs font-bold uppercase rounded-tr-lg px-6 py-4" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">
                                Note
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-if="disposal.items?.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No items found
                            </td>
                        </tr>
                        <tr v-for="item in disposal.items" :key="item.id" class="hover:bg-gray-50 border-b" style="border-bottom: 1px solid #B7C6E6;">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>
                                    <div class="font-medium text-gray-900">{{ item.product?.name || 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ item.product?.productID || 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ item.quantity }} {{ item.uom }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ formatNumber(item.unit_cost) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ formatNumber(item.total_cost) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="capitalize">{{ item.type || 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ item.location || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate" :title="item.note">
                                    {{ item.note || 'No note' }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import moment from 'moment';

const props = defineProps({
    disposal: Object,
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    return moment(date).format('DD/MM/YYYY HH:mm');
};

const formatNumber = (number) => {
    return Number(number).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getStatusClasses = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        reviewed: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script> 