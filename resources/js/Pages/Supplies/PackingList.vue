<template>
    <AuthenticatedLayout title="Packing List">
        <Link :href="route('supplies.packing-list.showPK')"
            class="flex items-center text-gray-500 hover:text-gray-700 cursor-pointer mb-4">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Supplies
        </Link>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 gap-6">
                <div class="w-[400px] mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select P.O
                    </label>
                    <Multiselect v-model="form" :value="form?.purchase_order_id" :options="props.purchaseOrders"
                        :searchable="true" :close-on-select="true" :show-labels="false" :allow-empty="true"
                        placeholder="Select supplier" track-by="id" label="po_number" @select="handlePOSelect">
                    </Multiselect>
                </div>
                <h2 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h2>

              <!-- halkaan ku soo celi -->
              <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                    <div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-24 mb-3"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-28"></div>
                    </div>
                    <div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-3"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-40 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-36 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-44"></div>
                    </div>
                    <div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-36 mb-3"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-28 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-32"></div>
                    </div>
                </div>

                <div v-else-if="!isLoading && form"
                    class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                    
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
                        <h3 class="text-sm font-medium text-gray-500">Purchase Order Info</h3>
                        <p class="mt-1 text-sm text-gray-900">PL No. #: <input type="text"
                                v-model="form.packing_list_number" class="border-0" /></p>
                        <p class="mt-1 text-sm text-gray-900">Ref. No. #: <input type="text" v-model="form.ref_no"
                                class="border-0" /></p>
                        <div class="mt-1 flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <span>P.O Data:</span> {{ moment(form.po_date).format('DD/MM/YYYY') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span>PL Data:</span>
                                <input type="date" v-model="form.pk_date" class="border-0" :min="form.po_date" />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Memo Field -->
                <div v-if="!isLoading && form" class="mt-4 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Memo</h3>
                    <textarea 
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Enter memo or additional notes here..."></textarea>
                </div>
                <div v-else>
                    <span>No P.O Data found</span>
                </div>
              <!-- halkaan ku past karee -->
                 <!-- Items List -->
                 <div class="mt-4 w-full">
                    <table class="w-full table-fixed whitespace-nowrap"
                        v-if="!isLoading && form">
                        <colgroup>
                            <col class="w-12">
                            <col class="w-48">
                            <col class="w-[150px]">
                            <col class="w-[200px]">
                            <col class="w-48">
                            <col class="w-48">
                            <col class="w-32">
                            <col class="w-32">
                            <col class="w-32">
                        </colgroup>
                        <thead class="bg-gray-50 border border-black">
                            <tr>
                                <th
                                    class="text-left text-xs text-black capitalize sticky left-0 bg-gray-50 z-10">
                                    #
                                </th>
                                <th
                                    class="text-left text-xs text-black capitalize sticky left-12 bg-gray-50 z-10">
                                    Item</th>
                                <th class="text-left text-xs text-black capitalize">
                                    Qty</th>
                                <th class="text-left text-xs text-black capitalize">
                                    S. Location</th>
                                <th class="text-left text-xs text-black capitalize">
                                   St. Locations</th>
                                <th class="text-left text-xs text-black capitalize">
                                    Item Detail</th>
                                <th class="text-left text-xs text-black capitalize">
                                    Unit Cost</th>
                                <th class="text-left text-xs text-black capitalize">
                                    Total Cost</th>
                                <th class="text-left text-xs text-black capitalize">
                                    Fullfillment Rate</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs">
                            <tr v-for="(item, index) in form.items" :key="index" :class="[
                                'hover:bg-gray-50',
                                { 'bg-red-50': hasIncompleteBackOrder(item) },
                                { 'border-red-500 border-2': item.hasError },
                                { 'bg-red-50/20': item.hasError }
                            ]" :data-row="index + 1">
                                <td :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'text-sm text-gray-500 align-top pt-4 sticky left-0 z-10']">{{ index + 1 }}</td>
                                <td
                                    :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'sticky left-[40px] z-10']">
                                    <div class="flex flex-col">
                                        {{ item.product?.name }}
                                        <span>UoM:  <input type="text" v-model="item.uom" readonly class="border-0"/></span>
                                    </div>

                                </td>
                                <td
                                    :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                    <div class="flex flex-col">
                                        <div>
                                            <input type="number" v-model="item.quantity" readonly
                                                class="block w-full text-left text-black focus:ring-0 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="received_quantity" class="text-xs">Received Quantity</label>
                                            <input type="number" v-model="item.received_quantity" required min="1"
                                                :disabled="item.status == 'approved'"
                                                class="block w-full text-left text-black focus:ring-0 sm:text-sm"
                                                @input="handleReceivedQuantityChange(index)">
                                        </div>
                                        <div>
                                            <label for="mismatches" class="text-xs">Mismatches</label>
                                            <input type="text" :value="calculateMismatches(item)" readonly
                                                class="block w-full text-left text-black focus:ring-0 sm:text-sm">
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
                                    :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }]">
                                    <Multiselect v-model="item.warehouse" :value="item.warehouse_id"
                                        :options="props.warehouses" :searchable="true" :close-on-select="true"
                                        :show-labels="false" :allow-empty="true" placeholder="Select Warehouse" required
                                        track-by="id" :disabled="item.status === 'approved'" :append-to-body="true"
                                        label="name" @select="hadleWarehouseSelect(index, $event)">
                                    </Multiselect>
                                </td>
                                <td
                                    :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-black border': !item.status || item.status === 'pending' }]">
                                    <Multiselect v-model="item.location" :value="item.location_id" required
                                        :disabled="item.status === 'approved' || !item.warehouse_id"
                                        :options="[ADD_NEW_LOCATION_OPTION, ...loadedLocation]"
                                        :searchable="true" :close-on-select="true" :show-labels="false" :allow-empty="true"
                                        placeholder="Select Location"
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
                                            <input type="text" v-model="item.batch_number" required
                                                :disabled="item.status === 'approved'"
                                                class="block w-full text-left text-black focus:ring-0 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="expire_date" class="text-xs">Expire Date</label>
                                            <input type="date" :value="formatDateForInput(item.expire_date)" required
                                                @input="item.expire_date = $event.target.value"
                                                :min="moment().add(6, 'months').format('YYYY-MM-DD')"
                                                :disabled="item.status === 'approved'"
                                                class="block w-full text-left text-black focus:ring-0 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="barcode" class="text-xs">Barcode</label>
                                            <input type="text" v-model="item.barcode" required
                                                :disabled="item.status === 'approved'"
                                                class="block w-full text-left text-black focus:ring-0 sm:text-sm">
                                        </div>
                                    </div>
                                </td>
                                <td
                                    :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border border-black': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                    {{ Number(item.unit_cost).toLocaleString('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }) }}
                                </td>
                                <td
                                    :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border border-black': !item.status || item.status === 'pending' }, 'px-3 py-2']">
                                    {{ Number(item.total_cost).toLocaleString('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }) }}
                                </td>
                                <td
                                    :class="[{ 'border-green-600 border-2': item.status === 'approved' }, { 'border-yellow-500 border-2': item.status === 'reviewed' }, { 'border-gray-500 border': !item.status || item.status === 'pending' }, 'text-left']">
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
                </div>

                <!-- Back Order Modal -->
                <Modal :show="showBackOrderModal" @close="attemptCloseModal" maxWidth="2xl">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Back Order Details</h2>

                        <div class="mb-4 bg-gray-50 p-3 rounded">
                            <p class="text-sm font-medium text-gray-600">Product: {{ selectedItem?.product?.name }}</p>
                            <p class="text-sm font-medium text-gray-600">Expected Quantity: {{ selectedItem?.quantity }}
                            </p>
                            <p class="text-sm font-medium text-gray-600">Received Quantity: {{
                                selectedItem?.received_quantity || 0 }}
                            </p>
                            <p class="text-sm font-medium text-gray-600">Back Orders: {{ totalExistingDifferences }}</p>
                            <p class="text-sm font-medium text-yellow-800">Actual Mismatches: {{ actualMismatches }}</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase">
                                            Quantity</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase">
                                            Status</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase">
                                            Note</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(row, index) in backOrderRows" :key="index">
                                        <td class="px-3 py-2">
                                            <input type="number" v-model="row.quantity" :disabled="row.finalized != null"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                min="0">
                                                <!-- min="0" @input="validateBackOrderQuantities"> -->
                                        </td>
                                        <td class="px-3 py-2">
                                            <select v-model="row.status"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option
                                                    v-for="status in [row.status, ...availableStatuses.filter(s => s !== row.status)]"
                                                    :key="status" :value="status">
                                                    {{ status }}
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" v-model="row.note" 
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                placeholder="Enter note...">
                                        </td>
                                        <td class="px-3 py-2">
                                            <button @click="removeBackOrderRow(index, row)"
                                                class="text-red-600 hover:text-red-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
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
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!canAddMoreRows">
                                    Add Row
                                </button>
                                <div class="text-sm">
                                    <span :class="{ 'text-green-600': isValidForSave, 'text-red-600': !isValidForSave }">
                                        {{ totalBackOrderQuantity }}
                                    </span>
                                    <span class="text-gray-600"> / {{ selectedItem?.quantity -
                                        (selectedItem?.received_quantity || 0) }}
                                        items recorded</span>
                                </div>
                            </div>

                            <div>
                                <PrimaryButton @click="attemptCloseModal">Save and Exit</PrimaryButton>
                            </div>
                        </div>
                    </div>
                </Modal>
                <!-- New Location Modal -->
                <Modal :show="showLocationModal" @close="showLocationModal = false">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900">Add New Location</h2>
                        <div class="mt-6">
                            <InputLabel for="new_location" value="Location Name" />
                            <TextInput id="new_location" type="text" class="mt-1 block w-full" v-model="newLocation" required />
                        </div>
                        <div class="mt-6">
                            <InputLabel for="warehouse_id" value="Warehouse" />
                            <Multiselect v-model="selectedWarehouse" :options="props.warehouses" :searchable="true" 
                                :close-on-select="true" :show-labels="false" :allow-empty="false"
                                placeholder="Select Warehouse" track-by="id" label="name" required>
                            </Multiselect>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <SecondaryButton @click="showLocationModal = false" :disabled="isNewLocation">Cancel
                            </SecondaryButton>
                            <PrimaryButton :disabled="isNewLocation || !selectedWarehouse" @click="createLocation">{{ isNewLocation ? "Waiting..." :
                                "Create new location" }}</PrimaryButton>
                        </div>
                    </div>
                </Modal>
                <div class="flex justify-end space-x-3">
                    <Link :href="route('supplies.index')" :disabled="isSubmitting"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Exit
                    </Link>
                    <button @click="submit" :disabled="isSubmitting || !canSubmit" :title="submitButtonTitle"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ isSubmitting ? "Saving..." : "Save and Exit" }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

import { useToast } from 'vue-toastification';

const toast = useToast();

// Add New Location option constant
const ADD_NEW_LOCATION_OPTION = {
    isAddNew: true,
    location: 'Add New Location'
};

const props = defineProps({
    purchaseOrders: {
        type: Array,
        required: true
    },
    warehouses: {
        required: true,
        type: Array
    }
})

const form = ref(null)

const isLoading = ref(false);
const isSubmitting = ref(false);
const showLocationModal = ref(false);
const newLocation = ref('');
const selectedItemIndex = ref(null);
const selectedWarehouse = ref(null);
const loadedLocation = ref([]);


function hadleWarehouseSelect(index, selected) {
    if (selected.isAddNew) {
        form.value.items[index].warehouse_id = "";
        form.value.items[index].warehouse = null;
        form.value.items[index].location = null;
        loadedLocation.value = [];
        return;
    }
    form.value.items[index].warehouse_id = selected.id;
    form.value.items[index].warehouse = selected;
    form.value.items[index].location = null;
    loadedLocation.value = [];
    loadLocations(selected.name);
}

async function loadLocations(warehouse) {
    if (!warehouse) {
        loadedLocation.value = [];
        location.value = '';
        return;
    };
    await axios
        .get(route("invetnories.getLocations"), {
            params: {
                warehouse: warehouse,
            }
        })
        .then((response) => {

            loadedLocation.value = response.data;
        })
        .catch((error) => {
            toast.error(error.response.data);
        });
}


const subTotal = computed(() => {
    return form.value?.items?.reduce((sum, i) => sum + i.total_cost || 0, 0) || 0;
})

function hadleLocationSelect(index, selected) {
    console.log(selected);
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

const isNewLocation = ref(false);

// Function to filter locations based on warehouse_id
function getFilteredLocations(warehouseId) {
    if (!warehouseId) return [];
    return props.locations.filter(location => location.warehouse_id === warehouseId);
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

    try {
        const response = await axios.post(route('supplies.store-location'), {
            location: newLocation.value,
            warehouse: selectedWarehouse.value.name
        });
        
        isNewLocation.value = false;
        const newLocationData = response.data.location;
        
        // Ensure the location data has the correct structure
        const formattedLocation = {
            id: newLocationData.id,
            location: newLocationData.location,
            warehouse: newLocationData.warehouse
        };
        
        // Add to loadedLocation array (for the current warehouse)
        loadedLocation.value.push(formattedLocation);
        
        // Update the selected item's location
        if (selectedItemIndex.value !== null) {
            form.value.items[selectedItemIndex.value].location = formattedLocation.location;
        }
        
        toast.success(response.data.message);
        closeLocationModal();
    } catch (error) {
        isNewLocation.value = false;
        console.error('Location creation error:', error);
        toast.error(error.response?.data || 'An error occurred while adding the location');
    }
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


async function handlePOSelect(selected) {
    if (!selected) {
        form.value = null;
        return;
    }
    isLoading.value = true;
    await axios.get(route('supplies.get-purchaseOrder', selected.id))
        .then((response) => {
            isLoading.value = false;
            form.value = response.data;
        })
        .catch((error) => {
            isLoading.value = false;
            console.log(error.response);
            toast.error(error);
        })
}
   
const validateForm = () => {
    let hasErrors = false;
    let errorItems = [];

    form.value.items.forEach((item, index) => {
        item.hasError = false;
        item.errorMessages = [];

        // Required field validation
        const requiredFields = [
            { field: 'received_quantity', message: 'Received quantity is required' },
            { field: 'warehouse_id', message: 'Warehouse selection is required' },
            { field: 'location_id', message: 'Location selection is required' },
            { field: 'batch_number', message: 'Batch number is required' },
            { field: 'expire_date', message: 'Expiry date is required' },
            { field: 'barcode', message: 'Barcode is required' },
            { field: 'uom', message: 'UOM is required' }
        ];

        requiredFields.forEach(({ field, message }) => {
            if (!item[field]) {
                item.hasError = true;
                item.errorMessages.push(message);
            }
        });

        // Additional validations
        if (item.received_quantity && item.received_quantity <= 0) {
            item.hasError = true;
            item.errorMessages.push('Received quantity must be greater than 0');
        }

        if (item.expire_date && new Date(item.expire_date) <= new Date()) {
            item.hasError = true;
            item.errorMessages.push('Expiry date must be in the future');
        }

        if (item.hasError) {
            hasErrors = true;
            errorItems.push(index + 1); // Add 1 for human-readable row numbers
        }
    });

    if (hasErrors) {
        // Show global error message with row numbers
        toast.error(`Please fix the errors in rows: ${errorItems.join(', ')}`);
        
        // Scroll to the first error row
        if (errorItems.length > 0) {
            const firstErrorRow = document.querySelector(`tr[data-row="${errorItems[0]}"]`);
            if (firstErrorRow) {
                firstErrorRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    return !hasErrors;
};

// Format date to YYYY-MM-DD for HTML date inputs
function formatDateForInput(dateString) {
    if (!dateString) return '';
    
    // If it's already in YYYY-MM-DD format, return as is
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
        return dateString;
    }
    
    // Parse the date and format it as YYYY-MM-DD
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return ''; // Invalid date
    
    return date.toISOString().split('T')[0];
}

async function submit() {
    if (!form.value?.items?.length) {
        toast.error('No items to submit');
        return;
    }
    console.log(form.value);
    

    // Check for incomplete back orders
    const incompleteItems = form.value.items.filter(hasIncompleteBackOrder);
    if (incompleteItems.length > 0) {
        toast.error(`Please complete back orders for ${incompleteItems.length} item(s) before submitting`);
        return;
    }
    
    // Format dates properly for all items
    form.value.items.forEach(item => {
        if (item.expire_date) {
            item.expire_date = formatDateForInput(item.expire_date);
        }
    });

    const confirm = await Swal.fire({
        title: 'Are you sure?',
        text: "You want to create packing list?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, create it!'
    });

    if (confirm.isConfirmed) {
        isSubmitting.value = true;

        await axios.post(route('supplies.storePK'), form.value)
            .then((response) => {
                console.log(response.data);
                isSubmitting.value = false;
                Swal.fire({
                    title: 'Success!',
                    text: "Packing list created successfully",
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                })
                .then(() => {
                    router.visit(route('supplies.packing-list.showPK'));
                });
            })
            .catch((error) => {
                isSubmitting.value = false;
                console.log(error);
                console.error('Submit error:', error);
                toast.error(error.response?.data || 'Error submitting packing list');
            })
    }

}

const processing = ref(false);
const showBackOrderModal = ref(false);
const selectedItem = ref(null);
const backOrderRows = ref([]);

// Computed properties for back order quantities
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
            status: 'Missing',
            note: ''
        }];

    showBackOrderModal.value = true;
};

const hasIncompleteBackOrder = (item) => {
    if (!item?.received_quantity || item.received_quantity === item?.quantity) return false;

    const mismatches = item.quantity - item.received_quantity;
    const totalDifferences = (item.differences || []).reduce(
        (total, diff) => total + (parseInt(diff?.quantity) || 0), 0
    );

    return totalDifferences !== mismatches;
};

const canSubmit = computed(() => {
    if (!form.value?.items?.length) return false;
    return !form.value.items.some(hasIncompleteBackOrder);
});

const submitButtonTitle = computed(() => {
    if (!form.value?.items?.length) return 'No items to submit';
    if (form.value.items.some(hasIncompleteBackOrder)) return 'Please complete all back orders before submitting';
    return '';
});

const attemptCloseModal = async () => {
    if (!selectedItem.value) {
        closeBackOrderModal();
        return;
    }

    const totalDifferences = backOrderRows.value.reduce((total, row) => total + (parseInt(row.quantity) || 0), 0);
    const expectedMismatches = selectedItem.value.quantity - (selectedItem.value.received_quantity || 0);

    if (totalDifferences !== expectedMismatches) {
        const result = await Swal.fire({
            title: 'Incomplete Back Orders',
            html: `You need to record all mismatches.<br><br>` +
                `Expected: ${expectedMismatches}<br>` +
                `Recorded: ${totalDifferences}<br>` +
                `Remaining: ${expectedMismatches - totalDifferences}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Continue Editing',
            cancelButtonText: 'Close Anyway',
            customClass: {
                container: 'swal-higher-z-index'
            }
        });

        if (result.isConfirmed) {
            return; // Keep modal open
        }
    } else {
        toast.success('All mismatches have been recorded');
    }

    closeBackOrderModal();
};

const closeBackOrderModal = () => {
    // Only clear if we have a selected item
    if (selectedItem.value) {
        // Sync one final time before closing
        syncBackOrdersWithDifferences();
    }
    showBackOrderModal.value = false;
    selectedItem.value = null;
    backOrderRows.value = [];
};

// Define all possible statuses
const allStatuses = ['Missing', 'Damaged', 'Lost', 'Expired', 'Low quality'];

// Compute available statuses (not used in other rows)
const availableStatuses = computed(() => {
    const usedStatuses = new Set(backOrderRows.value.map(row => row.status));
    return allStatuses.filter(status => !usedStatuses.has(status));
});

const addBackOrderRow = () => {
    const remaining = remainingToAllocate.value;
    if (remaining <= 0) return;

    // Get first available status
    const status = availableStatuses.value[0] || 'Missing';
    if (!status) {
        toast.error('No more status types available');
        return;
    }

    backOrderRows.value.push({
        id: null,
        quantity: 0,
        status: status,
        note: ''
    });

    // Sync after adding row
    nextTick(() => {
        syncBackOrdersWithDifferences();
    });
};

const removeBackOrderRow = async (index, item) => {
    try {
        if (item.id) {
            // If item has an ID, delete it from the server first
            const response = await axios.get(route('supplies.deletePackingListDiff', item.id));
            toast.success(response.data.message);
            // Update backOrderRows with server response
            if (response.data.differences) {
                backOrderRows.value = [...response.data.differences];
            } else {
                // If no server response differences, just remove the local row
                const newRows = [...backOrderRows.value];
                newRows.splice(index, 1);
                backOrderRows.value = newRows;
            }
        } else {
            // For items without ID, just remove locally
            const newRows = [...backOrderRows.value];
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
        toast.error(error.response?.data || 'Error removing back order');
    }
};

// Computed properties for validation
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

const validateBackOrderQuantities = () => {
    // Calculate total allocated excluding current row
    const validateRow = (currentRow, index) => {
        const otherRowsTotal = backOrderRows.value.reduce((total, row, i) => {
            if (i === index) return total; // Skip current row
            return total + (parseInt(row.quantity) || 0);
        }, 0);

        // Calculate max allowed for this row
        const maxAllowed = missingQuantity.value - otherRowsTotal;

        // Parse and validate the quantity
        let qty = parseInt(currentRow.quantity);
        if (isNaN(qty) || qty < 0) {
            qty = 0;
        } else if (qty > maxAllowed) {
            qty = maxAllowed;
        }

        // Update the row with validated quantity
        currentRow.quantity = qty;
    };

    // Validate each row
    backOrderRows.value.forEach((row, index) => validateRow(row, index));

    // After validation, sync with differences array
    syncBackOrdersWithDifferences();
};



const syncBackOrdersWithDifferences = () => {
    if (!selectedItem.value || !isValidForSave.value) return;

    const itemIndex = form.value.items.findIndex(item => item === selectedItem.value);
    if (itemIndex === -1) return;

    // Update the differences array with current back orders
    form.value.items[itemIndex].differences = backOrderRows.value.map(row => ({
        id: row.id ?? null,
        quantity: parseInt(row.quantity) || 0,
        status: row.status,
        note: row.note
    }));
};

</script>

<style>
.swal-higher-z-index {
    z-index: 9999 !important;
}
</style>