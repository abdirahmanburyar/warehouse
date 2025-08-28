<template>
    <AuthenticatedLayout :title="props.pageTitle" :description="props.pageDescription" img="/assets/images/asset-header.png">
        <div v-if="props.error">
            {{ props.error }}
        </div>
        <div v-else>
            {{ props.asset }}
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
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="props.asset.asset_items.length === 0">
                            <td colspan="9" class="px-3 py-3 text-center text-gray-500 border-b" style="border-bottom: 1px solid #B7C6E6;">
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
                            <td class="px-3 py-3 text-xs text-center">
                                <button @click="openHistoryModal(item)" 
                                    class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-150">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    History
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Asset Item History Modal -->
            <div v-if="showHistoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">
                            Asset Item History - {{ selectedItem?.asset_tag }}
                        </h3>
                        <button @click="closeHistoryModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto max-h-[70vh]">
                        <div v-if="selectedItem?.asset_history && selectedItem.asset_history.length > 0" class="space-y-4">
                            <div v-for="(history, index) in selectedItem.asset_history" :key="index" 
                                class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-sm font-semibold text-gray-900">{{ history.action }}</span>
                                            <span class="text-xs text-gray-500">•</span>
                                            <span class="text-xs text-gray-500">{{ formatDateTime(history.performed_at) }}</span>
                                        </div>
                                        <p v-if="history.notes" class="text-sm text-gray-700 mb-2">{{ history.notes }}</p>
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                            <span>By: {{ history.performer?.name || 'Unknown' }}</span>
                                            <span v-if="history.action_type" class="px-2 py-1 bg-gray-100 rounded-full">
                                                {{ history.action_type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No history found</h3>
                            <p class="mt-1 text-sm text-gray-500">This asset item doesn't have any history records yet.</p>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-200 flex justify-end">
                        <button @click="closeHistoryModal" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-150">
                            Close
                        </button>
                    </div>
                </div>
            </div>

            <!-- Asset Status Actions -->
            <div class="mt-8 mb-6 bg-white rounded-lg shadow-sm mx-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                    Asset Status Actions
                </h3>
                <div class="flex items-start mb-6">
                    <div class="flex flex-wrap items-center justify-center gap-4 px-1 py-2">
                        <!-- Review button - shows when actionable or completed -->
                        <div class="relative" v-if="props.asset.status === 'pending_approval' || assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf('reviewed')">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('reviewed')" 
                                    :disabled="isReviewing || assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf('reviewed')"
                                    :class="[
                                        assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf('reviewed')
                                            ? 'bg-green-500'
                                            : 'bg-yellow-500 hover:bg-yellow-600'
                                    ]" class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="isReviewing" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <img v-else src="/assets/images/review.png" class="w-5 h-5 mr-2" alt="Review" />
                                    <span class="text-sm font-bold text-white">{{
                                        assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf("reviewed")
                                            ? "Reviewed"
                                            : isReviewing
                                                ? "Please Wait..."
                                                : "Review"
                                    }}</span>
                                </button>
                                <span v-show="props.asset?.reviewed_at" class="text-sm text-gray-600">
                                    {{ formatDateTime(props.asset?.reviewed_at) }}
                                </span>
                                <span v-show="props.asset?.reviewed_by" class="text-sm text-gray-600">
                                    By {{ props.asset?.reviewed_by?.name }}
                                </span>
                            </div>
                            <div v-if="props.asset.status === 'pending_approval'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Approve button - shows when actionable or completed -->
                        <div class="relative" v-if="props.asset.status === 'reviewed' || assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf('approved')">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('approved')" 
                                    :disabled="isApproving || assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf('approved')"
                                    :class="[
                                        assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf('approved')
                                            ? 'bg-green-500'
                                            : 'bg-yellow-500 hover:bg-yellow-600'
                                    ]" class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="isApproving" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <img v-else src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
                                    <span class="text-sm font-bold text-white">{{
                                        assetStatusOrder.indexOf(props.asset.status) >= assetStatusOrder.indexOf("approved")
                                            ? "Approved"
                                            : isApproving ? "Please Wait..." : "Approve"
                                    }}</span>
                                </button>
                                <span v-show="props.asset?.approved_at" class="text-sm text-gray-600">
                                    {{ formatDateTime(props.asset?.approved_at) }}
                                </span>
                                <span v-show="props.asset?.approved_by" class="text-sm text-gray-600">
                                    By {{ props.asset?.approved_by?.name }}
                                </span>
                            </div>
                            <div v-if="props.asset.status === 'reviewed'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Reject button - only shows when actionable -->
                        <div class="relative" v-if="(props.asset.status === 'pending_approval' || props.asset.status === 'reviewed') && props.asset.status !== 'rejected'">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('rejected')" 
                                    :disabled="isRejecting"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-red-500 hover:bg-red-600">
                                    <svg v-if="isRejecting" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <img v-else src="/assets/images/rejected.png" class="w-5 h-5 mr-2" alt="Reject" />
                                    <span class="text-sm font-bold text-white">{{ isRejecting ? 'Please Wait...' : 'Reject' }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Rejected status display - shows when rejected -->
                        <div class="relative" v-if="props.asset.status === 'rejected'">
                            <div class="flex flex-col">
                                <button disabled
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-red-500">
                                    <img src="/assets/images/rejected.png" class="w-5 h-5 mr-2" alt="Rejected" />
                                    <span class="text-sm font-bold text-white">Rejected</span>
                                </button>
                                <span v-show="props.asset?.rejected_at" class="text-sm text-gray-600">
                                    {{ formatDateTime(props.asset?.rejected_at) }}
                                </span>
                                <span v-show="props.asset?.rejected_by" class="text-sm text-gray-600">
                                    By {{ props.asset?.rejected_by?.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Restore button - shows when rejected -->
                        <div class="relative" v-if="props.asset.status === 'rejected'">
                            <div class="flex flex-col">
                                <button @click="changeAssetStatus('pending_approval')" 
                                    :disabled="isRestoring"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-green-500 hover:bg-green-600">
                                    <svg v-if="isRestoring" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <img v-else src="/assets/images/restore.jpg" class="w-5 h-5 mr-2" alt="Restore" />
                                    <span class="text-sm font-bold text-white">{{ isRestoring ? 'Restoring...' : 'Restore' }}</span>
                                </button>
                            </div>
                            <div v-if="props.asset.status === 'rejected'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
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
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
    asset: Object,
    pageTitle: String,
    pageDescription: String,
    error: String,
});

// Loading states for different actions
const isReviewing = ref(false);
const isApproving = ref(false);
const isRejecting = ref(false);
const isRestoring = ref(false);
const isActivating = ref(false);

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

// State for history modal
const showHistoryModal = ref(false);
const selectedItem = ref(null);

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

// Open history modal
const openHistoryModal = (item) => {
    selectedItem.value = item;
    showHistoryModal.value = true;
};

// Close history modal
const closeHistoryModal = () => {
    showHistoryModal.value = false;
    selectedItem.value = null;
};

// Review asset function with Swal confirmation
const reviewAsset = async () => {
    const result = await Swal.fire({
        title: 'Review Asset',
        text: 'Are you sure you want to review this asset?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Review it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            isReviewing.value = true;
            const response = await axios.post(route('assets.review', props.asset.id));
            
            if (response.data.success) {
                Swal.fire(
                    'Reviewed!',
                    'Asset has been reviewed successfully.',
                    'success'
                ).then(() => {
                    // Refresh the page to show updated status
                    router.get(route('assets.show', props.asset.id));
                });
            } else {
                Swal.fire(
                    'Error!',
                    response.data.message || 'Failed to review asset',
                    'error'
                );
            }
        } catch (error) {
            console.error('Review error:', error);
            Swal.fire(
                'Error!',
                error.response?.data?.message || 'Failed to review asset',
                'error'
            );
        } finally {
            isReviewing.value = false;
        }
    }
};

// Approve asset function with Swal confirmation
const approveAsset = async () => {
    const result = await Swal.fire({
        title: 'Approve Asset',
        text: 'Are you sure you want to approve this asset?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Approve it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            isApproving.value = true;
            const response = await axios.post(route('assets.approve', props.asset.id));
            
            if (response.data.success) {
                Swal.fire(
                    'Approved!',
                    'Asset has been approved successfully.',
                    'success'
                ).then(() => {
                    // Refresh the page to show updated status
                    router.get(route('assets.show', props.asset.id));
                });
            } else {
                Swal.fire(
                    'Error!',
                    response.data.message || 'Failed to approve asset',
                    'error'
                );
            }
        } catch (error) {
            console.error('Approve error:', error);
            Swal.fire(
                'Error!',
                error.response?.data?.message || 'Failed to approve asset',
                'error'
            );
        } finally {
            isApproving.value = false;
        }
    }
};

// Reject asset function with Swal confirmation
const rejectAsset = async () => {
    const { value: rejectionReason } = await Swal.fire({
        title: 'Reject Asset',
        text: 'Please provide a reason for rejection:',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason...',
        inputAttributes: {
            'aria-label': 'Type your rejection reason here'
        },
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Reject Asset',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a rejection reason!';
            }
        }
    });

    if (rejectionReason) {
        try {
            isRejecting.value = true;
            const response = await axios.post(route('assets.reject', props.asset.id), {
                rejection_reason: rejectionReason
            });
            
            if (response.data.success) {
                Swal.fire(
                    'Rejected!',
                    'Asset has been rejected successfully.',
                    'success'
                ).then(() => {
                    // Refresh the page to show updated status
                    router.get(route('assets.show', props.asset.id));
                });
            } else {
                Swal.fire(
                    'Error!',
                    response.data.message || 'Failed to reject asset',
                    'error'
                );
            }
        } catch (error) {
            console.error('Reject error:', error);
            Swal.fire(
                'Error!',
                error.response?.data?.message || 'Failed to reject asset',
                'error'
            );
        } finally {
            isRejecting.value = false;
        }
    }
};

// Restore asset function with Swal confirmation
const restoreAsset = async () => {
    const result = await Swal.fire({
        title: 'Restore Asset',
        text: 'Are you sure you want to restore this asset to pending approval?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Restore it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            isRestoring.value = true;
            const response = await axios.post(route('assets.restore', props.asset.id));
            
            if (response.data.success) {
                Swal.fire(
                    'Restored!',
                    'Asset has been restored successfully.',
                    'success'
                ).then(() => {
                    // Refresh the page to show updated status
                    router.get(route('assets.show', props.asset.id));
                });
            } else {
                Swal.fire(
                    'Error!',
                    response.data.message || 'Failed to restore asset',
                    'error'
                );
            }
        } catch (error) {
            console.error('Restore error:', error);
            Swal.fire(
                'Error!',
                error.response?.data?.message || 'Failed to restore asset',
                'error'
            );
        } finally {
            isRestoring.value = false;
        }
    }
};

// Activate asset function with Swal confirmation
const activateAsset = async () => {
    const result = await Swal.fire({
        title: 'Activate Asset',
        text: 'Are you sure you want to activate this asset and put it in use?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Activate it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            isActivating.value = true;
            // You might need to create a custom endpoint for this or use a general update method
            const response = await axios.patch(route('assets.update', props.asset.id), {
                status: 'in_use',
                activated_by: auth()?.id(),
                activated_at: new Date().toISOString()
            });
            
            if (response.data.success) {
                Swal.fire(
                    'Activated!',
                    'Asset has been activated successfully.',
                    'success'
                ).then(() => {
                    // Refresh the page to show updated status
                    router.get(route('assets.show', props.asset.id));
                });
            } else {
                Swal.fire(
                    'Error!',
                    response.data.message || 'Failed to activate asset',
                    'error'
                );
            }
        } catch (error) {
            console.error('Activate error:', error);
            Swal.fire(
                'Error!',
                error.response?.data?.message || 'Failed to activate asset',
                'error'
            );
        } finally {
            isActivating.value = false;
        }
    }
};

// Change asset status function - now properly calls the specific functions
const changeAssetStatus = (newStatus) => {
    switch (newStatus) {
        case 'reviewed':
            reviewAsset();
            break;
        case 'approved':
            approveAsset();
            break;
        case 'rejected':
            rejectAsset();
            break;
        case 'pending_approval':
            restoreAsset();
            break;
        case 'in_use':
            activateAsset();
            break;
        default:
            console.log(`Unknown status: ${newStatus}`);
    }
};
</script>