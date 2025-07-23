<template>
    <AuthenticatedLayout
        title="Asset History"
        description="Complete audit trail and history of asset changes"
        img="/assets/images/history-header.png"
    >
        <div class="space-y-6">
            <!-- Asset Info Header -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-white">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Asset History</h1>
                                <p class="text-blue-100 text-sm mt-1">
                                    {{ asset?.asset_tag }} - {{ asset?.serial_number }}
                                </p>
                                <p class="text-blue-100 text-sm">
                                    {{ asset?.item_description }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 sm:mt-0">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ asset?.status }}</div>
                                <div class="text-blue-100 text-sm">Current Status</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
                            <select
                                v-model="filters.actionType"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Actions</option>
                                <option value="approval">Approvals</option>
                                <option value="transfer">Transfers</option>
                                <option value="retirement">Retirements</option>
                                <option value="status_change">Status Changes</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                            <select
                                v-model="filters.dateRange"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Performed By</label>
                            <select
                                v-model="filters.performedBy"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Users</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Timeline -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Activity Timeline</h2>
                    
                    <div v-if="loading" class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>

                    <div v-else-if="!history.data || history.data.length === 0" class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16 text-gray-400 mx-auto mb-4">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No history found</h3>
                        <p class="text-gray-500">There are no history records for this asset.</p>
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="record in history.data" :key="record.id" class="relative">
                            <!-- Timeline connector -->
                            <div class="absolute left-6 top-8 w-0.5 h-full bg-gray-200"></div>
                            
                            <div class="relative flex items-start space-x-4">
                                <!-- Action Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                                         :class="getActionIconClass(record.action_type)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-white">
                                            <path v-if="record.action_type === 'approval'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            <path v-else-if="record.action_type === 'transfer'" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            <path v-else-if="record.action_type === 'retirement'" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            <path v-else d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                {{ formatActionTitle(record.action, record.action_type) }}
                                            </h3>
                                            <span class="text-sm text-gray-500">
                                                {{ formatDate(record.performed_at) }}
                                            </span>
                                        </div>
                                        
                                        <div class="text-sm text-gray-600 mb-3">
                                            <p><strong>Performed by:</strong> {{ record.performer?.name || 'Unknown' }}</p>
                                            <p v-if="record.notes"><strong>Notes:</strong> {{ record.notes }}</p>
                                        </div>

                                        <!-- Change Details -->
                                        <div v-if="record.old_value || record.new_value" class="bg-white rounded border p-3">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Changes:</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div v-if="record.old_value">
                                                    <span class="text-gray-500">From:</span>
                                                    <span class="ml-2 text-red-600">{{ formatValue(record.old_value) }}</span>
                                                </div>
                                                <div v-if="record.new_value">
                                                    <span class="text-gray-500">To:</span>
                                                    <span class="ml-2 text-green-600">{{ formatValue(record.new_value) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Approval Context -->
                                        <div v-if="record.approval" class="mt-3 pt-3 border-t border-gray-200">
                                            <p class="text-xs text-gray-500">
                                                <strong>Approval ID:</strong> {{ record.approval.id }} | 
                                                <strong>Status:</strong> {{ record.approval.status }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="history.data && history.data.length > 0" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-end">
                        <TailwindPagination
                            :data="history"
                            @pagination-change-page="getResults"
                            :limit="2"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import moment from 'moment';
import TailwindPagination from '@/Components/Pagination.vue';

const props = defineProps({
    asset: Object,
    history: Object,
    users: Array,
    filters: Object,
});

const page = usePage();
const loading = ref(false);

const filters = ref({
    actionType: props.filters?.actionType || '',
    dateRange: props.filters?.dateRange || '',
    performedBy: props.filters?.performedBy || '',
});

// Watch for filter changes and reload data
watch(filters, async (newFilters) => {
    await loadHistory();
}, { deep: true });

async function loadHistory() {
    loading.value = true;
    try {
        router.get(route('assets.history', props.asset.id), filters.value, {
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Error loading history:', error);
    } finally {
        loading.value = false;
    }
}

function getActionIconClass(actionType) {
    const classes = {
        'approval': 'bg-blue-500',
        'transfer': 'bg-green-500',
        'retirement': 'bg-red-500',
        'status_change': 'bg-yellow-500',
    };
    return classes[actionType] || 'bg-gray-500';
}

function formatActionTitle(action, actionType) {
    const titles = {
        'reviewed': 'Asset Reviewed',
        'approved': 'Asset Approved',
        'rejected': 'Asset Rejected',
        'transfer_reviewed': 'Transfer Reviewed',
        'transfer_approved': 'Transfer Approved',
        'transfer_rejected': 'Transfer Rejected',
        'retirement_reviewed': 'Retirement Reviewed',
        'retirement_approved': 'Retirement Approved',
        'retirement_rejected': 'Retirement Rejected',
    };
    return titles[action] || `${actionType.replace('_', ' ').toUpperCase()}`;
}

function formatValue(value) {
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    return value;
}

function formatDate(date) {
    if (!date) return '-';
    return moment(date).format('DD/MM/YYYY HH:mm');
}

function getResults(page) {
    router.get(route('assets.history', props.asset.id), {
        ...filters.value,
        page: page
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

onMounted(() => {
    // Initialize any necessary data
});
</script>
