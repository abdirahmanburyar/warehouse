<template>
    <AuthenticatedLayout title="Assets" description="Manage your assets" img="/assets/images/asset.png">
        <div class="py-4">
            <!-- Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div v-for="(label, value) in statuses" :key="value"
                    class="bg-white rounded-lg shadow p-4 border-l-4"
                    :class="{
                        'border-blue-500': value === 'in_use',
                        'border-yellow-500': value === 'maintenance',
                        'border-gray-500': value === 'retired',
                        'border-red-500': value === 'damaged'
                    }">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ label }}</p>
                            <p class="mt-1 text-2xl font-semibold"
                                :class="{
                                    'text-blue-600': value === 'in_use',
                                    'text-yellow-600': value === 'maintenance',
                                    'text-gray-600': value === 'retired',
                                    'text-red-600': value === 'damaged'
                                }">
                                {{ getStatusCount(value) }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full"
                            :class="{
                                'bg-blue-100': value === 'in_use',
                                'bg-yellow-100': value === 'maintenance',
                                'bg-gray-100': value === 'retired',
                                'bg-red-100': value === 'damaged'
                            }">
                            <svg v-if="value === 'in_use'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                :class="{
                                    'text-blue-600': value === 'in_use',
                                    'text-yellow-600': value === 'maintenance',
                                    'text-gray-600': value === 'retired',
                                    'text-red-600': value === 'damaged'
                                }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <svg v-if="value === 'maintenance'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                :class="{
                                    'text-blue-600': value === 'in_use',
                                    'text-yellow-600': value === 'maintenance',
                                    'text-gray-600': value === 'retired',
                                    'text-red-600': value === 'damaged'
                                }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg v-if="value === 'retired'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                :class="{
                                    'text-blue-600': value === 'in_use',
                                    'text-yellow-600': value === 'maintenance',
                                    'text-gray-600': value === 'retired',
                                    'text-red-600': value === 'damaged'
                                }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            <svg v-if="value === 'damaged'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                :class="{
                                    'text-blue-600': value === 'in_use',
                                    'text-yellow-600': value === 'maintenance',
                                    'text-gray-600': value === 'retired',
                                    'text-red-600': value === 'damaged'
                                }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Bar with Search and Add Button -->
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <input type="text" v-model="search" placeholder="Search assets..."
                        class="rounded-md w-[200px] border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button @click="openModal"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Add Asset
                </button>
            </div>

            <!-- Assets Table -->
            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 px-6">Asset Name</th>
                            <th scope="col" class="py-3 px-6">Serial Number</th>
                            <th scope="col" class="py-3 px-6">Category</th>
                            <th scope="col" class="py-3 px-6">Location</th>
                            <th scope="col" class="py-3 px-6">Custody</th>
                            <th scope="col" class="py-3 px-6">Quantity</th>
                            <th scope="col" class="py-3 px-6">Transfer Date</th>
                            <th scope="col" class="py-3 px-6">Purchase</th>
                            <th scope="col" class="py-3 px-6">Status</th>
                            <th scope="col" class="py-3 px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!props.assets.data || props.assets.data.length === 0" class="bg-white">
                            <td colspan="10" class="py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span class="text-lg font-medium">No assets found</span>
                                    <p class="mt-1 text-sm">Get started by adding your first asset.</p>
                                    <button @click="openModal"
                                        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        Add Asset
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="asset in props.assets.data" :key="asset.id"
                            class="bg-white border-b hover:bg-gray-50">
                            <td class="py-4 px-6">{{ asset.name }}</td>
                            <td class="py-4 px-6">{{ asset.serial_number }}</td>
                            <td class="py-4 px-6">{{ asset.category }}</td>
                            <td class="py-4 px-6">{{ asset.location }}</td>
                            <td class="py-4 px-6">{{ asset.custody }}</td>
                            <td class="py-4 px-6">{{ asset.quantity }}</td>
                            <td class="py-4 px-6">{{ asset.transfer_date }}</td>
                            <td class="py-4 px-6 flex flex-col">
                                <span>Date: {{ asset.purchase_date }}</span>
                                <span>Cost: {{ asset.purchase_cost }}</span>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                <span :class="{
                                    'inline-flex rounded-full px-2 text-xs font-semibold leading-5': true,
                                    'bg-green-100 text-green-800': asset.status === 'active',
                                    'bg-blue-100 text-blue-800': asset.status === 'in_use',
                                    'bg-yellow-100 text-yellow-800': asset.status === 'maintenance',
                                    'bg-gray-100 text-gray-800': asset.status === 'retired',
                                    'bg-red-100 text-red-800': asset.status === 'damaged'
                                }">
                                    {{ formatStatus(asset.status) }}
                                </span>
                                <button @click="openStatusModal(asset)"
                                    class="ml-2 text-indigo-600 hover:text-indigo-900">
                                    <span class="sr-only">Change Status</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </td>
                            <td class="py-4 px-6">
                                <button @click="editAsset(asset)"
                                    class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                <button @click="viewHistory(asset)"
                                    class="text-gray-600 hover:text-gray-900 mr-2">History</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="props.assets.data.length > 0" class="px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link v-if="props.assets.meta.prev_page_url" :href="props.assets.meta.prev_page_url || '#'" 
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </Link>
                        <Link v-if="props.assets.meta.next_page_url" :href="props.assets.meta.next_page_url || '#'"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ props.assets.meta.from || 0 }}</span>
                                to
                                <span class="font-medium">{{ props.assets.meta.to || 0 }}</span>
                                of
                                <span class="font-medium">{{ props.assets.meta.total || 0 }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <template v-for="(link, i) in props.assets.meta.links" :key="i">
                                    <Link v-if="link.url" 
                                        :href="link.url || '#'"
                                        v-html="link.label"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            {
                                                'bg-indigo-50 border-indigo-500 text-indigo-600 z-10': link.active,
                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                                                'rounded-l-md': i === 0,
                                                'rounded-r-md': i === props.assets.meta.links.length - 1
                                            }
                                        ]"
                                    ></Link>
                                    <span v-else
                                        v-html="link.label"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed',
                                            {
                                                'rounded-l-md': i === 0,
                                                'rounded-r-md': i === props.assets.meta.links.length - 1
                                            }
                                        ]"
                                    ></span>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Modal -->
            <Modal :show="showModal" @close="closeModal">
                <div class="p-6 max-w-4xl mx-auto">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        {{ isEditing ? 'Edit Asset' : 'Add New Asset' }}
                    </h2>

                    <form @submit.prevent="submitForm" class="mt-6 space-y-4">
                        <div class="space-y-4">
                            <!-- Basic Info -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Asset Name</label>
                                <input type="text" v-model="form.name" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter asset name">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Custodian</label>
                                <input type="text" v-model="form.custody" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter custodian name">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                    <input type="number" v-model="form.quantity" required min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Enter quantity">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                                    <div class="relative">
                                        <input type="text" v-model="form.serial_number" required readonly
                                            class="mt-1 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :placeholder="getNextSerialNumber()">
                                        <span
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">
                                            Auto-generated
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <button type="button" @click="isCustomCategory"
                                        class="text-xs text-indigo-600 hover:text-indigo-800">
                                        {{ isCustomCategory ? 'Select from list' : 'Add category' }}
                                    </button>
                                </div>
                                <div class="relative mt-1 category-dropdown">
                                    <label for="category">Category</label>
                                    <div v-if="isSwitched">
                                        <input type="text" v-model="form.category" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Enter new category">
                                    </div>
                                    <div v-else>
                                        <select v-model="form.category" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option disabled value="">Select a category</option>
                                            <option v-for="category in props.categories" :value="category">
                                                {{ category }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                                    <input type="date" v-model="form.purchase_date" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Purchase Cost</label>
                                    <input type="number" v-model="form.purchase_cost"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Enter purchase cost">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Transfer Date</label>
                                    <input type="date" v-model="form.transfer_date" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Location</label>
                                    <input type="text" v-model="form.location" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Enter asset location">
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea v-model="form.notes" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Add any additional notes about the asset..."></textarea>
                            </div>

                            <div class="mt-4">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" v-model="form.status"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="closeModal" :disabled="isSubmitting"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmitting"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                {{ isSubmitting ? (isEditing ? 'Updating...' : 'Saving...') : (isEditing ? 'Update' :
                                'Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Status Change Modal -->
            <Modal :show="showStatusModal" @close="closeStatusModal">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Change Status</h2>
                            <p class="mt-1 text-sm text-gray-600" v-if="selectedAsset">
                                {{ selectedAsset.name }} ({{ selectedAsset.serial_number }})
                            </p>
                        </div>
                        <button @click="closeStatusModal" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" v-model="statusForm.status"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="notes" v-model="statusForm.notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Add any notes about this status change"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeStatusModal" :disabled="isSaving"
                            class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Cancel
                        </button>
                        <button type="button" @click="updateStatus" :disabled="isSaving"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ isSaving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </Modal>

            <!-- History Modal -->
            <Modal :show="showHistoryModal" @close="closeHistoryModal">
                <div class=" max-w-4xl mx-auto">
                    <div class="flex justify-between items-start mb-6 p-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Asset History</h2>
                            <p class="mt-1 text-sm text-gray-600" v-if="selectedAsset">
                                {{ selectedAsset.name }} ({{ selectedAsset.serial_number }})
                            </p>
                        </div>
                        <button @click="closeHistoryModal"
                            class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flow-root">
                        <ul role="list" class="mb-8">
                            <li v-for="(record, recordIdx) in selectedAsset?.custody_histories" :key="record.id">
                                <div class="relative pb-8">
                                    <span v-if="recordIdx !== selectedAsset.custody_histories.length - 1"
                                        class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <!-- Icon -->
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white bg-blue-500">
                                                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm-.75-6.75a.75.75 0 011.5 0v2.25h2.25a.75.75 0 010 1.5h-3a.75.75 0 01-.75-.75v-3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900">
                                                        Custodian: {{ record.custodian }}
                                                    </span>
                                                </div>
                                                <div class="mt-1 text-sm text-gray-500" v-if="record.status">
                                                    Status: <span :class="{
                                                        'text-green-600': record.status === 'active',
                                                        'text-blue-600': record.status === 'in_use',
                                                        'text-yellow-600': record.status === 'maintenance',
                                                        'text-gray-600': record.status === 'retired',
                                                        'text-red-600': record.status === 'damaged'
                                                    }">{{ formatStatus(record.status) }}</span>
                                                </div>
                                                <div class="mt-1 text-sm text-gray-500" v-if="record.status_notes">
                                                    {{ record.status_notes }}
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    By {{ record.assigned_by?.name || 'System' }} •
                                                    {{ formatDate(record.assigned_at) }}
                                                </p>
                                            </div>
                                            <!-- Notes -->
                                            <div v-if="record.assignment_notes || record.return_notes" class="mt-2 text-sm text-gray-700">
                                                <p v-if="record.assignment_notes">
                                                    <span class="font-medium">Assignment Notes:</span> {{ record.assignment_notes }}
                                                </p>
                                                <p v-if="record.return_notes">
                                                    <span class="font-medium">Return Notes:</span> {{ record.return_notes }}
                                                </p>
                                            </div>
                                            <div v-if="record.returned_at" class="mt-2">
                                                <p class="text-sm text-red-600">
                                                    Returned on {{ formatDate(record.returned_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import { debounce } from 'lodash';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

const toast = useToast();
const props = defineProps({
    assets: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            links: [],
            from: 0,
            to: 0,
            total: 0
        })
    },
    filters: Object,
    categories: Array,
    lastSerialNumber: String
});

const showModal = ref(false);
const isEditing = ref(false);
const search = ref(props.filters.search);
const showCategoryDropdown = ref(false);
const filteredCategories = ref([]);
const isSubmitting = ref(false);
const isSwitched = ref(false);
const isSaving = ref(false);

function isCustomCategory(){
    isSwitched.value = !isSwitched.value;
}

const form = ref({
    name: '',
    serial_number: '',
    custody: '',
    category: '',
    quantity: 1,
    purchase_date: '',
    purchase_cost: '',
    transfer_date: '',
    location: '',
    notes: '',
    status: ''
});

const statusForm = ref({
    status: '',
    notes: ''
});

watch([
    () => search.value
], () => {
    reloadAsset();
});

function reloadAsset() {
    const query = {}
    if (search.value) query.search = search.value
    router.get(route('assets.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: [
            'assets',
            'categories',
            'filters'
        ]
    })
}

// Close category dropdown when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.category-dropdown')) {
        showCategoryDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    filteredCategories.value = props.categories || [];
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const filterCategories = () => {
    if (!form.value.category) {
        filteredCategories.value = props.categories;
        return;
    }

    filteredCategories.value = props.categories.filter(category =>
        category.toLowerCase().includes(form.value.category.toLowerCase())
    );
};

const selectCategory = (category) => {
    form.value.category = category;
    showCategoryDropdown.value = false;
};

const openModal = () => {
    resetForm();
    isEditing.value = false;
    showModal.value = true;
    isCustomCategory.value = false;
    if (!isEditing.value) {
        form.value.serial_number = getNextSerialNumber();
    }
};

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

const resetForm = () => {
    form.value = {
        name: '',
        serial_number: '',
        custody: '',
        category: '',
        quantity: 1,
        purchase_date: '',
        purchase_cost: '',
        transfer_date: '',
        location: '',
        notes: '',
        status: ''
    };
    isCustomCategory.value = false;
    showCategoryDropdown.value = false;
    if (!isEditing.value) {
        form.value.serial_number = getNextSerialNumber();
    }
};

const editAsset = (asset) => {
    isEditing.value = true;
    form.value = {
        id: asset.id,
        name: asset.name,
        serial_number: asset.serial_number,
        custody: asset.current_custodian || '',
        category: asset.category,
        custody: asset.custody,
        quantity: asset.quantity,
        purchase_date: asset.purchase_date,
        purchase_cost: asset.purchase_cost,
        transfer_date: asset.transfer_date,
        location: asset.location,
        notes: asset.notes,
        status: asset.status
    };
    showModal.value = true;
};

const submitForm = async () => {
    isSubmitting.value = true;

    await axios.post(route('assets.store'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            toast.success(response.data);
            closeModal();
            reloadAsset();
        })
        .catch((error) => {
            console.log(error.response.data);
            isSubmitting.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response.data,
            });
        });
};

const viewHistory = (asset) => {
    selectedAsset.value = asset;
    showHistoryModal.value = true;
};

const showHistoryModal = ref(false);
const selectedAsset = ref(null);

const closeHistoryModal = () => {
    showHistoryModal.value = false;
    selectedAsset.value = null;
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getNextSerialNumber = () => {
    if (!props.lastSerialNumber) return '000001';

    const currentNumber = parseInt(props.lastSerialNumber);
    const nextNumber = currentNumber + 1;
    return nextNumber.toString().padStart(6, '0');
};

const formatStatus = (status) => {
    const statusMap = {
        'active': 'Active',
        'in_use': 'In Use',
        'maintenance': 'Maintenance',
        'retired': 'Retired',
        'damaged': 'Damaged'
    };
    return statusMap[status] || 'N/A';
};

const statuses = computed(() => {
    return {
        in_use: 'In Use',
        maintenance: 'Maintenance',
        retired: 'Retired',
        damaged: 'Damaged'
    };
});

const openStatusModal = (asset) => {
    selectedAsset.value = asset;
    statusForm.value.status = asset.status || 'active';
    statusForm.value.notes = '';
    showStatusModal.value = true;
};

const closeStatusModal = () => {
    showStatusModal.value = false;
    statusForm.value = {
        status: '',
        notes: ''
    };
};

const updateStatus = async () => {
    if (!selectedAsset.value) return;
    isSaving.value = true;
    
    await axios.post(route('assets.update-status', selectedAsset.value.id), statusForm.value)
        .then((response) => {
            closeStatusModal();
            reloadAsset();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: response.data,
                showConfirmButton: false,
                timer: 1500
            });
            isSaving.value = false;
        })
        .catch((error) => {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data || 'Failed to update status',
            });
            isSaving.value = false;
        });
};

const showStatusModal = ref(false);

const getStatusCount = (status) => {
    return props.assets.data.filter(asset => asset.status === status).length;
}

watch(() => props.filters, (newFilters) => {
    search.value = newFilters.search;
}, { deep: true });
</script>