<template>
    <AuthenticatedLayout title="Asset Approvals & Workflow" :description="props.assetItem?.asset_number || 'Select an asset to view approval workflow'">
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
                        <strong>Asset Approvals & Workflow:</strong> This page displays all assets that have been submitted for approval, including their current approval stage. 
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
                    placeholder="Select assets"
                    @select="handleSelectAsset"
                    class="multiselect-modern"
                    :searchable="true"
                    :show-labels="false"
                    :close-on-select="true"
                />
            </div>
        </div>

        <!-- Asset Details and Approval Interface -->
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
                        <span class="text-sm text-gray-600">Location: {{ props.assetItem.region?.name || 'N/A' }} - {{ props.assetItem.asset_location?.name || 'N/A' }} - {{ props.assetItem.sub_location?.name || 'N/A' }}</span>
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
                                    {{ props.assetItem.fund_source?.name || 'N/A' }}
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
                            <div class="text-sm font-medium text-gray-900">Submitted</div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.submitted_at">
                                {{ formatDate(props.assetItem.submitted_at) }}
                            </div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.submitted_by">
                                By {{ props.assetItem.submitted_by.name }}
                            </div>
                        </div>

                        <!-- Reviewed Stage -->
                        <div class="text-center p-3 rounded-lg" :class="props.assetItem?.reviewed_at ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                            <div class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center" :class="props.assetItem?.reviewed_at ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'">
                                <svg v-if="props.assetItem?.reviewed_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold">2</span>
                            </div>
                            <div class="text-sm font-medium text-gray-900">Reviewed</div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.reviewed_at">
                                {{ formatDate(props.assetItem.reviewed_at) }}
                            </div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.reviewed_by">
                                By {{ props.assetItem.reviewed_by.name }}
                            </div>
                        </div>

                        <!-- Approved Stage -->
                        <div class="text-center p-3 rounded-lg" :class="props.assetItem?.approved_at ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                            <div class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center" :class="props.assetItem?.approved_at ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'">
                                <svg v-if="props.assetItem?.approved_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold">3</span>
                            </div>
                            <div class="text-sm font-medium text-gray-900">Approved</div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.approved_at">
                                {{ formatDate(props.assetItem.approved_at) }}
                            </div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.approved_by">
                                By {{ props.assetItem.approved_by.name }}
                            </div>
                        </div>

                        <!-- Final Status -->
                        <div class="text-center p-3 rounded-lg" :class="getFinalStatusClass(props.assetItem)">
                            <div class="w-8 h-8 mx-auto mb-2 rounded-full flex items-center justify-center" :class="getFinalStatusIconClass(props.assetItem)">
                                <svg v-if="props.assetItem?.rejected_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <svg v-else-if="props.assetItem?.approved_at" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold">4</span>
                            </div>
                            <div class="text-sm font-medium text-gray-900">{{ getFinalStatusText(props.assetItem) }}</div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.rejected_at">
                                {{ formatDate(props.assetItem.rejected_at) }}
                            </div>
                            <div class="text-xs text-gray-500" v-if="props.assetItem?.rejected_by">
                                By {{ props.assetItem.rejected_by.name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Details Summary -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Approval Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Submission Details -->
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500 mb-1">Submission</div>
                            <div class="text-sm font-semibold text-gray-900" v-if="props.assetItem?.submitted_at">
                                {{ formatDate(props.assetItem.submitted_at) }}
                            </div>
                            <div class="text-sm text-gray-900" v-else>Not submitted</div>
                            <div class="text-xs text-gray-600" v-if="props.assetItem?.submitted_by">
                                By: {{ props.assetItem.submitted_by.name }}
                            </div>
                        </div>

                        <!-- Review Details -->
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500 mb-1">Review</div>
                            <div class="text-sm font-semibold text-gray-900" v-if="props.assetItem?.reviewed_at">
                                {{ formatDate(props.assetItem.reviewed_at) }}
                            </div>
                            <div class="text-sm text-gray-900" v-else>Not reviewed</div>
                            <div class="text-xs text-gray-600" v-if="props.assetItem?.reviewed_by">
                                By: {{ props.assetItem.reviewed_by.name }}
                            </div>
                        </div>

                        <!-- Approval Details -->
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500 mb-1">Approval</div>
                            <div class="text-sm font-semibold text-gray-900" v-if="props.assetItem?.approved_at">
                                {{ formatDate(props.assetItem.approved_at) }}
                            </div>
                            <div class="text-sm text-gray-900" v-else>Not approved</div>
                            <div class="text-xs text-gray-600" v-if="props.assetItem?.approved_by">
                                By: {{ props.assetItem.approved_by.name }}
                            </div>
                        </div>

                        <!-- Current Status -->
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500 mb-1">Current Status</div>
                            <div class="text-sm font-semibold" :class="getStatusTextClass(props.assetItem)">
                                {{ getCurrentApprovalStatus(props.assetItem).toUpperCase() }}
                            </div>
                            <div class="text-xs text-gray-600" v-if="props.assetItem?.rejected_at">
                                Rejected on {{ formatDate(props.assetItem.rejected_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Items Table -->
            <div class="bg-white">
                <div class="px-6 py-4">
                    <h2 class="text-xs text-gray-900 mb-4">Asset Items</h2>
                    <table class="w-full overflow-hidden text-sm text-left table-sm rounded-t-lg">
                        <thead>
                            <tr style="background-color: #F4F7FB;">
                                <th class="px-3 py-2 text-xs font-bold rounded-tl-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Item</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Serial Number</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Category</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Type</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Asset Details</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Value</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Assignee</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Status</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Approval Stage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!props.assetItem?.asset_items || props.assetItem.asset_items.length === 0">
                                <td colspan="9" class="px-3 py-3 text-center text-gray-500 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    {{ !props.assetItem?.asset_items ? 'No asset items found' : 'No asset items available' }}
                                </td>
                            </tr>
                            <template v-else v-for="item in props.assetItem.asset_items" :key="item.id">
                                <tr class="hover:bg-gray-50 transition-colors duration-150 border-b" style="border-bottom: 1px solid #F4F7FB;">
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.asset_tag }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.serial_number }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.category?.name || 'N/A' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.type?.name || 'N/A' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.asset_name }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">${{ item.original_value || '0.00' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.assignee?.name || 'N/A' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                      :class="getItemStatusBadgeClass(item.status)">
                                                    {{ formatItemStatus(item.status) }}
                                                </span>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-gray-900">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="getApprovalStageBadgeClass(props.assetItem)">
                                            {{ getApprovalStage(props.assetItem) }}
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                                </div>
                            </div>

            <!-- Asset Status Actions -->
            <div class="mt-8 mb-6 bg-white rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                    Asset Status Actions
                </h3>
                <div class="flex items-start mb-6">
                    <!-- Status Action Buttons -->
                    <div class="flex flex-wrap items-center justify-center gap-4 px-1 py-2">
                        <!-- Review button -->
                        <div class="relative">
                            <div class="flex flex-col">
                                <button @click="changeStatus(props.assetItem.id, 'reviewed', 'is_reviewing')" 
                                        :disabled="isType['is_reviewing'] || 
                                                    !canReviewAsset(props.assetItem) || 
                                                    !$page.props.auth.can?.asset_review"
                                        :class="[
                                            canReviewAsset(props.assetItem) && $page.props.auth.can?.asset_review
                                                ? 'bg-yellow-500 hover:bg-yellow-600'
                                                : props.assetItem?.reviewed_at
                                                ? 'bg-green-500'
                                                : 'bg-gray-300 cursor-not-allowed',
                                        ]" 
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <img src="/assets/images/review.png" class="w-5 h-5 mr-2" alt="Review" />
                                    <span class="text-sm font-bold text-white">{{
                                        props.assetItem?.reviewed_at
                                            ? "Reviewed"
                                            : isType["is_reviewing"]
                                                ? "Please Wait..."
                                                : "Review"
                                    }}</span>
                                </button>
                                <span v-show="props.assetItem?.reviewed_at" class="text-sm text-gray-600">
                                    On {{ formatDate(props.assetItem?.reviewed_at) }}
                                </span>
                                <span v-show="props.assetItem?.reviewed_by" class="text-sm text-gray-600">
                                    By {{ props.assetItem?.reviewed_by?.name }}
                                </span>
                            </div>
                            <div v-if="canReviewAsset(props.assetItem) && $page.props.auth.can?.asset_review"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                                </div>
                                
                        <!-- Approve button -->
                        <div class="relative">
                            <div class="flex flex-col">
                                <button @click="changeStatus(props.assetItem.id, 'approved', 'is_approve')" 
                                        :disabled="isType['is_approve'] || 
                                                    !canApproveAsset(props.assetItem) || 
                                                    !$page.props.auth.can?.asset_approve"
                                        :class="[
                                            canApproveAsset(props.assetItem) && $page.props.auth.can?.asset_approve
                                                ? 'bg-yellow-500 hover:bg-yellow-600'
                                                : props.assetItem?.approved_at
                                                ? 'bg-green-500'
                                                : 'bg-gray-300 cursor-not-allowed',
                                        ]" 
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="isType['is_approve']" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    <template v-else>
                                        <img src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
                                        <span class="text-sm font-bold text-white">{{
                                            props.assetItem?.approved_at
                                                ? "Approved"
                                                : isType["is_approve"] ? "Please Wait..." : "Approve"
                                        }}</span>
                                    </template>
                                    </button>
                                <span v-show="props.assetItem?.approved_at" class="text-sm text-gray-600">
                                    On {{ formatDate(props.assetItem?.approved_at) }}
                                </span>
                                <span v-show="props.assetItem?.approved_by" class="text-sm text-gray-600">
                                    By {{ props.assetItem?.approved_by?.name }}
                                </span>
                            </div>
                            <div v-if="canApproveAsset(props.assetItem) && $page.props.auth.can?.asset_approve"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Reject button -->
                        <div class="relative">
                            <div class="flex flex-col">
                                <button @click="showRejectModal()" 
                                        :disabled="isType['is_reject'] || 
                                                    !canRejectAsset(props.assetItem) || 
                                                    !$page.props.auth.can?.asset_approve"
                                        :class="[
                                            canRejectAsset(props.assetItem) && $page.props.auth.can?.asset_approve
                                                ? 'bg-red-500 hover:bg-red-600'
                                                : props.assetItem?.rejected_at
                                                ? 'bg-red-500'
                                                : 'bg-gray-300 cursor-not-allowed',
                                        ]" 
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="isType['is_reject']" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    <template v-else>
                                        <img src="/assets/images/rejected.png" class="w-5 h-5 mr-2" alt="Reject" />
                                        <span class="text-sm font-bold text-white">{{
                                            props.assetItem?.rejected_at
                                                ? "Rejected"
                                                : isType["is_reject"] ? "Please Wait..." : "Reject"
                                        }}</span>
                                    </template>
                                    </button>
                                <span v-show="props.assetItem?.rejected_at" class="text-sm text-gray-600">
                                    On {{ formatDate(props.assetItem?.rejected_at) }}
                                </span>
                                <span v-show="props.assetItem?.rejected_by" class="text-sm text-gray-600">
                                    By {{ props.assetItem?.rejected_by?.name }}
                                </span>
                            </div>
                            <div v-if="canRejectAsset(props.assetItem) && $page.props.auth.can?.asset_approve"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-red-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Restore button -->
                        <div class="relative">
                            <div class="flex flex-col">
                                <button @click="restoreAsset(props.assetItem.id, 'pending_approval', 'is_restore')" 
                                        :disabled="isRestoring || 
                                                    !canRestoreAsset(props.assetItem) || 
                                                    !$page.props.auth.can?.asset_approve"
                                        :class="[
                                            canRestoreAsset(props.assetItem) && $page.props.auth.can?.asset_approve
                                                ? 'bg-green-500 hover:bg-green-600'
                                                : 'bg-gray-300 cursor-not-allowed',
                                        ]" 
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="isRestoring" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    <template v-else>
                                        <img src="/assets/images/restore.jpg" class="w-5 h-5 mr-2" alt="Restore" />
                                        <span class="text-sm font-bold text-white">{{ isRestoring ? "Restoring..." : "Restore Asset" }}</span>
                                    </template>
                                </button>
                                </div>
                            <div v-if="canRestoreAsset(props.assetItem) && $page.props.auth.can?.asset_approve"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-green-400 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Reject Modal -->
        <Modal :show="showRejectModalFlag" @close="closeRejectModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Reject Asset Request</h2>
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea
                        id="rejection_reason"
                        v-model="rejectionReason"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Please provide a reason for rejection..."
                        required
                    ></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="closeRejectModal">Cancel</SecondaryButton>
                    <PrimaryButton @click="rejectAsset" :disabled="!rejectionReason.trim()" class="bg-red-600 hover:bg-red-700">
                        Reject Asset
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import moment from 'moment';

const toast = useToast();

const props = defineProps({
    assets: Array,
    assetItem: Object,
    filters: Object
});


const selectedAsset = ref(props.filters.selectedAsset);

const showRejectModalFlag = ref(false);
const rejectionReason = ref('');
const isType = ref({});
const isRestoring = ref(false);

function handleSelectAsset() {
    const query = {};
    if (selectedAsset.value){
        query.selectedAsset = selectedAsset.value;
    }
    router.get(route('assets.approvals.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: ['assetItem'],
    });
}

watch([
    () => props.filters.selectedAsset,
], (newVal) => {
    if (newVal !== selectedAsset.value) {
        selectedAsset.value = newVal;
    }
})

const changeStatus = async (assetId, newStatus, type) => {
    console.log(assetId, newStatus, type);
    
    Swal.fire({
        title: "Are you sure?",
        text: `Do you want to change the asset status to ${newStatus}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, change it!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Set loading state
            isType.value[type] = true;

            try {
                if (newStatus === 'reviewed') {
                    await router.post(route('assets.review', assetId));
                } else if (newStatus === 'approved') {
                    await router.post(route('assets.approve-simple', assetId));
                }
                
                toast.success(`Asset status updated to ${newStatus}`);
                
                // Reload the page to show updated status
                router.get(route('assets.approvals.index'), { selectedAsset: props.assetItem?.asset_number });
    } catch (error) {
                toast.error(`Failed to update asset status to ${newStatus}`);
            } finally {
                // Reset loading state
                isType.value[type] = false;
            }
        }
    });
};

const restoreAsset = async (assetId, newStatus, type) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to restore the asset?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
    })
    .then(async (result) => {
        if (result.isConfirmed) {
            isRestoring.value = true;
            
            try {
                await router.post(route('assets.restore', assetId));
                toast.success('Asset restored successfully');
                router.get(route('assets.approvals.index'), { selectedAsset: props.assetItem?.asset_number });
    } catch (error) {
                toast.error('Failed to restore asset');
            } finally {
                isRestoring.value = false;
            }
    }
    });
};

const showRejectModal = () => {
    showRejectModalFlag.value = true;
};

const closeRejectModal = () => {
    showRejectModalFlag.value = false;
    rejectionReason.value = '';
};

const rejectAsset = async () => {
    if (!props.assetItem || !rejectionReason.value.trim()) {
        toast.error('Please provide a rejection reason');
        return;
    }

    try {
        await router.post(route('assets.reject-simple', props.assetItem.id), {
            rejection_reason: rejectionReason.value
        });
        
        toast.success('Asset rejected successfully');
        closeRejectModal();
        router.get(route('assets.approvals.index'), { selectedAsset: props.assetItem?.asset_number });
    } catch (error) {
        toast.error('Failed to reject asset');
    }
};

const getItemStatusBadgeClass = (status) => {
    const classes = {
        'pending_approval': 'bg-yellow-100 text-yellow-800',
        'in_use': 'bg-green-100 text-green-800',
        'maintenance': 'bg-orange-100 text-orange-800',
        'retired': 'bg-gray-100 text-gray-800',
        'disposed': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatItemStatus = (status) => {
    const statusMap = {
        'pending_approval': 'Pending',
        'in_use': 'In Use',
        'maintenance': 'Maintenance',
        'retired': 'Retired',
        'disposed': 'Disposed',
    };
    return statusMap[status] || status;
};

const formatAssetStatus = (status) => {
    const statusMap = {
        'pending_approval': 'Pending Approval',
        'reviewed': 'Reviewed',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'in_use': 'In Use',
        'active': 'Active',
        'maintenance': 'Maintenance',
        'retired': 'Retired',
        'disposed': 'Disposed',
        'in_transfer_process': 'In Transfer Process',
    };
    return statusMap[status] || status;
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return moment(date).format('DD/MM/YYYY');
};

// Helper function to get non-approved asset items
const getNonApprovedItems = (assetItems) => {
    if (!assetItems || !Array.isArray(assetItems)) return [];
    return assetItems.filter(item => item.status !== 'in_use' && item.status !== 'approved');
};

// Helper function to get count of non-approved asset items
const getNonApprovedItemsCount = (assetItems) => {
    return getNonApprovedItems(assetItems).length;
};

// Helper functions for approval workflow
const canReviewAsset = (asset) => {
    if (!asset) return false;
    // Can review if asset is submitted but not yet reviewed
    return asset.submitted_at && !asset.reviewed_at && !asset.approved_at && !asset.rejected_at;
};

const canApproveAsset = (asset) => {
    if (!asset) return false;
    // Can approve if asset is reviewed but not yet approved
    return asset.reviewed_at && !asset.approved_at && !asset.rejected_at;
};

const canRejectAsset = (asset) => {
    if (!asset) return false;
    // Can reject if asset is reviewed but not yet approved
    return asset.reviewed_at && !asset.approved_at && !asset.rejected_at;
};

const canRestoreAsset = (asset) => {
    if (!asset) return false;
    // Can restore if asset is rejected
    return asset.rejected_at;
};

// Helper function to get asset status based on timestamp fields
const getAssetStatus = (asset) => {
    if (!asset) return 'unknown';
    
    // Check if asset is disposed
    if (asset.deleted_at) {
        return 'disposed';
    }
    
    // Check if asset is rejected
    if (asset.rejected_at) {
        return 'rejected';
    }
    
    // Check if asset is approved
    if (asset.approved_at) {
        return 'approved';
    }
    
    // Check if asset is reviewed
    if (asset.reviewed_at) {
        return 'reviewed';
    }
    
    // Check if asset is submitted for approval
    if (asset.submitted_at) {
        return 'pending_approval';
    }
    
    // Default status
    return 'draft';
};

// Helper function to get approval stage for display
const getApprovalStage = (asset) => {
    if (!asset) return 'Unknown';
    
    if (asset.rejected_at) {
        return 'Rejected';
    }
    
    if (asset.approved_at) {
        return 'Approved';
    }
    
    if (asset.reviewed_at) {
        return 'Reviewed';
    }
    
    if (asset.submitted_at) {
        return 'Pending Review';
    }
    
    return 'Draft';
};

// Helper function to get approval stage badge styling
const getApprovalStageBadgeClass = (asset) => {
    if (!asset) return 'bg-gray-100 text-gray-800';
    
    if (asset.rejected_at) {
        return 'bg-red-100 text-red-800';
    }
    
    if (asset.approved_at) {
        return 'bg-green-100 text-green-800';
    }
    
    if (asset.reviewed_at) {
        return 'bg-blue-100 text-blue-800';
    }
    
    if (asset.submitted_at) {
        return 'bg-yellow-100 text-yellow-800';
    }
    
    return 'bg-gray-100 text-gray-800';
};

// Helper function to get final status class for workflow display
const getFinalStatusClass = (asset) => {
    if (!asset) return 'bg-gray-50 border border-gray-200';
    
    if (asset.rejected_at) {
        return 'bg-red-50 border border-red-200';
    }
    
    if (asset.approved_at) {
        return 'bg-green-50 border border-green-200';
    }
    
    return 'bg-gray-50 border border-gray-200';
};

// Helper function to get final status icon class for workflow display
const getFinalStatusIconClass = (asset) => {
    if (!asset) return 'bg-gray-400 text-white';
    
    if (asset.rejected_at) {
        return 'bg-red-500 text-white';
    }
    
    if (asset.approved_at) {
        return 'bg-green-500 text-white';
    }
    
    return 'bg-gray-400 text-white';
};

// Helper function to get final status text for workflow display
const getFinalStatusText = (asset) => {
    if (!asset) return 'Pending';
    
    if (asset.rejected_at) {
        return 'Rejected';
    }
    
    if (asset.approved_at) {
        return 'Completed';
    }
    
    return 'Pending';
};

// Approval status order for timeline progression
const approvalStatusOrder = [
    "submitted",
    "reviewed", 
    "approved",
    "completed"
];

// Helper function to get current approval status for timeline
const getCurrentApprovalStatus = (asset) => {
    if (!asset) return 'submitted';
    
    if (asset.rejected_at) {
        return 'rejected';
    }
    
    if (asset.approved_at) {
        return 'completed';
    }
    
    if (asset.reviewed_at) {
        return 'reviewed';
    }
    
    if (asset.submitted_at) {
        return 'submitted';
    }
    
    return 'submitted';
};

// Helper function to get status text styling
const getStatusTextClass = (asset) => {
    if (!asset) return 'text-gray-900';
    
    if (asset.rejected_at) {
        return 'text-red-600';
    }
    
    if (asset.approved_at) {
        return 'text-green-600';
    }
    
    if (asset.reviewed_at) {
        return 'text-blue-600';
    }
    
    if (asset.submitted_at) {
        return 'text-yellow-600';
    }
    
    return 'text-gray-900';
};
</script>


