<template>

    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Purchase Orders" description="Manage your purchase orders">
        <!-- Supplier Selection -->
        <div class="">
            <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Suppliers
            </Link>
            

            <h2 class="text-lg font-medium text-gray-900 mb-4">Back Order</h2>
            <div class="grid grid-cols-1 gap-6">
                <div class="w-[400px] mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Supplier
                    </label>
                    <select v-model="packing_list_id" @change="onPOChange"
                        class="w-full block appearance-none py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select PL Number</option>
                        <option :value="s.id" v-for="s in props.packingLists">{{ s.packing_list_number }}</option>
                    </select>
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

import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    packingLists: Array,
    warehouses: Array,
    // suppliers: Array,
    // po_number: Number
});

const packing_list_id = ref("")

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

const updateQuantity = async () => {
    if (!validateQuantity(selectedItem.value)) return;


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

const validateDamagedQuantity = (item) => {
    const damagedQty = parseFloat(item.damaged_quantity);
    const currentQty = parseFloat(item.quantity);

    if (damagedQty > currentQty) {
        item.quantityError = `Cannot exceed quantity (${currentQty})`;
        item.damaged_quantity = currentQty;
        return false;
    } else if (damagedQty < 0) {
        item.quantityError = 'Cannot be negative';
        item.damaged_quantity = 0;
        return false;
    } else {
        item.quantityError = '';
        return true;
    }
};

async function updateDamagedQuantity(item) {
    if (!validateDamagedQuantity(item)) return;

    try {
        await axios.put(route('supplies.packing-list.update-damaged', item.id), {
            damaged_quantity: item.damaged_quantity
        });
        
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Damaged quantity updated successfully',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to update damaged quantity',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }
};

async function onPOChange() {
    isLoading.value = true;
    form.value = [];
    
    if(!packing_list_id.value) {
        isLoading.value = false;
        return;
    }
 
    await axios.get(route('supplies.get-packingList', packing_list_id.value))
        .then((response) => {
            isLoading.value = false;
            console.log(response.data);
            form.value = response.data;
        })
        .catch((error) => {
            isLoading.value = false;
            console.log(error.response.data);
        });
}

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