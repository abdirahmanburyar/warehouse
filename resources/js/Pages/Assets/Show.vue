<template>
    <AuthenticatedLayout title="Asset Details & Workflow" :description="props.assetItem?.asset_number || 'Select an asset to view details and workflow'">
        <!-- Page Description -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Asset Details & Workflow:</strong> This page displays comprehensive asset information including details, approval workflow, and asset items. 
                        You can view the complete approval workflow history for each asset, including submitted, reviewed, approved, and rejected items.
                    </p>
                </div>
            </div>
        </div>

        <!-- Asset Selection -->
        <div class="mb-6">
            <div class="w-[300px]">
                <Multiselect
                    v-model="selectedAsset"
                    :options="props.assets"
                    placeholder="Select an asset"
                    @select="handleSelectAsset"
                    class="multiselect-modern"
                    :searchable="true"
                    :show-labels="false"
                    :close-on-select="true"
                    track-by="id"
                    label="asset_number"
                    :custom-label="(asset) => `${asset.asset_number} - ${asset.region_name || 'N/A'}`"
                />
            </div>
        </div>

        <!-- Asset Details and Workflow Interface -->
        <div v-if="props.assetItem" class="space-y-6">
            <!-- Asset Information Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Asset Details -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h2 class="text-lg font-medium text-gray-900">
                        Asset Details
                    </h2>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-sm text-gray-600">Asset #{{ props.assetItem.asset_number }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm text-gray-600">Acquired: {{ formatDate(props.assetItem.acquisition_date) }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                                                 <span class="text-sm text-gray-600">Location: {{ props.assetItem.region?.name || 'N/A' }} - {{ props.assetItem.assetLocation?.name || 'N/A' }} - {{ props.assetItem.subLocation?.name || 'N/A' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600">Status: {{ formatAssetStatus(getAssetStatus(props.assetItem)) }}</span>
                    </div>
                </div>

                <!-- Asset Metadata -->
                <div>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <h2 class="text-xs font-medium text-gray-900">
                            Asset Information
                        </h2>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">
                                    Fund Source
                                </p>
                                <p class="text-xs text-gray-900">
                                                                         {{ props.assetItem.fundSource?.name || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Workflow Status -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Approval Workflow Status</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Submitted Stage -->
                        <div class="text-center p-3 rounded-lg" :class="props.assetItem?.submitted_at ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                            <div class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center" :class="props.assetItem?.submitted_at ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'">
                                <svg v-if="props.assetItem?.submitted_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold">1</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">Submitted</p>
                            <p class="text-xs text-gray-500">{{ props.assetItem?.submitted_at ? formatDate(props.assetItem.submitted_at) : 'Pending' }}</p>
                        </div>

                        <!-- Reviewed Stage -->
                        <div class="text-center p-3 rounded-lg" :class="props.assetItem?.reviewed_at ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                            <div class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center" :class="props.assetItem?.reviewed_at ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'">
                                <svg v-if="props.assetItem?.reviewed_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold">2</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">Reviewed</p>
                            <p class="text-xs text-gray-500">{{ props.assetItem?.reviewed_at ? formatDate(props.assetItem.reviewed_at) : 'Pending' }}</p>
                        </div>

                        <!-- Approved Stage -->
                        <div class="text-center p-3 rounded-lg" :class="props.assetItem?.approved_at ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                            <div class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center" :class="props.assetItem?.approved_at ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'">
                                <svg v-if="props.assetItem?.approved_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold">3</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">Approved</p>
                            <p class="text-xs text-gray-500">{{ props.assetItem?.approved_at ? formatDate(props.assetItem.approved_at) : 'Pending' }}</p>
                        </div>

                        <!-- Rejected Stage -->
                        <div class="text-center p-3 rounded-lg" :class="props.assetItem?.rejected_at ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200'">
                            <div class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center" :class="props.assetItem?.rejected_at ? 'bg-red-500 text-white' : 'bg-gray-400 text-white'">
                                <svg v-if="props.assetItem?.rejected_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold">4</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">Rejected</p>
                            <p class="text-xs text-gray-500">{{ props.assetItem?.rejected_at ? formatDate(props.assetItem.rejected_at) : 'Pending' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Items Table -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Asset Items</h2>
                </div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                                                         <tr v-for="item in props.assetItem.assetItems" :key="item.id">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link :href="route('assets.history.index', item.id)" class="text-blue-600 hover:text-blue-900">
                                        View History
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

                                    <!-- Approval Actions -->
                        <div class="bg-white rounded-lg shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">Approval Actions</h2>
                            </div>
                            <div class="p-6">
                                <!-- Review Button - Only show if submitted but not reviewed -->
                                <div v-if="canReview" class="mb-4">
                                    <button
                                        @click="reviewAsset"
                                        :disabled="isProcessing"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    >
                                        <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Review Asset
                                    </button>
                                </div>

                                <!-- Approve/Reject Buttons - Only show if reviewed but not approved/rejected -->
                                <div v-if="canApproveReject" class="mb-4 space-y-3">
                                    <div class="flex space-x-3">
                                        <button
                                            @click="approveAsset"
                                            :disabled="isProcessing"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        >
                                            <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Approve Asset
                                        </button>

                                        <button
                                            @click="showRejectModal = true"
                                            :disabled="isProcessing"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Reject Asset
                                        </button>
                                    </div>
                                </div>

                                <!-- Rollback Button - Only show if rejected -->
                                <div v-if="canRollback" class="mb-4">
                                    <button
                                        @click="rollbackAsset"
                                        :disabled="isProcessing"
                                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    >
                                        <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                        </svg>
                                        Rollback Rejection
                                    </button>
                                </div>

                                <!-- Status Messages -->
                                <div v-if="statusMessage" class="mt-4 p-3 rounded-md" :class="statusMessageClass">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg v-if="statusMessageType === 'success'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else-if="statusMessageType === 'error'" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium" :class="statusMessageTextClass">
                                                {{ statusMessage }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approval Details Summary -->
                        <div class="bg-white rounded-lg shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">Approval Details Summary</h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">Submitted By</h3>
                                        <p class="text-sm text-gray-900">{{ props.assetItem.submitted_by?.name || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">Submitted At</h3>
                                        <p class="text-sm text-gray-900">{{ props.assetItem.submitted_at ? formatDate(props.assetItem.submitted_at) : 'N/A' }}</p>
                                    </div>
                                    <div v-if="props.assetItem.reviewed_by">
                                        <h3 class="text-sm font-medium text-gray-500">Reviewed By</h3>
                                        <p class="text-sm text-gray-900">{{ props.assetItem.reviewed_by.name }}</p>
                                    </div>
                                    <div v-if="props.assetItem.reviewed_at">
                                        <h3 class="text-sm font-medium text-gray-500">Reviewed At</h3>
                                        <p class="text-sm text-gray-900">{{ formatDate(props.assetItem.reviewed_at) }}</p>
                                    </div>
                                    <div v-if="props.assetItem.approved_by">
                                        <h3 class="text-sm font-medium text-gray-500">Approved By</h3>
                                        <p class="text-sm text-gray-900">{{ props.assetItem.approved_by.name }}</p>
                                    </div>
                                    <div v-if="props.assetItem.approved_at">
                                        <h3 class="text-sm font-medium text-gray-500">Approved At</h3>
                                        <p class="text-sm text-gray-900">{{ formatDate(props.assetItem.approved_at) }}</p>
                                    </div>
                                    <div v-if="props.assetItem.rejected_by">
                                        <h3 class="text-sm font-medium text-gray-500">Rejected By</h3>
                                        <p class="text-sm text-gray-900">{{ props.assetItem.rejected_by.name }}</p>
                                    </div>
                                    <div v-if="props.assetItem.rejected_at">
                                        <h3 class="text-sm font-medium text-gray-500">Rejected At</h3>
                                        <p class="text-sm text-gray-900">{{ formatDate(props.assetItem.rejected_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="props.assetItem.rejection_reason">
                                    <h3 class="text-sm font-medium text-gray-500">Rejection Reason</h3>
                                    <p class="text-sm text-gray-900">{{ props.assetItem.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>
        </div>

        <!-- No Asset Selected Message -->
        <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No Asset Selected</h3>
            <p class="mt-1 text-sm text-gray-500">Select an asset from the dropdown above to view its details and workflow.</p>
                            </div>

                    <!-- Reject Modal -->
                    <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>

                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                                Reject Asset
                                            </h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Please provide a reason for rejecting this asset. This action cannot be undone.
                                                </p>
                                                <div class="mt-4">
                                                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">
                                                        Rejection Reason
                                                    </label>
                                                    <textarea
                                                        id="rejection_reason"
                                                        v-model="rejectionReason"
                                                        rows="3"
                                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="Enter rejection reason..."
                                                        required
                                                    ></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button
                                        type="button"
                                        @click="rejectAsset"
                                        :disabled="isProcessing || !rejectionReason.trim()"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Reject Asset
                                    </button>
                                    <button
                                        type="button"
                                        @click="showRejectModal = false"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </AuthenticatedLayout>
            </template>

<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import axios from 'axios';

import moment from 'moment';

const props = defineProps({
    assets: {
        type: Array,
        required: true,
    },
    assetItem: {
        type: Object,
        default: null,
    },
});

// Debug logging removed for production

const selectedAsset = ref(null);
const showRejectModal = ref(false);
const rejectionReason = ref('');
const isProcessing = ref(false);
const statusMessage = ref('');
const statusMessageType = ref('');
const statusMessageClass = ref('');
const statusMessageTextClass = ref('');

const handleSelectAsset = (asset) => {
    if (asset && asset.id) {
        try {
            // Navigate to the selected asset using Inertia
            router.visit(route('assets.show', asset.id));
        } catch (error) {
            // Fallback to manual URL construction
            router.visit(`/assets-management/${asset.id}`);
        }
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return moment(date).format('DD/MM/YYYY');
};

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

const formatAssetStatus = (status) => {
    if (!status) return 'Unknown';
    const statusMap = {
        'draft': 'Draft',
        'pending_approval': 'Pending Approval',
        'reviewed': 'Reviewed',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'in_use': 'In Use',
        'active': 'Active',
        'maintenance': 'Maintenance',
        'retired': 'Retired',
        'disposed': 'Disposed',
        'in_transfer_process': 'In Transfer Process'
    };
    return statusMap[status] || status.replace('_', ' ').toUpperCase();
};

const getAssetStatus = (asset) => {
    if (asset.rejected_at) return 'rejected';
    if (asset.approved_at) return 'approved';
    if (asset.reviewed_at) return 'reviewed';
    if (asset.submitted_at) return 'pending_approval';
    return 'draft';
};

// Computed properties for conditional visibility
const canReview = computed(() => {
    if (!props.assetItem) return false;
    return props.assetItem.submitted_at && !props.assetItem.reviewed_at;
});

const canApproveReject = computed(() => {
    if (!props.assetItem) return false;
    return props.assetItem.reviewed_at && !props.assetItem.approved_at && !props.assetItem.rejected_at;
});

const canRollback = computed(() => {
    if (!props.assetItem) return false;
    return props.assetItem.rejected_at;
});

// Approval workflow methods
const showStatusMessage = (message, type = 'success') => {
    statusMessage.value = message;
    statusMessageType.value = type;
    
    if (type === 'success') {
        statusMessageClass.value = 'bg-green-50 border border-green-200';
        statusMessageTextClass.value = 'text-green-800';
    } else {
        statusMessageClass.value = 'bg-red-50 border border-red-200';
        statusMessageTextClass.value = 'text-red-800';
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        statusMessage.value = '';
    }, 5000);
};

const reviewAsset = async () => {
    if (!props.assetItem) return;
    
    isProcessing.value = true;
    try {
        const response = await axios.post(route('assets.review', props.assetItem.id));
        
        if (response.data.success) {
            showStatusMessage(response.data.message, 'success');
            // Refresh the page to show updated status
            router.reload();
        } else {
            showStatusMessage(response.data.message, 'error');
        }
    } catch (error) {
        console.error('Review error:', error);
        showStatusMessage(error.response?.data?.message || 'Failed to review asset', 'error');
    } finally {
        isProcessing.value = false;
    }
};

const approveAsset = async () => {
    if (!props.assetItem) return;
    
    isProcessing.value = true;
    try {
        const response = await axios.post(route('assets.approve', props.assetItem.id));
        
        if (response.data.success) {
            showStatusMessage(response.data.message, 'success');
            // Refresh the page to show updated status
            router.reload();
        } else {
            showStatusMessage(response.data.message, 'error');
        }
    } catch (error) {
        console.error('Approve error:', error);
        showStatusMessage(error.response?.data?.message || 'Failed to approve asset', 'error');
    } finally {
        isProcessing.value = false;
    }
};

const rejectAsset = async () => {
    if (!props.assetItem || !rejectionReason.value.trim()) return;
    
    isProcessing.value = true;
    try {
        const response = await axios.post(route('assets.reject', props.assetItem.id), {
            rejection_reason: rejectionReason.value.trim()
        });
        
        if (response.data.success) {
            showStatusMessage(response.data.message, 'success');
            showRejectModal.value = false;
            rejectionReason.value = '';
            // Refresh the page to show updated status
            router.reload();
        } else {
            showStatusMessage(response.data.message, 'error');
        }
    } catch (error) {
        console.error('Reject error:', error);
        showStatusMessage(error.response?.data?.message || 'Failed to reject asset', 'error');
    } finally {
        isProcessing.value = false;
    }
};

const rollbackAsset = async () => {
    if (!props.assetItem) return;
    
    isProcessing.value = true;
    try {
        const response = await axios.post(route('assets.restore', props.assetItem.id));
        
        if (response.data.success) {
            showStatusMessage(response.data.message, 'success');
            // Refresh the page to show updated status
            router.reload();
        } else {
            showStatusMessage(response.data.message, 'error');
        }
    } catch (error) {
        console.error('Rollback error:', error);
        showStatusMessage(error.response?.data?.message || 'Failed to rollback asset', 'error');
    } finally {
        isProcessing.value = false;
    }
};
</script>

<style scoped>
.multiselect-modern {
    @apply border-gray-300 rounded-md shadow-sm;
}

.multiselect-modern :deep(.multiselect-tag) {
    @apply bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded;
}

.multiselect-modern :deep(.multiselect-option) {
    @apply text-sm;
}

.multiselect-modern :deep(.multiselect-option.is-selected) {
    @apply bg-blue-600 text-white;
}

.multiselect-modern :deep(.multiselect-option.is-highlighted) {
    @apply bg-blue-50 text-blue-900;
}
</style>
