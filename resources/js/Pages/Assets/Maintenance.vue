<template>
    <AuthenticatedLayout title="Asset Maintenance" description="Track and manage asset maintenance activities">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="bg-white shadow-xl rounded-2xl">
                <div class="bg-gradient-to-r from-green-600 to-emerald-700 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-white">
                                    <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 00.5.707A9.735 9.735 0 006 21c2.25 0 4.334-.609 6-1.657V4.533zM12.75 20.25c0 .966-.784 1.75-1.75 1.75s-1.75-.784-1.75-1.75V4.533c0-.966.784-1.75 1.75-1.75s1.75.784 1.75 1.75v15.717z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Asset Maintenance</h1>
                                <p class="text-green-100 text-sm mt-1">Track and manage asset maintenance activities</p>
                            </div>
                        </div>
                        <div class="mt-6 sm:mt-0">
                            <Link :href="route('asset.maintenance.create')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-green-700 bg-white hover:bg-green-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                                </svg>
                                Schedule Maintenance
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white shadow-xl rounded-2xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Scheduled</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.scheduled || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl rounded-2xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">In Progress</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.in_progress || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl rounded-2xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Completed</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.completed || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl rounded-2xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Overdue</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.overdue || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Search maintenance..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" @input="debouncedSearch" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" @change="applyFilters">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select v-model="filters.maintenance_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" @change="applyFilters">
                            <option value="">All Types</option>
                            <option value="preventive">Preventive</option>
                            <option value="corrective">Corrective</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset Item</label>
                        <select v-model="filters.asset_item_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" @change="applyFilters">
                            <option value="">All Assets</option>
                            <option v-for="item in assetItems" :key="item.id" :value="item.id">
                                {{ item.asset_tag }} - {{ item.asset_name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Maintenance Table -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Maintenance Records</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="maintenance in maintenanceRecords.data" :key="maintenance.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ maintenance.assetItem?.asset_tag }}</div>
                                        <div class="text-sm text-gray-500">{{ maintenance.assetItem?.asset_name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getTypeBadgeClass(maintenance.maintenance_type)">
                                        {{ maintenance.maintenance_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeClass(maintenance.status)">
                                        {{ maintenance.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(maintenance.scheduled_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ maintenance.cost || '0.00' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <Link :href="route('asset.maintenance.show', maintenance.id)" class="text-blue-600 hover:text-blue-900">View</Link>
                                        <Link :href="route('asset.maintenance.edit', maintenance.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                        <button v-if="maintenance.status === 'scheduled'" @click="startMaintenance(maintenance.id)" class="text-green-600 hover:text-green-900">Start</button>
                                        <button v-if="maintenance.status === 'in_progress'" @click="completeMaintenance(maintenance.id)" class="text-green-600 hover:text-green-900">Complete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="maintenanceRecords.links && maintenanceRecords.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                    <Pagination :links="maintenanceRecords.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { debounce } from 'lodash'

const props = defineProps({
    maintenanceRecords: Object,
    filters: Object,
    assetItems: Array,
    stats: Object,
})

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    maintenance_type: props.filters?.maintenance_type || '',
    asset_item_id: props.filters?.asset_item_id || '',
})

const debouncedSearch = debounce(() => {
    applyFilters()
}, 300)

const applyFilters = () => {
    router.get(route('asset.maintenance.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    filters.value = {
        search: '',
        status: '',
        maintenance_type: '',
        asset_item_id: '',
    }
    applyFilters()
}

const startMaintenance = (id) => {
    if (confirm('Start this maintenance task?')) {
        router.put(route('asset.maintenance.start', id))
    }
}

const completeMaintenance = (id) => {
    if (confirm('Mark this maintenance as completed?')) {
        router.put(route('asset.maintenance.complete', id))
    }
}

const getTypeBadgeClass = (type) => {
    const classes = {
        preventive: 'bg-blue-100 text-blue-800',
        corrective: 'bg-yellow-100 text-yellow-800',
        emergency: 'bg-red-100 text-red-800',
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const getStatusBadgeClass = (status) => {
    const classes = {
        scheduled: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>
