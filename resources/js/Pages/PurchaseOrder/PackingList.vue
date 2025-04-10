<template>

    <Head :title="props.purchase_order.po_number" />
    <AuthenticatedLayout :auth="$page.props.auth" :errors="$page.props.errors">
        <div class="">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Packing List</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Purchase Order #{{ props.purchase_order.po_number }}
                </p>
            </div>

            <!-- Remove debug output -->

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.name"
                        @click="currentTab = tab.name"
                        :class="[
                            currentTab === tab.name
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div v-if="currentTab === 'items'">
                <!-- Items Tab -->
                <div class="space-y-4">
                    <!-- Action Buttons -->
                    <div class="flex justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <!-- Packing List Dropdown -->
                            <div class="relative">
                                <div>
                                    <button type="button" @click="isOpen = !isOpen"
                                        class="inline-flex items-center justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        <i class="fas fa-file-alt -ml-0.5 h-5 w-5" aria-hidden="true"></i>
                                        {{ selectedPackingList ? 'Packing List #' + selectedPackingList.packing_list_number :
                                        'Select Packing List' }}
                                        <i class="fas fa-chevron-down -mr-1 h-5 w-5" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div v-if="isOpen"
                                    class="absolute left-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <!-- Search Box -->
                                    <div class="sticky top-0 bg-white p-2 border-b">
                                        <input type="text" v-model="searchQuery" placeholder="Search packing lists..."
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                    </div>

                                    <!-- Create New Button -->
                                    <div class="py-1 sticky top-[50px] bg-white z-10 border-b">
                                        <button @click="createPackingList(); isOpen = false"
                                            class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                            <i class="fas fa-plus mr-3"></i>
                                            Generate New Packing List
                                        </button>
                                    </div>

                                    <!-- Packing List Items -->
                                    <div v-if="props.purchase_order.packing_lists.length > 0"
                                        class="py-1 max-h-60 overflow-y-auto">
                                        <button v-for="packingList in props.purchase_order.packing_lists" :key="packingList.id"
                                            @click="selectPackingList(packingList); isOpen = false"
                                            class="flex w-full flex-col px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                            <div class="flex items-center w-full">
                                                <i class="fas fa-file-alt mr-3 text-gray-400"></i>
                                                <div class="flex-1">
                                                    <div class="font-medium">Packing List #{{ packingList.packing_list_number }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        <div class="flex items-center space-x-4">
                                                            <span><i class="fas fa-calendar-alt mr-1"></i> {{
                                                                formatDate(packingList.packing_date) }}</span>
                                                            <span v-if="packingList.warehouse_name"><i
                                                                    class="fas fa-warehouse mr-1"></i> {{
                                                                packingList.warehouse_name }}</span>
                                                            <span v-if="packingList.location"><i
                                                                    class="fas fa-map-marker-alt mr-1"></i> {{
                                                                packingList.location }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span
                                                    :class="['ml-2 shrink-0 px-2 py-0.5 text-xs rounded-full',
                                                        packingList.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800']">
                                                    {{ packingList.status }}
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                    <div v-else class="px-4 py-2 text-sm text-gray-500">
                                        No packing lists found
                                    </div>
                                </div>
                            </div>

                            <!-- Other Action Buttons -->
                            <button type="button" @click="showImportModal = true"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-5 h-5 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5a2.25 2.25 0 002.25 2.25H15" />
                                </svg>
                                Import PO Items
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="mt-4">
                    <!-- Table wrapper with fixed header -->
                    <div>
                        <form @submit.prevent="savePackingList"  >
                            <div  class="relative shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg max-h-[calc(100vh-200px)] overflow-auto">
                                <div v-if="!selectedPackingList" class="absolute inset-0 bg-black bg-opacity-10 flex items-center justify-center z-20 pointer-events-none">
                                    <div class="bg-white px-4 py-2 rounded-md shadow-lg text-gray-700 font-medium">
                                        Please select a packing list first
                                    </div>
                                </div>
                                <table class="min-w-full divide-y divide-gray-300 text-xs">
                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Item</th>
                                            <th class="px-2 text-left text-xs font-semibold text-gray-900">Quantity</th>
                                            <th class="px-2 text-left text-xs font-semibold text-gray-900">Received</th>
                                            <th class="px-2 text-left text-xs font-semibold text-gray-900">Damaged</th>
                                            <th class="px-2 text-left text-xs font-semibold text-gray-900">Warehouse</th>
                                            <th class="px-2 text-left text-xs font-semibold text-gray-900">Location</th>
                                            <th class="px-2 text-left text-xs font-semibold text-gray-900">Batch #</th>
                                            <th class="px-2 text-left text-xs font-semibold text-gray-900">Expiry Date</th>
                                            <th class="px-2 text-right text-xs font-semibold text-gray-900">Unit Cost</th>
                                            <th class="px-2 pr-6 text-right text-xs font-semibold text-gray-900">Total Cost</th>
                                            <th class="px-2 pr-6 text-right text-xs font-semibold text-gray-900">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-if="!form.items?.length" class="text-center">
                                            <td colspan="10" class="py-4 pl-4 pr-3 text-sm font-medium text-gray-500">
                                                No items available
                                            </td>
                                        </tr>
                                        <tr v-for="item in form.items" 
                                            :key="item.id"
                                            class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-xs font-medium text-gray-900">
                                                {{ item.product_name }}
                                                <p v-if="item.generic_name" class="text-xs text-gray-500 mt-0.5">{{ item.generic_name }}</p>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-xs text-gray-500">
                                                {{ Number(item.quantity).toLocaleString() }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <div class="flex items-center space-x-2">
                                                    <input 
                                                        type="number"
                                                        v-model="item.received_quantity"
                                                        :max="item.quantity"
                                                        min="0"
                                                        :disabled="!selectedPackingList"
                                                        :class="['block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6', 
                                                            !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']"
                                                        placeholder="received qty"
                                                        @input="validateReceivedQuantity(item)"
                                                    />
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <div class="flex items-center space-x-2">
                                                    <input 
                                                        type="number"
                                                        v-model="item.damage_quantity"
                                                        :max="item.received_quantity"
                                                        min="0"
                                                        :disabled="!selectedPackingList"
                                                        :class="['block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                            !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']"
                                                        placeholder="damaged qty"
                                                        @input="validateDamageQuantity(item)"
                                                    />
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-xs text-gray-500 min-w-[300px]">
                                                <select 
                                                    v-model="item.warehouse_id"
                                                    :disabled="!selectedPackingList"
                                                    :class="['w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 text-ellipsis',
                                                        !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']"                                                   
                                                >
                                                    <option value="" selected>Select Warehouse</option>
                                                    <option 
                                                        v-for="warehouse in props.warehouses" 
                                                        :key="warehouse.id" 
                                                        :value="warehouse.id"
                                                        class="truncate"
                                                    >
                                                        {{ warehouse.name }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 min-w-[300px]">
                                                <input 
                                                    type="text"
                                                    v-model="item.location"
                                                    :disabled="!selectedPackingList"
                                                    :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                        !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']"
                                                    placeholder="location"
                                                />
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 min-w-[200px]">
                                                <input 
                                                    type="text"
                                                    v-model="item.batch_number"
                                                    :disabled="!selectedPackingList"
                                                    :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                        !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']"
                                                    placeholder="batch number"
                                                />
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <input 
                                                    type="date"
                                                    v-model="item.expiry_date"
                                                    :disabled="!selectedPackingList"
                                                    :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                        !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']"
                                                    placeholder="expiry date"
                                                />
                                            </td>
                                            <td class="px-3 py-4 text-xs text-gray-500 text-right">
                                                {{ Number(item.unit_cost).toLocaleString('en-US', { style: 'currency', currency: 'USD' }) }}
                                            </td>
                                            <td class="px-3 pr-6 py-4 text-xs font-medium text-gray-900 text-right">
                                                {{ Number(calculateItemTotal(item)).toLocaleString('en-US', { style: 'currency', currency: 'USD' }) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-end p-3 mb-[50px]">
                                <button
                                    v-if="selectedPackingList && props.packingLists.find(pl => pl.id === selectedPackingList)?.items?.every(item => item.warehouse_id && item.location)"
                                    @click="exportToExcel"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-2"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2.25 0 01-2-2V5a2 2.25 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2.25 0 01-2 2z" />
                                    </svg>
                                    Export to Excel
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="w-5 h-5 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 17.25v3.75a.75.75 0 01-1.5 0v-3.75m-7.5-7.5h-1.5a.75.75 0 00-1.5 0v7.5a.75.75 0 001.5 0v-7.5m7.5 0v3.75a.75.75 0 001.5 0v-3.75m-7.5 0h1.5a.75.75 0 001.5 0h-1.5z" />
                                    </svg>
                                    {{ isSubmitting ? 'Processing...' : 'Submit' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Import Modal -->
                <Modal :show="showImportModal" @close="showImportModal = false">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900">
                            Import Items from Excel
                        </h2>

                        <div class="mt-4">
                            <div class="flex items-center justify-center w-full">
                                <label
                                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5a2.25 2.25 0 002.25 2.25H15" />
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500">
                                            <span class="font-semibold">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500">XLSX files only</p>
                                    </div>
                                    <input type="file" class="hidden" accept=".xlsx" @change="handleFileUpload"
                                        ref="fileInput" />
                                </label>
                            </div>

                            <!-- Processing State -->
                            <div v-if="importing" class="mt-4">
                                <div class="flex items-center justify-center">
                                    <svg class="animate-spin h-5 w-5 text-indigo-500 mr-2" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    <span>Importing items...</span>
                                </div>
                            </div>

                            <!-- Download Template Button -->
                            <div class="mt-4 text-center">
                                <button type="button" class="text-sm text-indigo-600 hover:text-indigo-900"
                                    @click="downloadTemplate">
                                    Download Template
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                @click="showImportModal = false">
                                Cancel
                            </button>
                        </div>
                    </div>
                </Modal>

                <!-- Edit Modal -->
                <Modal :show="showEditModal" @close="closeEditModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900">Edit Item</h2>
                        <form @submit.prevent="updateItem" class="mt-6">
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="received_quantity" value="Received Quantity" />
                                    <TextInput
                                        id="received_quantity"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="editForm.received_quantity"
                                        required
                                    />
                                </div>
                                <div>
                                    <InputLabel for="damage_quantity" value="Damage Quantity" />
                                    <TextInput
                                        id="damage_quantity"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="editForm.damage_quantity"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="batch_number" value="Batch Number" />
                                    <TextInput
                                        id="batch_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="editForm.batch_number"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="expiry_date" value="Expiry Date" />
                                    <TextInput
                                        id="expiry_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="editForm.expiry_date"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="location" value="Location" />
                                    <TextInput
                                        id="location"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="editForm.location"
                                    />
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end space-x-3">
                                <SecondaryButton @click="closeEditModal">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="editForm.processing">Update</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>
            </div>

            <div v-if="currentTab === 'packing_lists'">
                <!-- Bulk Actions Bar -->
                <div class="mb-4 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <span v-if="selectedItems.length > 0" class="text-sm text-gray-600">
                            {{ selectedItems.length }} items selected
                        </span>
                        <button 
                            v-if="selectedItems.length > 0"
                            @click="bulkApprove"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                        >
                            Approve Selected
                        </button>
                    </div>
                </div>

                <!-- Packing Lists Table -->
                <div class="relative shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <div class="max-h-[calc(100vh-250px)] overflow-auto mb-[50px]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        <input 
                                            type="checkbox" 
                                            v-model="selectAll"
                                            @change="toggleSelectAll"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                        >
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Packing List NO#</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Received QTY</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Damaged</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Received By</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Batch Number</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in props.packingLists" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <input 
                                            type="checkbox" 
                                            v-model="selectedItems" 
                                            :value="item.id"
                                            :disabled="item.status === 'approved'"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                        >
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.packing_list_number }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ formatDate(item.packing_list_date) }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.product?.barcode }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.product?.name }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">{{ item.received_quantity }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.damage_quantity }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.created_by }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.warehouse?.name }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.location }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.batch_number }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.expiry_date) }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.unit_cost) }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">{{ formatCurrency(item.total_cost) }}</td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                @click="openEditModal(item)" 
                                                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button 
                                                v-if="!item.status || item.status === 'pending'"
                                                @click="approveItem(item.id)" 
                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            >
                                                Approve
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="px-4 py-2 text-right font-bold">
                                        {{ props.packingLists.reduce((total, item) => total + parseFloat(item.received_quantity), 0) }}
                                    </td>
                                    <td colspan="6" class="px-4 py-2 text-right font-bold">
                                        Total:
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold">
                                        {{ formatCurrency(props.packingLists.reduce((total, item) => total + parseFloat(item.total_cost), 0)) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- Edit Modal -->
                <Modal :show="showEditModal" @close="closeEditModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900">Edit Packing List Item</h2>
                        <form @submit.prevent="updateItem" class="mt-6">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <InputLabel for="received_quantity" value="Received Quantity" />
                                    <TextInput
                                        id="received_quantity"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="editForm.received_quantity"
                                        required
                                    />
                                </div>
                                <div>
                                    <InputLabel for="damage_quantity" value="Damage Quantity" />
                                    <TextInput
                                        id="damage_quantity"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="editForm.damage_quantity"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="batch_number" value="Batch Number" />
                                    <TextInput
                                        id="batch_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="editForm.batch_number"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="expiry_date" value="Expiry Date" />
                                    <TextInput
                                        id="expiry_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="editForm.expiry_date"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="location" value="Location" />
                                    <TextInput
                                        id="location"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="editForm.location"
                                    />
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end space-x-3">
                                <SecondaryButton @click="closeEditModal">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="editForm.processing">Update</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>
            </div>

        </div>

    </AuthenticatedLayout>
</template>
       

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';

const props = defineProps({
    purchase_order: {
        type: Object,
        required: true
    },
    packingLists: {
        type: Array,
        default: () => []
    },
    warehouses: {
        type: Array,
        required: true
    }
});

// Tab Management
const tabs = [
    { name: 'items', label: 'Items' },
    { name: 'packing_lists', label: 'Packing Lists' }
];
const currentTab = ref('items');

// Existing Variables
const form = ref({
    items: props.purchase_order?.po_items || []
});

const selectedPackingList = ref(null);
const searchQuery = ref('');
const showImportModal = ref(false);
const importing = ref(false);
const fileInput = ref(null);
const isOpen = ref(false);

function reloadPO(){
    console.log('Reloading PO');
    const query = {}
    router.get(route('purchase-orders.packing-list', props.purchase_order.id), query, {
        preserveState: false,
        preserveScroll: true,
        only: [
            'purchase_order',
            'packingLists',
            'warehouses'
        ]
    });
}

const createPackingList = async () => {
    try {
        if (!props.purchase_order?.id) {
            Swal.fire({
                title: 'Error!',
                text: 'Purchase order ID is required',
                icon: 'error',
                confirmButtonColor: '#EF4444',
                customClass: {
                    popup: 'rounded-lg',
                    title: 'text-lg font-semibold text-gray-900',
                    htmlContainer: 'text-gray-700'
                }
            });
            return;
        }

        const response = await axios.post(route('purchase-orders.packing-list.create', props.purchase_order.id));
        const newPackingList = response.data.packing_list;

        // Add the new packing list to the list if it exists
        if (props.packingLists) {
            props.packingLists.unshift(newPackingList);
        }

        // Close the dropdown
        isOpen.value = false;

        // Select the newly created packing list
        await selectPackingList(newPackingList);

        Swal.fire({
            title: 'Success!',
            text: 'New packing list created successfully',
            icon: 'success',
            confirmButtonColor: '#10B981',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
    } catch (error) {
        console.error('Failed to create packing list:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to create packing list',
            icon: 'error',
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
    }
};


const formatDate = (value) => {
    if (!value) return '';
    const date = new Date(value);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const handleFileUpload = async (event) => {
    try {
        const file = event.target.files?.[0];
        if (!file) return;

        // Validate file type
        if (!file.name.endsWith('.xlsx')) {
            Swal.fire({
                title: 'Error!',
                text: 'Please upload an XLSX file',
                icon: 'error',
                confirmButtonColor: '#EF4444',
                customClass: {
                    popup: 'rounded-lg',
                    title: 'text-lg font-semibold text-gray-900',
                    htmlContainer: 'text-gray-700'
                }
            });
            return;
        }

        importing.value = true;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('purchase_order_id', props.purchase_order.id);

        await axios.post(
            route('purchase-orders.import-items'),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );

        Swal.fire({
            title: 'Success!',
            text: 'Items imported successfully',
            icon: 'success',
            confirmButtonColor: '#10B981',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
        
        // Refresh the items list if a packing list is selected
        if (selectedPackingList.value) {
            await selectPackingList(selectedPackingList.value);
        }
        reloadPO();
    } catch (error) {
        console.error('Import failed:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to import items',
            icon: 'error',
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
    } finally {
        importing.value = false;
        if (fileInput.value) {
            fileInput.value.value = ''; // Reset file input
        }
    }
};

const downloadTemplate = () => {
    // Create a sample Excel template with the correct format
    const worksheet = XLSX.utils.aoa_to_sheet([
        ['Item Code', 'Item Description', 'UoM', 'Quantity', 'Unit Cost'], // Header row
        ['ITEM001', 'Sample Product', 'PCS', '100', '10.50'], // Sample data row
        ['ITEM002', 'Another Product', 'BOX', '50', '25.00'] // Another sample row
    ]);

    // Set column widths
    const wscols = [
        { wch: 15 }, // Item Code
        { wch: 40 }, // Item Description
        { wch: 10 }, // UoM
        { wch: 12 }, // Quantity
        { wch: 12 }  // Unit Cost
    ];
    worksheet['!cols'] = wscols;

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Items');

    // Generate and download the file
    XLSX.writeFile(workbook, `purchase_order_items_template.xlsx`);

    Swal.fire({
        title: 'Success!',
        text: 'Template downloaded successfully',
        icon: 'success',
        confirmButtonColor: '#10B981',
        customClass: {
            popup: 'rounded-lg',
            title: 'text-lg font-semibold text-gray-900',
            htmlContainer: 'text-gray-700'
        }
    });
};

const selectPackingList = async (packingList) => {
    selectedPackingList.value = packingList;

    try {
        // Get packing list items if they exist
        const response = await axios.get(route('purchase-orders.packing-list.items', packingList.id));
        const packingListItems = response.data.items || [];

        // Use packing list items if available, otherwise use purchase order items
        const items = packingListItems.length > 0 ? packingListItems : (props.purchase_order?.po_items || []);

        form.value.items = items.map(item => ({
            id: item.id || null,
            purchase_order_id: props.purchase_order.id,
            packing_list_id: packingList.id,
            product_id: item.product_id || null,
            warehouse_id: item.warehouse_id || "",
            location: item.location || '',
            expiry_date: item.expiry_date || null,
            batch_number: item.batch_number || '',
            generic_name: item.generic_name || '',
            product_name: item.product_name || '',
            quantity: Number(item.quantity || 0),
            received_quantity: Number(item.received_quantity || 0),
            unit_cost: Number(item.unit_cost || 0),
            total_cost: Number(item.total_cost || 0)
        }));
    } catch (error) {
        console.error('Failed to fetch packing list items:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to load packing list items',
            icon: 'error',
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
    }
};

const calculateItemTotal = (item) => {
    return Number(item.received_quantity || 0) * Number(item.unit_cost || 0);
};

const formatCurrency = (value) => {
    return Number(value).toLocaleString('en-US', { 
        style: 'currency', 
        currency: 'USD' 
    });
};

const validateReceivedQuantity = (item) => {
    // Convert to number and handle empty/invalid input
    let value = Number(item.received_quantity);
    if (isNaN(value) || value < 0) {
        item.received_quantity = 0;
    } else if (value > Number(item.quantity)) {
        item.received_quantity = item.quantity; // Reset to maximum allowed quantity
        item.damage_quantity = 0;
    }
};

const validateDamageQuantity = (item) => {
    // Convert to number and handle empty/invalid input
    let value = Number(item.damage_quantity);
    let maxDamage = Math.min(
        Number(item.received_quantity),
        Number(item.quantity) - Number(item.received_quantity)
    );
    
    if (isNaN(value) || value < 0) {
        item.damage_quantity = 0;
    } else if (value > maxDamage) {
        item.damage_quantity = maxDamage; // Reset to maximum allowed quantity
    }
};

const isSubmitting = ref(false);

async function savePackingList(){
    try {
        isSubmitting.value = true;
        // Filter out incomplete items but don't show error if some items are incomplete
        const completeItems = form.value.items.filter(item => 
            item.warehouse_id && 
            item.received_quantity > 0 &&
            item.location &&
            item.expiry_date &&
            item.batch_number &&
            item.product_name &&
            item.quantity > 0 &&
            item.unit_cost > 0 &&
            item.total_cost > 0
        );

        // Only proceed if we have at least one complete item
        if (completeItems.length === 0) {
            Swal.fire({
                title: 'Warning',
                text: 'No items are ready to be saved. Please complete at least one item.',
                icon: 'warning',
                confirmButtonColor: '#F59E0B',
                customClass: {
                    popup: 'rounded-lg',
                    title: 'text-lg font-semibold text-gray-900',
                    htmlContainer: 'text-gray-700'
                }
            });
            isSubmitting.value = false;
            return;
        }

        // Show info if some items were skipped
        if (completeItems.length < form.value.items.length) {
            const skippedCount = form.value.items.length - completeItems.length;
            Swal.fire({
                title: 'Information',
                text: `${skippedCount} incomplete item(s) will be skipped. Proceeding with ${completeItems.length} complete item(s).`,
                icon: 'info',
                confirmButtonColor: '#3B82F6',
                showCancelButton: true,
                cancelButtonColor: '#6B7280',
                customClass: {
                    popup: 'rounded-lg',
                    title: 'text-lg font-semibold text-gray-900',
                    htmlContainer: 'text-gray-700'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithSave(completeItems);
                } else {
                    isSubmitting.value = false;
                }
            });
        } else {
            // All items are complete, proceed directly
            await proceedWithSave(completeItems);
        }
    } catch (error) {
        console.error('Failed to save items:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to save items',
            icon: 'error',
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
        isSubmitting.value = false;
    }
}

async function proceedWithSave(completeItems) {
    try {
        const formData = {
            purchase_order_id: props.purchase_order.id,
            items: completeItems
        };

        const response = await axios.post(route('purchase-orders.packing-list.store'), formData);
        Swal.fire({
            title: 'Success!',
            text: response.data,
            icon: 'success',
            confirmButtonColor: '#10B981',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
        
        // Wait a bit before reloading to ensure the server has processed everything
        setTimeout(() => {
            reloadPO();
            isSubmitting.value = false;
        }, 500);
    } catch (error) {
        throw error; // Re-throw to be caught by the outer catch block
    }
}

const exportToExcel = () => {
    // Get the items from props
    const packingList = props.packingLists.find(pl => pl.id === selectedPackingList);
    if (!packingList) return;

    // Create worksheet data
    const wsData = packingList.items.map(item => ({
        'Item Code': item.product?.barcode || '',
        'Name': item.product?.name || '',
        'Quantity': item.quantity || 0,
        'Received Quantity': item.received_quantity || 0,
        'Damage Quantity': item.damage_quantity || 0,
        'Warehouse': item.warehouse?.name || '',
        'Location': item.location || '',
        'Batch Number': item.batch_number || '',
        'Expiry Date': item.expiry_date ? new Date(item.expiry_date).toLocaleDateString() : '',
        'Unit Cost': Number(item.unit_cost || 0).toFixed(3),
        'Total Cost': Number(item.total_cost || 0).toFixed(3)
    }));

    // Create worksheet
    const ws = XLSX.utils.json_to_sheet(wsData);

    // Create workbook
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Packing List');

    // Generate filename
    const fileName = `packing_list_${new Date().toISOString().split('T')[0]}.xlsx`;

    // Export to file
    XLSX.writeFile(wb, fileName);
};

const selectedItems = ref([]);
const selectAll = ref(false);

// Toggle all checkboxes
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedItems.value = props.packingLists
            .filter(item => item.status !== 'approved')
            .map(item => item.id);
    } else {
        selectedItems.value = [];
    }
};

// Watch for changes in selected items
watch(selectedItems, () => {
    const selectableItems = props.packingLists.filter(item => item.status !== 'approved');
    selectAll.value = selectableItems.length > 0 && 
                     selectedItems.value.length === selectableItems.length;
});

// Bulk approve selected items
const bulkApprove = () => {
    if (selectedItems.value.length === 0) {
        Swal.fire({
            title: 'No Items Selected',
            text: 'Please select items to approve',
            icon: 'warning',
            confirmButtonColor: '#F59E0B',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
        return;
    }

    Swal.fire({
        title: 'Bulk Approve Items',
        text: `Are you sure you want to approve ${selectedItems.value.length} items?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve All',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg',
            title: 'text-lg font-semibold text-gray-900',
            htmlContainer: 'text-gray-700'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('purchase-orders.packing-list.bulk-approve', { purchaseOrder: props.purchase_order.id }), {
                items: selectedItems.value,
                purchase_order_id: props.purchase_order.id
            })
            .then(response => {
                Swal.fire({
                    title: 'Approved!',
                    text: response.data.message,
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                    customClass: {
                        popup: 'rounded-lg',
                        title: 'text-lg font-semibold text-gray-900',
                        htmlContainer: 'text-gray-700'
                    }
                });
                selectedItems.value = [];
                selectAll.value = false;
                reloadPO();
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'Failed to approve items',
                    icon: 'error',
                    confirmButtonColor: '#EF4444',
                    customClass: {
                        popup: 'rounded-lg',
                        title: 'text-lg font-semibold text-gray-900',
                        htmlContainer: 'text-gray-700'
                    }
                });
            });
        }
    });
};

// Individual item approval
const approveItem = (itemId) => {
    Swal.fire({
        title: 'Approve Item',
        text: 'Are you sure you want to approve this item?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg',
            title: 'text-lg font-semibold text-gray-900',
            htmlContainer: 'text-gray-700'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('purchase-orders.packing-list.bulk-approve', { purchaseOrder: props.purchase_order.id }), {
                items: [itemId],
                purchase_order_id: props.purchase_order.id
            })
            .then(response => {
                Swal.fire({
                    title: 'Approved!',
                    text: response.data.message,
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                    customClass: {
                        popup: 'rounded-lg',
                        title: 'text-lg font-semibold text-gray-900',
                        htmlContainer: 'text-gray-700'
                    }
                });
                reloadPO();
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.error || 'Failed to approve item',
                    icon: 'error',
                    confirmButtonColor: '#EF4444',
                    customClass: {
                        popup: 'rounded-lg',
                        title: 'text-lg font-semibold text-gray-900',
                        htmlContainer: 'text-gray-700'
                    }
                });
            });
        }
    });
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return `px-2 py-1 text-xs font-medium rounded-full ${classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800'}`;
};

const showEditModal = ref(false);
const editForm = ref({
    id: '',
    received_quantity: '',
    damage_quantity: '',
    batch_number: '',
    expiry_date: '',
    location: ''
});

const openEditModal = (item) => {
    editForm.value = {
        id: item.id,
        received_quantity: item.received_quantity,
        damage_quantity: item.damage_quantity,
        batch_number: item.batch_number,
        expiry_date: item.expiry_date,
        location: item.location
    };
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.value = {
        id: '',
        received_quantity: '',
        damage_quantity: '',
        batch_number: '',
        expiry_date: '',
        location: ''
    };
};

const updateItem = async () => {
    try {
        const response = await axios.post(route('purchase-orders.packing-list.update-item', props.purchase_order.id), editForm.value);
        Swal.fire({
            title: 'Updated!',
            text: response.data.message,
            icon: 'success',
            confirmButtonColor: '#10B981',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
        reloadPO();
        closeEditModal();
    } catch (error) {
        console.error('Failed to update item:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to update item',
            icon: 'error',
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-lg font-semibold text-gray-900',
                htmlContainer: 'text-gray-700'
            }
        });
    }
}
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}

.overflow-x-auto {
    overflow: auto;
}

/* Custom scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* For Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

/* Ensure the table header stays fixed */
thead {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f9fafb;
}

/* Table container */
.table-container {
    position: relative;
    overflow: auto;
    max-height: calc(100vh - 200px);
    border-radius: 0.5rem;
}
</style>