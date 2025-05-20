<template>
    <AuthenticatedLayout title="Edit Packing List" descript="Edit Packing List">
        <Link :href="route('supplies.packing-list.showPK')"
            class="flex items-center text-gray-500 hover:text-gray-700 cursor-pointer">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Supplies
        </Link>
        <!-- Supplier Selection -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 gap-6">

                <div v-if="form" class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Supplier Details</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ form.supplier?.name }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ form.supplier?.contact_person }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ form.supplier?.email }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ form.supplier?.phone }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ form.supplier?.address }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">More Info</h3>
                        <p class="mt-1 text-sm text-gray-900">PL Number #: {{ form.items[0]?.packing_list_number }}</p>
                        <p class="mt-1 text-sm text-gray-900">Ref. No #: <input type="text" :value="form.items[0]?.ref_no"/> </p>
                        <p class="mt-1 text-sm text-gray-900">PL Date. #: <input type="date" :value="form.items[0]?.pk_date"/> </p>
                    </div>
                </div>

                <div v-else>
                    <span>No P.O Data found</span>
                </div>
                <!-- Items List -->
                <table class="table-fixed w-full" style="min-width: 1200px; position: relative; overflow-y: visible;"
                    v-if="!isLoading && form">
                    <thead class="bg-gray-50 border border-blck">
                        <tr>
                            <th
                                class="w-[40px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50 z-10">
                                #
                            </th>
                            <th
                                class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky left-[40px]  bg-gray-50 z-10">
                                Item</th>
                            <th class="w-[150px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Qty</th>
                            <th class="w-[300px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Warehouse</th>
                            <th class="w-[300px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Locations</th>
                            <th class="w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Batch Number</th>
                            <th class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Unit Cost</th>
                            <th class="w-[110px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Fullfillment Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.items" :key="index" :class="[
                            'hover:bg-gray-50',
                            { 'bg-red-50': hasIncompleteBackOrder(item) }
                        ]">
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2 text-sm text-gray-500 align-top pt-4 sticky left-0 z-10']">
                                {{ index + 1 }}</td>
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2 sticky left-[40px] z-10']">
                                <div class="flex flex-col">
                                    {{ item.product?.name }}
                                <span>UoM: <input type="text" v-model="item.uom" class="border-0" /></span>
                                </div>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                <div class="flex flex-col">
                                    <div>
                                        <input type="number" v-model="item.quantity" readonly
                                            :disabled="item.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <div>
                                        <label for="received_quantity" class="text-xs">Received Quantity</label>
                                        <input type="number" v-model="item.received_quantity"
                                            :disabled="item.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500"
                                            @input="handleReceivedQuantityChange(index)">
                                    </div>
                                    <div>
                                        <label for="mismatches" class="text-xs">Mismatches</label>
                                        <input type="text" :value="calculateMismatches(item)" readonly
                                            :disabled="item.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <button v-if="calculateFulfillmentRate(item) < 100"
                                            @click="openBackOrderModal(index)"
                                            class="mt-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                            Back Order
                                        </button>

                                        <!-- Add tooltip for incomplete back orders -->
                                        <div v-if="hasIncompleteBackOrder(item)"
                                            class="mt-8 bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                            Please record the mismatch
                                        </div>
                                </div>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                <Multiselect v-model="item.warehouse" :value="item.warehouse_id"
                                    :options="props.warehouses" :searchable="true" :close-on-select="true"
                                    :show-labels="false" :allow-empty="true" placeholder="Select Warehouse"
                                    track-by="id" :disabled="item.status === 'approved'" :append-to-body="true"
                                    label="name" @select="hadleWarehouseSelect(index, $event)">
                                </Multiselect>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                <Multiselect v-model="item.location" :value="item.location_id"
                                    :disabled="item.status === 'approved'"
                                    :options="[{ id: 'new', name: '+ Add New Location', isAddNew: true }, ...props.locations]"
                                    :searchable="true" :close-on-select="true" :show-labels="false" :allow-empty="true"
                                    placeholder="Select Location" track-by="id" label="location"
                                    @select="hadleLocationSelect(index, $event)">
                                    <template v-slot:option="{ option }">
                                        <div :class="{ 'add-new-option': option.isAddNew }">
                                            <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New
                                                Location</span>
                                            <span v-else>{{ option.location }}</span>
                                        </div>
                                    </template>
                                </Multiselect>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                <div class="flex flex-col">
                                    <div>
                                        <label for="batch_number" class="text-xs">Batch Number</label>
                                        <input type="text" v-model="item.batch_number"
                                            :disabled="item.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <div>
                                        <label for="expire_date" class="text-xs">Expire Date</label>
                                        <input type="date" v-model="item.expire_date"
                                            :min="moment().add(6, 'months').format('YYYY-MM-DD')"
                                            :disabled="item.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <div>
                                        <label for="barcode" class="text-xs">Barcode</label>
                                        <input type="text" v-model="item.barcode"
                                            :disabled="item.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                </div>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border border-black': !item.status || item.status === 'pending' }]">
                                <div class="flex flex-col justify-center items-center">
                                    <div class="flex flex-col">
                                        <label class="text-xs">Unit Cost</label>
                                        {{ item.unit_cost }}
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <label class="text-xs">Total Cost</label>
                                        <div class="text-sm text-gray-900">
                                            {{ Number(item.total_cost).toLocaleString('en-US', {
                                                style: 'currency',
                                                currency: 'USD'
                                            }) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-gray-500 border': !item.status || item.status === 'pending' }, 'px-3 py-2 text-left']">
                                <div class="space-y-2">
                                    <div class="flex items-center flex-col">
                                        <span>{{ calculateFulfillmentRate(item) }}%</span>
                                        
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="form?.items?.length === 0">
                            <td colspan="7" class="px-3 py-4 text-center text-sm text-gray-500">
                                No items added. Click "Add Item" to start creating your purchase order.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Footer -->
                <div class="border-t border-gray-200 px-3 py-4">
                    <div class="flex justify-end items-center">
                        <div class="w-72">
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Subtotal</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ Number(subTotal).toLocaleString('en-US', { style: 'currency', currency: 'USD' })
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
            <div class="flex flex-col items-end mb-[100px]">
                <div class="flex justify-end space-x-3 mb-[80px]">
                    <div class="mt-6 flex justify-end gap-x-4">
                        <button type="button" @click="reviewPackingList" :class="[
                            'inline-flex items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out',
                            hasReviewedItems || hasAllApproved
                                ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                : 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2'
                        ]" :disabled="isSubmitting || hasReviewedItems || !hasPendingItems || hasAllApproved">
                            <svg v-if="hasReviewedItems || hasAllApproved" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            {{ isSubmitting && !hasReviewedItems ? 'Reviewing...' : ((hasReviewedItems || hasAllApproved) ? 'Reviewed' : 'Review') }}
                        </button>
                        <button type="button" @click="approvePackingList" :class="[
                            'inline-flex items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out',
                            hasAllApproved
                                ? 'bg-green-50 text-green-700 border border-green-200'
                                : !hasReviewedItems
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : 'bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-green-500 focus:ring-offset-2'
                        ]" :disabled="isSubmitting || !hasReviewedItems || hasAllApproved">
                            <svg v-if="hasAllApproved" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg v-else-if="!hasReviewedItems" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m0 0v2m0-2h2m-2 0H8m4-6V4"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            {{ isSubmitting && !hasAllApproved ? 'Approving...' : (hasAllApproved ? 'Approved' : 'Approve') }}
                        </button>
                        <button type="button" @click="router.visit(route('supplies.index'))" :disabled="isSubmitting"
                            class="inline-flex items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white ring-1 ring-inset ring-gray-300 hover:bg-gray-50 shadow-sm transition-all duration-200 ease-in-out">
                            Back
                        </button>
                        <button v-if="!hasAllApproved" type="button" @click="submit"
                            class="flex justify-center items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isSubmitting">
                            {{ isSubmitting ? "Saving..." : "Save Changes" }}
                        </button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Status Information</h3>
                    <div class="mt-2 space-y-2 text-sm">
                        <div v-if="form?.value?.reviewed_by" class="flex items-center gap-x-2 text-amber-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Reviewed on {{ formatDate(form.value.reviewed_at) }}</span>
                        </div>
                        <div v-if="form?.value?.approved_by" class="flex items-center gap-x-2 text-green-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Approved on {{ formatDate(form.value.approved_at) }}</span>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <!-- New Location Modal -->
        <Modal :show="showLocationModal" @close="showLocationModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Location</h2>
                <div class="mt-6">
                    <InputLabel for="new_location" value="Location Name" />
                    <TextInput id="new_location" type="text" class="mt-1 block w-full" v-model="newLocation" required />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showLocationModal = false" :disabled="isNewLocation">Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="isNewLocation" @click="createLocation">{{ isNewLocation ? "Waiting..." :
                        "Create new location" }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Back Order Modal -->
        <Modal :show="showBackOrderModal" @close="attemptCloseModal" maxWidth="2xl">
            <div class="p-6">
                <div v-if="showIncompleteBackOrderModal" class="mb-6">
                    <div class="flex items-center mb-4">
                        <div class="rounded-full bg-yellow-100 p-3 mr-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-medium text-gray-900">Incomplete Back Orders</h2>
                    </div>

                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 mb-2">Item: {{ selectedItem?.product?.name }}</p>
                        <p class="text-sm font-medium text-gray-600 mb-2">Missing Quantity: {{ missingQuantity }}</p>
                        <p class="text-sm font-medium text-gray-600">Remaining to Allocate: {{ remainingToAllocate }}</p>
                    </div>
                </div>

                <h2 v-else class="text-lg font-medium text-gray-900 mb-4">Back Order Details</h2>

                <div class="mb-4 bg-gray-50 p-3 rounded">
                    <p class="text-sm font-medium text-gray-600">Product: {{ selectedItem?.product?.name }}</p>
                    <p class="text-sm font-medium text-gray-600">Expected Quantity: {{ selectedItem?.quantity }}</p>
                    <p class="text-sm font-medium text-gray-600">Received Quantity: {{ selectedItem?.received_quantity || 0 }}</p>
                    <p class="text-sm font-medium text-gray-600">Back Orders: {{ totalExistingDifferences }}</p>
                    <p class="text-sm font-medium text-yellow-800">Actual Mismatches: {{ actualMismatches }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(row, index) in backOrderRows" :key="index">
                                <td class="px-3 py-2">
                                    <input type="number" v-model="row.quantity"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        min="0" @input="validateBackOrderQuantities">
                                </td>
                                <td class="px-3 py-2">
                                    <select v-model="row.status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option v-for="status in ['Missing', 'Damaged', 'Expired', 'Lost']" 
                                                :key="status" 
                                                :value="status">
                                            {{ status }}
                                        </option>
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <button @click="removeBackOrderRow(index, row)"
                                        class="text-red-600 hover:text-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <button @click="addBackOrderRow"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!canAddMoreRows">
                            Add Row
                        </button>
                        <div class="text-sm">
                            <span :class="{'text-green-600': isValidForSave, 'text-red-600': !isValidForSave}">
                                {{ totalBackOrderQuantity }}
                            </span>
                            <span class="text-gray-600"> / {{ selectedItem?.quantity - (selectedItem?.received_quantity || 0) }} items recorded</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <PrimaryButton @click="attemptCloseModal">Save and Exit</PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import axios from 'axios';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import moment from 'moment';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    warehouses: {
        required: true,
        type: Array
    },
    locations: {
        required: true,
        type: Array
    },
    packing_list: {
        required: true,
        type: Object
    }
})

const form = ref(null);
const processing = ref(false);
const isSubmitting = ref(false);
const showLocationModal = ref(false);
const isLoading = ref(false);

const hasIncompleteBackOrder = (item) => {
    if (!item?.received_quantity || item.received_quantity === item?.quantity) return false;
    const mismatches = item.quantity - item.received_quantity;
    const totalDifferences = (item.differences || []).reduce(
        (total, diff) => total + (parseInt(diff?.quantity) || 0), 0
    );
    return totalDifferences !== mismatches;
};

const showBackOrderModal = ref(false);
const newLocation = ref('');
const showIncompleteBackOrderModal = ref(false);
const selectedItem = ref(null);
const backOrderRows = ref([]);
const isNewLocation = ref(false);

const hasNotApprovedItems = computed(() => {
    return form.value?.items?.some(item => item.status != 'approved') ?? false;
});

const subTotal = computed(() => {
    return form.value?.items?.reduce((sum, i) => sum + i.total_cost || 0, 0) || 0;
});

const totalExistingDifferences = computed(() => {
    if (!selectedItem.value?.differences) return 0;
    return selectedItem.value.differences.reduce((total, diff) => total + (parseInt(diff.quantity) || 0), 0);
});

const actualMismatches = computed(() => {
    if (!selectedItem.value) return 0;
    return selectedItem.value.quantity - (selectedItem.value.received_quantity || 0);
});

const missingQuantity = computed(() => {
    if (!selectedItem.value) return 0;
    return selectedItem.value.quantity - (selectedItem.value.received_quantity || 0);
});

const allocatedQuantity = computed(() => {
    return backOrderRows.value.reduce((total, row) => total + (parseInt(row.quantity) || 0), 0);
});

const remainingToAllocate = computed(() => {
    if (!selectedItem.value) return 0;
    const total = selectedItem.value.quantity - (selectedItem.value.received_quantity || 0);
    return total - allocatedQuantity.value;
});

const totalBackOrderQuantity = computed(() => {
    return backOrderRows.value.reduce((total, row) => total + (parseInt(row.quantity) || 0), 0);
});

const isValidForSave = computed(() => {
    if (!selectedItem.value) return false;
    const missingQty = selectedItem.value.quantity - (selectedItem.value.received_quantity || 0);
    return totalBackOrderQuantity.value <= missingQty;
});

const canAddMoreRows = computed(() => {
    return remainingToAllocate.value > 0;
});

const hasAllApproved = computed(() => {
    return form.value?.items?.every(item => item.status === 'approved');
});

const hasPendingItems = computed(() => {
    return form.value?.items?.some(item => item.status == 'pending');
});

const hasReviewedItems = computed(() => {
    return form.value?.items?.every(item => item.status == 'reviewed');
});

onMounted(() => {
    // Initialize form with packing list data
    const { packing_lists, ...poData } = props.packing_list;

    // First create a reactive form object
    form.value = {
        ...poData,
        items: []
    };


    // Then set the items
    if (packing_lists?.length > 0) {

        form.value.items = packing_lists.map(item => ({
            id: item.id,
            product_id: item.product_id,
            product: item.product,
            warehouse_id: item.warehouse_id,
            warehouse: item.warehouse,
            location_id: item.location_id,
            location: item.location,
            quantity: item.po_item?.quantity,
            received_quantity: item.quantity,
            unit_cost: item.unit_cost,
            total_cost: item.total_cost,
            batch_number: item.batch_number || '',
            expire_date: item.expire_date || '',
            barcode: item.barcode || '',
            uom: item.uom || '',
            packing_list_number: item.packing_list_number,
            ref_no: item.ref_no,
            pk_date: item.pk_date,
            status: item.status || 'pending',
            created_at: item.created_at,
            differences: item.differences
        }));
        
    }
    // Remove the packing_lists array to avoid duplication
    delete form.value.packing_lists;
});

function handleWarehouseSelect(index, selected) {
    if (selected.isAddNew) {
        form.value.items[index].warehouse_id = "";
        form.value.items[index].warehouse = null;
        return;
    }
    form.value.items[index].warehouse_id = selected.id;
    form.value.items[index].warehouse = selected;
}

function hadleLocationSelect(index, selected) {
    if (selected.isAddNew) {
        selectedItemIndex.value = index;
        showLocationModal.value = true;
        return;
    }
    form.value.items[index].location_id = selected.id;
    form.value.items[index].location = selected;
}

function closeLocationModal() {
    showLocationModal.value = false;
    newLocation.value = '';
}

async function createLocation() {
    if (!newLocation.value) {
        toast.error('Please enter a location name');
        return;
    }
    isNewLocation.value = true;

    await axios.post(route('supplies.packing-list.location.store'), {
        location: newLocation.value
    })
        .then((response) => {
            isNewLocation.value = false;
            const newLocationData = response.data.location;
            props.locations.push(newLocationData);
            // Update the selected item's location
            if (selectedItemIndex.value !== null) {
                form.value.items[selectedItemIndex.value].location_id = newLocationData.id;
                form.value.items[selectedItemIndex.value].location = newLocationData;
            }
            toast.success(response.data.message);
            closeLocationModal();
        })
        .catch((error) => {
            isNewLocation.value = false;
            toast.error(error.response?.data || 'An error occurred while adding the location');
        });
}

function handleReceivedQuantityChange(index) {
    const item = form.value.items[index];
    // Ensure received quantity doesn't exceed total quantity
    if (item.received_quantity > item.quantity) {
        item.received_quantity = item.quantity;
    }
    calculateTotal(index);
}

function calculateTotal(index) {
    const item = form.value.items[index];
    item.total_cost = item.received_quantity * item.unit_cost;
}

function calculateMismatches(item) {
    if (!item.quantity || !item.received_quantity) return 0;
    return item.quantity - item.received_quantity;
}

function calculateFulfillmentRate(item) {
    if (!item.quantity || !item.received_quantity) return 0;
    const rate = (item.received_quantity / item.quantity) * 100;
    return rate.toFixed(2);
}

function openBackOrderModal(index) {
    const item = form.value.items[index];
    if (!item) return;

    selectedItem.value = item;

    // Initialize backOrderRows with existing differences or a new row
    backOrderRows.value = item.differences?.length > 0
        ? [...item.differences] // Create a copy of existing differences
        : [{
            id: null,
            quantity: 0,
            status: 'Missing'
        }];

    showBackOrderModal.value = true;
}

const syncBackOrdersWithDifferences = () => {
    if (!selectedItem.value) return;
    
    // Filter out rows with zero quantity
    const validRows = backOrderRows.value.filter(row => parseInt(row.quantity) > 0);
    
    // Update the differences array
    selectedItem.value.differences = validRows.map(row => ({
        id: row.id,
        quantity: parseInt(row.quantity),
        status: row.status
    }));

    const itemIndex = form.value.items.findIndex(item => item === selectedItem.value);
    if (itemIndex === -1) return;

    // Update the differences array with current back orders
    form.value.items[itemIndex].differences = backOrderRows.value.map(row => ({
        id: row.id ?? null,
        quantity: parseInt(row.quantity) || 0,
        status: row.status
    }));
};

const validateBackOrderQuantities = () => {
    let remaining = missingQuantity.value;

    // First pass: validate and adjust quantities
    backOrderRows.value.forEach((row, index) => {
        let qty = parseInt(row.quantity);
        if (isNaN(qty) || qty < 0) {
            qty = 0;
        } else if (qty > remaining) {
            qty = remaining;
        }
        remaining -= qty;
        row.quantity = qty;
    });

    // Second pass: clean up empty rows except the last one
    const newRows = backOrderRows.value.filter((row, index) => {
        if (index === backOrderRows.value.length - 1) return true; // Always keep last row
        return parseInt(row.quantity) > 0; // Remove other empty rows
    });

    // Update the array if rows were removed
    if (newRows.length !== backOrderRows.value.length) {
        backOrderRows.value = newRows;
    }

    // Ensure at least one row exists
    if (backOrderRows.value.length === 0 && remaining > 0) {
        addBackOrderRow();
    }

    // After validation, sync with differences array
    syncBackOrdersWithDifferences();
};

const closeBackOrderModal = () => {
    showBackOrderModal.value = false;
    selectedItem.value = null;
    backOrderRows.value = [];
};

const attemptCloseModal = () => {
    if (!selectedItem.value) {
        closeBackOrderModal();
        return;
    }

    const totalDifferences = backOrderRows.value.reduce((total, row) => total + (parseInt(row.quantity) || 0), 0);
    const expectedMismatches = selectedItem.value.quantity - (selectedItem.value.received_quantity || 0);

    if (totalDifferences !== expectedMismatches) {
        showIncompleteBackOrderModal.value = true;
        toast.error('Please record all mismatched quantities before closing');
        return;
    }

    toast.success('All mismatches have been recorded');
    closeBackOrderModal();
};

const addBackOrderRow = () => {
    backOrderRows.value.push({
        id: null,
        quantity: 0,
        status: 'Missing'
    });
};

const removeBackOrderRow = async (index, item) => {
    try {
        if (item.id) {
            // If item has an ID, delete it from the server first
            const response = await axios.get(route('supplies.deletePackingListDiff', item.id));
            if (!response.data.message) {
                throw new Error(response.data || 'Failed to delete back order');
            }
        }

        // Remove from local array
        const newRows = [...backOrderRows.value];
        if (newRows[index]) {
            newRows.splice(index, 1);
            backOrderRows.value = newRows;
        }
        
        // After successful removal, sync the differences
        await nextTick();
        syncBackOrdersWithDifferences();
        
        // Check if we need to add a new row
        await nextTick();
        const remaining = remainingToAllocate.value;
        if (remaining > 0 && backOrderRows.value.length === 0) {
            addBackOrderRow();
        }
    } catch (error) {
        console.log(error);
        toast.error(error.response?.data || 'Error removing back order');
    }
};

const allStatuses = ['Missing', 'Damaged', 'Lost', 'Expired'];

const availableStatuses = computed(() => {
    const usedStatuses = new Set(backOrderRows.value.map(row => row.status));
    return allStatuses.filter(status => !usedStatuses.has(status));
});

async function reviewPackingList() {
    const confirm = await Swal.fire({
        title: 'Review Packing List',
        text: 'Are you sure you want to mark these items as reviewed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, review it!'
    });

    if (confirm.isConfirmed) {
        isSubmitting.value = true;
        try {
            await axios.post(route('supplies.packing-list.review'), {
                id: form.value.id,
                items: form.value.items.filter(item => item.status === 'pending').map(item => ({
                    id: item.id,
                    status: 'reviewed'
                }))
            });

            await Swal.fire({
                title: 'Success!',
                text: 'Items have been reviewed',
                icon: 'success',
                confirmButtonColor: '#10B981',
            });

            router.visit(route('supplies.packing-list.showPK'));
        } catch (error) {
            toast.error(error.response?.data || 'An error occurred while reviewing the items');
        } finally {
            isSubmitting.value = false;
        }
    }
}

async function approvePackingList() {
    const confirm = await Swal.fire({
        title: 'Approve Packing List',
        text: 'Are you sure you want to approve these items?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!'
    });

    if (confirm.isConfirmed) {
        isSubmitting.value = true;
        await axios.post(route('supplies.packing-list.approve'), {
            id: form.value.id,
            items: form.value.items.filter(item => item.status === 'reviewed').map(item => ({
                id: item.id,
                status: 'approved',
                quantity: item.quantity,
                received_quantity: item.received_quantity,
                product_id: item.product_id,
            }))
        })
            .then((response) => {
                isSubmitting.value = false;
                Swal.fire({
                    title: 'Success!',
                    text: 'Items have been approved',
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                }).then(() => {
                    router.visit(route('supplies.packing-list.showPK'));
                });
            })
            .catch((error) => {
                console.log(error);
                isSubmitting.value = false;
                toast.error(error.response?.data || 'An error occurred while approving the items');
            });
    }
}



async function submit() {
    if (!form.value?.items?.length) {
        toast.error('No items to submit');
        return;
    }

    // Check for incomplete back orders
    const incompleteItems = form.value.items.filter(item => {
        if (!item.received_quantity || item.received_quantity === item.quantity) return false;
        const mismatches = item.quantity - item.received_quantity;
        const totalDifferences = (item.differences || []).reduce(
            (total, diff) => total + (parseInt(diff.quantity) || 0), 0
        );
        return totalDifferences !== mismatches;
    });

    if (incompleteItems.length > 0) {
        // Find the index of the first incomplete item
        const incompleteIndex = form.value.items.findIndex(item => 
            incompleteItems.includes(item)
        );
        
        // Open the back order modal for the first incomplete item
        if (incompleteIndex !== -1) {
            showIncompleteBackOrderModal.value = true;
            openBackOrderModal(incompleteIndex);
            return;
        }
    }

    const confirm = await Swal.fire({
        title: 'Are you sure?',
        text: "You want to update this packing list?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    });

    if (confirm.isConfirmed) {
        isSubmitting.value = true;

        // Validate required fields
        const invalidItems = form.value.items.filter(item => 
            !item.warehouse_id || 
            !item.location_id || 
            !item.batch_number || 
            !item.expire_date || 
            item.received_quantity === null || 
            item.received_quantity < 0
        );

        if (invalidItems.length > 0) {
            isSubmitting.value = false;
            toast.error('Please fill in all required fields for each item');
            return;
        }
        

        try {
            const response = await axios.post(route('supplies.packing-list.update'), form.value);
            await Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonColor: '#10B981',
            });
            router.visit(route('supplies.packing-list.edit', props.packing_list?.id));
        } catch (error) {
            toast.error(error.response?.data || 'An error occurred while updating the packing list');
        } finally {
            isSubmitting.value = false;
        }
    }
}

</script>