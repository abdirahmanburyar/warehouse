<template>
    <AuthenticatedLayout title="Edit Packing List" description="Edit Packing List" img="/assets/images/Instock.png">
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
                        <p class="mt-1 text-sm text-gray-900">{{ form.purchase_order?.supplier?.name }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ form.purchase_order?.supplier?.contact_person }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ form.purchase_order?.supplier?.email }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ form.purchase_order?.supplier?.phone }}</p>
                        <p class="mt-1 text-sm text-gray-900">{{ form.purchase_order?.supplier?.address }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">More Info</h3>
                        <p class="mt-1 text-sm text-gray-900">PL Number #: {{ form.packing_list_number }}</p>
                        <p class="mt-1 text-sm text-gray-900">Ref. No #: <input type="text" :value="form.ref_no" /> </p>
                        <p class="mt-1 text-sm text-gray-900">PL Date. #: <input type="date" :value="form.pk_date" />
                        </p>
                    </div>
                </div>

                <div v-else>
                    <span>No P.O Data found</span>
                </div>
                <!-- Items List -->
                <table class="table w-full" v-if="!isLoading && form">
                    <thead class="bg-gray-50 border border-blck">
                        <tr>
                            <th
                                class="w-[40px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50 z-10">
                                #
                            </th>
                            <th
                                class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky left-[40px]  bg-gray-50 z-10">
                                Item</th>
                            <th class="min-w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Qty</th>
                            <th class="min-w-[180px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Warehouse</th>
                            <th class="min-w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Locations</th>
                            <th class="w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Item Detail</th>
                            <th class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Unit Cost</th>
                            <th class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                Total Cost</th>
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
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2 text-sm text-gray-500 align-top pt-4 sticky left-0 z-10']">
                                {{ index + 1 }}</td>
                            <td
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2 sticky left-[40px] z-10']">
                                <div class="flex flex-col">
                                    {{ item.product?.name }}
                                    <span>UoM: <input type="text" v-model="item.uom" class="border-0" /></span>
                                </div>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                <div class="flex flex-col">
                                    <div>
                                        <input type="number" v-model="item.purchase_order_item.quantity" readonly
                                            :disabled="props.packing_list.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <div>
                                        <label for="quantity" class="text-xs">Received Quantity</label>
                                        <input type="number" v-model="item.quantity"
                                            :disabled="props.packing_list.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500"
                                            @input="handleReceivedQuantityChange(index)">
                                    </div>
                                    <div>
                                        <label for="mismatches" class="text-xs">Mismatches</label>
                                        <input type="text" :value="calculateMismatches(item)" readonly
                                            :disabled="props.packing_list.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <button v-if="calculateFulfillmentRate(item) != 100"
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
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                <Multiselect v-model="item.warehouse" :value="item.warehouse_id"
                                    :options="props.warehouses" :searchable="true" :close-on-select="true"
                                    :show-labels="false" :allow-empty="true" placeholder="Select Warehouse"
                                    track-by="id" :disabled="props.packing_list.status === 'approved'"
                                    :append-to-body="true" label="name" @select="handleWarehouseSelect(index, $event)">
                                </Multiselect>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                {{ props.packing_list.location }}
                                <Multiselect 
                                    :model-value="getLocationForItem(item)"
                                    :disabled="props.packing_list.status == 'approved' || !item.warehouse_id"
                                    :options="[ADD_NEW_LOCATION_OPTION, ...loadedLocation]"
                                    :searchable="true" :close-on-select="true" :show-labels="false" :allow-empty="true"
                                    placeholder="Select Location" track-by="location" label="location"
                                    @select="hadleLocationSelect(index, $event)" :append-to-body="true">
                                </Multiselect>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                <div class="flex flex-col">
                                    <div>
                                        <label for="batch_number" class="text-xs">Batch Number</label>
                                        <input type="text" v-model="item.batch_number"
                                            :disabled="props.packing_list.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <div>
                                        <label for="expire_date" class="text-xs">Expire Date</label>
                                        <input type="date" v-model="item.expire_date"
                                            :min="moment().add(6, 'months').format('YYYY-MM-DD')"
                                            :disabled="props.packing_list.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                    <div>
                                        <label for="barcode" class="text-xs">Barcode</label>
                                        <input type="text" v-model="item.barcode"
                                            :disabled="props.packing_list.status === 'approved'"
                                            class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500">
                                    </div>
                                </div>
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border border-black': !item.status || item.status === 'pending' }]">
                                {{ Number(item.unit_cost).toLocaleString('en-US', {
                                    style: 'currency',
                                    currency: 'USD'
                                }) }}
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': props.packing_list.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border border-black': !item.status || item.status === 'pending' }]">
                                {{ Number(item.total_cost).toLocaleString('en-US', {
                                    style: 'currency',
                                    currency: 'USD'
                                }) }}
                            </td>
                            <td
                                :class="[{ 'border-green-600 border-2': props.packing_list.status == 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-gray-500 border': !item.status || item.status === 'pending' }, 'px-3 py-2 text-left']">
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
                <div class="flex flex-col items-center mb-[100px]">
                    <div class="flex justify-end space-x-3">
                        <div class="mt-6 flex justify-end gap-x-4">
                            <button type="button" @click="reviewPackingList" :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out',
                                hasReviewedItems || hasAllApproved
                                    ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                    : 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2'
                            ]" :disabled="isReviewing || hasReviewedItems || !hasPendingItems || hasAllApproved && $page.props.auth.can.purchase_order_review">
                                <svg v-if="!hasReviewedItems" class="w-5 h-5" fill="none" stroke="currentColor"
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
                                {{ isReviewing && !hasReviewedItems ? 'Reviewing...' : ((hasReviewedItems ||
                                    hasAllApproved) ? 'Reviewed on ' + formatDate(props.packing_list.reviewed_at) : 'Review') }}
                            </button>
                            <button type="button" @click="approvePackingList" :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out',
                                hasAllApproved
                                    ? 'bg-green-50 text-green-700 border border-green-200'
                                    : !hasReviewedItems
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-green-500 focus:ring-offset-2'
                            ]" :disabled="isApproving || !hasReviewedItems || hasAllApproved && !$page.props.auth.can.purchase_order_approve">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                                {{ isApproving && !hasAllApproved ? 'Approving...' : (hasAllApproved ? 'Approved on ' +
                                    formatDate(props.packing_list.approved_at) : 'Approve') }}
                            </button>
                            <button type="button" @click="router.visit(route('supplies.index'))"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white ring-1 ring-inset ring-gray-300 hover:bg-gray-50 shadow-sm transition-all duration-200 ease-in-out">
                                Back
                            </button>
                            <button v-if="!hasAllApproved" type="button" @click="submit"
                                class="flex justify-center items-center gap-x-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="isSubmitting || isApproving || isReviewing">
                                {{ isSubmitting ? "Updating..." : "Update Changes" }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- New Location Modal -->
        <Modal :show="showLocationModal" @close="closeLocationModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Location</h2>
                
                <div class="mb-4">
                    <InputLabel for="warehouse" value="Warehouse" />
                    <TextInput 
                        :modelValue="selectedWarehouse?.name || ''" 
                        class="mt-1 block w-full bg-gray-100" 
                        disabled 
                    />
                </div>
                
                <div class="mb-4">
                    <InputLabel for="location" value="Location Name" />
                    <TextInput 
                        id="location"
                        v-model="newLocation"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Enter location name"
                        :disabled="isNewLocation"
                        required
                    />
                </div>
                
                <div class="flex justify-end space-x-2">
                    <SecondaryButton @click="closeLocationModal" :disabled="isNewLocation">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="createLocation" :disabled="isNewLocation || !newLocation">
                        <span v-if="isNewLocation">Creating...</span>
                        <span v-else>Create Location</span>
                    </PrimaryButton>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-medium text-gray-900">Incomplete Back Orders</h2>
                    </div>

                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm font-medium text-gray-600 mb-2">Item: {{ selectedItem?.product?.name }}</p>
                        <p class="text-sm font-medium text-gray-600 mb-2">Missing Quantity: {{ missingQuantity }}</p>
                        <p class="text-sm font-medium text-gray-600">Remaining to Allocate: {{ remainingToAllocate }}
                        </p>
                    </div>
                </div>

                <h2 v-else class="text-lg font-medium text-gray-900 mb-4">Back Order Details</h2>

                <div class="mb-4 bg-gray-50 p-3 rounded">
                    <p class="text-sm font-medium text-gray-600">Product: {{ selectedItem?.product?.name }}</p>
                    <p class="text-sm font-medium text-gray-600">Expected Quantity: {{
                        selectedItem?.purchase_order_item?.quantity }}</p>
                    <p class="text-sm font-medium text-gray-600">Received Quantity: {{ selectedItem?.quantity || 0 }}
                    </p>
                    <p class="text-sm font-medium text-gray-600">Back Orders: {{ totalExistingDifferences }}</p>
                    <p class="text-sm font-medium text-yellow-800">Actual Mismatches: {{ actualMismatches }}</p>
                </div>

                <div class="overflow-x-auto">
                    <div v-if="error" class="mb-4 bg-red-50 border border-red-200 text-red-600 p-4 rounded">
                        {{ error }}
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(row, index) in backOrderRows" :key="index">
                                <td class="px-3 py-2">
                                    <input type="number" v-model="row.quantity"                                    
                                         :disabled="props.packing_list.status == 'approved'"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        min="1" @input="validateBackOrderQuantities">
                                </td>
                                <td class="px-3 py-2">
                                    <select v-model="row.status"
                                        :disabled="props.packing_list.status == 'approved'"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option v-for="status in ['Missing', 'Damaged', 'Lost', 'Expired', 'Low quality']"
                                            :key="status" :value="status">
                                            {{ status }}
                                        </option>
                                    </select>
                                    <span>{{ row.finalized }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <button @click="removeBackOrderRow(index, row)" v-if="!row.finalized"
                                        :disabled="props.packing_list.status == 'approved'"
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
                            <span :class="{ 'text-green-600': isValidForSave, 'text-red-600': !isValidForSave }">
                                {{ totalBackOrderQuantity }}
                            </span>
                            <span class="text-gray-600"> / {{ selectedItem?.quantity - (selectedItem?.quantity || 0) }}
                                items
                                recorded</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <PrimaryButton @click="showBackOrderModal = false">Exit</PrimaryButton>
                        <PrimaryButton :disabled="props.packing_list.status == 'approved'" @click="attemptCloseModal">Save and Exit</PrimaryButton>
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

const form = ref(props.packing_list || {});
const processing = ref(false);
const isSubmitting = ref(false);
const showBackOrderModal = ref(false);
const showLocationModal = ref(false);
const isLoading = ref(false);
const selectedItemIndex = ref(null);
const error = ref("");
const loadedLocation = ref([]);
const newLocation = ref('');
const showIncompleteBackOrderModal = ref(false);
const selectedItem = ref(null);
const backOrderRows = ref([]);
const isNewLocation = ref(false);
const selectedWarehouse = ref(null);

const hasIncompleteBackOrder = (item) => {
    if (!item?.quantity || item.quantity === item?.purchase_order_item?.quantity) return false;
    const mismatches = item.purchase_order_item.quantity - item.quantity;
    const totalDifferences = (item.differences || []).reduce(
        (total, diff) => total + (parseInt(diff?.quantity) || 0), 0
    );
    return totalDifferences !== mismatches;
};

// Add New Location option constant
const ADD_NEW_LOCATION_OPTION = {
    isAddNew: true,
    location: 'Add New Location'
};

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
    return selectedItem.value.purchase_order_item.quantity - (selectedItem.value.quantity || 0);
});

const missingQuantity = computed(() => {
    if (!selectedItem.value) return 0;
    return selectedItem.value.purchase_order_item.quantity - (selectedItem.value.quantity || 0);
});

const allocatedQuantity = computed(() => {
    return backOrderRows.value.reduce((total, row) => total + (parseInt(row.quantity) || 0), 0);
});

const remainingToAllocate = computed(() => {
    if (!selectedItem.value) return 0;
    const total = selectedItem.value.purchase_order_item.quantity - (selectedItem.value.quantity || 0);
    return total - allocatedQuantity.value;
});

const totalBackOrderQuantity = computed(() => {
    return backOrderRows.value.reduce((total, row) => total + (parseInt(row.quantity) || 0), 0);
});

const isValidForSave = computed(() => {
    if (!selectedItem.value) return false;
    const missingQty = selectedItem.value.quantity - (selectedItem.value.quantity || 0);
    return totalBackOrderQuantity.value <= missingQty;
});

const canAddMoreRows = computed(() => {
    return remainingToAllocate.value > 0;
});

const hasAllApproved = computed(() => {
    return props.packing_list?.status == 'approved';
});

const hasPendingItems = computed(() => {
    return props.packing_list?.status == 'pending';
});

const hasReviewedItems = computed(() => {
    return props.packing_list?.status == 'reviewed';
});

onMounted(async () => {
    // First create a reactive form object
    form.value = props.packing_list;
    form.value.pk_date = moment(form.value.pk_date).format('YYYY-MM-DD');

    // Load locations for existing items that have warehouses selected
    const existingWarehouses = new Set();
    
    form.value.items?.forEach(item => {
        if (item.warehouse?.name) {
            existingWarehouses.add(item.warehouse.name);
        }
    });
    
    // Load locations for all existing warehouses
    for (const warehouseName of existingWarehouses) {
        await loadLocationsByWarehouse(warehouseName);
    }
});

function handleWarehouseSelect(index, selected) {
    form.value.items[index].warehouse_id = selected.id;
    form.value.items[index].warehouse = selected;
    
    // Reset location when warehouse changes
    form.value.items[index].location = null;
    
    // Load locations for the selected warehouse
    if (selected && selected.name) {
        loadLocationsByWarehouse(selected.name);
    }
}

function hadleLocationSelect(index, selected) {
    if (selected.isAddNew) {
        // Check if warehouse is selected
        if (!form.value.items[index].warehouse_id) {
            toast.error('Please select a warehouse first');
            return;
        }

        selectedItemIndex.value = index;
        // Pre-select the warehouse in the modal based on the item's warehouse
        selectedWarehouse.value = form.value.items[index].warehouse;
        showLocationModal.value = true;
        return;
    }
    // Set the location name for backend (packing list items use location name, not location_id)
    form.value.items[index].location = selected.location;
}

function closeLocationModal() {
    showLocationModal.value = false;
    newLocation.value = '';
    selectedWarehouse.value = null;
}

async function createLocation() {
    if (!newLocation.value) {
        toast.error('Please enter a location name');
        return;
    }

    if (!selectedWarehouse.value) {
        toast.error('Please select a warehouse');
        return;
    }

    isNewLocation.value = true;

    await axios.post(route('supplies.store-location'), {
        location: newLocation.value,
        warehouse: selectedWarehouse.value.name
    })
        .then((response) => {
            isNewLocation.value = false;
            const formattedLocation = {
                id: response.data.location.id,
                location: response.data.location.location,
                warehouse: response.data.location.warehouse
            };
            
            // Add to locations array
            props.locations.push(formattedLocation);
            
            // Update the selected item's location (store location name, not ID)
            if (selectedItemIndex.value !== null) {
                form.value.items[selectedItemIndex.value].location = formattedLocation.location;
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
    if (item.quantity > item.purchase_order_item?.quantity) {
        item.quantity = item.purchase_order_item?.quantity;
    }
    calculateTotal(index);
}

function calculateTotal(index) {
    const item = form.value.items[index];
    item.total_cost = item.quantity * item.unit_cost;
}

function calculateMismatches(item) {
    if (!item.purchase_order_item?.quantity || !item.quantity) return 0;
    return item.purchase_order_item?.quantity - item.quantity;
}

function calculateFulfillmentRate(item) {
    if (!item.purchase_order_item?.quantity || !item.quantity) return 0;
    const rate = (item.quantity / item.purchase_order_item?.quantity) * 100;
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

async function loadLocationsByWarehouse(warehouseName) {
    try {
        const response = await axios.get(route('invetnories.getLocations'), {
            params: { warehouse: warehouseName }
        });
        
        loadedLocation.value = response.data;
    } catch (error) {
        console.error('Error loading locations:', error);
        toast.error('Failed to load locations');
        loadedLocation.value = [];
    }
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
    error.value = "";

    // First pass: validate all quantities
    const invalidRow = backOrderRows.value.find(row => {
        const qty = parseFloat(row.quantity);
        return qty !== null && (qty <= 0 || isNaN(qty));
    });

    if (invalidRow) {
        error.value = "Back order quantities must be greater than zero";
        return false;
    }

    // Check if first row has a valid quantity
    if (!backOrderRows.value[0]?.quantity || parseFloat(backOrderRows.value[0].quantity) <= 0) {
        error.value = "The first back order row must have a valid quantity";
        return false;
    }

    // Calculate total differences and validate against mismatches
    const totalDifferences = backOrderRows.value.reduce(
        (total, row) => total + (parseFloat(row.quantity) || 0),
        0
    );

    if (totalDifferences > actualMismatches.value) {
        error.value = `Total back order quantities (${totalDifferences}) cannot exceed the actual mismatches (${actualMismatches.value})`;
        return false;
    }

    return true;

    // // Second pass: clean up empty rows except the last one
    // const newRows = backOrderRows.value.filter((row, index) => {
    //     if (index === backOrderRows.value.length - 1) return true; // Always keep last row
    //     return parseInt(row.quantity) > 0; // Remove other empty rows
    // });

    // // Update the array if rows were removed
    // if (newRows.length !== backOrderRows.value.length) {
    //     backOrderRows.value = newRows;
    // }

    // // Ensure at least one row exists
    // if (backOrderRows.value.length === 0 && remaining > 0) {
    //     addBackOrderRow();
    // }

    // After validation, sync with differences array
    syncBackOrdersWithDifferences();
};


const attemptCloseModal = () => {
    if (!selectedItem.value) {
        error.value = "";
        closeBackOrderModal();
        return;
    }

    // Check for any zero or invalid quantities
    const invalidRow = backOrderRows.value.find(row => {
        const qty = parseFloat(row.quantity);
        return qty !== null && (qty <= 0 || isNaN(qty));
    });

    if (invalidRow) {
        error.value = "All back order quantities must be greater than zero";
        return;
    }

    // Check if first row has a valid quantity
    const firstRow = backOrderRows.value[0];
    if (!firstRow || !firstRow.quantity || parseFloat(firstRow.quantity) <= 0) {
        error.value = 'Please enter a valid quantity for the first back order item';
        return;
    }

    const totalDifferences = backOrderRows.value.reduce((total, row) => total + (parseFloat(row.quantity) || 0), 0);
    const expectedMismatches = selectedItem.value.purchase_order_item?.quantity - (selectedItem.value.quantity || 0);

    if (totalDifferences !== expectedMismatches) {
        showIncompleteBackOrderModal.value = true;
        error.value = 'Please record all mismatched quantities before closing';
        return;
    }

    // Sync differences before closing
    syncBackOrdersWithDifferences();
    toast.success('All mismatches have been recorded');
    closeBackOrderModal();
};

const closeBackOrderModal = () => {
    showBackOrderModal.value = false;

    // If we have a selected item and it has differences, remove it from pending items
    if (selectedItem.value?.differences?.length > 0) {
        const itemIndex = pendingIncompleteItems.value.findIndex(i => i.id === selectedItem.value.id);
        if (itemIndex !== -1) {
            pendingIncompleteItems.value.splice(itemIndex, 1);
            // If there are more pending items, show the dialog again after a short delay
            if (pendingIncompleteItems.value.length > 0) {
                setTimeout(() => {
                    checkAndHandleIncompleteItems();
                }, 500);
            }
        }
    }

    selectedItem.value = null;
    backOrderRows.value = [];
}

const onBackOrderSaved = (item) => {
    // Remove the item from pending incomplete items if it now has differences
    if (item.differences && item.differences.length > 0) {
        const itemIndex = pendingIncompleteItems.value.findIndex(i => i.id === item.id);
        if (itemIndex !== -1) {
            pendingIncompleteItems.value.splice(itemIndex, 1);
        }
    }

    // If there are more pending items, show the dialog again
    if (pendingIncompleteItems.value.length > 0) {
        checkAndHandleIncompleteItems();
    }
};

// Track incomplete items that need back orders
const pendingIncompleteItems = ref([]);

const checkAndHandleIncompleteItems = async () => {
    // Check for incomplete items (received quantity less than expected)
    const incompleteItems = form.value.items.filter(item => {
        if (!item.quantity || item.quantity == item.purchase_order_item.quantity) return false;
        const mismatches = item.purchase_order_item.quantity - item.quantity;
        const totalDifferences = (item.differences || []).reduce(
            (total, diff) => total + (parseInt(diff?.quantity) || 0), 0
        );
        return totalDifferences !== mismatches;
    });

    if (incompleteItems.length > 0) {
        // Initialize pending items if not already set
        if (pendingIncompleteItems.value.length === 0) {
            pendingIncompleteItems.value = [...incompleteItems];
        }

        const itemsList = pendingIncompleteItems.value.map(item =>
            `${item.product.name} (Expected: ${item.purchase_order_item.quantity}, Received: ${item.quantity})`
        ).join('\n');

        const result = await Swal.fire({
            title: 'Incomplete Back Orders',
            html: `The following items still need back orders:<br><br><pre>${itemsList}</pre><br>Please record back orders for these items before proceeding.`,
            icon: 'warning',
            confirmButtonText: 'Continue Recording',
            showCancelButton: true,
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            // Find the next incomplete item that hasn't been handled
            const nextIncompleteIndex = form.value.items.findIndex(item =>
                pendingIncompleteItems.value.includes(item)
            );
            if (nextIncompleteIndex !== -1) {
                showIncompleteBackOrderModal.value = true;
                openBackOrderModal(nextIncompleteIndex);
            }
        }
        return false;
    }
    return true;
};

function formatDate(date) {
    return moment(date).format("DD/MM/YYYY");
}
const submit = async () => {
    if (!form.value?.items?.length) {
        toast.error('No items to submit');
        return;
    }

    // Check for incomplete items first
    const canProceed = await checkAndHandleIncompleteItems();
    if (!canProceed) return;

    // Reset pending items since we can proceed
    pendingIncompleteItems.value = [];

    // Show confirmation dialog
    const confirm = await Swal.fire({
        title: 'Are you sure?',
        text: "You want to update this packing list?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    });

    if (!confirm.isConfirmed) return;

    isSubmitting.value = true;

    // Validate required fields
    const invalidItems = form.value.items.filter(item =>
        !item.warehouse_id ||
        !item.location ||
        !item.batch_number ||
        !item.expire_date ||
        item.quantity === null ||
        item.quantity < 0
    );

    if (invalidItems.length > 0) {
        toast.error('Please fill in all required fields for each item');
        return;
    }


    console.log(form.value);

    await axios.post(route('supplies.packing-list.update'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            console.log(response.data);
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonColor: '#10B981',
            })
                .then(() => {
                    router.visit(route('supplies.packing-list.edit', form.value.id));
                });
        })
        .catch((error) => {
            isSubmitting.value = false;
            console.error('Failed to update packing list:', error);
            toast.error(error.response?.data || 'Failed to update packing list');
        });
}

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

const allStatuses = ['Missing', 'Damaged', 'Lost', 'Expired', 'Low quality'];

const availableStatuses = computed(() => {
    const usedStatuses = new Set(backOrderRows.value.map(row => row.status));
    return allStatuses.filter(status => !usedStatuses.has(status));
});

const isReviewing = ref(false);

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
        isReviewing.value = true;
        try {
            await axios.post(route('supplies.reviewPK'), {
                id: form.value.id,
                status: 'reviewed'
            });

            await Swal.fire({
                title: 'Success!',
                text: 'Items have been reviewed',
                icon: 'success',
                confirmButtonColor: '#10B981',
            })
                .then(() => {
                    router.visit(route('supplies.packing-list.showPK'));
                });

        } catch (error) {
            toast.error(error.response?.data || 'An error occurred while reviewing the items');
        } finally {
            isReviewing.value = false;
        }
    }
}

const isApproving = ref(false);

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
        isApproving.value = true;
        await axios.post(route('supplies.approvePK'), {
            id: form.value.id,
            status: 'approved',
            items: form.value.items
        })
            .then((response) => {
                isApproving.value = false;
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
                isApproving.value = false;
                toast.error(error.response?.data || 'An error occurred while approving the items');
            });
    }
}

function getLocationForItem(item) {
    // If item.location is a string, find the matching location object
    if (typeof item.location === 'string' && item.location) {
        const location = loadedLocation.value.find(loc => loc.location === item.location);
        if (location) {
            return location;
        }
        // If not found in loadedLocation, create a temporary object for display
        return {
            location: item.location,
            warehouse: item.warehouse?.name || '',
            id: 'temp-' + item.location
        };
    }
    
    // If item.location is already an object, return it
    if (item.location && typeof item.location === 'object') {
        return item.location;
    }
    
    return null;
}
</script>