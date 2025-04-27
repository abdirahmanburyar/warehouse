<template>
    <div class="min-h-screen bg-gray-100">
        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Asset Details Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ asset.name }}</h1>
                                <div class="mt-2 text-sm text-gray-600">
                                    <p><span class="font-medium">Serial Number:</span> {{ asset.serial_number }}</p>
                                    <p><span class="font-medium">Category:</span> {{ asset.category }}</p>
                                    <p><span class="font-medium">Current Custodian:</span> {{ asset.custody || 'None' }}</p>
                                </div>
                            </div>
                            <Link :href="route('assets.index')" 
                                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Back to Assets
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- History Timeline -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Asset History</h2>
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                <li v-for="(record, recordIdx) in history" :key="record.id">
                                    <div class="relative pb-8">
                                        <span v-if="recordIdx !== history.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <!-- Icon -->
                                            <div>
                                                <span :class="[
                                                    'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white',
                                                    {
                                                        'bg-green-500': record.action === 'created',
                                                        'bg-blue-500': record.action === 'updated',
                                                        'bg-yellow-500': record.action === 'assigned',
                                                        'bg-red-500': record.action === 'returned'
                                                    }
                                                ]">
                                                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm-.75-6.75a.75.75 0 011.5 0v2.25h2.25a.75.75 0 010 1.5h-3a.75.75 0 01-.75-.75v-3z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900">
                                                            {{ formatAction(record.action) }}
                                                        </span>
                                                    </div>
                                                    <p class="mt-0.5 text-sm text-gray-500">
                                                        By {{ record.assigned_by?.name || 'System' }} • 
                                                        {{ formatDate(record.assigned_at) }}
                                                    </p>
                                                </div>
                                                <!-- Changes -->
                                                <div v-if="record.formatted_changes?.length" class="mt-2">
                                                    <div class="rounded-md bg-gray-50 p-4">
                                                        <div class="text-sm text-gray-700">
                                                            <div v-for="change in record.formatted_changes" :key="change.field" class="mb-1">
                                                                <span class="font-medium">{{ change.field }}:</span> {{ change.value }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Notes -->
                                                <div v-if="record.assignment_notes || record.return_notes" class="mt-2 text-sm text-gray-700">
                                                    <p v-if="record.assignment_notes">
                                                        <span class="font-medium">Assignment Notes:</span> {{ record.assignment_notes }}
                                                    </p>
                                                    <p v-if="record.return_notes">
                                                        <span class="font-medium">Return Notes:</span> {{ record.return_notes }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    asset: Object,
    history: Array
});

const formatAction = (action) => {
    const actions = {
        'created': 'Asset Created',
        'updated': 'Asset Updated',
        'assigned': 'Assigned to Custodian',
        'returned': 'Returned from Custodian'
    };
    return actions[action] || action;
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
