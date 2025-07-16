<template>
    <AuthenticatedLayout title="Asset Management" description="Comprehensive asset tracking and approval system"
        img="/assets/images/asset-header.png">
        <div class="space-y-6">
            <!-- Header Section with Stats -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-10 h-10 text-white">
                                    <path
                                        d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z" />
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
                            <Link v-if="$page.props.auth.can.asset_import" :href="route('assets.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-indigo-700 bg-white hover:bg-indigo-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-4 h-4 mr-2">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                            </svg>
                            Add Asset
                            </Link>
                            <Link :href="route('assets.approvals.index')"
                                class="inline-flex items-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-4 h-4 mr-2">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Approvals
                            </Link>
                            <button v-if="$page.props.auth.can.asset_export" @click="exportToExcel"
                                class="inline-flex items-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-4 h-4 mr-2">
                                    <path
                                        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                                </svg>
                                Export
                            </button>
                            <button v-if="$page.props.auth.can.asset_import" @click="showImportModal = true"
                                class="inline-flex items-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-4 h-4 mr-2">
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
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
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-8 h-8 text-blue-600">
                                        <path
                                            d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z" />
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
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-8 h-8 text-green-600">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-green-600">Active Assets</p>
                                    <p class="text-2xl font-bold text-green-900">
                                        {{props.assets.data.filter(a => a.status === 'active' || a.status ===
                                        'in_use').length }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-8 h-8 text-yellow-600">
                                        <path fill-rule="evenodd"
                                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                            clip-rule="evenodd" />
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

                        <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-6 border border-red-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-8 h-8 text-red-600">
                                        <path fill-rule="evenodd"
                                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-red-600">Retired</p>
                                    <p class="text-2xl font-bold text-red-900">
                                        {{ props.assets.data.filter(a => a.status === 'retired').length }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search Section -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        class="w-4 h-4 text-gray-400">
                                        <path fill-rule="evenodd"
                                            d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input v-model="search" type="text" id="search"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Search by asset tag, model, or brand..." />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <Multiselect v-model="selectedLocation" :options="safeLocationOptions" placeholder="All Locations"
                                label="name" track-by="id" :show-labels="false" :close-on-select="true"
                                :clear-on-select="false" :preserve-search="true" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <Multiselect v-model="selectedStatus" :options="statusOptions" placeholder="All Statuses"
                                label="label" track-by="value" :show-labels="false" :close-on-select="true"
                                :clear-on-select="false" :preserve-search="true" />
                        </div>

                        <div class="flex items-end space-x-2">
                            <button @click="clearFilters"
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors text-sm">
                                Clear Filters
                            </button>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <select v-model="per_page" @change="props.filters.page = 1"
                                    class="w-[200px] border border-gray-300 rounded-md py-1 px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500">
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
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                </div>

                <div v-else-if="props.assets.data.length === 0" class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="w-16 h-16 text-gray-400 mx-auto mb-4">
                        <path
                            d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No assets found</h3>
                    <p class="text-gray-500">No assets match your current filters.</p>
                </div>

                <div v-else class="shadow-lg rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-slate-800 to-slate-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-white capitalize tracking-wider border-r border-slate-600 last:border-r-0">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M4.25 2A2.25 2.25 0 002 4.25v11.5A2.25 2.25 0 004.25 18h11.5A2.25 2.25 0 0018 15.75V4.25A2.25 2.25 0 0015.75 2H4.25zM15 5.75a.75.75 0 00-1.5 0v8.5a.75.75 0 001.5 0v-8.5zm-8.5 6a.75.75 0 01.75-.75h5a.75.75 0 010 1.5h-5a.75.75 0 01-.75-.75zm0 2.5a.75.75 0 01.75-.75h3a.75.75 0 010 1.5h-3a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Asset Details</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-white capitalize tracking-wider border-r border-slate-600 last:border-r-0">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.757.433c.112.057.218.11.281.14l.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Location</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-white capitalize tracking-wider border-r border-slate-600 last:border-r-0">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L8.107 10.5a.75.75 0 00-1.214 1.029l1.5 2.25a.75.75 0 001.214-.15l4-5.75z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Status</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-white capitalize tracking-wider border-r border-slate-600 last:border-r-0">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Acquisition Date</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-white capitalize tracking-wider border-r border-slate-600 last:border-r-0">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.202.592.037.051.08.102.128.152z" />
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-6a.75.75 0 01.75.75v.316a3.78 3.78 0 011.653.713c.426.33.744.74.925 1.2a.75.75 0 01-1.395.55 1.35 1.35 0 00-.447-.563 2.187 2.187 0 00-.736-.363V9.3c.698.093 1.383.32 1.959.696.787.514 1.29 1.27 1.29 2.13 0 .86-.504 1.616-1.29 2.13-.576.377-1.261.603-1.959.696v.299a.75.75 0 11-1.5 0v-.3c-.697-.092-1.382-.318-1.958-.695C5.896 13.744 5.25 12.845 5.25 12.26c0-.686.647-1.484 1.342-1.997.576-.377 1.261-.603 1.958-.696V6.934a2.187 2.187 0 00-.736.363 1.35 1.35 0 00-.447.563.75.75 0 11-1.395-.55c.181-.46.499-.87.925-1.2A3.78 3.78 0 018.25 5.25V4.75A.75.75 0 0110 4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Value & Source</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-white capitalize tracking-wider border-r border-slate-600 last:border-r-0">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Files</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-white capitalize tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path d="M10 3.75a2 2 0 10-4 0 2 2 0 004 0zM17.25 4.5a.75.75 0 000-1.5h-5.5a.75.75 0 000 1.5h5.5zM5 3.75a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5a.75.75 0 01.75.75zM8.25 8a2 2 0 100-4 2 2 0 000 4zM17.25 7.5a.75.75 0 000-1.5h-5.5a.75.75 0 000 1.5h5.5zM5 6.75a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5a.75.75 0 01.75.75zM8.25 12a2 2 0 100-4 2 2 0 000 4zM17.25 11.5a.75.75 0 000-1.5h-5.5a.75.75 0 000 1.5h5.5zM5 10.75a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5a.75.75 0 01.75.75z" />
                                        </svg>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="asset in props.assets.data" :key="asset.id"
                                 class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 border-b border-gray-100 hover:shadow-md group">
                                <td class="px-6 py-5 border-r border-gray-100 last:border-r-0">
                                    <div class="flex items-center">
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center space-x-3">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    <Link :href="route('assets.show', asset.id)"
                                                        class="hover:text-indigo-600 transition-colors">
                                                    {{ asset.asset_tag }}
                                                    </Link>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-3 mt-2">
                                                <span class="text-sm font-medium text-gray-600">S/N:</span>
                                                <span class="text-sm text-gray-900">{{ asset.serial_number
                                                    || 'N/A' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <div class="text-sm text-gray-500">
                                                    <span class="font-medium">Assigned:</span>
                                                </div>
                                                <!-- Assignment Status Indicator -->
                                                <div class="flex items-center text-xs">
                                                    <span v-if="asset.person_assigned"
                                                        class="inline-flex items-center text-sm text-green-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                            fill="currentColor" class="w-4 h-4 mr-1">
                                                            <path
                                                                d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                                                        </svg>
                                                        {{ asset.person_assigned }}
                                                    </span>
                                                    <span v-else
                                                        class="inline-flex items-center text-sm text-orange-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                            fill="currentColor" class="w-4 h-4 mr-1">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zM10 15a1 1 0 100-2 1 1 0 000 2z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        Unassigned
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 border-r border-gray-100 last:border-r-0">
                                    <div class="space-y-2">
                                        <!-- Location Hierarchy with Indicators -->
                                        <div class="flex items-center space-x-2">
                                            <div class="flex items-center space-x-2">
                                                <!-- Region Indicator -->
                                                <div class="w-3 h-3 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 shadow-sm" title="Region"></div>
                                                <div class="text-xs font-medium text-gray-900">{{ asset.region?.name
                                                    || 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2 pl-5">
                                            <div class="flex items-center space-x-2">
                                                <!-- Location Indicator -->
                                                <div class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-green-500 to-green-600 shadow-sm" title="Location">
                                                </div>
                                                <div class="text-xs text-gray-700">{{ asset.location?.name || 'N/A'
                                                    }}</div>
                                            </div>
                                        </div>
                                        <div v-if="asset.sub_location?.name"
                                            class="flex items-center space-x-2 pl-10">
                                            <div class="flex items-center space-x-2">
                                                <!-- Sub-location Indicator -->
                                                <div class="w-2 h-2 rounded-full bg-gradient-to-r from-gray-400 to-gray-500 shadow-sm"
                                                    title="Sub-location"></div>
                                                <div class="text-xs text-gray-500">{{ asset.sub_location?.name || 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 border-r border-gray-100 last:border-r-0">
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-2">
                                            <span :class="{
                                                'bg-green-100 text-green-800': asset.status === 'active' || asset.status === 'in_use',
                                                'bg-yellow-100 text-yellow-800': asset.status === 'pending_approval',
                                                'bg-orange-100 text-orange-800': asset.status === 'maintenance',
                                                'bg-red-100 text-red-800': asset.status === 'disposed' || asset.status === 'retired',
                                                'bg-gray-100 text-gray-800': !['active', 'in_use', 'pending_approval', 'maintenance', 'disposed', 'retired'].includes(asset.status)
                                            }"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                <span v-if="asset.status === 'pending_approval'"
                                                    class="w-2 h-2 bg-yellow-400 rounded-full mr-1 animate-pulse"></span>
                                                <span
                                                    v-else-if="asset.status === 'active' || asset.status === 'in_use'"
                                                    class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                                                <span v-else-if="asset.status === 'maintenance'"
                                                    class="w-2 h-2 bg-orange-400 rounded-full mr-1 animate-pulse"></span>
                                                <span v-else class="w-2 h-2 bg-gray-400 rounded-full mr-1"></span>
                                                {{ formatStatus(asset.status) }}
                                            </span>
                                            <!-- Maintenance Due Indicator -->
                                            <span v-if="isMaintenanceDue(asset)"
                                                class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                                title="Maintenance Due">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" class="w-3 h-3 mr-1">
                                                    <path fill-rule="evenodd"
                                                        d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Maint
                                            </span>
                                        </div>
                                        <div v-if="asset.submitted_for_approval"
                                            class="flex items-center space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor" class="w-4 h-4 text-yellow-500">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L8.107 10.5a.75.75 0 00-1.214 1.029l1.5 2.25a.75.75 0 001.214-.15l4-5.75z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-xs text-yellow-600">
                                                Submitted {{ formatDate(asset.submitted_at) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 border-r border-gray-100 last:border-r-0">
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-gradient-to-r from-slate-100 to-slate-200 rounded-lg shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" class="w-4 h-4 text-slate-600">
                                                    <path fill-rule="evenodd"
                                                        d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ formatDate(asset.acquisition_date) }}
                                            </div>
                                        </div>
                                        <div v-if="getAssetAge(asset) > 0" class="flex items-center space-x-2 pl-9">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-xs font-medium text-amber-700">
                                                {{ getAssetAge(asset) }} years old
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 border-r border-gray-100 last:border-r-0">
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-gradient-to-r from-emerald-100 to-emerald-200 rounded-lg shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-emerald-600">
                                                    <path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.202.592.037.051.08.102.128.152z" />
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-6a.75.75 0 01.75.75v.316a3.78 3.78 0 011.653.713c.426.33.744.74.925 1.2a.75.75 0 01-1.395.55 1.35 1.35 0 00-.447-.563 2.187 2.187 0 00-.736-.363V9.3c.698.093 1.383.32 1.959.696.787.514 1.29 1.27 1.29 2.13 0 .86-.504 1.616-1.29 2.13-.576.377-1.261.603-1.959.696v.299a.75.75 0 11-1.5 0v-.3c-.697-.092-1.382-.318-1.958-.695C5.896 13.744 5.25 12.845 5.25 12.26c0-.686.647-1.484 1.342-1.997.576-.377 1.261-.603 1.958-.696V6.934a2.187 2.187 0 00-.736.363 1.35 1.35 0 00-.447.563.75.75 0 11-1.395-.55c.181-.46.499-.87.925-1.2A3.78 3.78 0 018.25 5.25V4.75A.75.75 0 0110 4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="text-lg font-bold text-gray-900">
                                                ${{ parseFloat(asset.original_value || 0).toLocaleString() }}
                                            </div>
                                            <!-- Critical Asset Indicator -->
                                            <span v-if="isCriticalAsset(asset)" 
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 shadow-sm"
                                                  title="Critical Asset">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" class="w-3 h-3 mr-1">
                                                    <path fill-rule="evenodd"
                                                        d="M9.661 2.237a.531.531 0 01.678 0 11.947 11.947 0 007.078 2.749.5.5 0 01.479.425c.069.52.104 1.05.104 1.589 0 5.162-3.26 9.563-7.834 11.256a.48.48 0 01-.332 0C5.26 16.564 2 12.162 2 7c0-.539.035-1.07.104-1.589a.5.5 0 01.48-.425 11.947 11.947 0 007.077-2.749zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Critical
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2 pl-9">
                                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                            <div class="text-sm font-medium text-gray-600">{{ asset.fund_source?.name || 'N/A'
                                                }}</div>
                                        </div>
                                    </div>
                                </td>

                                    <!-- Files Column -->
                                    <td class="px-6 py-5 border-r border-gray-100 last:border-r-0">
                                        <div v-if="asset.attachments && asset.attachments.length > 0"
                                            class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-amber-50 to-amber-100 text-amber-700 text-xs font-medium rounded-md border border-amber-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                                                <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                                            </svg>
                                            {{ asset.attachments.length }} file(s)
                                        </div>
                                        <div v-else class="text-sm text-gray-500">
                                            No files
                                        </div>
                                    </td>

                                    <!-- Actions Dropdown Column -->
                                    <td class="px-6 py-5 relative">
                                        <div class="relative">
                                            <button @click="toggleDropdown(asset.id)" 
                                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 text-gray-700 hover:text-gray-900 text-xs font-medium rounded-md border border-gray-200 transition-all duration-200 group-hover:shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                    <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                                                </svg>
                                            </button>
                                            
                                            <!-- Dropdown Menu -->
                                            <div v-if="activeDropdown === asset.id" 
                                                class="absolute right-0 z-10 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1">
                                                <Link :href="route('assets.show', asset.id)"
                                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-2 text-indigo-600">
                                                        <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                                        <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A11.008 11.008 0 0110 1.5c4.257 0 7.893 2.66 9.336 6.41.147.381.147.804 0 1.186A11.008 11.008 0 0110 15.5c-4.257 0-7.893-2.66-9.336-6.91zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    View Asset
                                                </Link>
                                                <Link :href="route('assets.edit', asset.id)"
                                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-2 text-gray-600">
                                                        <path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                                                    </svg>
                                                    Edit Asset
                                                </Link>
                                                <button v-if="asset.status === 'active' || asset.status === 'in_use'"
                                                    @click="openTransferModal(asset); closeDropdown()"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-2 text-blue-600">
                                                        <path fill-rule="evenodd" d="M13.2 2.24a.75.75 0 00.04 1.06L15.54 5H9.5a7 7 0 000 14h.75a.75.75 0 000-1.5H9.5A5.5 5.5 0 019.5 6.5h6.04l-2.3 1.7a.75.75 0 001.02 1.1l3.5-2.6a.75.75 0 000-1.2l-3.5-2.6a.75.75 0 00-1.06.04z" clip-rule="evenodd" />
                                                    </svg>
                                                    Transfer Asset
                                                </button>
                                                <template v-if="asset.status === 'pending_approval' && canApproveAsset(asset)">
                                                    <div class="border-t border-gray-100"></div>
                                                    <button @click="openApprovalModal(asset, 'approve'); closeDropdown()"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-2 text-green-600">
                                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                                        </svg>
                                                        Approve Asset
                                                    </button>
                                                    <button @click="openApprovalModal(asset, 'reject'); closeDropdown()"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-2 text-red-600">
                                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                        </svg>
                                                        Reject Asset
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 mb-[80px]">
                    <div class="flex items-center justify-end">
                        <TailwindPagination :data="props.assets" @pagination-change-page="getResults" :limit="2" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <TransitionRoot as="template" :show="showImportModal">
            <Dialog as="div" class="fixed z-50 inset-0 overflow-y-auto" @close="showImportModal = false">
                <div class="flex items-center justify-center min-h-screen p-4 text-center">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                        enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100"
                        leave-to="opacity-0">
                        <DialogOverlay class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                    </TransitionChild>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <div
                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div
                                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-6 h-6 text-blue-600">
                                            <path
                                                d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900">
                                            Import Assets
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                Upload an Excel file to import assets. Make sure your file follows the
                                                required format.
                                            </p>
                                            <div class="mt-4">
                                                <input ref="fileInput" type="file" accept=".xlsx,.xls"
                                                    @change="handleFileSelect"
                                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="importAssets" :disabled="!selectedFile || importing"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                    <svg v-if="importing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ importing ? 'Importing...' : 'Import' }}
                                </button>
                                <button type="button" @click="showImportModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                        enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100"
                        leave-to="opacity-0">
                        <DialogOverlay class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                    </TransitionChild>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <div
                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div
                                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-6 h-6 text-blue-600">
                                            <path d="M16 17l-4 4-4-4m4 4V3" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900">
                                            Transfer Asset
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                Transfer asset <strong>{{ selectedAsset?.asset_tag }}</strong> to a new
                                                custodian.
                                            </p>
                                            <div class="mt-4 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">New
                                                        Custodian</label>
                                                    <input v-model="transferData.custodian" type="text"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                        placeholder="Enter custodian name" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Transfer
                                                        Date</label>
                                                    <input v-model="transferData.transfer_date" type="date"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Notes
                                                        (Optional)</label>
                                                    <textarea v-model="transferData.assignment_notes" rows="3"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                        placeholder="Add any notes about the transfer"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="transferAsset"
                                    :disabled="!transferData.custodian || !transferData.transfer_date || transferring"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                    <svg v-if="transferring" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ transferring ? 'Transferring...' : 'Transfer' }}
                                </button>
                                <button type="button" @click="showTransferModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                        enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100"
                        leave-to="opacity-0">
                        <DialogOverlay class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                    </TransitionChild>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <div
                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                        :class="approvalAction === 'approve' ? 'bg-green-100' : 'bg-red-100'">
                                        <svg v-if="approvalAction === 'approve'" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-green-600">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                            fill="currentColor" class="w-6 h-6 text-red-600">
                                            <path d="M6 18L18 6M6 6l12 12" />
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
                                            <label for="approval-notes"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Notes (Optional)
                                            </label>
                                            <textarea id="approval-notes" v-model="approvalNotes" rows="3"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Add any notes about your decision..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" 
                                    @click="processApproval" 
                                    :disabled="processingApproval"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                                    :class="approvalAction === 'approve' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'">
                                    <svg v-if="processingApproval" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ processingApproval ? 'Processing...' : (approvalAction === 'approve' ? 'Approve'
                                    : 'Reject') }}
                                </button>
                                <button type="button" @click="showApprovalModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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

// Filter state for multiselect components
const selectedLocation = ref(null);
const selectedStatus = ref(null);

// Status options for multiselect
const statusOptions = ref([
    { label: 'Active', value: 'active' },
    { label: 'In Use', value: 'in_use' },
    { label: 'Maintenance', value: 'maintenance' },
    { label: 'Retired', value: 'retired' },
    { label: 'Disposed', value: 'disposed' },
    { label: 'Pending Approval', value: 'pending_approval' }
]);

// Region, Location, SubLocation filter state
const regionFilter = ref(null);
const locationFilter = ref(null);
const subLocationFilter = ref(null);
const subLocationOptions = ref([]);
const fundSourceFilter = ref(null);

// Dropdown functionality
const activeDropdown = ref(null);

function toggleDropdown(assetId) {
    activeDropdown.value = activeDropdown.value === assetId ? null : assetId;
}

function closeDropdown() {
    activeDropdown.value = null;
}

// Close dropdown when clicking outside
onMounted(() => {
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.relative')) {
            activeDropdown.value = null;
        }
    });
});

const regionOptions = computed(() => props.regions || []);

// Safe location options with fallback
const safeLocationOptions = computed(() => props.locations || []);

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
        () => (selectedLocation.value ? selectedLocation.value.id : null),
        () => (selectedStatus.value ? selectedStatus.value.value : null),
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
    if (selectedLocation.value && selectedLocation.value.id)
        query.location_id = selectedLocation.value.id;
    if (selectedStatus.value && selectedStatus.value.value)
        query.status = selectedStatus.value.value;
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
    selectedLocation.value = null;
    selectedStatus.value = null;
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

// New functions for asset indicators
const getWarrantyStatus = (asset) => {
    if (!asset.warranty_expiry_date) return 'none';
    const expiryDate = moment(asset.warranty_expiry_date);
    const today = moment();
    const diffDays = expiryDate.diff(today, 'days');

    if (diffDays < 0) {
        return 'expired';
    } else if (diffDays < 30) {
        return 'expiring';
    }
    return 'valid';
};

const getValueLevel = (asset) => {
    if (asset.original_value > 100000) return 'high';
    if (asset.original_value > 10000) return 'medium';
    return 'low';
};

const getAssetAge = (asset) => {
    if (!asset.acquisition_date) return 0;
    const acquisitionDate = moment(asset.acquisition_date);
    const today = moment();
    return today.diff(acquisitionDate, 'years');
};

const isMaintenanceDue = (asset) => {
    if (!asset.maintenance_due_date) return false;
    const dueDate = moment(asset.maintenance_due_date);
    const today = moment();
    return dueDate.diff(today, 'days') < 0;
};

const isCriticalAsset = (asset) => {
    return asset.critical_asset === true;
};

// Asset Tag Validation Function
const isValidAssetTag = (assetTag) => {
    if (!assetTag) return false;

    // Standard asset tag format: letters/numbers, usually 6+ characters
    // Example patterns: AST001, COMP-2024-001, etc.
    const standardPatterns = [
        /^[A-Z]{2,4}[0-9]{3,6}$/i,              // AST001, COMP001234
        /^[A-Z]{2,4}-[0-9]{4}-[0-9]{3,6}$/i,    // AST-2024-001
        /^[A-Z0-9]{6,12}$/i,                    // ASSET001, ABC123DEF
        /^[A-Z]{2,4}_[0-9]{4}_[0-9]{3,6}$/i     // AST_2024_001
    ];

    return standardPatterns.some(pattern => pattern.test(assetTag));
};

// Serial Number Duplicate Check Function
const isDuplicateSerialNumber = (asset) => {
    if (!asset.serial_number) return false;

    // Check if any other asset in the current data has the same serial number
    const duplicates = props.assets.data.filter(a =>
        a.id !== asset.id &&
        a.serial_number &&
        a.serial_number.toLowerCase() === asset.serial_number.toLowerCase()
    );

    return duplicates.length > 0;
};
</script>
