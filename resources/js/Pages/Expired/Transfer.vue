<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import axios from 'axios';
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2';
import moment from 'moment';

const toast = useToast();

const props = defineProps({
    inventory: Object,
    warehouses: {
        type: Array,
        default: () => []
    },
    facilities: {
        type: Array,
        default: () => []
    }
});

const transferType = ref('warehouse');
const loading = ref(false);
const selectedDestination = ref(null);
const quantityToTransfer = ref('');

const destinations = computed(() => {
    return transferType.value === 'warehouse' ? props.warehouses : props.facilities;
});

const isSubmitting = ref(false);

const handleSubmit = async () => {
    if (!selectedDestination.value || !quantityToTransfer.value) {
        toast.error('Please fill in all required fields');
        return;
    }

    if (parseInt(quantityToTransfer.value) > props.inventory.quantity) {
        toast.error('Transfer quantity cannot exceed available quantity');
        return;
    }

    const result = await Swal.fire({
        title: 'Confirm Transfer',
        html: `
            <div class="text-left">
                <p><strong>Item:</strong> ${props.inventory.product.name}</p>
                <p><strong>Quantity:</strong> ${quantityToTransfer.value}</p>
                <p><strong>Destination:</strong> ${selectedDestination.value.name}</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, transfer it!',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    isSubmitting.value = true;

    try {
        const response = await axios.post(route('transfers.store'), {
            source_type: 'warehouse',
            source_id: props.inventory.warehouse_id,
            destination_type: transferType.value,
            destination_id: selectedDestination.value.id,
            items: [{
                inventory_id: props.inventory.id,
                product_id: props.inventory.product_id,
                quantity: parseInt(quantityToTransfer.value),
                batch_number: props.inventory.batch_number,
                barcode: props.inventory.barcode || null,
                expire_date: props.inventory.expiry_date || null,
                uom: props.inventory.uom || null
            }],
            notes: `Transferred ${quantityToTransfer.value} items to ${selectedDestination.value.name}`
        });

        await Swal.fire({
            icon: 'success',
            title: 'Transfer Successful!',
            text: `Transfer ID: ${response.data.transfer_id}`,
            showConfirmButton: false,
            timer: 1500
        });

        router.get(route('expired.transfer', props.inventory.id), {}, {
            preserveState: true,
            preserveScroll: true,
            only: ['inventory']
        });
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || 'An error occurred');
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <AuthenticatedLayout title="Transfer Item" description="Transfer expired or soon to be expired items to warehouse or facility" img="/assets/images/transfer.png">
        <form @submit.prevent="handleSubmit">
            <div class="p-6 text-gray-900 mb-6">
            <div class="flex flex-col space-y-6">
                <div class="flex items-start flex-col w-full gap-5 mb-6">
                    <div class="flex items-start gap-5">
                        <label class="inline-flex items-center">
                            <input type="radio" v-model="transferType" value="warehouse" class="form-radio" />
                            <span class="ml-2">Warehouse</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" v-model="transferType" value="facility" class="form-radio" />
                            <span class="ml-2">Facility</span>
                        </label>
                    </div>

                    <div class="w-[400px]">
                        <Multiselect
                            v-model="selectedDestination"
                            :options="destinations"
                            :searchable="true"
                            :create-option="false"
                            placeholder="Select destination"
                            label="name"
                            track-by="id"
                        />
                    </div>                   
                </div>

                <div class="border border-black overflow-hidden">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="border-b border-black">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border-r border-black">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border-r border-black">UOM</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border-r border-black">Item Information</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border-r border-black">Available Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border-r border-black">Quantity to Transfer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-black">
                                <td class="px-6 py-3 text-sm border-r border-black">
                                    {{ inventory.product.name }}
                                </td>
                                <td class="px-6 py-3 text-sm border-r border-black">
                                    {{ inventory.uom || 'N/A' }}
                                </td>
                                <td class="px-6 py-3 text-sm border-r border-black">
                                    <div class="space-y-1">
                                        <p>Batch Number: {{ inventory.batch_number }}</p>
                                        <p>Barcode: {{ inventory.barcode }}</p>
                                        <p>Expire Date: {{ moment(inventory.expiry_date).format('DD/MM/YYYY') || 'N/A' }}</p>
                                        <p>Warehouse: {{ inventory.warehouse?.name || 'N/A' }}</p>
                                        <p>Location: {{ inventory.location?.location || 'N/A' }}</p>

                                    </div>
                                </td>
                                <td class="px-6 py-3 text-sm font-medium border-r border-black">
                                    {{ inventory.quantity }}
                                </td>
                                <td class="px-6 py-3 text-sm border-r border-black">
                                    <input
                                        type="number"
                                        v-model="quantityToTransfer"
                                        class="w-full px-2 py-1 border border-gray-300 rounded"
                                        min="1"
                                        :max="inventory.quantity"
                                        placeholder="0"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end items-center space-x-6">
                    <a
                        :href="route('expired.index')"
                        :disabled="loading"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        Exit
                    </a>
                    <button
                        type="submit"
                        :disabled="isSubmitting || !selectedDestination || !quantityToTransfer"
                        class="text-white bg-blue-600 px-4 py-2 rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50 min-w-[100px]"
                    >
                        <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Processing...' : 'Transfer' }}
                    </button>                    
                </div>
            </div>
        </div>
        </form>
    </AuthenticatedLayout>
</template>