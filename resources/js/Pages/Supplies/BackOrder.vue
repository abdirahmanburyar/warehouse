<template>

    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Back Orderse" description="Manage your Back orders">
        <!-- Supplier Selection -->
        <div class="">
            <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Suppliers
            </Link>
            

            <div class="grid grid-cols-1 gap-6">
                <div class="w-[400px] mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select P.O
                    </label>
                    <Multiselect v-model="purchase_order"
                        :options="props.po"
                        :searchable="true" :close-on-select="true" :show-labels="false"
                        :allow-empty="true" placeholder="Select P.O Number" track-by="id" label="po_number"
                    @select="onPOChange"></Multiselect>
                </div>
            </div>
        </div>

        <!-- Back Order Table -->
        <div class="overflow-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3  border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3  border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Packing List</th>
                        <th class="px-6 py-3  border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3  border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3  border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3  border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="isLoading">
                        <td colspan="6" class="text-center text-black text-lg">Loading...</td>
                    </tr>
                    <template v-else-if="groupedItems.length > 0">
                        <template v-for="group in groupedItems" :key="group.product_id + '-' + group.packing_list_number">
                            <tr v-for="(item, index) in group.items" :key="item.id" 
                                :class="{'hover:bg-gray-50': true, 'border-t-2 border-gray-300': index === 0}">
                                <td v-if="index === 0" :rowspan="group.items.length" class="px-6 py-4 border border-black">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-900">{{ group.product_name }}</div>
                                        <div class="text-sm text-gray-500">{{ group.product_barcode }}</div>
                                    </div>
                                </td>
                                <td v-if="index === 0" :rowspan="group.items.length" class="px-6 py-4 border border-black text-sm text-gray-500">
                                    {{ group.packing_list_number }}
                                </td>
                                <td class="px-6 py-4 border border-black text-sm text-gray-900">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-6 py-4 border border-black">
                                    <span :class="{
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                        'bg-red-100 text-red-800': item.status === 'Damaged',
                                        'bg-yellow-100 text-yellow-800': item.status === 'Missing',
                                        'bg-green-100 text-green-800': item.status === 'Received'
                                    }">
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 border border-black text-sm text-gray-500">
                                    {{ new Date(item.created_at).toLocaleString() }}
                                </td>
                                <td class="px-6 py-4 border border-black text-sm">
                                    <div class="flex space-x-2">
                                        <button 
                                        @click="openUpdateModal(item)"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs"
                                    >
                                        Receive
                                    </button>
                                    <button 
                                        @click="liquidate(item)"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs"
                                    >
                                        Liquidate
                                    </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <tr v-else>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 border border-black">
                            No back orders found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Quantity Update Modal -->
        <Modal :show="showUpdateModal" @close="closeUpdateModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Update Quantity</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Current Quantity
                    </label>
                    <input
                        type="number"
                        v-model="selectedItem.quantity"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': selectedItem.quantityError }"
                        min="0"
                        step="1"
                    >
                    <p v-if="selectedItem.quantityError" class="mt-1 text-sm text-red-600">{{ selectedItem.quantityError }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="selectedItem.status">
                        <option value="Missing" >Received</option>
                        <option value="Lost" >Lost</option>
                        <option value="Damaged" >Damaged</option>
                        <option value="Expired" >Expired</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        @click="closeUpdateModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateQuantity"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :disabled="processing"
                    >
                        {{ processing ? 'Updating...' : 'Update' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    po: Array,
    warehouses: Array,
    // suppliers: Array,
    // po_number: Number
});

const purchase_order = ref(null)

const form = ref([]);

const processing = ref(false);

const isLoading = ref(false);
const showUpdateModal = ref(false);
const selectedItem = ref({});

const openUpdateModal = (item) => {
    selectedItem.value = { ...item };
    showUpdateModal.value = true;
};

const liquidate = (item) => {
    alert("hi");
};

const closeUpdateModal = () => {
    showUpdateModal.value = false;
    selectedItem.value = {};
};

const validateFields = (item) => {
    const errors = [];
    if (!item.quantity || item.quantity <= 0) errors.push('Quantity is required and must be greater than 0');
    if (!item.status) errors.push('Status is required');
    if (!item.packing_list_id) errors.push('Packing list is required');
    if (!item.product_id) errors.push('Product is required');
    return errors;
};

const updateQuantity = async () => {
    if (!validateQuantity(selectedItem.value)) return;

    // Validate all required fields
    const errors = validateFields(selectedItem.value);
    if (errors.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: errors.join('<br>'),
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }

    // Ask for confirmation
    const confirmed = await Swal.fire({
        title: 'Confirm Update',
        text: 'Are you sure you want to update this back order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'No, cancel'
    });

    if (!confirmed.isConfirmed) return;

    try {
        processing.value = true;
        await axios.post(route('supplies.backorder-status'), {
            id: selectedItem.value.id,
            packing_list_id: selectedItem.value.packing_list_id,
            product_id: selectedItem.value.product_id,
            quantity: selectedItem.value.quantity,
            status: selectedItem.value.status,
            action: 'Received'
        });
        
        toast.success('Back order updated successfully');
        await onPOChange(); // Refresh the data
        closeUpdateModal();
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data || 'Failed to update back order');
    } finally {
        processing.value = false;
    }
};

const validateQuantity = (item) => {
    const qty = parseFloat(item.quantity);
    
    if (isNaN(qty) || qty < 0) {
        item.quantityError = 'Quantity must be a positive number';
        return false;
    }
    
    item.quantityError = '';
    return true;
};

const groupedItems = computed(() => {
    const groups = {};
    
    if (!form.value || !Array.isArray(form.value)) return [];
    
    form.value.forEach(item => {
        if (!item.product_id) return;
        
        const key = `${item.product_id}-${item.packing_list?.packing_list_number}`;
        
        if (!groups[key]) {
            groups[key] = {
                product_id: item.product_id,
                packing_list_number: item.packing_list?.packing_list_number,
                product_name: item.product?.name,
                product_barcode: item.product?.barcode,
                items: []
            };
        }
        groups[key].items.push(item);
    });

    // Sort by product name and packing list number
    return Object.values(groups).sort((a, b) => {
        if (a.product_name === b.product_name) {
            return (a.packing_list_number || '').localeCompare(b.packing_list_number || '');
        }
        return (a.product_name || '').localeCompare(b.product_name || '');
    });
});


async function onPOChange(selected) {
    isLoading.value = true;
    form.value = [];
    
    if(!selected) {
        isLoading.value = false;
        purchase_order.value = null;
        return;
    }
    purchase_order.value = selected;
 
    await axios.get(route('supplies.get-packingList', selected.id))
        .then((response) => {
            isLoading.value = false;
            form.value = response.data;
        })
        .catch((error) => {
            isLoading.value = false;
            console.log(error.response.data);
        });
}

</script>

