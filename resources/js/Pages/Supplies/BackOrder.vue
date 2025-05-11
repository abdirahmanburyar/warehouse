<template>

    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Purchase Orders" description="Manage your purchase orders">
        <!-- Supplier Selection -->
        <div class="">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Back Order</h2>
            <div class="grid grid-cols-1 gap-6">
                <div class="w-[400px] mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Supplier
                    </label>
                    <select v-model="purchase_order_id" @change="onPOChange"
                        class="w-full block appearance-none py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select P.O Number</option>
                        <option :value="s.id" v-for="s in props.purchaseOrders">{{ s.po_number }}</option>
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
                    <tr v-for="(item, index) in form" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4  border border-black">
                            <div class="flex flex-col">
                                <div class="text-sm font-medium text-gray-900">{{ item.product?.name }}</div>
                                <div class="text-sm text-gray-500">{{ item.product?.barcode }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4  border border-black text-sm text-gray-500">
                            {{ item.packing_list?.packing_list_number }}
                        </td>
                        <td class="px-6 py-4  border border-black text-sm text-gray-900">
                            {{ item.quantity }}
                        </td>
                        <td class="px-6 py-4  border border-black">
                            <span :class="{
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                'bg-red-100 text-red-800': item.status === 'Damaged',
                                'bg-yellow-100 text-yellow-800': item.status === 'Expired',
                                'bg-gray-100 text-gray-800': item.status === 'Missing'
                            }">
                                {{ item.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4  border border-black text-sm text-gray-500">
                            {{ moment(item.created_at).format('LLL') }}
                        </td>
                        <td class="px-6 py-4 border border-black text-sm text-gray-500">
                            <div class="flex space-x-2">
                                <button 
                                    v-if="item.status.toLowerCase() === 'missing'" 
                                    @click="handleBackOrder(item, 'update', index)"
                                    :disabled="processing[index]"
                                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50">
                                    {{ processing[index] ? "Processing..." : "Update"}}
                                </button>
                                <button 
                                    @click="handleBackOrder(item, 'dispose', index)"
                                    :disabled="processing[index]"
                                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50">
                                    {{ processing[index] ? "Processing..." : "Dispose"}}
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="form.length === 0 && !isLoading">
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 border border-black">
                            No back orders found
                        </td>
                    </tr>
                </tbody>
            </table>
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

const toast = useToast();

const props = defineProps({
    purchaseOrders: Array,
    warehouses: Array,
    // suppliers: Array,
    // po_number: Number
});

const purchase_order_id = ref(null)

const form = ref([]);

const processing = ref([]);

async function handleBackOrder(item, action, index) {
    try {
        let quantity = item.quantity;

        if (action === 'update' && item.status.toLowerCase() === 'missing') {
            const { value: inputQuantity } = await Swal.fire({
                title: 'Enter received quantity',
                input: 'number',
                inputLabel: `Maximum quantity: ${item.quantity}`,
                inputValue: 1,
                showCancelButton: true,
                inputValidator: (value) => {
                    const num = parseInt(value);
                    if (!value) {
                        return 'Please enter a quantity';
                    }
                    if (num <= 0) {
                        return 'Quantity must be greater than 0';
                    }
                    if (num > item.quantity) {
                        return `Quantity cannot exceed ${item.quantity}`;
                    }
                }
            });

            if (!inputQuantity) return;
            quantity = parseInt(inputQuantity);
        }

        processing.value[index] = true;
        const response = await axios.post(route('supplies.backorder-status'), {
            ...item,
            action: action,
            quantity: quantity
        });
        
        toast.success(response.data);
        // Refresh the list
        await onPOChange();
    } catch (error) {
        console.log(error.response.data);
        toast.error(error.response?.data || 'An error occurred');
    } finally {
        processing.value[index] = false;
    }
}

const isLoading = ref(false);

async function onPOChange() {
    isLoading.value = true;
    form.value = [];
    
    if(!purchase_order_id.value) {
        isLoading.value = false;
        return;
    }
 
    await axios.get(route('supplies.get-packingList', purchase_order_id.value))
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