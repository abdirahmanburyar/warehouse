<template>
    <AuthenticatedLayout title="Asset Documents" description="Manage documents and files related to assets">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="bg-white shadow-xl rounded-2xl">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-10 h-10 text-white">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Asset Documents</h1>
                                <p class="text-blue-100 text-sm mt-1">
                                    Manage documents and files related to assets
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 sm:mt-0">
                            <Link :href="route('asset.documents.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-blue-700 bg-white hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-4 h-4 mr-2">
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                                </svg>
                                Upload Document
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by filename or description..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Asset -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset</label>
                        <select
                            v-model="filters.asset_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Assets</option>
                            <option v-for="asset in assets" :key="asset.id" :value="asset.id">
                                {{ asset.asset_number }}
                            </option>
                        </select>
                    </div>

                    <!-- Document Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                        <select
                            v-model="filters.document_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option value="invoice">Invoice</option>
                            <option value="warranty">Warranty</option>
                            <option value="manual">Manual</option>
                            <option value="receipt">Receipt</option>
                            <option value="contract">Contract</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Documents Grid -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Documents</h3>
                </div>
                
                <div class="p-6">
                    <div v-if="documents.data.length === 0" class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by uploading a new document.</p>
                        <div class="mt-6">
                            <Link :href="route('asset.documents.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Upload Document
                            </Link>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="document in documents.data" :key="document.id" 
                            class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            
                            <!-- Document Preview -->
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <svg v-if="document.isImage" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <svg v-else-if="document.isPDF" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ document.document_type || 'Document' }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ document.getFormattedFileSize }}</div>
                                </div>

                                <h4 class="font-medium text-gray-900 mb-2 truncate">{{ document.file_name }}</h4>
                                
                                <div v-if="document.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    {{ document.description }}
                                </div>

                                <div class="text-xs text-gray-500 mb-3">
                                    Asset: {{ document.asset?.asset_number || 'N/A' }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    Uploaded: {{ formatDate(document.created_at) }}
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex space-x-2">
                                        <Link :href="route('asset.documents.show', document.id)"
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                            View
                                        </Link>
                                        <Link :href="route('asset.documents.edit', document.id)"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Edit
                                        </Link>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a :href="route('asset.documents.download', document.id)"
                                            class="text-green-600 hover:text-green-900 text-sm font-medium">
                                            Download
                                        </a>
                                        <button
                                            @click="deleteDocument(document.id)"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="documents.links && documents.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                    <Pagination :links="documents.links" />
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
    documents: Object,
    filters: Object,
    assets: Array,
})

const filters = ref({
    search: props.filters?.search || '',
    asset_id: props.filters?.asset_id || '',
    document_type: props.filters?.document_type || '',
})

const debouncedSearch = debounce(() => {
    applyFilters()
}, 300)

const applyFilters = () => {
    router.get(route('asset.documents.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    filters.value = {
        search: '',
        asset_id: '',
        document_type: '',
    }
    applyFilters()
}

const deleteDocument = (id) => {
    if (confirm('Are you sure you want to delete this document?')) {
        router.delete(route('asset.documents.destroy', id))
    }
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
