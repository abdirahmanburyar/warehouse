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
                                <span class="text-sm">{{ props.facilities.total }} Total</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <!-- Excel Upload Button -->
                        <label class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 cursor-pointer border-2 border-transparent hover:border-blue-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Import Excel
                            <input type="file" class="hidden" @change="handleFileUpload" accept=".xlsx,.xls"/>
                        </label>
                        
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                                        <div v-if="facility.email" class="flex items-center text-xs text-gray-600">
                                            <svg class="h-3 w-3 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ facility.email }}
                                        </div>
                                        <div v-if="facility.phone" class="flex items-center text-xs text-gray-600">
                                            <svg class="h-3 w-3 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            {{ facility.phone }}
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
import { TailwindPagination } from "laravel-vue-pagination";

const toast = useToast()
const selectedFile = ref(null)
const isUploading = ref(false)
const uploadErrors = ref([])

const props = defineProps({
    facilities: {
        type: Object,
        required: true
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
    }
})

// Computed properties for statistics
const activeCount = computed(() => {
    return props.facilities.data?.filter(facility => facility.is_active).length || 0
})

const inactiveCount = computed(() => {
    return props.facilities.data?.filter(facility => !facility.is_active).length || 0
})

const per_page = ref(props.filters.per_page || 25)
const search = ref(props.filters.search)
const district = ref(props.filters.district)
const loadingProducts = ref(new Set());

// Handle file selection
const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
            file.type === 'application/vnd.ms-excel') {
            selectedFile.value = file
            uploadErrors.value = []
            uploadFile() // Auto-upload when file is selected
        } else {
            toast.error('Please select a valid Excel file (.xlsx or .xls)')
        }
    }
}

// Upload the file
const uploadFile = async () => {
    if (!selectedFile.value) return
    
    isUploading.value = true
    uploadErrors.value = []
    
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    
    try {
        const response = await axios.post(route('facilities.import'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        
        toast.success(response.data.message)
        
        // Show processing notification
        toast.info('Processing facilities in the background. The page will refresh in 10 seconds.')
        
        // Wait 10 seconds before reloading to allow some processing time
        setTimeout(() => {
            router.reload()
        }, 10000)
    } catch (error) {
        if (error.response?.data?.errors) {
            uploadErrors.value = Object.values(error.response.data.errors).flat()
        } else {
            uploadErrors.value = [error.response?.data?.message || 'Failed to import facilities']
        }
    } finally {
        isUploading.value = false
    }
}

watch([
    () => per_page.value,
    () => props.filters.page,
    () => search.value,
    () => district.value
], () => {
    reloadFacility();
})

const reloadFacility = () => {
    const query = {}
    if (per_page.value) query.per_page = per_page.value
    if (props.filters.page) query.page = props.filters.page
    if (search.value) query.search = search.value
    if (district.value) query.district = district.value
    
    router.get(route('facilities.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: ['facilities']
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
