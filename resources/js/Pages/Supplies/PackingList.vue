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
            <h2 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h2>
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
                        <p class="mt-1 text-sm text-gray-900">Ref. No. #: <input type="text"
                                v-model="form.ref_no" class="border-0" /></p>
                        <div class="mt-1 flex flex-col gap-2">
                            <div class="flex items-center">
                                P.O Data: {{ form.po_date }}
                            </div>
                            <div class="flex items-center">
                                PL Data: <input type="date" v-model="form.pk_date" class="border-0" />
                            </div>

                        </div>
                    </div>
                </div>
                <div v-else>
                    <span>No P.O Data found</span>
                </div>
                <!-- Items List -->
                <div class="overflow-x-auto relative w-full">
                    <table class="table-fixed w-full divide-y mb-[200px] divide-gray-200" style="min-width: 1200px; position: relative; overflow-y: visible;" v-if="!isLoading && form">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="w-[40px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50 z-10 border-r">
                                    #
                                </th>
                                <th
                                    class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky left-[40px]  bg-gray-50 z-10 border-r">
                                    Item</th>
                                <th
                                    class="w-[150px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Qty</th>
                                <th
                                    class="w-[150px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Received QTY</th>
                                <th
                                    class="w-[150px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Missmatched QTY</th>
                                <th
                                    class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Warehouse</th>
                                <th
                                    class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Locations</th>
                                <th
                                    class="w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Batch Number</th>
                                <th
                                    class="w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Expire Date</th>
                                <th
                                    class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Unit Cost</th>
                                <th
                                    class="w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Total Cost</th>
                                <th
                                    class="w-[200px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Fullfillment Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2 text-sm text-gray-500 align-top pt-4 sticky left-0 bg-white z-10 border-r']">{{ index + 1 }}</td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2 sticky left-[40px] bg-white z-10 border-r whitespace-normal']">{{ item.product?.name }}</td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <input type="number" v-model="item.quantity" readonly
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm bg-transparent">
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <input type="number" v-model="item.received_quantity" :disabled="item.status == 'approved'"
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm bg-transparent"
                                        @input="handleReceivedQuantityChange(index)">
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <input type="text" :value="calculateMismatches(item)" readonly
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm bg-transparent">
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <Multiselect v-model="item.warehouse" :value="item.warehouse_id"                                    
                                        :options="props.warehouses" :searchable="true" :close-on-select="true" :show-labels="false"
                                        :allow-empty="true" placeholder="Select Warehouse" track-by="id"
                                        :disabled="item.status === 'approved'"
                                        :append-to-body="true"
                                        label="name" @select="hadleWarehouseSelect(index, $event)">
                                    </Multiselect>
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <Multiselect v-model="item.location" :value="item.location_id"
                                        :disabled="item.status === 'approved'"
                                        :options="[{ id: 'new', name: '+ Add New Location', isAddNew: true }, ...props.locations]"
                                        :searchable="true" :close-on-select="true" :show-labels="false"
                                        :allow-empty="true" placeholder="Select Location" track-by="id"
                                        label="location" @select="hadleLocationSelect(index, $event)">
                                        <template v-slot:option="{ option }">
                                            <div :class="{ 'add-new-option': option.isAddNew }">
                                                <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New Location</span>
                                                <span v-else>{{ option.location }}</span>
                                            </div>
                                        </template>
                                    </Multiselect>
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <input type="text" v-model="item.batch_number"  :disabled="item.status === 'approved'"
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm">
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <input type="date" v-model="item.expire_date" :min="new Date(Date.now() + 86400000 * 180).toISOString().split('T')[0]"  :disabled="item.status == 'approved'"
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm">
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <input type="number" v-model="item.unit_cost" readonly
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm"
                                        step="0.01" min="0">
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2']">
                                    <input type="text" :value="item.total_cost" readonly
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm bg-transparent">
                                </td>
                                <td :class="[{'border-green-600 border-2': item.status === 'approved'}, {'border-yellow-500 border-2': item.status === 'reviewed'}, {'border-gray-500 border': !item.status || item.status === 'pending'}, 'px-3 py-2 text-left']">
                                   {{ calculateFulfillmentRate(item) }}%
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

                <!-- Footer -->
                <div class="border-t border-gray-200 px-3 py-4">
                    <div class="flex justify-end items-center">
                        <div class="w-72 space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-900">Total</span>
                                <span class="text-gray-900">{{ "subtotal" }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <Link :href="route('supplies.index')" :disabled="isSubmitting" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Exit
                    </Link>
                    <button v-if="hasApprovedItems" :disabled="isSubmitting" @click="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{  isSubmitting ? "Saving..." : "Save and Exit" }}
                    </button>
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
    </AuthenticatedLayout>
</template>
<script setup>
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

import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    purchaseOrders: {
        type: Array,
        required: true
    },
    warehouses: {
        required: true,
        type: Array
    },
    locations: {
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

const hasApprovedItems = computed(() => {
    return form.value?.items?.some(item => item.status == 'approved') ?? false;
});

function hadleWarehouseSelect(index, selected) {
    console.log(selected, index);
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

const isNewLocation = ref(false);
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
        console.log(newLocationData);
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
        console.log(error.response);
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


async function handlePOSelect(selected) {
    if(!selected) {
        form.value = null;
        return;
    }
    isLoading.value = true;
    await axios.get(route('supplies.get-purchaseOrder', selected.id), {
        params: {
            po_id: selected.id
        }
    })
        .then((response) => {
            isLoading.value = false;
            form.value = response.data;
            form.value['purchase_order_id'] = selected.id;
        })
        .catch((error) => {
            isLoading.value = false;
            console.log(error.response);
            toast.error(error.response.data);
        })
}

async function submit(){
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
        form.value.items = form.value.items.filter(item => item.warehouse_id && item.location_id && item.batch_number && item.expire_date && item.received_quantity >= 0);
        isSubmitting.value = true;
        await axios.post(route('supplies.storePK'), form.value)
            .then((response) => {
                isSubmitting.value = false;
                console.log(response.data);
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
                toast.error(error.response.data);
            });
    }
    
}

</script>