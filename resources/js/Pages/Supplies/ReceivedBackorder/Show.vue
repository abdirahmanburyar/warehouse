<template>
    <Head title="Received Back Order Details" />
    <AuthenticatedLayout title="Received Back Order Details"
        description="View received back order information" img="/assets/images/supplies.png">
        <div class="overflow-hidden mb-[80px]">
            <div class="text-gray-900">
                <Link :href="route('supplies.received-backorder.index')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Received Back Orders
                </Link>

                <div class="max-w-4xl mx-auto">
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-medium text-gray-900">
                                        {{ receivedBackorder.received_backorder_number }}
                                    </h2>
                                    <p class="text-sm text-gray-500">Received Back Order Details</p>
                                </div>
                                <div class="flex space-x-3">
                                    <Link :href="route('supplies.received-backorder.edit', receivedBackorder.id)"
                                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Edit
                                    </Link>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full"
                                        :class="statusClass(receivedBackorder.status)">
                                        {{ receivedBackorder.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Basic Information -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Product</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.product?.name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Barcode</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.barcode || '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Batch Number</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.batch_number || '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Unit of Measure</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.uom || '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.quantity }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                                            <dd class="text-sm text-gray-900">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    :class="typeClass(receivedBackorder.type)">
                                                    {{ receivedBackorder.type }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Received Date</dt>
                                            <dd class="text-sm text-gray-900">{{ formatDate(receivedBackorder.received_at) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Expire Date</dt>
                                            <dd class="text-sm text-gray-900">{{ formatDate(receivedBackorder.expire_date) || '-' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Financial Information -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h3>
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Unit Cost</dt>
                                            <dd class="text-sm text-gray-900">{{ formatCurrency(receivedBackorder.unit_cost) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Total Cost</dt>
                                            <dd class="text-sm text-gray-900 font-medium">{{ formatCurrency(receivedBackorder.total_cost) }}</dd>
                                        </div>
                                    </dl>

                                    <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">Location Information</h3>
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Warehouse</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.warehouse || '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Location</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.location || '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Facility</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.facility || '-' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- User Information -->
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Received By</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.received_by?.name || '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Reviewed By</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.reviewed_by?.name || '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                                            <dd class="text-sm text-gray-900">{{ receivedBackorder.approved_by?.name || '-' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Status Timeline -->
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status Timeline</h3>
                                    <div class="space-y-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900">Created</p>
                                                <p class="text-sm text-gray-500">{{ formatDateTime(receivedBackorder.created_at) }}</p>
                                            </div>
                                        </div>

                                        <div v-if="receivedBackorder.reviewed_at" class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900">Reviewed</p>
                                                <p class="text-sm text-gray-500">{{ formatDateTime(receivedBackorder.reviewed_at) }}</p>
                                            </div>
                                        </div>

                                        <div v-if="receivedBackorder.approved_at" class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900">Approved</p>
                                                <p class="text-sm text-gray-500">{{ formatDateTime(receivedBackorder.approved_at) }}</p>
                                            </div>
                                        </div>

                                        <div v-if="receivedBackorder.rejected_at" class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900">Rejected</p>
                                                <p class="text-sm text-gray-500">{{ formatDateTime(receivedBackorder.rejected_at) }}</p>
                                                <p v-if="receivedBackorder.rejection_reason" class="text-sm text-red-600 mt-1">
                                                    Reason: {{ receivedBackorder.rejection_reason }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Note -->
                                <div v-if="receivedBackorder.note" class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Note</h3>
                                    <div class="bg-gray-50 p-4 rounded-md">
                                        <p class="text-sm text-gray-700">{{ receivedBackorder.note }}</p>
                                    </div>
                                </div>

                                <!-- Attachments -->
                                <div v-if="receivedBackorder.attachments && receivedBackorder.attachments.length" class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Attachments</h3>
                                    <div class="space-y-2">
                                        <div v-for="(attachment, index) in receivedBackorder.attachments" :key="index" 
                                            class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="text-sm text-gray-700">{{ attachment.name }}</span>
                                            </div>
                                            <a :href="`/storage/${attachment.path}`" target="_blank"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    receivedBackorder: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
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
</script> 