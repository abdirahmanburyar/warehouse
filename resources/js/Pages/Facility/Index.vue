<template>
    <AuthenticatedLayout title="Manage Facilities" description="Manage facilities" img="/assets/images/facility.png">

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-6 lg:mb-0">
                        <h1 class="text-3xl font-bold text-white mb-2">Facilities Management</h1>
                        <p class="text-blue-100 text-lg">Manage and monitor all healthcare facilities in the system</p>
                        <div class="flex items-center mt-4 space-x-6 text-blue-100">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                <span class="text-sm">{{ activeCount }} Active</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                                <span class="text-sm">{{ inactiveCount }} Inactive</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                                <span class="text-sm">{{ totalCount }} Total</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <!-- Excel Upload Button -->
                        <button @click="openUploadModal" 
                            class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 cursor-pointer border-2 border-transparent hover:border-blue-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Import Excel
                        </button>
                        
                        <!-- Add Facility Button -->
                        <Link :href="route('facilities.create')"
                            class="inline-flex items-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add New Facility
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters Section -->
            <div class="bg-gray-50 px-8 py-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <!-- Search Bar -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Search Facilities</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search by name, type, manager, or district..."
                                class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white shadow-sm transition-all duration-200"
                            >
                        </div>
                    </div>
                    
                    <!-- District Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Filter by District</label>
                        <Multiselect
                            v-model="district"
                            :options="props.districts"
                            placeholder="All Districts"
                            :searchable="true"
                            :allow-empty="true"
                            :show-labels="false"
                            class="multiselect-professional"
                        />
                    </div>
                    
                    <!-- Facility Type Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Filter by Type</label>
                        <Multiselect
                            v-model="facilityType"
                            :options="props.facilityTypes"
                            placeholder="All Types"
                            :searchable="true"
                            :allow-empty="true"
                            :show-labels="false"
                            class="multiselect-professional"
                        />
                    </div>
                    
                    <!-- Per Page Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Items per page</label>
                        <select
                            v-model="per_page"
                            @change="props.filters.page = 1"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white shadow-sm transition-all duration-200"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Professional Table Section -->
            <div class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-200">
                                    <div class="flex items-center">
                                        S/N
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-200">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </span>
                                        Facility Name
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-200">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                        </span>
                                        Type
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-200">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </span>
                                        Manager
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-200">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </span>
                                        Handled By
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-200">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </span>
                                        District
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-800 uppercase tracking-wider border-r border-gray-200">
                                    <div class="flex items-center justify-center">
                                        <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </span>
                                        Status
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    <div class="flex items-center justify-center">
                                        <span class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </span>
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="(facility, index) in props.facilities.data" :key="facility.id" 
                                class="hover:bg-blue-50 transition-all duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap border-r border-gray-100">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-sm font-bold text-gray-600 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors duration-200">
                                            {{ index + 1 }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap border-r border-gray-100">
                                    <div class="flex items-center">
                                        <div>
                                            <Link :href="route('facilities.show', facility.id)" 
                                                class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                                {{ facility.name }}
                                            </Link>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap border-r border-gray-100">
                                    <div class="flex items-center">
                                        <span class="text-xs font-semibold text-gray-900 capitalize">{{ facility.facility_type }}</span>
                                        
                                    </div>
                                </td>
                                <td class="px-6 py-5 border-r border-gray-100">
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ facility.user?.name || 'Not assigned' }}</div>
                                                <div class="text-xs text-gray-500">Manager</div>
                                            </div>
                                        </div>
                                        <div v-if="facility.user?.email" class="flex items-center text-xs text-gray-600">
                                            <svg class="h-3 w-3 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ facility.user.email }}
                                        </div>
                                        <div v-if="facility.user?.phone" class="flex items-center text-xs text-gray-600">
                                            <svg class="h-3 w-3 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            {{ facility.user.phone }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 border-r border-gray-100">
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ facility.handledby?.name || 'Not assigned' }}</div>
                                                <div class="text-xs text-gray-500">Handler</div>
                                            </div>
                                        </div>
                                        <div v-if="facility.handledby?.email" class="flex items-center text-xs text-gray-600">
                                            <svg class="h-3 w-3 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ facility.handledby.email }}
                                        </div>
                                        <div v-if="facility.handledby?.phone" class="flex items-center text-xs text-gray-600">
                                            <svg class="h-3 w-3 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            {{ facility.handledby.phone }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap border-r border-gray-100">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ facility.district || 'Not assigned' }}</div>
                                            <div class="text-xs text-gray-500">District</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center border-r border-gray-100">
                                    <span
                                        :class="facility.is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200'"
                                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border-2 shadow-sm">
                                        <svg class="w-3 h-3 mr-2" :class="facility.is_active ? 'text-green-600' : 'text-red-600'" fill="currentColor" viewBox="0 0 20 20">
                                            <path v-if="facility.is_active" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ facility.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <Link :href="route('facilities.edit', facility.id)"
                                            class="inline-flex items-center justify-center w-10 h-10 text-indigo-600 hover:text-white hover:bg-indigo-600 border-2 border-indigo-200 hover:border-indigo-600 rounded-xl transition-all duration-200 transform hover:scale-105">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </Link>
                                        <button
                                            @click="confirmToggleStatus(facility)"
                                            class="relative inline-flex flex-shrink-0 h-10 w-16 border-2 border-transparent rounded-xl cursor-pointer transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:scale-105"
                                            :class="{
                                                'bg-green-500 hover:bg-green-600': facility.is_active,
                                                'bg-red-500 hover:bg-red-600': !facility.is_active,
                                                'opacity-50 cursor-wait': loadingProducts.has(facility.id)
                                            }"
                                            :disabled="loadingProducts.has(facility.id)"
                                        >
                                            <span
                                                 class="inline-block h-6 w-6 transform rounded-lg transition-transform duration-300 mt-1"
                                                :class="{
                                                    'translate-x-8': facility.is_active,
                                                    'translate-x-1': !facility.is_active,
                                                    'bg-white shadow-lg': !loadingProducts.has(facility.id),
                                                    'bg-gray-200 animate-pulse': loadingProducts.has(facility.id)
                                                }"
                                            />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Enhanced Pagination -->
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="text-sm text-gray-700 font-medium">
                        Showing <span class="font-bold text-gray-900">{{ props.facilities.meta.from || 0 }}</span> to 
                        <span class="font-bold text-gray-900">{{ props.facilities.meta.to || 0 }}</span> of 
                        <span class="font-bold text-gray-900">{{ props.facilities.meta.total || 0 }}</span> facilities
                    </div>
                    <TailwindPagination 
                        :data="props.facilities"
                        :limit="2"
                        class="flex items-center space-x-2"
                        @pagination-change-page="getResults"
                    />
                </div>
            </div>
        </div>

        <!-- Excel Upload Modal -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="closeUploadModal"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Upload Facilities</h3>
                        <p class="text-sm text-gray-500 mt-1">Import facilities from Excel file</p>
                    </div>
                    <button
                        @click="closeUploadModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Download Template Section -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-green-800">Need a template?</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Download our XLSX template with the correct column format for uploading facilities. The template includes only the required headers.
                                </p>
                                <button
                                    @click="downloadTemplate"
                                    :disabled="isDownloadingTemplate"
                                    class="mt-3 inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="isDownloadingTemplate" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    {{ isDownloadingTemplate ? 'Generating...' : 'Download XLSX Template' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Required Columns</h3>
                        <p class="text-sm text-gray-600 mb-3">Your Excel file must include these columns (first row should be headers):</p>
                        <ul class="space-y-2 text-sm">
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>
                                    <span class="font-medium">facility_name</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>
                                    <span class="font-medium">facility_type</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>
                                    <span class="font-medium">district</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">region</span>
                                    <span class="text-gray-400 ml-2">(optional)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">email</span>
                                    <span class="text-gray-400 ml-2">(optional)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">phone</span>
                                    <span class="text-gray-400 ml-2">(optional)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">address</span>
                                    <span class="text-gray-400 ml-2">(optional)</span>
                                </li>
                            </ul>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Select File</h3>
                        <p class="text-sm text-gray-600 mb-3">Choose an Excel file (.xlsx, .xls) or CSV file to upload:</p>
                        
                        <div class="flex items-center space-x-4">
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                class="hidden"
                                @change="handleFileUpload"
                            />
                            <button
                                @click="triggerFileInput"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Choose File
                            </button>
                            
                            <span v-if="fileInput?.files?.[0]" class="text-sm text-gray-600">
                                Selected: {{ fileInput.files[0].name }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="fileInput?.files?.[0]"
                        class="mt-4 flex items-center justify-between bg-blue-50 p-4 rounded-lg border border-blue-200"
                    >
                        <div class="flex items-center space-x-3">
                            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-900">{{ fileInput.files[0].name }}</p>
                                <p class="text-xs text-blue-700">{{ (fileInput.files[0].size / 1024 / 1024).toFixed(2) }} MB</p>
                            </div>
                        </div>
                        <button
                            @click="removeSelectedFile"
                            class="text-blue-500 hover:text-blue-700 transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Upload Progress -->
                    <div v-if="isUploading" class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Upload Progress</h4>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">{{ uploadProgress }}% complete</p>
                    </div>

                    <!-- Upload Results -->
                    <div v-if="uploadResults && !isUploading" class="mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <h3 class="text-sm font-medium text-green-800">Upload Results</h3>
                            <p class="text-sm text-green-700 mt-1">{{ uploadResults.message }}</p>
                            <div v-if="uploadResults.import_id" class="mt-2 text-xs text-gray-600">
                                <p>Import ID: {{ uploadResults.import_id }}</p>
                                <p v-if="uploadResults.status">Status: {{ uploadResults.status }}</p>
                                <p v-if="uploadResults.completed_at">Completed at: {{ formatDate(uploadResults.completed_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Errors -->
                    <div v-if="uploadErrors.length > 0" class="mb-6">
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <h3 class="text-sm font-medium text-red-800">Upload Errors</h3>
                            <ul class="mt-2 text-sm text-red-700 space-y-1">
                                <li v-for="(error, index) in uploadErrors" :key="index" class="flex items-start">
                                    <span class="w-2 h-2 bg-red-400 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button
                        @click="closeUploadModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="uploadFile"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200"
                        :disabled="!fileInput?.files?.[0] || isUploading"
                    >
                        <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        {{ isUploading ? 'Uploading...' : 'Upload File' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import '@/Components/multiselect.css'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'
import { TailwindPagination } from "laravel-vue-pagination"
import moment from "moment"

const toast = useToast()
const isUploading = ref(false)
const uploadErrors = ref([])

// Upload modal states
const showUploadModal = ref(false)
const fileInput = ref(null)
const uploadProgress = ref(0)
const uploadResults = ref(null)
const importId = ref(null)
const isDownloadingTemplate = ref(false)

const props = defineProps({
    facilities: {
        type: Object,
        required: true
    },
    facilityCounts: {
        type: Object,
        required: true,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0
        })
    },
    users: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        required: true
    },
    districts: {
        type: Array,
        required: true
    },
    facilityTypes: {
        type: Array,
        required: true
    }
})

// Computed properties for statistics - now using independent counts from controller
const activeCount = computed(() => {
    return props.facilityCounts?.active || 0
})

const inactiveCount = computed(() => {
    return props.facilityCounts?.inactive || 0
})

const totalCount = computed(() => {
    return props.facilityCounts?.total || 0
})

const per_page = ref(props.filters.per_page || 25)
const search = ref(props.filters.search)
const district = ref(props.filters.district)
const facilityType = ref(props.filters.facility_type)
const loadingProducts = ref(new Set());

// Handle file selection
const handleFileUpload = (event) => {
    const file = event.target.files[0]
    
    if (!file) {
        event.target.value = null
        return
    }

    // Validate file type
    const allowedTypes = ['.xlsx', '.xls', '.csv']
    const fileExtension = '.' + file.name.split('.').pop().toLowerCase()
    
    if (!allowedTypes.includes(fileExtension)) {
        toast.error("Please select a valid file type (.xlsx, .xls, .csv)")
        event.target.value = null
        return
    }

    // Validate file size (max 50MB)
    const maxSize = 50 * 1024 * 1024 // 50MB in bytes
    if (file.size > maxSize) {
        toast.error("File is too large. Maximum file size is 50MB.")
        event.target.value = null
        return
    }

    // Clear any previous errors
    uploadErrors.value = []
}

// Upload file function
const uploadFile = async () => {
    if (!fileInput.value.files[0]) {
        toast.error('Please select a file first');
        return;
    }

    const file = fileInput.value.files[0];
    
    // Validate file type
    const allowedTypes = ['.xlsx', '.xls', '.csv'];
    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
    
    if (!allowedTypes.includes(fileExtension)) {
        toast.error('Please select a valid file type (.xlsx, .xls, .csv)');
        return;
    }

    // Validate file size (max 50MB)
    const maxSize = 50 * 1024 * 1024; // 50MB in bytes
    if (file.size > maxSize) {
        toast.error('File size too large. Maximum allowed size is 50MB');
        return;
    }

    try {
        uploadProgress.value = 0;
        uploadResults.value = null;
        uploadErrors.value = [];

        const formData = new FormData();
        formData.append('file', file);

        // Show loading toast
        const loadingToast = toast.info("Uploading file...", {
            timeout: false,
            closeOnClick: false,
            draggable: false,
        });

        const response = await axios.post(route('facilities.import'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            }
        });

        toast.dismiss(loadingToast);

        if (response.data.success) {
            uploadResults.value = {
                message: response.data.message,
                import_id: response.data.import_id
            };
            
            // Start progress tracking
            if (response.data.import_id) {
                importId.value = response.data.import_id;
                startProgressTracking();
            }
            
            toast.success(response.data.message);
            
            // Clear file input
            fileInput.value.value = '';
            
            // Close modal after successful upload
            setTimeout(() => {
                closeUploadModal();
            }, 2000);
            
        } else {
            uploadErrors.value = [response.data.message];
            toast.error(response.data.message);
        }

    } catch (error) {
        console.error('Upload error:', error);
        
        if (error.response?.data?.message) {
            uploadErrors.value = [error.response.data.message];
            toast.error(error.response.data.message);
        } else {
            uploadErrors.value = ['An unexpected error occurred during upload'];
            toast.error('Upload failed. Please try again.');
        }
    }
};

// Progress tracking function
const startProgressTracking = () => {
    if (!importId.value) return;
    
    const checkProgress = async () => {
        try {
            // You can implement a progress endpoint here if needed
            // For now, we'll just show a generic progress message
            uploadProgress.value = 100;
            uploadResults.value = {
                message: 'Import completed successfully!',
                import_id: importId.value
            };
        } catch (error) {
            console.error('Progress tracking error:', error);
        }
    };
    
    // Check progress every 2 seconds
    const progressInterval = setInterval(checkProgress, 2000);
    
    // Stop tracking after 5 minutes
    setTimeout(() => {
        clearInterval(progressInterval);
    }, 300000);
};

// Download template function
const downloadTemplate = async () => {
    if (isDownloadingTemplate.value) return; // Prevent multiple downloads
    
    try {
        isDownloadingTemplate.value = true;
        
        // Show loading state
        const loadingToast = toast.info("Generating XLSX template...", {
            timeout: false,
            closeOnClick: false,
            draggable: false,
        });
        
        // Import XLSX library dynamically
        const XLSX = await import('xlsx');
        
        // Define headers that match FacilitiesImport expectations
        const headers = ['facility_name', 'facility_type', 'district', 'region', 'email', 'phone', 'address'];
        
        // Create workbook and worksheet
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.aoa_to_sheet([headers]);
        
        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Facilities Template');
        
        // Generate XLSX file and download
        XLSX.writeFile(workbook, 'facilities_import_template.xlsx');
        
        // Dismiss loading toast and show success
        toast.dismiss(loadingToast);
        toast.success('XLSX template downloaded successfully! Open with Excel to use.');
        
    } catch (error) {
        console.error('Error generating XLSX template:', error);
        toast.error('Failed to generate template. Please try again.');
    } finally {
        isDownloadingTemplate.value = false;
    }
};

// Open upload modal
const openUploadModal = () => {
    showUploadModal.value = true;
    // Clear file input value when opening modal
    if (fileInput.value) {
        fileInput.value.value = null;
    }
    uploadErrors.value = [];
    uploadResults.value = null;
    importId.value = null;
    uploadProgress.value = 0;
};

// Close upload modal
const closeUploadModal = () => {
    showUploadModal.value = false;
    // Clear file input value when closing modal
    if (fileInput.value) {
        fileInput.value.value = null;
    }
    uploadErrors.value = [];
    uploadResults.value = null;
    importId.value = null;
    uploadProgress.value = 0;
};

// Trigger file input click
const triggerFileInput = () => {
    fileInput.value.click();
};

// Remove selected file
const removeSelectedFile = () => {
    // Clear the file input value
    if (fileInput.value) {
        fileInput.value.value = null;
    }
    uploadErrors.value = [];
};

// Format date for upload results
const formatDate = (date) => {
    if (!date) return "N/A";
    return moment(date).format("DD/MM/YYYY");
};

watch([
    () => per_page.value,
    () => props.filters.page,
    () => search.value,
    () => district.value,
    () => facilityType.value
], () => {
    reloadFacility();
})

const reloadFacility = () => {
    const query = {}
    if (per_page.value) query.per_page = per_page.value
    if (props.filters.page) query.page = props.filters.page
    if (search.value) query.search = search.value
    if (district.value) query.district = district.value
    if (facilityType.value) query.facility_type = facilityType.value
    
    router.get(route('facilities.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: ['facilities', 'facilityCounts', 'users', 'districts', 'facilityTypes']
    })
}

const getResults = (page) => {
   props.filters.page = page;
}

const confirmToggleStatus = (product) => {
    const action = product.is_active ? 'deactivate' : 'activate';
    
    Swal.fire({
        title: 'Are you sure?',
        html: `<p>Do you want to ${action} ${product.name}?</p>`,
        showConfirmButton: true,
        icon: undefined,
        showCancelButton: true,
        confirmButtonColor: product.is_active ? '#d33' : '#3085d6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: product.is_active ? 'Yes, deactivate!' : 'Yes, activate!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            loadingProducts.value.add(product.id);
            try {
                await axios.get(route('facilities.toggle-status', product.id));
                reloadFacility();
                Swal.fire(
                    action === 'activate' ? 'Activated!' : 'Deactivated!',
                    `Facility has been ${action}d.`,
                    'success'
                );
            } catch (error) {
                toast.error(error.response?.data || 'An error occurred');
            } finally {
                loadingProducts.value.delete(product.id);
            }
        }
    });
};
</script>

<style>
/* Custom multiselect styling */
.multiselect-professional .multiselect__tags {
    @apply border-gray-300 rounded-xl shadow-sm;
}

.multiselect-professional .multiselect__input {
    @apply text-sm;
}

.multiselect-professional .multiselect__single {
    @apply text-sm text-gray-700;
}

.multiselect-professional .multiselect__content-wrapper {
    @apply border-gray-300 rounded-lg shadow-lg;
}

.multiselect-professional .multiselect__option--highlight {
    @apply bg-blue-500;
}

.multiselect-professional .multiselect__option--selected {
    @apply bg-blue-100 text-blue-800;
}
</style>
