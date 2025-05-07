<template>

    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Purchase Orders" description="Manage your purchase orders">
        <div class="">
            <!-- Supplier Selection -->
            <div class=" p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Received New Supply</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div class="w-[400px] mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Supplier
                        </label>
                        <select v-model="form.supplier_id" @change="onPOChange"
                            class="w-full block appearance-none py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Select P.O Number</option>
                            <option :value="s.id" v-for="s in props.purchaseOrders">{{ s.po_number }}</option>
                        </select>
                    </div>
                    <!-- Supplier Details Card -->
                    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-gray-50 rounded-lg p-4">
                        <div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-24 mb-3"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-28"></div>
                        </div>
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
                    <div v-else-if="selectedPO"
                        class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-gray-50 rounded-lg p-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">P.O Details</h3>
                            <p class="mt-1 text-sm text-gray-900">P.O No. {{ selectedPO.po_number }}</p>
                            <p class="mt-1 text-sm text-gray-900">P.O Date: {{ moment(selectedPO.po_date).format('LL') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier Details</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.name }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.contact_person }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.email }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.phone }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.address }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Purchase Order Info</h3>
                            <p class="mt-1 text-sm text-gray-900">P.L No. {{ form.packing_list_number }}</p>
                            <div class="mt-1 flex">
                                Data: <input 
                                    type="date" 
                                    v-model="form.pk_date"
                                    class="block ml-2 border-0 p-0 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                    :min="moment().format('YYYY-MM-DD')"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="mt-8 flex-1 flex flex-col">
                <h2 class='h2'>Items</h2>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="w-[40px] px-3 py-2 text-xs text-start border border-black text-gray-500 uppercase">SN#</th>
                                <th class="px-3 py-2 min-w-[200px] text-xs text-start border border-black text-gray-500 uppercase">Item</th>
                                <th class="w-[120px] px-3 py-2 text-xs text-start border border-black text-gray-500 uppercase">P.O Qty</th>
                                <th class="w-[120px] px-3 py-2 text-xs text-start border border-black text-gray-500 uppercase">Received Qty</th>
                                <th class="min-w-[200px] px-3 py-2 text-sm text-start border border-black text-gray-500 uppercase">Warehouse</th>
                                <th class=" px-3 py-2 text-sm text-start border border-black text-gray-500 uppercase">Location</th>
                                <th class=" px-3 py-2 text-sm text-start border border-black text-gray-500 uppercase">Expire date</th>
                                <th class=" px-3 py-2 text-sm text-start border border-black text-gray-500 uppercase">Batch Number</th>
                                <th class="w-[120px] px-3 py-2 text-xs text-start border border-black text-gray-500 uppercase">Unit Cost</th>
                                <th class="min-w-[120px] px-3 py-2 text-xs text-start border border-black text-gray-500 uppercase">Total Cost</th>
                                <th class="min-w-[120px] px-3 py-2 text-xs text-start border border-black text-gray-500 uppercase">Fullfilment Rate</th>
                                <th class="min-w-[40px] px-3 border border-black py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="">
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm text-gray-500 align-top border border-black pt-4">{{ index + 1 }}</td>
                                <td class="px-3 py-2 relative border border-black ">
                                    <div class="relative flex flex-col">
                                        <span>{{item.searchQuery}}</span>
                                        <span>Barcode: {{item.barcode}}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    <input type="number" v-model="item.quantity" @input="calculateTotal(index)"
                                        class="block w-full p-0 border-0"
                                        min="1">
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    <input type="number" v-model="item.received_quantity" @input="calculateTotal(index)"
                                        class="block w-full p-0 border-0"
                                        min="1">
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    <select v-model="item.warehouse_id" class="block w-full p-0 border-0" placeholder="Select a warehouse">
                                        <option value="" disable selected="selected">Select a warehouse</option>
                                        <option :value="w.id" v-for="w in props.warehouses">{{w.name}}</option>
                                    </select>
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    <input type="text" v-model="item.location" placeholder="Add location"
                                        class="block w-full p-0 border-0">
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    <input type="date" v-model="item.expire_date"
                                        class="block w-full p-0 border-0">
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    <input type="text" v-model="item.batch_number"  placeholder="Add Batch number"
                                        class="block w-full p-0 border-0">
                                </td>                                
                                <td class="px-3 py-2 border border-black">
                                    <input type="number" v-model="item.unit_cost"
                                        class="block w-full p-0 border-0"
                                        readonly
                                        step="0.01" min="0">
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    <input type="text" :value="formatCurrency(item.total_cost)" readonly
                                        class="block w-full p-0 bg-transparent border-0">
                                </td>
                                <td class="px-3 py-2 border border-black">
                                    90%
                                </td>
                                <td class="px-3 py-2 border border-black text-center">
                                    <button type="button" @click="showDifference(item, index)"
                                        class="text-gray-400 hover:text-red-600">
                                        Back Order
                                    </button>
                                    <button type="button" @click="removeItem(index)"
                                        class="text-gray-400 hover:text-red-600">
                                        <TrashIcon class="h-4 w-4 text-red-500"/>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="form.items.length === 0">
                                <td colspan="12" class="px-3 py-4 text-center text-sm text-gray-500 border border-black">
                                    No items added. Click "Add Item" to start creating your purchase order.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Difference Modal -->
                    <TransitionRoot appear :show="isDifferenceModalOpen" as="template">
                        <Dialog as="div" @close="closeDifferenceModal" class="relative z-10">
                            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                                leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                                <div class="fixed inset-0 bg-black bg-opacity-25" />
                            </TransitionChild>

                            <div class="fixed inset-0 overflow-y-auto">
                                <div class="flex min-h-full items-center justify-center p-4 text-center">
                                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                                        enter-to="opacity-100 scale-100" leave="duration-200 ease-in"
                                        leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                                        <DialogPanel
                                            class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                            <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                                Record Differences
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <div class="flex justify-between items-center mb-1">
                                                    <p class="text-sm text-gray-500">
                                                        Total Difference: {{ totalDifferenceQuantity }} / {{ originalItemQuantity }}
                                                    </p>
                                                    <span :class="{
                                                        'text-green-600': totalDifferenceQuantity < originalItemQuantity,
                                                        'text-yellow-600': totalDifferenceQuantity === originalItemQuantity,
                                                        'text-red-600': totalDifferenceQuantity > originalItemQuantity
                                                    }" class="text-sm font-medium">
                                                        {{ Math.round((totalDifferenceQuantity / originalItemQuantity) * 100) }}%
                                                    </span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="h-2 rounded-full transition-all duration-300"
                                                        :class="{
                                                            'bg-green-500': totalDifferenceQuantity < originalItemQuantity,
                                                            'bg-yellow-500': totalDifferenceQuantity === originalItemQuantity,
                                                            'bg-red-500': totalDifferenceQuantity > originalItemQuantity
                                                        }"
                                                        :style="`width: ${Math.min(100, (totalDifferenceQuantity / originalItemQuantity) * 100)}%`">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Difference Table -->
                                            <div class="mt-4">
                                                <table class="min-w-full divide-y divide-gray-200 border border-black">
                                                    <thead>
                                                        <tr>
                                                            <th class="px-4 py-2 text-left border border-black">Quantity</th>
                                                            <th class="px-4 py-2 text-left border border-black">Status</th>
                                                            <th class="w-10 px-4 py-2 border border-black"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(diff, idx) in selectedItemDifferences" :key="idx" class="border-b border-black">
                                                            <td class="px-4 py-2 border-r border-black">
                                                                <input type="number" 
                                                                    v-model="diff.quantity" 
                                                                    min="1"
                                                                    @input="validateQuantity(idx)"
                                                                    class="w-full border-0 p-0 focus:ring-0">
                                                            </td>
                                                            <td class="px-4 py-2 border-r border-black">
                                                                <select v-model="diff.status" class="w-full border-0 p-0 focus:ring-0">
                                                                    <option value="" disabled>Select status</option>
                                                                    <option value="Expired">Expired</option>
                                                                    <option value="Damaged">Damaged</option>
                                                                </select>
                                                            </td>
                                                            <td class="px-4 py-2 text-center">
                                                                <button @click="removeDifference(idx)" class="text-red-500 hover:text-red-700">
                                                                    <TrashIcon class="h-4 w-4" />
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <button @click="addDifference"
                                                    :disabled="totalDifferenceQuantity >= originalItemQuantity"
                                                    class="mt-4 inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    :class="{
                                                        'bg-white hover:bg-gray-50': totalDifferenceQuantity < originalItemQuantity,
                                                        'bg-gray-100': totalDifferenceQuantity >= originalItemQuantity
                                                    }">
                                                    <PlusIcon class="h-4 w-4 mr-1" />
                                                    Add Row
                                                </button>
                                            </div>

                                            <div class="mt-6 flex justify-end space-x-3">
                                                <button @click="closeDifferenceModal"
                                                    class="inline-flex justify-center rounded-md border border-transparent bg-gray-100 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-200">
                                                    Cancel
                                                </button>
                                                <button @click="saveDifferences"
                                                    class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                                    Save
                                                </button>
                                            </div>
                                        </DialogPanel>
                                    </TransitionChild>
                                </div>
                            </div>
                        </Dialog>
                    </TransitionRoot>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 px-3 py-4">
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-2">
                            </div>
                            <div class="w-72 space-y-2">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-900">Total</span>
                                    <span class="text-gray-900">{{ formatCurrency(subtotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end space-x-3 mb-[80px]">
                <button type="button" :disabled="isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button @click="submitForm" type="button" :disabled="!selectedPO | isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ isSubmitting ? 'Saving, Wait....' : 'Save and Exit'}}
                </button>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2';

import { useToast } from 'vue-toastification';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const toast = useToast();

const props = defineProps({
    purchaseOrders: Array,
    warehouses: Array,
    // suppliers: Array,
    // po_number: Number
});

const selectedPO = ref(null);
const form = ref({
    supplier_id: "",
    packing_list_number: null,
    purchase_order_id: null,
    pk_date: moment().format('YYYY-MM-DD'),
    items: [{
        product_id: null,
        searchQuery: '',
        barcode: "",
        warehouse_id: null,
        expire_date: null,
        batch_number: "",
        location: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0,
        differences: []
    }]
});

const filteredProducts = ref([[]]);  // Initialize with one empty array for first row

function handleProductSelect(product, index) {
    console.log(product)
    form.value.items[index].product_id = product.id;
    form.value.items[index].searchQuery = product.name;
    form.value.items[index].barcode = product.barcode || '';
    form.value.items[index].unit_cost = product.unit_cost || 0;
    calculateTotal(index);

    // Clear the filtered products to hide dropdown
    filteredProducts.value[index] = [];

    // Add new item after selection
    addItem();
}

function calculateTotal(index) {
    const item = form.value.items[index];
    item.total_cost = item.quantity * item.unit_cost;
}

function showDifference(item, index){
    selectedItemIndex.value = index;
    selectedItemDifferences.value = item.differences || [];
    isDifferenceModalOpen.value = true;
}

// Modal state
const isDifferenceModalOpen = ref(false);
const selectedItemIndex = ref(null);
const selectedItemDifferences = ref([]);

function closeDifferenceModal() {
    isDifferenceModalOpen.value = false;
    selectedItemIndex.value = null;
}

function addDifference() {
    selectedItemDifferences.value.push({
        quantity: 1,
        status: ''
    });
}

function removeDifference(index) {
    selectedItemDifferences.value.splice(index, 1);
}

const totalDifferenceQuantity = computed(() => {
    return selectedItemDifferences.value.reduce((sum, diff) => sum + (Number(diff.quantity) || 0), 0);
});

// Watch for changes in difference quantities
watch(totalDifferenceQuantity, (newTotal) => {
    if (newTotal > originalItemQuantity.value) {
        toast.error(`Total quantity cannot exceed ${originalItemQuantity.value}`);
    }
});

function validateQuantity(index) {
    const currentTotal = totalDifferenceQuantity.value;
    const maxQuantity = originalItemQuantity.value;
    
    if (currentTotal > maxQuantity) {
        // Calculate how much we need to reduce this item's quantity by
        const excess = currentTotal - maxQuantity;
        const currentItem = selectedItemDifferences.value[index];
        const newQuantity = Math.max(1, Number(currentItem.quantity) - excess);
        
        // Update the quantity with a slight delay to allow for the input to settle
        setTimeout(() => {
            selectedItemDifferences.value[index].quantity = newQuantity;
        }, 0);
    }
};

const originalItemQuantity = computed(() => {
    if (selectedItemIndex.value === null) return 0;
    return Number(form.value.items[selectedItemIndex.value].quantity) || 0;
});

function saveDifferences() {
    if (selectedItemIndex.value === null) return;

    if (totalDifferenceQuantity.value > originalItemQuantity.value) {
        toast.error(`Total difference quantity (${totalDifferenceQuantity.value}) cannot exceed original quantity (${originalItemQuantity.value})`);
        return;
    }

    // Validate that all differences have a status
    const hasInvalidStatus = selectedItemDifferences.value.some(diff => !diff.status);
    if (hasInvalidStatus) {
        toast.error('Please select a status for all differences');
        return;
    }

    form.value.items[selectedItemIndex.value].differences = [...selectedItemDifferences.value];
    closeDifferenceModal();
}

function removeItem(index){
    form.value.items.splice(index, 1);
}

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
});

const isSubmitting = ref(false);

async function submitForm() {
    if (!form.value.packing_list_number) {
        toast.warning('Packing list number must be provided');
        return;
    }

    if (!form.value.pk_date) {
        toast.warning('Please select a date');
        return;
    }

    Swal.fire({
        title: "Confirm Creation",
        text: "Are you sure you want to create this Packing list?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, create it!"
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Filter out items without product_id
            isSubmitting.value = true;
            const validItems = form.value.items.filter(item => item.product_id);
            if (validItems.length === 0) {
                toast.warning('Please add at least one item');
                return;
            }

            const formData = {
                packing_list_number: form.value.packing_list_number,
                purchase_order_id: form.value.purchase_order_id,
                pk_date: form.value.pk_date,
                items: form.value.items.map(item => ({
                    product_id: item.product_id,
                    warehouse_id: item.warehouse_id,
                    quantity: item.quantity,
                    batch_number: item.batch_number,
                    expire_date: item.expire_date || item.expired_date,
                    location: item.location,
                    unit_cost: item.unit_cost,
                    total_cost: item.total_cost,
                    differences: item.differences?.length > 0 ? item.differences.map(i => ({
                        quantity: i.quantity,
                        status: i.status,
                    })) : []
                }))
            };

            console.log(formData);

            await axios.post(route('supplies.storePK'), formData)
                .then((response) => {
                    isSubmitting.value = false;
                    Swal.fire({
                        title: "Success!",
                        text: response.data,
                        icon: "success"
                    }).then(() => {
                        router.visit(route('supplies.index'));
                    });

                })
                .catch((error) => {
                    console.log(error.response.data);
                    isSubmitting.value = false;
                });

        }
    });
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

const isLoading = ref(false);
async function onPOChange(e) {
    isLoading.value = true;
    selectedPO.value = null;
    console.log(e.target.value);
    const id = e.target.value;
    await axios.get(route('supplies.get-packingList', id))
        .then((response) => {
            isLoading.value = false;
            // selectedPO.value = response.data;
            // form.value.packing_list_number = response.data?.packing_list_number;
            // form.value.purchase_order_id = response.data?.id;
            console.log(response.data);
        })
        .catch((error) => {
            isLoading.value = false;
            console.log(error);
        })
}

watch([
    () => selectedPO.value
], () => {
    if(selectedPO.value && selectedPO.value.items){
        form.value.items = selectedPO.value.items.map(item => ({
            ...item,
            searchQuery: item.searchQuery || '',
            barcode: item.barcode || '',
            warehouse_id: item.warehouse_id || null,
            expire_date: item.expire_date || null,
            batch_number: item.batch_number || '',
            location: item.location || '',
            quantity: item.quantity || 1,
            unit_cost: item.unit_cost || 0,
            total_cost: item.total_cost || 0
        }));
    } else {
        form.value.items = [{
            product_id: null,
            searchQuery: '',
            barcode: '',
            warehouse_id: null,
            expire_date: null,
            batch_number: '',
            location: '',
            quantity: 1,
            unit_cost: 0,
            total_cost: 0
        }];
    }
})

function filterProducts(index) {
    const query = form.value.items[index].searchQuery?.toLowerCase() || '';
    if (!query) {
        filteredProducts.value[index] = [];
        return;
    }
    filteredProducts.value[index] = props.products.filter(p =>
        p.name.toLowerCase().includes(query)
    );
}

// const po_date = ref(moment().format('YYYY-MM-DD'));
</script>

<style>
.multiselect-option.is-pointed {
    color: white;
    background: #4f46e5;
}

.multiselect-option.is-selected {
    color: white;
    background: #4f46e5;
}

.multiselect-option {
    padding: 8px 12px;
}

.multiselect-single-label {
    padding: 4px 0;
}

.multiselect.is-active {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    border-color: #4f46e5;
}

.multiselect {
    min-height: 42px;
}

.multiselect-no-options {
    padding: 8px 12px;
    color: #6b7280;
}

.product-select {
    width: 100%;
    --ms-tag-bg: #4f46e5;
    --ms-tag-color: #ffffff;
}

select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    height: 38px;
}

select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 1px #4f46e5;
}

.relative {
    position: relative;
}

.absolute {
    position: absolute;
}

.z-50 {
    z-index: 50;
}

.shadow-xl {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

tbody {
    position: relative;
}

tr {
    position: relative;
}

td {
    position: relative;
}
</style>