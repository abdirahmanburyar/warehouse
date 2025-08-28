<template>
    <AuthenticatedLayout :title="props.pageTitle" :description="props.pageDescription" img="/assets/images/asset-header.png">
        <div v-if="props.error">
            {{ props.error }}
        </div>
        <div v-else>
            <!-- Header Section -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <Link :href="route('assets.index')" class="text-blue-600 hover:text-blue-800">Back to assets</Link>
                        <h1 class="text-xs font-semibold text-gray-900">
                            Asset ID: {{ props.asset.asset_number }}
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span :class="[
                            statusClasses[props.asset.status] ||
                            statusClasses.default,
                        ]" class="flex items-center text-xs font-bold px-4 py-2">
                            <!-- Status Icon -->
                            <span class="mr-3">
                                <!-- Pending Approval Icon -->
                                <img v-if="props.asset.status === 'pending_approval'" src="/assets/images/pending.png"
                                    class="w-4 h-4" alt="Pending Approval" />
                                <!-- Approved Icon -->
                                <img v-else-if="props.asset.status === 'approved'" src="/assets/images/approved.png"
                                    class="w-4 h-4" alt="Approved" />
                                <!-- Rejected Icon -->
                                <img v-else-if="props.asset.status === 'rejected'" src="/assets/images/rejected.png"
                                    class="w-4 h-4" alt="Rejected" />
                                <!-- In Use Icon -->
                                <img v-else-if="props.asset.status === 'in_use'" src="/assets/images/inprocess.png"
                                    class="w-4 h-4" alt="In Use" />
                            </span>
                            {{ props.asset.status.replace('_', ' ').toUpperCase() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Asset Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                <!-- Basic Asset Information -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h2 class="text-lg font-medium text-gray-900">
                        Asset Details
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Asset Number</p>
                            <p class="text-xs text-gray-900 font-semibold">{{ props.asset.asset_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Acquisition Date</p>
                            <p class="text-xs text-gray-900">{{ formatDate(props.asset.acquisition_date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Fund Source</p>
                            <p class="text-xs text-gray-900">{{ props.asset.fund_source.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Region</p>
                            <p class="text-xs text-gray-900">{{ props.asset.region.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Asset Location</p>
                            <p class="text-xs text-gray-900">{{ props.asset.asset_location.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Sub Location</p>
                            <p class="text-xs text-gray-900">{{ props.asset.sub_location.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submission Information -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h2 class="text-xs font-medium text-gray-900">
                        Submission Details
                    </h2>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Submitted By</p>
                            <p class="text-xs text-gray-900">{{ props.asset.submitted_by.name }}</p>
                            <p class="text-xs text-gray-600">{{ props.asset.submitted_by.title }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Submitted At</p>
                            <p class="text-xs text-gray-900">{{ formatDateTime(props.asset.submitted_at) }}</p>
                        </div>
                        <div v-if="props.asset.reviewed_by">
                            <p class="text-xs font-medium text-gray-500">Reviewed By</p>
                            <p class="text-xs text-gray-900">{{ props.asset.reviewed_by.name }}</p>
                            <p class="text-xs text-gray-600">{{ formatDateTime(props.asset.reviewed_at) }}</p>
                        </div>
                        <div v-if="props.asset.approved_by">
                            <p class="text-xs font-medium text-gray-500">Approved By</p>
                            <p class="text-xs text-gray-900">{{ props.asset.approved_by.name }}</p>
                            <p class="text-xs text-gray-600">{{ formatDateTime(props.asset.approved_at) }}</p>
                        </div>
                        <div v-if="props.asset.rejected_by">
                            <p class="text-xs font-medium text-gray-500">Rejected By</p>
                            <p class="text-xs text-gray-900">{{ props.asset.rejected_by.name }}</p>
                            <p class="text-xs text-gray-600">{{ formatDateTime(props.asset.rejected_at) }}</p>
                            <p v-if="props.asset.rejection_reason" class="text-xs text-red-600 mt-1">{{ props.asset.rejection_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Status Timeline -->
            <div v-if="props.asset.status === 'rejected'" class="flex flex-col items-center mb-6">
                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10 bg-white border-red-500">
                    <img src="/assets/images/rejected.png" class="w-7 h-7" alt="Rejected" />
                </div>
                <h1 class="mt-3 text-2xl text-red-600 font-bold">Rejected</h1>
            </div>
            <div v-else class="col-span-2 mb-6">
                <div class="relative">
                    <!-- Timeline Track Background -->
                    <div class="absolute top-7 left-0 right-0 h-2 bg-gray-200 z-0"></div>

                    <!-- Timeline Progress -->
                    <div class="absolute top-7 left-0 h-2 bg-green-500 z-0 transition-all duration-500 ease-in-out"
                        :style="{
                            width: `${(assetStatusOrder.indexOf(props.asset.status) /
                                (assetStatusOrder.length - 1)) *
                                100
                                }%`,
                        }"></div>

                    <!-- Timeline Steps -->
                    <div class="relative flex justify-between">
                        <!-- Submitted -->
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10" :class="[
                                assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('pending_approval')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]">
                                <img src="/assets/images/pending.png" class="w-7 h-7" alt="Submitted" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('pending_approval')
                                    ? ''
                                    : 'opacity-40'
                                    " />
                            </div>
                            <span class="mt-3 text-xs font-bold" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                assetStatusOrder.indexOf('pending_approval')
                                ? 'text-green-600'
                                : 'text-gray-500'
                                ">Submitted</span>
                        </div>

                        <!-- Reviewed -->
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10" :class="[
                                assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('reviewed')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]">
                                <img src="/assets/images/review.png" class="w-7 h-7" alt="Reviewed" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('reviewed')
                                    ? ''
                                    : 'opacity-40'
                                    " />
                            </div>
                            <span class="mt-3 text-xs font-bold" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                assetStatusOrder.indexOf('reviewed')
                                ? 'text-green-600'
                                : 'text-gray-500'
                                ">Reviewed</span>
                        </div>

                        <!-- Approved -->
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10" :class="[
                                assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('approved')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]">
                                <img src="/assets/images/approved.png" class="w-7 h-7" alt="Approved" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('approved')
                                    ? ''
                                    : 'opacity-40'
                                    " />
                            </div>
                            <span class="mt-3 text-xs font-bold" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                assetStatusOrder.indexOf('approved')
                                ? 'text-green-600'
                                : 'text-gray-500'
                                ">Approved</span>
                        </div>

                        <!-- In Use -->
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10" :class="[
                                assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('in_use')
                                    ? 'bg-white border-green-500'
                                    : 'bg-white border-gray-200',
                            ]">
                                <img src="/assets/images/inprocess.png" class="w-7 h-7" alt="In Use" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                    assetStatusOrder.indexOf('in_use')
                                    ? ''
                                    : 'opacity-40'
                                    " />
                            </div>
                            <span class="mt-3 text-xs font-bold" :class="assetStatusOrder.indexOf(props.asset.status) >=
                                assetStatusOrder.indexOf('in_use')
                                ? 'text-green-600'
                                : 'text-gray-500'
                                ">In Use</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Items Table -->
            <h2 class="text-xs text-gray-900 mb-4 px-6">Asset Items</h2>
            <div class="px-6">
                <table class="w-full overflow-hidden text-sm text-left table-sm rounded-t-lg">
                    <thead>
                        <tr style="background-color: #F4F7FB;">
                            <th class="px-3 py-2 text-xs font-bold rounded-tl-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Asset Tag</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Asset Name</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Serial Number</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Category</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Type</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Status</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Assignee</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Original Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="props.asset.asset_items.length === 0">
                            <td colspan="8" class="px-3 py-3 text-center text-gray-500 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                No asset items found.
                            </td>
                        </tr>
                        <tr v-for="item in props.asset.asset_items" :key="item.id" 
                            class="hover:bg-gray-50 transition-colors duration-150 border-b"
                            style="border-bottom: 1px solid #F4F7FB;">
                            <td class="px-3 py-3 text-xs text-gray-900">
                                <span class="font-semibold">{{ item.asset_tag }}</span>
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-900">{{ item.asset_name }}</td>
                            <td class="px-3 py-3 text-xs text-gray-900">{{ item.serial_number }}</td>
                            <td class="px-3 py-3 text-xs text-gray-900">{{ item.category.name }}</td>
                            <td class="px-3 py-3 text-xs text-gray-900">{{ item.type.name }}</td>
                            <td class="px-3 py-3 text-xs text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                                    :class="getStatusClasses(item.status)">
                                    {{ item.status.replace('_', ' ').toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-900">{{ item.assignee.name }}</td>
                            <td class="px-3 py-3 text-xs text-gray-900">${{ item.original_value }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Asset Status Actions -->
            <div class="mt-8 mb-6 bg-white rounded-lg shadow-sm mx-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                    Asset Status Actions
                </h3>
                <div class="flex items-start mb-6">
                    <div class="flex flex-wrap items-center justify-center gap-4 px-1 py-2">
                        <!-- Review button -->
                        <div class="relative" v-if="props.asset.status === 'pending_approval'">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('reviewed')" 
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-yellow-500 hover:bg-yellow-600">
                                    <img src="/assets/images/review.png" class="w-5 h-5 mr-2" alt="Review" />
                                    <span class="text-sm font-bold text-white">Review</span>
                                </button>
                            </div>
                            <div class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Approve button -->
                        <div class="relative" v-if="props.asset.status === 'reviewed'">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('approved')" 
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-yellow-500 hover:bg-yellow-600">
                                    <img src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
                                    <span class="text-sm font-bold text-white">Approve</span>
                                </button>
                            </div>
                            <div class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Activate button -->
                        <div class="relative" v-if="props.asset.status === 'approved'">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('in_use')" 
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-yellow-500 hover:bg-yellow-600">
                                    <img src="/assets/images/inprocess.png" class="w-5 h-5 mr-2" alt="Activate" />
                                    <span class="text-sm font-bold text-white">Activate</span>
                                </button>
                            </div>
                            <div class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Reject button -->
                        <div class="relative" v-if="props.asset.status === 'pending_approval' || props.asset.status === 'reviewed'">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('rejected')" 
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-red-500 hover:bg-red-600">
                                    <img src="/assets/images/rejected.png" class="w-5 h-5 mr-2" alt="Reject" />
                                    <span class="text-sm font-bold text-white">Reject</span>
                                </button>
                            </div>
                        </div>

                        <!-- Restore button -->
                        <div class="relative" v-if="props.asset.status === 'rejected'">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('pending_approval')" 
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-green-500 hover:bg-green-600">
                                    <img src="/assets/images/restore.jpg" class="w-5 h-5 mr-2" alt="Restore" />
                                    <span class="text-sm font-bold text-white">Restore</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    asset: Object,
    pageTitle: String,
    pageDescription: String,
    error: String,
});

// Status classes for styling
const statusClasses = {
    pending_approval: "bg-yellow-100 text-yellow-800 rounded-full font-bold",
    reviewed: "bg-green-100 text-green-800 rounded-full font-bold",
    approved: "bg-green-100 text-green-800 rounded-full font-bold",
    in_use: "bg-blue-100 text-blue-800 rounded-full font-bold",
    rejected: "bg-red-100 text-red-800 rounded-full font-bold",
    default: "bg-gray-100 text-gray-800 rounded-full font-bold",
};

// Asset status order for timeline
const assetStatusOrder = [
    "pending_approval",
    "reviewed",
    "approved",
    "in_use",
];

// Helper function to format dates
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Helper function to format date and time
const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Get status classes for asset items
const getStatusClasses = (status) => {
    const statusClassMap = {
        'in_use': 'bg-green-100 text-green-800',
        'available': 'bg-blue-100 text-blue-800',
        'maintenance': 'bg-yellow-100 text-yellow-800',
        'retired': 'bg-gray-100 text-gray-800',
        'lost': 'bg-red-100 text-red-800',
    };
    return statusClassMap[status] || 'bg-gray-100 text-gray-800';
};

// Change asset status function
const changeAssetStatus = (newStatus) => {
    // This would typically make an API call to update the asset status
    console.log(`Changing asset status to: ${newStatus}`);
    // You can implement the actual status change logic here
};
</script>