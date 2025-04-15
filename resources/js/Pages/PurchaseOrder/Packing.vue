<template>
    <Head :title="props.purchase_order.po_number" />
    <AuthenticatedLayout :auth="$page.props.auth" :errors="$page.props.errors">
        <div class="flex h-full relative">
            <div class="fixed w-[300px] border-r border-gray-200 bg-white top-[200px] bottom-[50px] overflow-y-auto mr-[100px]">
                <div class="h-full">
                    <div class="sticky top-0 bg-white p-4 z-10">
                        <h3 class="text-lg font-semibold text-gray-900">Purchase Orders</h3>
                    </div>
                    <div class="px-4 pb-4 space-y-2">
                        <div v-for="p in props.purchase_orders" :key="p.id"
                            @click="selectPurchaseOrder(p)"
                            class="p-3 rounded-md hover:bg-gray-50 cursor-pointer border border-gray-100"
                            :class="{ 'bg-indigo-50 border-indigo-200': selectedPurchaseOrder?.id == p.id }">
                            <div class="flex justify-between">
                                {{ p.po_number }}
                                <span
                                    :class="['px-2 py-1 text-xs rounded-full', p.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800']">{{
                                    p.status }}</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ p.po_date }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-0 overflow-hidden p-3 ml-[300px]">
                <!-- Header -->
                <div class="mb-6">
                    <p class="mt-1 text-sm text-gray-600">
                        Purchase Order #{{ props.purchase_order.po_number }}
                    </p>
                </div>
                <!-- Packing List Dropdown -->
                <h2 class="text-lg font-semibold mb-2">Select Packing List</h2>
                <div class="relative">
                    <div>
                        <button type="button" @click="isOpen = !isOpen"
                            class="inline-flex items-center justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <i class="fas fa-file-alt -ml-0.5 h-5 w-5" aria-hidden="true"></i>
                            {{ selectedPackingList ? 'Packing List #' +
                                selectedPackingList.packing_list_number :
                                'Select Packing List' }}
                            <i class="fas fa-chevron-down -mr-1 h-5 w-5" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div v-if="isOpen"
                        class="absolute left-0 z-[100] mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
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
                        <div v-if="props.purchase_order.packing_lists.length > 0" class="py-1 max-h-60 overflow-y-auto">
                            <button v-for="packingList in props.purchase_order.packing_lists" :key="packingList.id"
                                @click="selectPackingList(packingList); isOpen = false"
                                class="flex w-full flex-col px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <div class="flex items-center w-full">
                                    <i class="fas fa-file-alt mr-3 text-gray-400"></i>
                                    <div class="flex-1">
                                        <div class="font-medium">Packing List #{{
                                            packingList.packing_list_number }}
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

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button v-for="tab in tabs" :key="tab.name" @click="currentTab = tab.name" :class="[
                            currentTab === tab.name
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
                        ]">
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div v-if="currentTab === 'items'">
                    <!-- Items Tab -->
                    <div class="space-y-4">
                        <!-- Action Buttons -->
                        <div class="flex justify-end mb-4">
                            <div class="flex items-center space-x-4">
                                <!-- Other Action Buttons -->
                                <button v-if="selectedItems && selectedItems.length > 0"
                                    @click="deleteSelectedItems"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2.25 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete Selected ({{ selectedItems.length }})
                                </button>
                                <button type="button" @click="showImportModal = true" v-if="props.purchase_order.po_items.length === 0"
                                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
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
                            <form @submit.prevent="savePackingList">
                                <div
                                    class="relative shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg max-h-[calc(100vh-200px)] overflow-auto">
                                    <div v-if="!selectedPackingList"
                                        class="absolute inset-0 bg-black bg-opacity-10 flex items-center justify-center z-20 pointer-events-none">
                                        <div class="bg-white px-4 py-2 rounded-md shadow-lg text-gray-700 font-medium">
                                            Please select a packing list first
                                        </div>
                                    </div>
                                    <table class="min-w-full divide-y divide-gray-300 text-xs relative">
                                        <thead class="bg-gray-50 sticky top-0 z-10">
                                            <tr>
                                                <th class="sticky left-0 z-20 bg-gray-50 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 border-r">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" v-model="selectAllItems" @change="toggleSelectAllItems" 
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                                            :disabled="!form.items || form.items.length === 0" />
                                                        <span class="ml-2">Item</span>
                                                    </div>
                                                </th>
                                                <th class="px-2 text-left text-xs font-semibold text-gray-900">Quantity
                                                </th>
                                                <th class="px-2 text-left text-xs font-semibold text-gray-900">Received
                                                </th>
                                                <th class="px-2 text-left text-xs font-semibold text-gray-900">Damaged
                                                </th>
                                                <th class="px-2 text-left text-xs font-semibold text-gray-900">Warehouse
                                                </th>
                                                <th class="px-2 text-left text-xs font-semibold text-gray-900">Location
                                                </th>
                                                <th class="px-2 text-left text-xs font-semibold text-gray-900">Batch #
                                                </th>
                                                <th class="px-2 text-left text-xs font-semibold text-gray-900">Expiry
                                                    Date</th>
                                                <th class="px-2 text-right text-xs font-semibold text-gray-900">Unit
                                                    Cost</th>
                                                <th class="px-2 pr-6 text-right text-xs font-semibold text-gray-900">
                                                    Total Cost</th>
                                                <th class="px-2 pr-6 text-right text-xs font-semibold text-gray-900">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <tr v-if="!form.items?.length" class="text-center">
                                                <td colspan="10"
                                                    class="py-4 pl-4 pr-3 text-sm font-medium text-gray-500">
                                                    No items available
                                                </td>
                                            </tr>
                                            <tr v-for="item in form.items" :key="item.id"
                                                class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                                <td class="sticky left-0 z-10 bg-white border-r whitespace-nowrap py-4 pl-4 pr-3 text-xs font-medium text-gray-900">
                                                    
                                                    <div class="flex items-center">
                                                        <input type="checkbox" 
                                                            :value="item.id" 
                                                            v-model="selectedItems" 
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mr-2" />
                                                        {{ item.product_name }}
                                                        <p v-if="item.generic_name" class="text-xs text-gray-500 mt-0.5">{{
                                                            item.generic_name }}</p>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-xs text-gray-500">
                                                    {{ Number(item.quantity).toLocaleString() }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    <div class="flex items-center space-x-2">
                                                        <input type="number" v-model="item.received_quantity"
                                                            :max="item.quantity" min="0"
                                                            :disabled="!selectedPackingList"
                                                            :class="['block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                                !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']"
                                                            placeholder="received qty"
                                                            @input="validateReceivedQuantity(item)" />
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    <div class="flex items-center space-x-2">
                                                        <input type="number" v-model="item.damage_quantity"
                                                            :max="item.received_quantity" min="0"
                                                            :disabled="!selectedPackingList"
                                                            :class="['block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                                !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']" placeholder="damaged qty"
                                                            @input="validateDamageQuantity(item)" />
                                                    </div>
                                                </td>
                                                <td
                                                    class="whitespace-nowrap px-3 py-4 text-xs text-gray-500 min-w-[300px]">
                                                    <select v-model="item.warehouse_id" :disabled="!selectedPackingList"
                                                        :class="['w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 text-ellipsis',
                                                            !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']">
                                                        <option value="" selected>Select Warehouse</option>
                                                        <option v-for="warehouse in props.warehouses"
                                                            :key="warehouse.id" :value="warehouse.id" class="truncate">
                                                            {{ warehouse.name }}
                                                        </option>
                                                    </select>
                                                </td>
                                                <td
                                                    class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 min-w-[300px]">
                                                    <input type="text" v-model="item.location"
                                                        :disabled="!selectedPackingList"
                                                        :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                            !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']" placeholder="location" />
                                                </td>
                                                <td
                                                    class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 min-w-[200px]">
                                                    <input type="text" v-model="item.batch_number"
                                                        :disabled="!selectedPackingList"
                                                        :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                            !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']" placeholder="batch number" />
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    <input type="date" v-model="item.expiry_date"
                                                        :disabled="!selectedPackingList"
                                                        :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                                            !selectedPackingList ? 'bg-gray-100 cursor-not-allowed' : '']" placeholder="expiry date" />
                                                </td>
                                                <td class="px-3 py-4 text-xs text-gray-500 text-right">
                                                    {{ Number(item.unit_cost).toLocaleString('en-US', {
                                                        style:
                                                            'currency', currency: 'USD'
                                                    }) }}
                                                </td>
                                                <td class="px-3 pr-6 py-4 text-xs font-medium text-gray-900 text-right">
                                                    {{ Number(calculateItemTotal(item)).toLocaleString('en-US', {
                                                        style:
                                                            'currency', currency: 'USD'
                                                    }) }}
                                                </td>
                                                <td class="px-2 pr-6 text-right text-xs font-semibold text-gray-900">
                                                    <button v-if="getItemAction(item)"
                                                        @click="handleAction(item, getItemAction(item).action)"
                                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white"
                                                        :class="{
                                                            'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': getItemAction(item).color === 'blue',
                                                            'bg-green-600 hover:bg-green-700 focus:ring-green-500': getItemAction(item).color === 'green'
                                                        }">
                                                        {{ getItemAction(item).action === 'verify' ? 'Verify' : 'Approve' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="flex justify-end p-3 mb-[50px]">
                                    <button
                                        v-if="selectedPackingList && props.packingLists.find(pl => pl.id === selectedPackingList)?.items?.every(item => item.warehouse_id && item.location)"
                                        @click="exportToExcel" type="button"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2.25 0 01-2-2V5a2 2.25 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2.25 0 01-2 2z" />
                                        </svg>
                                        Export to Excel
                                    </button>
                                    <button type="submit" :disabled="isSubmitting"
                                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 17.25v3.75a.75.75 0 01-1.5 0v-3.75m-7.5-7.5h-1.5a.75.75 0 00-1.5 0v7.5a.75.75 0 001.5 0v-7.5m7.5 0v3.75a.75.75 0 001.5 0v-3.75m-7.5 0h1.5a.75.75 0 001.5 0h-1.5z" />
                                        </svg>
                                        {{ isSubmitting ? 'Processing...' : 'Submit' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div v-if="currentTab === 'packing_lists'">
                    <!-- Bulk Actions Bar -->
                    <div class="mb-4 flex justify-end items-center space-x-4">
                        <div class="flex items-center">
                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label class="ml-2 text-sm text-gray-600">Select All</label>
                        </div>
                        <button
                            @click="bulkVerify"
                            :disabled="verifiableItemsCount === 0"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Bulk Verify ({{ verifiableItemsCount }})
                        </button>
                        <button
                            @click="bulkApprove"
                            :disabled="approvableItemsCount === 0"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            Bulk Approve ({{ approvableItemsCount }})
                        </button>
                    </div>
                    <!-- Packing Lists Table -->
                    <div class="relative shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <div class="max-h-[calc(100vh-250px)] overflow-auto mb-[50px]">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Packing List NO#</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item
                                            Code</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Quantity</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Received QTY</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Damaged</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Received By</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Warehouse</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Location</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Batch Number</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Expiry Date</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit
                                            Cost</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Total Cost</th>
                                        <th scope="col"
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in props.packingLists" :key="item.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" v-model="selectedItems" :value="item.id"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{
                                            item.packing_list_number }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{
                                            formatDate(item.packing_list_date) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{
                                            item.product?.barcode }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.product?.name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">{{
                                            item.received_quantity }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{
                                            item.damage_quantity }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.created_by
                                        }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{
                                            item.warehouse?.name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ item.location
                                        }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{
                                            item.batch_number }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{
                                            formatDate(item.expiry_date) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{
                                            formatCurrency(item.unit_cost) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">{{
                                            formatCurrency(item.total_cost) }}</td>
                                        <td class="px-3 py-4 text-sm">
                                            <div class="flex items-center space-x-2">
                                                <button @click="openEditModal(item)"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                        fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.25 2.25 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button v-if="canVerify(item, $page.props.auth.roles)"
                                                    @click="updateItemStatus(item.id, 'verify')"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                    Verify
                                                </button>
                                                <button v-if="canApprove(item, $page.props.auth.roles)"
                                                    @click="updateItemStatus(item.id, 'approve')"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                    Approve
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="px-4 py-2 text-right font-bold">
                                            {{props.packingLists.reduce((total, item) => total +
                                                parseFloat(item.received_quantity), 0)}}
                                        </td>
                                        <td colspan="7" class="px-4 py-2 text-right font-bold">
                                            Total:
                                        </td>
                                        <td class="px-4 py-2 text-right font-bold">
                                            {{formatCurrency(props.packingLists.reduce((total, item) => total +
                                                parseFloat(item.total_cost), 0))}}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <!-- Bulk Actions Bar -->
                    <div class="mb-4 flex justify-end items-center space-x-4">
                        <button v-if="canUserVerify && verifiableItemsCount > 0"
                            @click="bulkVerify"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Bulk Verify ({{ verifiableItemsCount }})
                        </button>
                        <button v-if="canUserApprove && approvableItemsCount > 0"
                            @click="bulkApprove"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            Bulk Approve ({{ approvableItemsCount }})
                        </button>
                    </div>
                    <!-- Edit Modal -->
                    <Modal :show="showEditModal" @close="closeEditModal" :maxWidth="'7xl'" class="print:hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900">Edit Packing List Item</h2>
                            <form @submit.prevent="updateItem" class="mt-6">
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <InputLabel for="received_quantity" value="Received Quantity" />
                                        <TextInput id="received_quantity" type="number" class="mt-1 block w-full"
                                            v-model="editForm.received_quantity" required />
                                    </div>
                                    <div>
                                        <InputLabel for="damage_quantity" value="Damage Quantity" />
                                        <TextInput id="damage_quantity" type="number" class="mt-1 block w-full"
                                            v-model="editForm.damage_quantity" />
                                    </div>
                                    <div>
                                        <InputLabel for="batch_number" value="Batch Number" />
                                        <TextInput id="batch_number" type="text" class="mt-1 block w-full"
                                            v-model="editForm.batch_number" />
                                    </div>
                                    <div>
                                        <InputLabel for="expiry_date" value="Expiry Date" />
                                        <TextInput id="expiry_date" type="date" class="mt-1 block w-full"
                                            v-model="editForm.expiry_date" />
                                    </div>
                                    <div>
                                        <InputLabel for="location" value="Location" />
                                        <TextInput id="location" type="text" class="mt-1 block w-full"
                                            v-model="editForm.location" />
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

                <div v-if="currentTab === 'received_goods_notes'">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">RGN Number</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Packing List</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Warehouse</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Receiver</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Received At</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="rgn in props.purchase_order.received_goods_notes" :key="rgn.id">
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ rgn.rgn_number }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ rgn.packing_list?.packing_list_number }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ rgn.warehouse?.name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ rgn.receiver?.name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span :class="{
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5': true,
                                            'bg-yellow-100 text-yellow-800': rgn.status === 'pending',
                                            'bg-green-100 text-green-800': rgn.status === 'received'
                                        }">
                                            {{ rgn.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ rgn.received_at || '-' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">  
                                        <button @click="showRGN(rgn)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Modal :show="showRGNModal" @close="closeRGNModal" :maxWidth="'7xl'" class="print:hidden">
                        <div class="bg-white">
                            <!-- Print/Download Actions -->
                            <div class="absolute top-4 right-4 flex items-center gap-3 print:hidden">
                                <button @click="downloadPDF"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-download mr-2"></i> Download PDF
                                </button>
                                <button @click="closeRGNModal" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-times text-lg"></i>
                                </button>
                            </div>

                            <!-- Printable Content -->
                            <div id="print-content" style="width: 350mm; min-height: 350mm; margin: 0 auto; padding: 20mm;">

                                <div v-if="currentRGN" class="space-y-4">
                                    <div class="flex justify-between border-b border-gray-300">
                                        <div>
                                            <img src="/assets/images/psi.jpg" class="w-16" alt="Logo" />
                                        </div>
                                        <div>
                                            <p class="text-sm">GRN Number: {{ currentRGN.rgn_number }}</p>
                                            <p class="text-sm">Date: {{ formatDate(currentRGN.created_at) }}</p>
                                        </div>
                                    </div>
                                    <!-- Header Information -->
                                    <div class="flex justify-between">
                                        <div>
                                            <h3 class="font-semibold text-lg mb-2">Purchase Order Information</h3>
                                            <p class="text-sm">PO Number: {{ props.purchase_order.po_number }}</p>
                                            <p class="text-sm">Order Date: {{ formatDate(props.purchase_order.po_date) }}</p>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-lg mb-2">Supplier Information</h3>
                                            <p class="text-sm">Name: {{ props.purchase_order.supplier?.name }}</p>
                                            <p class="text-sm">Contact: {{ props.purchase_order.supplier?.contact_person }}</p>
                                            <p class="text-sm">Phone: {{ props.purchase_order.supplier?.phone }}</p>
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <p class="text-base font-semibold">Date: {{ moment().format('LL') }}</p>
                                    </div>

                                    <!-- Items Table -->
                                    <div>
                                        <h3 class="font-semibold text-lg mb-3">Items Received</h3>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-300">
                                                <thead>
                                                    <tr>
                                                        <th class="px-3 py-2 text-left text-sm font-semibold bg-gray-50">Item</th>
                                                        <th class="px-3 py-2 text-right text-sm font-semibold bg-gray-50">Quantity</th>
                                                        <th class="px-3 py-2 text-left text-sm font-semibold bg-gray-50">Warehouse</th>
                                                        <th class="px-3 py-2 text-left text-sm font-semibold bg-gray-50">Location</th>
                                                        <th class="px-3 py-2 text-left text-sm font-semibold bg-gray-50">Expiry Date</th>
                                                        <th class="px-3 py-2 text-left text-sm font-semibold bg-gray-50">Batch #</th>
                                                        <th class="px-3 py-2 text-left text-sm font-semibold bg-gray-50">Unit Cost</th>
                                                        <th class="px-3 py-2 text-left text-sm font-semibold bg-gray-50">Total Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    <tr v-for="item in currentRGN.packing_list?.purchase_order_items" :key="item.id">
                                                        <td class="px-3 py-2 text-sm">{{ item.product?.name }}</td>
                                                        <td class="px-3 py-2 text-sm text-right">{{ item.quantity }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ item.warehouse?.name }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ item.location }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ item.expiry_date ? moment(item.expiry_date).format('LL') : '' }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ item.batch_number }}</td>
                                                        <td class="px-3 py-2 text-sm text-right">{{ formatMoney(item.unit_cost) }}</td>
                                                        <td class="px-3 py-2 text-sm text-right">{{ formatMoney(item.total_cost) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Signatures -->
                                    <div class="mt-[100px] flex justify-between">
                                        <div class="text-start">
                                            <div class="border-gray-300 pt-2">
                                                <p class="font-semibold">Received By</p>
                                                <p class="text-sm">{{ currentRGN.receiver?.name }}</p>
                                                <p class="text-sm text-gray-500">{{ formatDate(currentRGN.received_at) }}</p>
                                            </div>
                                        </div>
                                        <div class="text-start">
                                            <div class="border-gray-300 pt-2">
                                                <p class="font-semibold">Verified By</p>
                                                <p class="text-sm">{{ props.purchase_order.approvals?.find(a => a.action === 'verify')?.role?.name || 'N/A'}}</p>
                                                <p class="text-sm text-gray-500">Date: {{ moment().format('LL') }}</p>                                                
                                            </div>
                                        </div>
                                        <div class="text-start">
                                            <div class="border-gray-300 pt-2">
                                                <p class="font-semibold">Approved By</p>
                                                <p class="text-sm">{{ props.purchase_order.approvals?.find(a => a.action === 'approve')?.role?.name || 'N/A'}}</p>
                                                <p class="text-sm text-gray-500">Date: {{ moment().format('LL') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Modal>
                </div>
            </div>
        </div>
        <Modal :show="showImportModal" @close="showImportModal = false" max-width="xl" >
                        <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Import Items</h2>
                <p class="mt-1 text-sm text-gray-600">Please upload an Excel file (.xlsx) with the following columns: Item Code, Item Description, UoM, Quantity, Unit Cost, Total Cost</p>

                <div class="mt-4 space-y-4">
                    <div class="flex items-center w-full">
                        <label for="file" class="flex items-center space-x-2 border border-gray-300 rounded-md p-2">
                            <i class="fas fa-file-excel text-lg text-gray-500"></i>
                            <span class="text-sm text-gray-700 w-full">Select Excel file</span>
                            <input type="file" class="hidden" id="file" name="file" accept=".xlsx" @change="handleFileUpload" />
                        </label>
                    </div>
                    <div class="flex items-center flex-col">
                        <span>{{  importing ? 'Importing...' : ''}}</span>
                        <button type="button" @click="downloadTemplate" class="ml-2 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 border border-transparent rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" :disabled="importing">
                            Download Template
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';
import { useToast } from 'vue-toastification';
import jsPDF from 'jspdf';
import moment from 'moment';
import { usePage } from '@inertiajs/vue3';

const toast = useToast();

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
    },
    purchase_orders: {
        type: Array,
        default: () => []
    }
});

const formatMoney = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
};

const selectedPurchaseOrder = ref(props.purchase_order);

function selectPurchaseOrder(purchaseOrder) {        
    router.get(route('purchase-orders.packing-list', purchaseOrder.id), {}, {
        preserveState: false, // Don't preserve state to ensure fresh data
        preserveScroll: true,
        only: ['purchase_order', 'packingLists', 'warehouses']
    });

}

// Tab Management
const tabs = [
    { name: 'items', label: 'PO Items' },
    { name: 'packing_lists', label: 'Packing Lists' },
    { name: 'received_goods_notes', label: 'Received Goods Notes' }
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

function reloadPO() {
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
        if (props.purchase_order.packing_lists) {
            props.purchase_order.packing_lists.push(newPackingList);
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
            text: error.response?.data || 'Failed to create packing list',
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

        toast.success('PO Items imported successfully');
        // Refresh the items list if a packing list is selected
        if (selectedPackingList.value) {
            await selectPackingList(selectedPackingList.value);
        }
        reloadPO();
    } catch (error) {
        console.error('Import failed:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Failed to import items',
            icon: 'error',
            confirmButtonColor: '#EF4444',
            zIndex: 9999,
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
        ['Item Code', 'Item Description', 'UoM', 'Quantity', 'Unit Cost', 'Total Cost'], // Header row
        ['ITEM001', 'Sample Product', 'PCS', '100', '10.50', '1050'], // Sample data row
        ['ITEM002', 'Another Product', 'BOX', '50', '25.00', '1250'] // Another sample row
    ]);

    // Set column widths
    const wscols = [
        { wch: 15 }, // Item Code
        { wch: 40 }, // Item Description
        { wch: 10 }, // UoM
        { wch: 12 }, // Quantity
        { wch: 12 }, // Unit Cost
        { wch: 12 }  // Total Cost
    ];
    worksheet['!cols'] = wscols;

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Items');

    // Generate and download the file
    XLSX.writeFile(workbook, `purchase_order_items_template.xlsx`);
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
            text: error.response?.data || 'Failed to load packing list items',
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

const userRoles = computed(() => usePage().props.auth?.user?.roles || []);

const getItemAction = (item) => {
    const verifyApproval = props.purchase_order.approvals?.find(a => 
        a.action === 'verify' && 
        a.model === 'PurchaseOrderItem' && 
        userRoles.value.includes(a.role_id)
    );
    const approveApproval = props.purchase_order.approvals?.find(a => 
        a.action === 'approve' && 
        a.model === 'PurchaseOrderItem' && 
        userRoles.value.includes(a.role_id)
    );

    if (verifyApproval && (!item.status || item.status === 'pending')) {
        return { action: 'verify', color: 'blue' };
    }

    if (approveApproval && item.status === 'verified') {
        return { action: 'approve', color: 'green' };
    }

    return null;
};

const updateItemStatus = async (itemIds, action) => {
    try {
        const isArray = Array.isArray(itemIds);
        const items = isArray ? itemIds : [itemIds];
        const status = action === 'verify' ? 'verified' : 'approved';
        const actionText = isArray ? `Bulk ${action}` : action;
        
        const result = await Swal.fire({
            title: `${actionText} ${items.length > 1 ? 'Items' : 'Item'}`,
            text: `Are you sure you want to ${action} ${items.length} ${items.length > 1 ? 'items' : 'item'}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: action === 'verify' ? '#3085d6' : '#10B981',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${action} ${items.length > 1 ? 'them' : 'it'}!`
        });

        if (result.isConfirmed) {
            await axios.post(route('purchase-orders.packing-list.bulk-approve', props.purchase_order.id), {
                items: items,
                purchase_order_id: props.purchase_order.id,
                status: status
            });

            Swal.fire({
                title: 'Success!',
                text: `${items.length} ${items.length > 1 ? 'items have' : 'item has'} been ${status}`,
                icon: 'success',
                confirmButtonColor: action === 'verify' ? '#3085d6' : '#10B981'
            });

            // Clear selections after bulk actions
            if (isArray) {
                selectedItems.value = [];
                selectAll.value = false;
            }
            reloadPO();
        }
    } catch (error) {
        console.error(`Error ${action} items:`, error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || `Failed to ${action} items`,
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
};

const handleAction = (item, action) => {
    if (action === 'verify') {
        updateItemStatus(item.id, 'verify');
    } else if (action === 'approve') {
        Swal.fire({
            title: 'Approve Item',
            text: 'Are you sure you want to approve this item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                updateItemStatus(item.id, 'approve');
            }
        });
    }
};

const showRGNModal = ref(false);
const currentRGN = ref(null);

const showRGN = (rgn) => {
    console.log('Modal should show:', showRGNModal.value);
    console.log('RGN data:', rgn);
    currentRGN.value = rgn;
    showRGNModal.value = true;
};

const closeRGNModal = () => {
    showRGNModal.value = false;
    currentRGN.value = null;
};

const isSubmitting = ref(false);

async function savePackingList() {
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
            isSubmitting.value = false;
        } else {
            // All items are complete, proceed directly
            await proceedWithSave(completeItems);
            isSubmitting.value = false;
        }
    } catch (error) {
        isSubmitting.value = false;
        console.error('Failed to save items:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Failed to save items',
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
            .filter(item => {
                if (canUserVerify && !canUserApprove) {
                    return item.status === 'pending';
                } else if (canUserApprove && !canUserVerify) {
                    return item.status === 'verified';
                }
                return true;
            })
            .map(item => item.id);
    } else {
        selectedItems.value = [];
    }
};

// Computed properties for bulk actions
const verifiableItemsCount = computed(() => {
    return selectedItems.value.filter(id => {
        const item = props.packingLists.find(i => i.id === id);
        return item && item.status === 'pending';
    }).length;
});

const approvableItemsCount = computed(() => {
    return selectedItems.value.filter(id => {
        const item = props.packingLists.find(i => i.id === id);
        return item && item.status === 'verified';
    }).length;
});

// Watch for changes in selected items
watch(selectedItems, (newVal) => {
    selectAll.value = newVal.length === props.packingLists.length && props.packingLists.length > 0;
});

const canVerify = (item, roles) => {
    const verifyApproval = props.purchase_order.approvals?.find(a => 
        a.action === 'verify' && 
        a.model === 'PurchaseOrderItem' &&
        roles.includes(a.role_id)
    );
    return !!verifyApproval && item.status === 'pending';
};

const canApprove = (item, roles) => {
    const approveApproval = props.purchase_order.approvals?.find(a => 
        a.action === 'approve' && 
        a.model === 'PurchaseOrderItem' &&
        roles.includes(a.role_id)
    );
    return !!approveApproval && item.status === 'verified';
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
        received_quantity: item.received_quantity || '',
        damage_quantity: item.damage_quantity || '',
        batch_number: item.batch_number || '',
        expiry_date: item.expiry_date || '',
        location: item.location || ''
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

const downloadPDF = async () => {
    const doc = new jsPDF('l', 'mm', [350, 350]);
    const element = document.getElementById('print-content'); // Changed from 'printable-content' to 'print-content'

    if (!element) {
        console.error('Print element not found');
        return;
    }

    // Create a clone of the element to ensure proper rendering
    const printContent = element.cloneNode(true);

    // Temporarily append to document to ensure proper rendering
    printContent.style.display = 'block';
    document.body.appendChild(printContent);

    try {
        await doc.html(printContent, {
            callback: (doc) => {
                // Remove blank pages if they exist
                if (doc.internal.pages.length > 1 &&
                    doc.internal.pages[doc.internal.pages.length - 1].length < 100) {
                    doc.deletePage(doc.internal.pages.length);
                }
                doc.save(`rgn-${currentRGN.value.rgn_number}.pdf`);
            },
            x: 10,
            y: 10,
            width: 330, // Leave some margin
            windowWidth: 1320,
            autoPaging: 'text'
        });
    } catch (error) {
        console.error('Error generating PDF:', error);
    } finally {
        // Clean up the temporary element
        document.body.removeChild(printContent);
    }
};

const canUserVerify = computed(() => {
    const verifyApproval = props.purchase_order.approvals?.find(a => 
        a.action === 'verify' && 
        a.model === 'PurchaseOrderItem' && 
        userRoles.value.includes(a.role_id)
    );
    console.log('Verify Approval:', verifyApproval);
    console.log('User Roles:', userRoles.value);
    console.log('Props Approvals:', props.purchase_order.approvals);
    return !!verifyApproval;
});

const canUserApprove = computed(() => {
    const approveApproval = props.purchase_order.approvals?.find(a => 
        a.action === 'approve' && 
        a.model === 'PurchaseOrderItem' && 
        userRoles.value.includes(a.role_id)
    );
    console.log('Approve Approval:', approveApproval);
    console.log('User Roles:', userRoles.value);
    console.log('Props Approvals:', props.purchase_order.approvals);
    return !!approveApproval;
});

const bulkVerify = () => {
    const verifiableItems = selectedItems.value.filter(id => {
        const item = props.packingLists.find(i => i.id === id);
        return item && item.status === 'pending';
    });
    
    if (!verifiableItems.length) {
        Swal.fire({
            title: 'No Items to Verify',
            text: 'Please select items that need verification',
            icon: 'warning'
        });
        return;
    }
    updateItemStatus(verifiableItems, 'verify');
};

const bulkApprove = () => {
    const approvableItems = selectedItems.value.filter(id => {
        const item = props.packingLists.find(i => i.id === id);
        return item && item.status === 'verified';
    });
    
    if (!approvableItems.length) {
        Swal.fire({
            title: 'No Items to Approve',
            text: 'Please select verified items to approve',
            icon: 'warning'
        });
        return;
    }
    updateItemStatus(approvableItems, 'approve');
};

const selectAllItems = ref(false);

const toggleSelectAllItems = () => {
    selectAllItems.value = !selectAllItems.value;
    if (selectAllItems.value) {
        // Make sure we're selecting from the correct data source
        // Check if we have form items, otherwise fall back to po_items
        if (form.value.items && form.value.items.length > 0) {
            selectedItems.value = form.value.items.map(item => item.id);
        } else if (props.purchase_order.po_items) {
            selectedItems.value = props.purchase_order.po_items.map(item => item.id);
        }
    } else {
        selectedItems.value = [];
    }
};

const deleteSelectedItems = async () => {
        if (!selectedItems.value.length) {
            Swal.fire({
                title: 'No Items Selected',
                text: 'Please select items to delete',
                icon: 'warning'
            });
            return;
        }

        const result = await Swal.fire({
            title: 'Delete Selected Items',
            text: `Are you sure you want to delete ${selectedItems.value.length} item(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, delete them!'
        });

        if (result.isConfirmed) {
            await axios.post(route('purchase-orders.deleteItems'), {
                items: selectedItems.value,
                purchase_order_id: props.purchase_order.id
            })
            .then((response) => {
                console.log('Response:', response.data);
                selectedItems.value = [];
                selectAllItems.value = false;
                reloadPO();
                Swal.fire({
                    title: 'Deleted!',
                    text: response.data,
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                    customClass: {
                        popup: 'rounded-lg',
                        title: 'text-lg font-semibold text-gray-900',
                        htmlContainer: 'text-gray-700'
                    }
                });
            })
            .catch((error) => {
                console.error('Error deleting items:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data || 'Failed to delete items',
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            });
        }
    }

</script>

<style scoped>
/* A4 dimensions */
.a4-page {
    width: 21cm;
    min-height: 29.7cm;
    padding: 2cm;
    margin: 0 auto;
    background: white;
    box-shadow: 0 0 0.5cm rgba(0,0,0,0.1);
}

/* Modal max width override */
:deep(.modal-content) {
    max-width: none !important;
    width: auto !important;
    margin: 0 auto;
}

/* Print styles */
@media print {
    @page {
        size: A4 portrait;
        margin: 0;
    }
    
    body * {
        visibility: hidden;
    }
    
    .modal-content-wrapper {
        padding: 0;
        margin: 0;
        max-height: none;
        width: 21cm;
    }
    
    #print-content,
    #print-content * {
        visibility: visible;
    }
    
    #print-content {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        padding: 2cm;
        width: 21cm;
        min-height: 29.7cm;
        background: white;
    }

    .no-print {
        display: none !important;
    }
}
</style>