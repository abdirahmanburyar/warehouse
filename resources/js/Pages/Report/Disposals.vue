<template>
    <AuthenticatedLayout title="Disposals - Report" description="Inventory Verification Tool" img="/assets/images/report.png">
        <div class="px-5 mb-6">
            <div class="flex justify-between items-center mb-6">
                <Link :href="route('reports.index')">
                    <div class="flex items-center">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight ml-2">
                            Disposal Reports
                        </h2>
                    </div>
                </Link>
                <button 
                    @click="downloadPDF"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-3xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download
                </button>
            </div>

            <div class="flex justify-between items-center mb-6">
                <input type="month" v-model="month" class="border-black rounded-3xl w-[300px]">
                <div class="w-[300px]">
                    <label for="per_page" class="block text-sm font-medium text-gray-700">Per Page</label>
                    <select v-model="per_page" class="border-black rounded-3xl w-full">
                        <option value="2">2 per page</option>
                        <option value="200">200 per page</option>
                        <option value="500">500 per page</option>
                    </select>
                </div>
            </div>

            <div>
                <div v-if="disposals.data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Disposal ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product Info
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Source
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notes
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Disposal Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Documents
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="disposal in disposals.data" :key="disposal.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">#{{ disposal.disposal_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ disposal.product?.name || 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-500">Barcode: {{ disposal.barcode }}</div>
                                    <div class="text-xs text-gray-500">Batch: {{ disposal.batch_number }}</div>
                                    <div class="text-xs text-gray-500">UOM: {{ disposal.uom }}</div>
                                    <div v-if="disposal.expire_date" class="text-xs text-red-600">Expires: {{ moment(disposal.expire_date).format('DD/MM/YYYY') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ disposal.quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div v-if="disposal.inventory_id">Inventory #{{ disposal.inventory_id }}</div>
                                        <div v-else-if="disposal.packing_list_id">Packing List #{{ disposal.packing_list_id }}</div>
                                        <div v-else-if="disposal.purchase_order_id">Purchase Order #{{ disposal.purchase_order_id }}</div>
                                        <div v-else-if="disposal.transfer_id">Transfer #{{ disposal.transfer_id }}</div>
                                        <div v-else>N/A</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ disposal.note || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex flex-col gap-1">
                                        <div>{{ moment(disposal.disposed_at).format('DD/MM/YYYY') }}</div>
                                        <div class="text-xs text-gray-400">by {{ disposal.disposed_by?.name || 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-2">
                                        <span :class="getStatusClass(disposal.status)" class="font-medium">
                                            {{ disposal.status }}
                                        </span>
                                        <div v-if="disposal.reviewed_at" class="text-xs text-gray-500">
                                            Reviewed at {{ moment(disposal.reviewed_at).format('DD/MM/YYYY HH:mm') }}
                                            <div class="text-gray-400">by {{ disposal.reviewed_by?.name || 'N/A' }}</div>
                                        </div>
                                        <div v-if="disposal.approved_at" class="text-xs text-gray-500">
                                            Approved at {{ moment(disposal.approved_at).format('DD/MM/YYYY HH:mm') }}
                                            <div class="text-gray-400">by {{ disposal.approved_by?.name || 'N/A' }}</div>
                                        </div>
                                        <div v-if="disposal.rejected_at" class="text-xs text-gray-500">
                                            Rejected at {{ moment(disposal.rejected_at).format('DD/MM/YYYY HH:mm') }}
                                            <div class="text-gray-400">by {{ disposal.rejected_by?.name || 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <button 
                                        @click="showDetails(disposal)" 
                                        class="text-indigo-600 hover:text-indigo-900 focus:outline-none flex items-center gap-1"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <p class="text-lg">No disposal reports available.</p>
                </div>
                <div class="px-6 py-4 flex justify-end mt-4">
                    <TailwindPagination
                        :data="props.disposals"
                        @pagination-change-page="getResult"
                        />
                </div>
            </div>
        </div>
        <!-- Details Modal -->
        <div v-if="selectedDisposal" class="fixed inset-0 overflow-y-auto z-50" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="selectedDisposal = null"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Disposal Details #{{ selectedDisposal.disposal_id }}</h3>
                                    <button @click="selectedDisposal = null" class="text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Content -->
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="font-medium text-gray-500">Source Information</h4>
                                            <div class="mt-2">
                                                <div v-if="selectedDisposal.inventory_id">From Inventory #{{ selectedDisposal.inventory_id }}</div>
                                                <div v-else-if="selectedDisposal.packing_list_id">From Packing List #{{ selectedDisposal.packing_list_id }}</div>
                                                <div v-else-if="selectedDisposal.purchase_order_id">From Purchase Order #{{ selectedDisposal.purchase_order_id }}</div>
                                                <div v-else-if="selectedDisposal.transfer_id">From Transfer #{{ selectedDisposal.transfer_id }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-500">Product Information</h4>
                                            <div class="mt-2">
                                                <div v-if="selectedDisposal.product">
                                                    <div>Product: {{ selectedDisposal.product.name }}</div>
                                                    <div v-if="selectedDisposal.product.category">Category: {{ selectedDisposal.product.category.name }}</div>
                                                    <div v-if="selectedDisposal.product.dosage">Dosage: {{ selectedDisposal.product.dosage.name }}</div>
                                                </div>
                                                <div>Barcode: {{ selectedDisposal.barcode || 'N/A' }}</div>
                                                <div>Batch: {{ selectedDisposal.batch_number }}</div>
                                                <div>UOM: {{ selectedDisposal.uom }}</div>
                                                <div v-if="selectedDisposal.expire_date" class="text-red-600">
                                                    Expires: {{ moment(selectedDisposal.expire_date).format('DD/MM/YYYY') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Disposal Info -->
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <h4 class="font-medium text-gray-500 mb-2">Disposal Information</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-sm">Quantity: <span class="font-medium">{{ selectedDisposal.quantity }}</span></div>
                                            <div class="text-sm">Disposed At: {{ moment(selectedDisposal.disposed_at).format('DD/MM/YYYY') }}</div>
                                            <div class="text-sm">Disposed By: {{ selectedDisposal.disposed_by?.name || 'N/A' }}</div>
                                            <div class="text-sm">Note: {{ selectedDisposal.note }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm mb-2">
                                                Status: <span :class="getStatusClass(selectedDisposal.status)">{{ selectedDisposal.status }}</span>
                                            </div>
                                            <div v-if="selectedDisposal.reviewed_at" class="text-sm">
                                                Reviewed: {{ moment(selectedDisposal.reviewed_at).format('DD/MM/YYYY HH:mm') }}
                                                <div class="text-xs text-gray-500">by {{ selectedDisposal.reviewed_by?.name || 'N/A' }}</div>
                                            </div>
                                            <div v-if="selectedDisposal.approved_at" class="text-sm">
                                                Approved: {{ moment(selectedDisposal.approved_at).format('DD/MM/YYYY HH:mm') }}
                                                <div class="text-xs text-gray-500">by {{ selectedDisposal.approved_by?.name || 'N/A' }}</div>
                                            </div>
                                            <div v-if="selectedDisposal.rejected_at" class="text-sm text-red-600">
                                                Rejected: {{ moment(selectedDisposal.rejected_at).format('DD/MM/YYYY HH:mm') }}
                                                <div class="text-xs">by {{ selectedDisposal.rejected_by?.name || 'N/A' }}</div>
                                                <div class="mt-1 text-xs">Reason: {{ selectedDisposal.rejection_reason }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-500 mb-2">Status Information</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Status</span>
                                            <span :class="getStatusClass(selectedDisposal.status)">
                                                {{ selectedDisposal.status }}
                                            </span>
                                        </div>
                                        <div v-if="selectedDisposal.reviewed_at" class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Reviewed</span>
                                            <div class="text-sm text-right">
                                                <div>{{ moment(selectedDisposal.reviewed_at).format('DD/MM/YYYY HH:mm') }}</div>
                                                <div class="text-xs text-gray-500">by {{ selectedDisposal.reviewed_by?.name || 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div v-if="selectedDisposal.approved_at" class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Approved</span>
                                            <div class="text-sm text-right">
                                                <div>{{ moment(selectedDisposal.approved_at).format('DD/MM/YYYY HH:mm') }}</div>
                                                <div class="text-xs text-gray-500">by {{ selectedDisposal.approved_by?.name || 'N/A' }}</div>
                                            </div>
                                        </div>
                                        <div v-if="selectedDisposal.rejected_at" class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Rejected</span>
                                            <div class="text-sm text-right">
                                                <div>{{ moment(selectedDisposal.rejected_at).format('DD/MM/YYYY HH:mm') }}</div>
                                                <div class="text-xs text-gray-500">by {{ selectedDisposal.rejected_by?.name || 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attachments -->
                                <div v-if="selectedDisposal.attachments" class="bg-gray-50 p-4 rounded-lg mt-4">
                                    <h4 class="font-medium text-gray-500 mb-2">Attachments</h4>
                                    <div class="space-y-2">
                                        <div 
                                            v-for="(attachment, index) in JSON.parse(selectedDisposal.attachments)" 
                                            :key="index" 
                                            class="flex items-center justify-between p-2 bg-white rounded hover:bg-gray-50"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                <span class="text-sm text-gray-600">{{ attachment.name }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <a 
                                                    :href="attachment.path" 
                                                    target="_blank" 
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium flex items-center gap-1"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                    Open
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <TransitionRoot appear :show="isDetailsOpen" as="template">
            <Dialog as="div" @close="closeDetails" class="relative z-10">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black bg-opacity-25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                    Disposal Details
                                </DialogTitle>

                                <div v-if="selectedDisposal" class="mt-4">
                                    <!-- Product Info -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Product Information</h4>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="text-lg font-medium text-gray-900 mb-2">{{ selectedDisposal.product.name }}</div>
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="text-gray-500">Barcode:</span>
                                                    <span class="text-gray-900 ml-2">{{ selectedDisposal.product.barcode }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">UOM:</span>
                                                    <span class="text-gray-900 ml-2">{{ selectedDisposal.product.uom }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Batch Number:</span>
                                                    <span class="text-gray-900 ml-2">{{ selectedDisposal.batch_number || 'N/A' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Expiry Date:</span>
                                                    <span class="text-gray-900 ml-2">{{ selectedDisposal.expiry_date ? formatDate(selectedDisposal.expiry_date) : 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Disposal Info -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Disposal Information</h4>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="text-gray-500">Disposal ID:</span>
                                                    <span class="text-gray-900 ml-2">{{ selectedDisposal.disposal_id }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Quantity:</span>
                                                    <span class="text-gray-900 ml-2">{{ selectedDisposal.quantity }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Source:</span>
                                                    <span class="text-gray-900 ml-2">{{ selectedDisposal.source_type }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Status:</span>
                                                    <span :class="getStatusClass(selectedDisposal.status)" class="ml-2">{{ selectedDisposal.status.toUpperCase() }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <span class="text-gray-500">Notes:</span>
                                                <p class="text-gray-900 mt-1">{{ selectedDisposal.notes || 'No notes provided' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Timeline -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Timeline</h4>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="space-y-4">
                                                <div>
                                                    <div class="text-sm text-gray-900">Created</div>
                                                    <div class="text-xs text-gray-500">{{ formatDateTime(selectedDisposal.created_at) }}</div>
                                                    <div class="text-xs text-gray-400">by {{ selectedDisposal.disposed_by?.name || 'N/A' }}</div>
                                                </div>
                                                <div v-if="selectedDisposal.reviewed_at">
                                                    <div class="text-sm text-gray-900">Reviewed</div>
                                                    <div class="text-xs text-gray-500">{{ formatDateTime(selectedDisposal.reviewed_at) }}</div>
                                                    <div class="text-xs text-gray-400">by {{ selectedDisposal.reviewed_by?.name || 'N/A' }}</div>
                                                </div>
                                                <div v-if="selectedDisposal.approved_at">
                                                    <div class="text-sm text-gray-900">Approved</div>
                                                    <div class="text-xs text-gray-500">{{ formatDateTime(selectedDisposal.approved_at) }}</div>
                                                    <div class="text-xs text-gray-400">by {{ selectedDisposal.approved_by?.name || 'N/A' }}</div>
                                                </div>
                                                <div v-if="selectedDisposal.rejected_at">
                                                    <div class="text-sm text-gray-900">Rejected</div>
                                                    <div class="text-xs text-gray-500">{{ formatDateTime(selectedDisposal.rejected_at) }}</div>
                                                    <div class="text-xs text-gray-400">by {{ selectedDisposal.rejected_by?.name || 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Attachments -->
                                    <div v-if="selectedDisposal.attachments?.length > 0">
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Attachments</h4>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="space-y-2">
                                                <div v-for="attachment in selectedDisposal.attachments" :key="attachment.id" class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                        </svg>
                                                        <span class="ml-2 text-sm text-gray-900">{{ attachment.name }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <a :href="attachment.url" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">Open</a>
                                                        <a :href="attachment.url" download class="text-sm text-indigo-600 hover:text-indigo-900">Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button
                                        type="button"
                                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-900 hover:bg-indigo-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                                        @click="closeDetails"
                                    >
                                        Close
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import moment from 'moment';
import html2pdf from 'html2pdf.js';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    disposals: Object,
    filters: Object
});

const month = ref(props.filters.month);
const per_page = ref(props.filters.per_page);

watch([
    () => month.value,
    () => per_page.value,
    () => props.filters.page,
], () => {
    reloadPage();
});

function getResult(page = 1) {
    props.filters.page = page;
}

function reloadPage() {
    const query = {};
    if (month.value) query.month = month.value;
    if (per_page.value) {
        props.filters.page = 1;
        query.per_page = per_page.value;
    }
    if (props.filters.page) query.page = props.filters.page;
    router.get(route('reports.disposals'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['disposals']
    });
}

const getStatusClass = (status) => {
    const baseClasses = 'px-2 py-1 text-xs font-medium rounded-full';
    const statusClasses = {
        pending: `${baseClasses} bg-yellow-100 text-yellow-800`,
        reviewed: `${baseClasses} bg-blue-100 text-blue-800`,
        approved: `${baseClasses} bg-green-100 text-green-800`,
        rejected: `${baseClasses} bg-red-100 text-red-800`
    };
    return statusClasses[status] || `${baseClasses} bg-gray-100 text-gray-800`;
}


const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
};

const formatDateTime = (date) => {
    return moment(date).format('DD/MM/YYYY HH:mm');
};

const selectedDisposal = ref(null);
const isDetailsOpen = ref(false);

const showDetails = (disposal) => {
    selectedDisposal.value = disposal;
    isDetailsOpen.value = true;
};

const closeDetails = () => {
    isDetailsOpen.value = false;
    selectedDisposal.value = null;
};

const downloadPDF = async () => {
    const disposals = props.disposals.data;
    if (!disposals || disposals.length === 0) {
        alert('No data available to export');
        return;
    }
    
    // Format the month for filename
    const monthDate = moment(month.value).format('MMMM-YYYY');

    // Create a clean version of the report for PDF
    const pdfContent = document.createElement('div');
    pdfContent.innerHTML = `
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="font-size: 24px; color: #1f2937; margin-bottom: 10px;">Disposals Report - Warehouse</h1>
            </div>

            <!-- Items Table -->
            <div style="margin-top: 20px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #f3f4f6;">
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Disposal ID</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Item</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Product Info</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Quantity</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Source</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Notes</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Disposal Date</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${disposals.map(disposal => {
                            const product = disposal.product || {};
                            return `
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px 8px;">${disposal.disposal_id || ''}</td>
                                <td style="padding: 12px 8px;">
                                    <div style="font-weight: 500;">${product.name || 'N/A'}</div>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <div>Barcode: ${disposal.barcode || 'N/A'}</div>
                                    <div>Batch: ${disposal.batch_number || 'N/A'}</div>
                                    <div>UOM: ${disposal.uom || 'N/A'}</div>
                                    <div>Expiry: ${disposal.expiry_date ? formatDate(disposal.expiry_date) : 'N/A'}</div>
                                </td>
                                <td style="padding: 12px 8px; text-align: right;">${disposal.quantity || ''}</td>
                                <td style="padding: 12px 8px;">${disposal.source_type || ''}</td>
                                <td style="padding: 12px 8px;">${disposal.note || 'N/A'}</td>
                                <td style="padding: 12px 8px;">
                                    ${disposal.created_at ? formatDateTime(disposal.created_at) : ''}<br>
                                    by ${disposal.disposed_by?.name || 'N/A'}
                                </td>
                                <td style="padding: 12px 8px;">
                                    <div style="padding: 4px 8px; border-radius: 4px; display: inline-block; 
                                        ${disposal.status === 'approved' ? 'background-color: #dcfce7; color: #166534;' : 
                                        disposal.status === 'rejected' ? 'background-color: #fee2e2; color: #991b1b;' : 
                                        'background-color: #fef3c7; color: #92400e;'}">
                                        ${(disposal.status || '').toUpperCase()}
                                    </div>
                                    ${disposal.approved_by ? `
                                        <div style="margin-top: 4px; font-size: 11px;">
                                            Approved: ${formatDateTime(disposal.approved_at)}<br>
                                            by ${disposal.approved_by.name}
                                        </div>
                                    ` : ''}
                                    ${disposal.reviewed_by ? `
                                        <div style="margin-top: 4px; font-size: 11px;">
                                            Reviewed: ${formatDateTime(disposal.reviewed_at)}<br>
                                            by ${disposal.reviewed_by.name}
                                        </div>
                                    ` : ''}
                                    ${disposal.rejected_by ? `
                                        <div style="margin-top: 4px; font-size: 11px;">
                                            Rejected: ${formatDateTime(disposal.rejected_at)}<br>
                                            by ${disposal.rejected_by.name}
                                        </div>
                                    ` : ''}
                                </td>
                            </tr>
                        `}).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    // Configure PDF options
    const options = {
        margin: [15, 15],
        filename: `disposals-report-${monthDate}`,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            logging: false
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'landscape',
            compress: true
        },
        pagebreak: { mode: 'avoid-all' }
    };

    try {
        // Generate PDF
        await html2pdf().set(options).from(pdfContent).save();
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('There was an error generating the PDF. Please try again.');
    }
};
</script>