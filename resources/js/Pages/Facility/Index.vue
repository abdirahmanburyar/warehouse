<template>
    <AuthenticatedLayout title="Manage Facilities" description="Manage facilities" img="/assets/images/facility.png">
        <div class="max-w-7xl mx-auto py-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Facilities</h1>
                            <p class="mt-1 text-sm text-gray-600">Manage and monitor all facilities in the system</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Excel Upload Button -->
                            <label class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 cursor-pointer">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Upload Excel
                                <input type="file" class="hidden" @change="handleFileUpload" accept=".xlsx,.xls"/>
                            </label>
                            
                            <!-- Add Facility Button -->
                            <Link :href="route('facilities.create')"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Facility
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Search Bar -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Facilities</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    v-model="search" 
                                    placeholder="Search by name, type, manager..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </div>
                        </div>
                        
                        <!-- District Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">District</label>
                            <Multiselect
                                v-model="district"
                                :options="props.districts"
                                placeholder="Select district"
                                :searchable="true"
                                :allow-empty="true"
                                :show-labels="false"
                                class="multiselect-indigo"
                            />
                        </div>

                        <!-- Per Page Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Items per page</label>
                            <select 
                                v-model="per_page"
                                @change="props.filters.page = 1"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        SN
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        Name
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        Type
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Manager
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Handled By
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        District
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Status
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(facility, index) in props.facilities.data" :key="facility.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <Link :href="route('facilities.show', facility.id)" class="text-blue-600 hover:text-blue-900 font-medium">
                                            {{ facility.name }}
                                        </Link>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center mr-3"
                                             :class="{
                                                 'bg-blue-100': facility.facility_type === 'hospital',
                                                 'bg-green-100': facility.facility_type === 'clinic',
                                                 'bg-purple-100': facility.facility_type === 'pharmacy'
                                             }">
                                            <svg class="h-4 w-4" :class="{
                                                'text-blue-600': facility.facility_type === 'hospital',
                                                'text-green-600': facility.facility_type === 'clinic',
                                                'text-purple-600': facility.facility_type === 'pharmacy'
                                            }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 capitalize">{{ facility.facility_type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-900">{{ facility.user?.name || 'Not assigned' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600">{{ facility.email || 'No email' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600">{{ facility.phone || 'No phone' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-900">{{ facility.handledby?.name || 'Not assigned' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600">{{ facility.handledby?.email || 'No email' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600">{{ facility.handledby?.phone || 'No phone' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-900">{{ facility.district || 'Not assigned' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="facility.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium">
                                        <svg class="w-3 h-3 mr-1" :class="facility.is_active ? 'text-green-500' : 'text-red-500'" fill="currentColor" viewBox="0 0 20 20">
                                            <path v-if="facility.is_active" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ facility.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <Link :href="route('facilities.edit', facility.id)"
                                            class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-100 transition-colors duration-200">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </Link>
                                        <button
                                            @click="confirmToggleStatus(facility)"
                                            class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            :class="{
                                                'bg-red-600': facility.is_active,
                                                'bg-green-600': !facility.is_active,
                                                'opacity-50 cursor-wait': loadingProducts.has(facility.id)
                                            }"
                                            :disabled="loadingProducts.has(facility.id)"
                                        >
                                            <span 
                                                class="inline-block h-4 w-4 transform rounded-full transition-transform duration-300"
                                                :class="{
                                                    'translate-x-6': facility.is_active,
                                                    'translate-x-1': !facility.is_active,
                                                    'bg-white': !loadingProducts.has(facility.id),
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

                <!-- Pagination -->
                <div class="px-8 py-6 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ props.facilities.from || 0 }} to {{ props.facilities.to || 0 }} of {{ props.facilities.total || 0 }} results
                        </div>
                        <TailwindPagination 
                            :data="props.facilities" 
                            :limit="2" 
                            class="flex items-center space-x-3 text-sm" 
                            @pagination-change-page="getResults" 
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import '@/Components/multiselect.css'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'
import { TailwindPagination } from "laravel-vue-pagination";

const toast = useToast()

const selectedFile = ref(null)
const isUploading = ref(false)
const uploadErrors = ref([])

// Handle file selection
const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
            file.type === 'application/vnd.ms-excel') {
            selectedFile.value = file
            uploadErrors.value = []
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
        closeUploadModal()
        
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

const per_page = ref(props.filters.per_page || 25)
const search = ref(props.filters.search)
const district = ref(props.filters.district)
const loadingProducts = ref(new Set());

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

    console.log(query)
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
                    `Product has been ${action}d.`,
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

const deleteFacility = async (id) => {
    if (confirm('Are you sure you want to delete this facility?')) {
        try {
            const response = await axios.delete(route('facilities.destroy', id))
            toast.success(response.data)
            reloadFacility()
        } catch (error) {
            toast.error(error.response.data);
        }
    }
}
</script>