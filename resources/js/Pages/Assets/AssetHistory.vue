<template>
    <AuthenticatedLayout :title="pageTitle" :description="pageDescription">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="bg-white shadow-xl rounded-2xl">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-10 h-10 text-white">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Asset History</h1>
                                <p class="text-blue-100 text-sm mt-1">
                                    {{ pageDescription }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 sm:mt-0">
                            <Link :href="route('assets.index')"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-blue-700 bg-white hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-4 h-4 mr-2">
                                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Assets
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Information -->
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Asset Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Asset Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ asset?.asset_number || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Asset Tag</label>
                        <p class="mt-1 text-sm text-gray-900">{{ asset?.asset_tag || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Category</label>
                        <p class="mt-1 text-sm text-gray-900">{{ assetItems[0]?.category?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Type</label>
                        <p class="mt-1 text-sm text-gray-900">{{ assetItems[0]?.type?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Region</label>
                        <p class="mt-1 text-sm text-gray-900">{{ asset?.region?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Location</label>
                        <p class="mt-1 text-sm text-gray-900">{{ asset?.asset_location?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Sub Location</label>
                        <p class="mt-1 text-sm text-gray-900">{{ asset?.sub_location?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Fund Source</label>
                        <p class="mt-1 text-sm text-gray-900">{{ asset?.fund_source?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Acquisition Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ formatDate(asset?.acquisition_date) }}</p>
                    </div>
                </div>
            </div>

            <!-- Asset Items -->
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Asset Items</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Asset Tag
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Serial Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Assignee
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Original Value
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in assetItems" :key="item.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ item.asset_tag }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ item.serial_number || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ formatStatus(item.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ item.assignee?.name || 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${{ item.original_value ? Number(item.original_value).toLocaleString() : 'N/A' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- History Records -->
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">History Records</h2>
                <div class="space-y-4">
                    <div v-for="item in assetItems" :key="item.id" class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Asset Item: {{ item.asset_tag }}</h3>
                        <div v-if="item.asset_history && item.asset_history.length > 0" class="space-y-3">
                            <div v-for="history in item.asset_history" :key="history.id" 
                                class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-sm font-medium text-gray-900">{{ history.action }}</span>
                                            <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">
                                                {{ history.action_type }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2">{{ history.notes }}</p>
                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                            <span>Performed: {{ formatDate(history.performed_at) }}</span>
                                            <span v-if="history.assignee">Assignee: {{ history.assignee?.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p>No history records found for this asset item.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import moment from 'moment';

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    assetItems: {
        type: Array,
        required: true,
    },
    pageTitle: {
        type: String,
        default: 'Asset History',
    },
    pageDescription: {
        type: String,
        default: 'View detailed history for this asset',
    },
});

// Utility functions
const formatStatus = (status) => {
    if (!status) return '-';
    const statusMap = {
        'active': 'Active',
        'in_use': 'In Use',
        'maintenance': 'Maintenance',
        'retired': 'Retired',
        'disposed': 'Disposed',
        'pending_approval': 'Pending Approval'
    };
    return statusMap[status] || status.replace('_', ' ').toUpperCase();
};

const formatDate = (date) => {
    if (!date) return '-';
    return moment(date).format('DD/MM/YYYY');
};

const getStatusClass = (status) => {
    const statusClasses = {
        'active': 'bg-green-100 text-green-800',
        'in_use': 'bg-blue-100 text-blue-800',
        'maintenance': 'bg-yellow-100 text-yellow-800',
        'retired': 'bg-gray-100 text-gray-800',
        'disposed': 'bg-red-100 text-red-800',
        'pending_approval': 'bg-orange-100 text-orange-800'
    };
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
};
</script>
