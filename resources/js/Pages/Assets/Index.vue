<template>
    <AuthenticatedLayout
        title="Asset Management"
        description="Comprehensive asset tracking and approval system"
        img="/assets/images/asset-header.png"
    >
        <div class="space-y-6">
            <!-- Header Section with Stats -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-white">
                                    <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Asset Management</h1>
                                <p class="text-blue-100 text-sm mt-1">
                                    Track, manage, and approve your organization's assets
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 sm:mt-0 flex space-x-3">
                            <Link
                                v-if="$page.props.auth.can.asset_import"
                                :href="route('assets.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-indigo-700 bg-white hover:bg-indigo-50 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                </svg>
                                Add Asset
                            </Link>
                            <Link
                                :href="route('assets.approvals.index')"
                                class="inline-flex items-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approvals
                            </Link>
                            <button
                                v-if="$page.props.auth.can.asset_export"
                                @click="exportToExcel"
                                class="inline-flex items-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                Export
                            </button>
                            <button
                                v-if="$page.props.auth.can.asset_import"
                                @click="showImportModal = true"
                                class="inline-flex items-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                </svg>
                                Import
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-blue-600">
                                        <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-blue-600">Total Assets</p>
                                    <p class="text-2xl font-bold text-blue-900">{{ props.assetsCount }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-green-600">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-green-600">Active Assets</p>
                                    <p class="text-2xl font-bold text-green-900">
                                        {{ props.assets.data.filter(a => a.status === 'active' || a.status === 'in_use').length }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-yellow-600">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-yellow-600">Pending Approval</p>
                                    <p class="text-2xl font-bold text-yellow-900">
                                        {{ props.assets.data.filter(a => a.status === 'pending_approval').length }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-purple-600">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-purple-600">In Maintenance</p>
                                    <p class="text-2xl font-bold text-purple-900">
                                        {{ props.assets.data.filter(a => a.status === 'maintenance').length }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white shadow-xl rounded-2xl">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters & Search</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Asset tag, serial number, description..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>

                        <!-- Region Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                            <Multiselect
                                v-model="regionFilter"
                                :options="regionOptions"
                                label="name"
                                track-by="id"
                                placeholder="Select Region"
                                :close-on-select="true"
                                :clear-on-select="false"
                                :allow-empty="true"
                                :show-labels="false"
                                @input="onRegionChange"
                            />
                        </div>

                        <!-- Location Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-2" :class="regionFilter ? 'text-gray-700' : 'text-gray-400'">Location</label>
                            <Multiselect
                                v-model="locationFilter"
                                :options="locationOptions"
                                label="name"
                                track-by="id"
                                :placeholder="regionFilter ? 'Select Location' : 'Select Region first'"
                                :show-labels="false"
                                :close-on-select="true"
                                :clear-on-select="false"
                                :allow-empty="true"
                                :disabled="!regionFilter"
                                @input="onLocationChange"
                            />
                        </div>

                        <!-- Sub-Location Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-2" :class="locationFilter ? 'text-gray-700' : 'text-gray-400'">Sub-Location</label>
                            <Multiselect
                                v-model="selectedSubLocations"
                                :options="filteredSubLocations"
                                label="name"                                
                                track-by="id"
                                :placeholder="locationFilter ? 'Select Sub-Locations' : 'Select Location first'"
                                :multiple="true"
                                :show-labels="false"
                                :allow-empty="true"
                                :disabled="!locationFilter"
                            />
                        </div>

                        <!-- Fund Source Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fund Source</label>
                            <Multiselect
                                v-model="fundSourceFilter"
                                :options="props.fundSources"
                                label="name"
                                track-by="id"
                                placeholder="Select Fund Source"
                                :show-labels="false"
                                :close-on-select="true"
                                :clear-on-select="false"
                                :allow-empty="true"
                            />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center mt-6">
                        <div class="flex items-center space-x-4">
                            <button
                                @click="clearFilters"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                </svg>
                                Clear Filters
                            </button>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <select
                                    v-model="per_page"
                                    @change="props.filters.page = 1"
                                    class="w-[200px] border border-gray-300 rounded-md py-1 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="10">10 Per Page</option>
                                    <option value="25">25 Per Page</option>
                                    <option value="50">50 Per Page</option>
                                    <option value="100">100 Per Page</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assets Table -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6">
                    <div v-if="loading" class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>

                    <div v-else-if="props.assets.data.length === 0" class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16 text-gray-400 mx-auto mb-4">
                            <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No assets found</h3>
                        <p class="text-gray-500">No assets match your current filters.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Asset Details
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Value & Source
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="asset in props.assets.data" :key="asset.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-indigo-600">
                                                        <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <Link :href="route('assets.show', asset.id)" class="hover:text-indigo-600">
                                                        {{ asset.asset_tag }}
                                                    </Link>
                                                </div>
                                                <div class="text-sm text-gray-500">{{ asset.serial_number }}</div>
                                                <div class="text-sm text-gray-500 truncate max-w-xs" :title="asset.item_description">
                                                    {{ asset.item_description }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <span class="font-medium">Assigned:</span> {{ asset.person_assigned || 'Unassigned' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ asset.location?.name || 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ asset.sub_location?.name || 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ asset.region?.name || 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800': asset.status === 'active' || asset.status === 'in_use',
                                                    'bg-yellow-100 text-yellow-800': asset.status === 'pending_approval',
                                                    'bg-orange-100 text-orange-800': asset.status === 'maintenance',
                                                    'bg-red-100 text-red-800': asset.status === 'disposed' || asset.status === 'retired',
                                                    'bg-gray-100 text-gray-800': !['active', 'in_use', 'pending_approval', 'maintenance', 'disposed', 'retired'].includes(asset.status)
                                                }"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                <span v-if="asset.status === 'pending_approval'" class="w-2 h-2 bg-yellow-400 rounded-full mr-1"></span>
                                                <span v-else-if="asset.status === 'active' || asset.status === 'in_use'" class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                                                <span v-else-if="asset.status === 'maintenance'" class="w-2 h-2 bg-orange-400 rounded-full mr-1"></span>
                                                <span v-else class="w-2 h-2 bg-gray-400 rounded-full mr-1"></span>
                                                {{ formatStatus(asset.status) }}
                                            </span>
                                        </div>
                                        <div v-if="asset.submitted_for_approval" class="text-xs text-yellow-600 mt-1">
                                            Submitted {{ formatDate(asset.submitted_at) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            ${{ parseFloat(asset.original_value || 0).toLocaleString() }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ asset.fund_source?.name || 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ formatDate(asset.acquisition_date) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <Link
                                                :href="route('assets.show', asset.id)"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="route('assets.edit', asset.id)"
                                                class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                v-if="asset.status === 'active' || asset.status === 'in_use'"
                                                @click="openTransferModal(asset)"
                                                class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                            >
                                                Transfer
                                            </button>
                                            
                                            <!-- Approval Actions -->
                                            <div v-if="asset.status === 'pending_approval' && canApproveAsset(asset)" class="flex items-center space-x-1">
                                                <button
                                                    @click="openApprovalModal(asset, 'approve')"
                                                    class="text-green-600 hover:text-green-900 text-sm font-medium"
                                                >
                                                    Approve
                                                </button>
                                                <button
                                                    @click="openApprovalModal(asset, 'reject')"
                                                    class="text-red-600 hover:text-red-900 text-sm font-medium"
                                                >
                                                    Reject
                                                </button>
                                            </div>
                                            
                                            <div v-if="asset.attachments && asset.attachments.length > 0" class="text-sm text-gray-500">
                                                {{ asset.attachments.length }} file(s)
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 mb-[80px]">
                    <div class="flex items-center justify-end">
                        <TailwindPagination
                            :data="props.assets"
                            @pagination-change-page="getResults"
                            :limit="2"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <TransitionRoot as="template" :show="showImportModal">
            <Dialog as="div" class="fixed z-50 inset-0 overflow-y-auto" @close="showImportModal = false">
                <div class="flex items-center justify-center min-h-screen p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <DialogOverlay class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                    </TransitionChild>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-blue-600">
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900">
                                            Import Assets
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                Upload an Excel file to import assets. Make sure your file follows the required format.
                                            </p>
                                            <div class="mt-4">
                                                <input
                                                    ref="fileInput"
                                                    type="file"
                                                    accept=".xlsx,.xls"
                                                    @change="handleFileSelect"
                                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="button"
                                    @click="importAssets"
                                    :disabled="!selectedFile || importing"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                                >
                                    <svg v-if="importing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ importing ? 'Importing...' : 'Import' }}
                                </button>
                                <button
                                    type="button"
                                    @click="showImportModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Transfer Modal -->
        <TransitionRoot as="template" :show="showTransferModal">
            <Dialog as="div" class="fixed z-50 inset-0 overflow-y-auto" @close="showTransferModal = false">
                <div class="flex items-center justify-center min-h-screen p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <DialogOverlay class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                    </TransitionChild>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-blue-600">
                                            <path d="M16 17l-4 4-4-4m4 4V3"/>
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900">
                                            Transfer Asset
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                Transfer asset <strong>{{ selectedAsset?.asset_tag }}</strong> to a new custodian.
                                            </p>
                                            <div class="mt-4 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">New Custodian</label>
                                                    <input
                                                        v-model="transferData.custodian"
                                                        type="text"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                        placeholder="Enter custodian name"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Transfer Date</label>
                                                    <input
                                                        v-model="transferData.transfer_date"
                                                        type="date"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                                    <textarea
                                                        v-model="transferData.assignment_notes"
                                                        rows="3"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                        placeholder="Add any notes about the transfer"
                                                    ></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="button"
                                    @click="transferAsset"
                                    :disabled="!transferData.custodian || !transferData.transfer_date || transferring"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                                >
                                    <svg v-if="transferring" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ transferring ? 'Transferring...' : 'Transfer' }}
                                </button>
                                <button
                                    type="button"
                                    @click="showTransferModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Approval Modal -->
        <TransitionRoot as="template" :show="showApprovalModal">
            <Dialog as="div" class="fixed z-50 inset-0 overflow-y-auto" @close="showApprovalModal = false">
                <div class="flex items-center justify-center min-h-screen p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <DialogOverlay class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                    </TransitionChild>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                         :class="approvalAction === 'approve' ? 'bg-green-100' : 'bg-red-100'">
                                        <svg v-if="approvalAction === 'approve'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-green-600">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-600">
                                            <path d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900">
                                            {{ approvalAction === 'approve' ? 'Approve' : 'Reject' }} Asset
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-600 mb-2">
                                                {{ selectedAsset?.asset_tag }} - {{ selectedAsset?.serial_number }}
                                            </p>
                                            <label for="approval-notes" class="block text-sm font-medium text-gray-700 mb-2">
                                                Notes (Optional)
                                            </label>
                                            <textarea
                                                id="approval-notes"
                                                v-model="approvalNotes"
                                                rows="3"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Add any notes about your decision..."
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="button"
                                    @click="processApproval"
                                    :disabled="processingApproval"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                                    :class="approvalAction === 'approve' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'"
                                >
                                    <svg v-if="processingApproval" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ processingApproval ? 'Processing...' : (approvalAction === 'approve' ? 'Approve' : 'Reject') }}
                                </button>
                                <button
                                    type="button"
                                    @click="showApprovalModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { TailwindPagination } from "laravel-vue-pagination";
import { Link, router } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import { debounce } from "lodash";
import axios from "axios";
import * as XLSX from "xlsx";
import moment from "moment";
import { useToast } from "vue-toastification";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { reactive } from "vue";
import {
    Dialog,
    DialogOverlay,
    DialogTitle,
    TransitionRoot,
    DialogPanel,
    TransitionChild,
} from "@headlessui/vue";

const toast = useToast();

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

const props = defineProps({
    locations: {
        type: Array,
        required: true,
    },
    assets: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
    },
    regions: {
        type: Array,
        required: true,
    },
    fundSources: {
        type: Array,
        required: true,
    },
    assetsCount: {
        type: Number,
        required: true,
    },
});

const showImportModal = ref(false);
const showTransferModal = ref(false);
const selectedAsset = ref(null);
const transferData = reactive({
    asset_id: null,
    custodian: "",
    transfer_date: "",
    assignment_notes: "",
});

function openTransferModal(asset) {
    selectedAsset.value = asset;
    transferData.asset_id = asset.id;
    transferData.custodian = asset.person_assigned || "";
    transferData.transfer_date = "";
    transferData.assignment_notes = "";
    showTransferModal.value = true;
}

function closeTransferModal() {
    showTransferModal.value = false;
}



const assets = ref([]);
const loading = ref(false);
const selectedLocations = ref([]);
const selectedSubLocations = ref([]);
const collapsedLocations = ref([]);
const isHistoryModalOpen = ref(false);
const selectedAssetHistory = ref([]);

function openHistoryModal(asset) {
    selectedAssetHistory.value = [...asset.history].sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );
    isHistoryModalOpen.value = true;
}
function closeHistoryModal() {
    isHistoryModalOpen.value = false;
    selectedAssetHistory.value = [];
}
const isAttachmentsModalOpen = ref(false);
const selectedAttachments = ref([]);

function openAttachmentsModal(attachments) {
    selectedAttachments.value = attachments;
    isAttachmentsModalOpen.value = true;
}
function closeAttachmentsModal() {
    isAttachmentsModalOpen.value = false;
    selectedAttachments.value = [];
}

const search = ref(props.filters?.search || '');
const per_page = ref(props.filters.per_page || 25);

// Region, Location, SubLocation filter state
const regionFilter = ref(null);
const locationFilter = ref(null);
const subLocationFilter = ref(null);
const subLocationOptions = ref([]);
const fundSourceFilter = ref(null);

const regionOptions = computed(() => props.regions || []);

// Filter locations based on selected region (locations that have assets in the selected region)
const locationOptions = computed(() => {
    if (!regionFilter.value) return [];
    
    // Get unique location IDs that have assets in the selected region
    const locationIdsInRegion = new Set();
    props.assets.data.forEach(asset => {
        if (asset.region_id === regionFilter.value.id && asset.asset_location_id) {
            locationIdsInRegion.add(asset.asset_location_id);
        }
    });
    
    // Return only locations that have assets in the selected region
    return (props.locations || []).filter(location => 
        locationIdsInRegion.has(location.id)
    );
});

// subLocationOptions is now a ref, loaded dynamically
const filteredSubLocations = computed(() => subLocationOptions.value);

function onRegionChange(selected) {
    locationFilter.value = null;
    selectedSubLocations.value = [];
    subLocationOptions.value = [];
    
    // Clear sub-location options when region changes
    if (!selected) {
        subLocationOptions.value = [];
    }
}

async function onLocationChange(selected) {
    selectedSubLocations.value = [];
    subLocationOptions.value = [];
    
    if (!selected) return;
    
    const locationId = selected.id || selected;
    try {
        const response = await axios.get(
            route("assets.locations.sub-locations", { location: locationId })
        );
        subLocationOptions.value = response.data;
    } catch (error) {
        subLocationOptions.value = [];
    }
}

// Asset transfer
async function transferAsset() {
    if (!transferData.custodian || !transferData.transfer_date) {
        toast.error("Custodian and transfer date are required.");
        return;
    }
    loading.value = true;
    try {
        const response = await axios.post(
            route("assets.transfer", { asset: transferData.asset_id }),
            {
                asset_id: transferData.asset_id,
                custodian: transferData.custodian,
                transfer_date: transferData.transfer_date,
                assignment_notes: transferData.assignment_notes,
            }
        );
        if (selectedAsset.value) {
            selectedAsset.value.person_assigned = transferData.custodian;
            selectedAsset.value.transfer_date = transferData.transfer_date;
        }
        toast.success("Asset transferred successfully!");
        closeTransferModal();
    } catch (error) {
        toast.error(error.response?.data || "Transfer failed.");
    } finally {
        loading.value = false;
    }
}

// Computed property for filtered assets
const filteredAssets = computed(() => {
    return assets.value.filter((asset) => {
        const regionMatch =
            !regionFilter.value || asset.region_id === regionFilter.value.id;
        const locationMatch =
            !locationFilter.value ||
            asset.asset_location_id === locationFilter.value.id;
        const subLocationMatch =
            !subLocationFilter.value ||
            asset.sub_location_id === subLocationFilter.value.id;
        const searchMatch =
            !search.value ||
            [asset.asset_tag, asset.serial_number, asset.item_description].some(
                (field) =>
                    field &&
                    field.toLowerCase().includes(search.value.toLowerCase())
            );
        return regionMatch && locationMatch && subLocationMatch && searchMatch;
    });
});

function getResults(page = 1) {
    props.filters.page = page;
}

// Watch for location change to load sub-locations
watch(
    () => locationFilter.value,
    async (newLocation) => {
        selectedSubLocations.value = [];
        subLocationOptions.value = [];
        
        if (newLocation && newLocation.id) {
            // Fetch sub-locations for the selected location
            try {
                const response = await axios.get(
                    route("assets.locations.sub-locations", newLocation.id)
                );
                subLocationOptions.value = response.data;
            } catch (e) {
                subLocationOptions.value = [];
            }
        }
    },
    { immediate: true }
);

// Debounced search function
const debouncedSearch = debounce(() => {
    reloadAssets();
}, 300);

watch(
    [
        () => props.filters.page,
        () => per_page.value,
        () => (regionFilter.value ? regionFilter.value.id : null),
        () => (locationFilter.value ? locationFilter.value.id : null),
        () => (fundSourceFilter.value ? fundSourceFilter.value.id : null),
    ],
    () => {
        reloadAssets();
    }
);

// Watch search separately with debouncing
watch(
    () => search.value,
    () => {
        debouncedSearch();
    }
);

// Watch for changes in selectedSubLocations and reload assets
watch(
    selectedSubLocations,
    () => {
        reloadAssets();
    },
    { deep: true }
);

function reloadAssets() {
    const query = {};
    if (props.filters.page) query.page = props.filters.page;
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (regionFilter.value && regionFilter.value.id)
        query.region_id = regionFilter.value.id;
    if (locationFilter.value && locationFilter.value.id)
        query.location_id = locationFilter.value.id;
    if (selectedSubLocations.value && selectedSubLocations.value.length) {
        query["sub_location_ids"] = selectedSubLocations.value.map((x) => x.id);
    }
    if (fundSourceFilter.value && fundSourceFilter.value.id)
        query.fund_source_id = fundSourceFilter.value.id;
    router.get(route("assets.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["assets", "locations"],
    });
}

// Toggle location selection
const toggleLocation = (locationId) => {
    const index = selectedLocations.value.indexOf(locationId);
    if (index === -1) {
        selectedLocations.value.push(locationId);
        // Auto-select all sub-locations
        const location = props.locations.find((l) => l.id === locationId);
        if (location?.sub_locations) {
            location.sub_locations.forEach((sub) => {
                if (!selectedSubLocations.value.includes(sub.id)) {
                    selectedSubLocations.value.push(sub.id);
                }
            });
        }
    } else {
        selectedLocations.value.splice(index, 1);
        // Deselect all sub-locations of this location
        const location = props.locations.find((l) => l.id === locationId);
        if (location?.sub_locations) {
            location.sub_locations.forEach((sub) => {
                const subIndex = selectedSubLocations.value.indexOf(sub.id);
                if (subIndex !== -1) {
                    selectedSubLocations.value.splice(subIndex, 1);
                }
            });
        }
    }
};

// Format money
const formatMoney = (amount) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

// Toggle sub-location selection
const toggleSubLocation = (subLocationId, parentLocationId) => {
    const index = selectedSubLocations.value.indexOf(subLocationId);
    if (index === -1) {
        selectedSubLocations.value.push(subLocationId);
        // Ensure parent location is selected
        if (!selectedLocations.value.includes(parentLocationId)) {
            selectedLocations.value.push(parentLocationId);
        }
    } else {
        selectedSubLocations.value.splice(index, 1);
        // Check if all sub-locations are deselected
        const location = props.locations.find((l) => l.id === parentLocationId);
        const anySubSelected = location?.sub_locations.some((sub) =>
            selectedSubLocations.value.includes(sub.id)
        );
        if (!anySubSelected) {
            const parentIndex =
                selectedLocations.value.indexOf(parentLocationId);
            if (parentIndex !== -1) {
                selectedLocations.value.splice(parentIndex, 1);
            }
        }
    }
};

// Toggle location collapse state
const toggleCollapse = (locationId) => {
    const index = collapsedLocations.value.indexOf(locationId);
    if (index === -1) {
        collapsedLocations.value.push(locationId);
    } else {
        collapsedLocations.value.splice(index, 1);
    }
};

// Computed properties for the cards
const totalAssets = computed(() => assets.value.length);
const activeAssets = computed(
    () => assets.value.filter((asset) => asset.status === "in_use").length
);
const maintenanceAssets = computed(
    () => assets.value.filter((asset) => asset.status === "maintenance").length
);


const fileInput = ref(null);
const selectedFile = ref(null);
const importing = ref(false);
const transferring = ref(false);

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0];
};

const importAssets = async () => {
    if (!selectedFile.value) {
        toast.error("Please select a file to import.");
        return;
    }
    const formData = new FormData();
    formData.append("file", selectedFile.value);
    importing.value = true;
    try {
        await axios.post(route("assets.import"), formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        toast.success("Import started. You will be notified when complete.");
        fileInput.value.value = ""; // reset input
        showImportModal.value = false; // close modal after upload
    } catch (error) {
        toast.error(error.response?.data || "Import failed.");
    } finally {
        importing.value = false;
    }
};

// Excel export function
const exportToExcel = () => {
    // Prepare the data
    const exportData = props.assets.data.map((asset) => ({
        "Asset Tag": asset.asset_tag,
        Category: asset.category?.name || '',
        "Serial Number": asset.serial_number,
        Description: asset.item_description,
        "Assigned To": asset.person_assigned,
        Region: asset.region?.name || '',
        Location: asset.location?.name || '',
        "Sub Location": asset.sub_location?.name || "",
        "Acquisition Date": formatDate(asset.acquisition_date),
        Status: formatStatus(asset.status),
        "Original Value": asset.original_value,
        "Source Agency": asset.fund_source?.name || "",
        "Approval Status": asset.submitted_for_approval ? 'Pending Approval' : 'Approved',
    }));

    // Create worksheet
    const worksheet = XLSX.utils.json_to_sheet(exportData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Assets");

    // Generate file name with current date
    const date = new Date().toISOString().split("T")[0];
    const fileName = `assets_${date}.xlsx`;

    // Save the file
    XLSX.writeFile(workbook, fileName);
};

// Clear all filters
const clearFilters = () => {
    search.value = '';
    regionFilter.value = null;
    locationFilter.value = null;
    selectedSubLocations.value = [];
    subLocationOptions.value = [];
    fundSourceFilter.value = null;
    props.filters.page = 1;
    reloadAssets();
};

// Approval system
const showApprovalModal = ref(false);
const approvalAction = ref('');
const approvalNotes = ref('');
const processingApproval = ref(false);

function canApproveAsset(asset) {
    // Check if user has approval permissions
    return $page.props.auth.can.asset_approve || $page.props.auth.can.transfer_approve;
}

function openApprovalModal(asset, action) {
    selectedAsset.value = asset;
    approvalAction.value = action;
    approvalNotes.value = '';
    showApprovalModal.value = true;
}

async function processApproval() {
    if (!selectedAsset.value) return;

    processingApproval.value = true;
    try {
        const response = await axios.post(route('assets.approve', selectedAsset.value.id), {
            action: approvalAction.value,
            notes: approvalNotes.value
        });

        toast.success(response.data.message);
        showApprovalModal.value = false;
        router.reload();
    } catch (error) {
        toast.error(error.response?.data || 'Failed to process approval');
    } finally {
        processingApproval.value = false;
    }
}
</script>
