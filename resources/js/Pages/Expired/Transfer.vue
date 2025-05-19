<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import axios from 'axios';
import Swal from 'sweetalert2';

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

const form = ref({
    inventory_id: props.inventory?.id,
    destination_type: 'warehouse',
    destination_id: null,
    quantity: props.inventory?.quantity || 0,
    notes: '',
});

const errors = ref({});

const destinations = computed(() => {
    return transferType.value === 'warehouse' ? (props.warehouses || []) : (props.facilities || []);
});

const handleDestinationSelect = (selected) => {
    form.value.destination_id = selected ? selected.id : null;
};

const validateForm = () => {
    errors.value = {};
    if (!form.value.destination_id) {
        errors.value.destination_id = 'Please select a destination';
        return false;
    }
    if (!form.value.quantity || form.value.quantity <= 0) {
        errors.value.quantity = 'Please enter a valid quantity';
        return false;
    }
    return true;
};

const submit = async () => {
    if (!validateForm()) {
        return;
    }

   
        const result = await Swal.fire({
            title: 'Confirm Transfer',
            text: `Are you sure you want to transfer ${form.value.quantity} units to the selected ${transferType.value}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, transfer it!'
        });

        if (result.isConfirmed) {
            loading.value = true;
            errors.value = {};

           await axios.post(route('transfers.store'), form.value)
                .then((response) => {
                    loading.value = false;
                    console.log(response.data);
                    
                    Swal.fire({
                        title: 'Transfer Successful!',
                        text: `Transfer ID: ${response.data.transfer_id}`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    })
                    .then(() => {
                        router.visit(route('expired.index'));
                    })

                })
                .catch((error) => {
                    loading.value = false;
                    console.log(error);
                    toast.error(error.response.data);
                    
                })
            
        }
        
    
};
</script>

<template>
    <AuthenticatedLayout title="Transfer Expired Item">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-semibold mb-6">Transfer Expired Item</h2>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex items-start">
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Item Details</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p><strong>Product:</strong> {{ inventory?.product?.name }}</p>
                                        <p><strong>Batch Number:</strong> {{ inventory?.batch_number }}</p>
                                        <p><strong>Expiry Date:</strong> {{ inventory?.expiry_date }}</p>
                                        <p><strong>Current Location:</strong> {{ inventory?.location?.location }}</p>
                                        <p><strong>Available Quantity:</strong> {{ inventory?.quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Transfer Type Selection -->
                                <div>
                                    <InputLabel value="Transfer To" />
                                    <div class="mt-2 space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                v-model="transferType" 
                                                value="warehouse" 
                                                class="form-radio" 
                                                @change="form.destination_type = 'warehouse'; form.destination_id = null; selectedDestination = null">
                                            <span class="ml-2">Warehouse</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                v-model="transferType" 
                                                value="facility" 
                                                class="form-radio"
                                                @change="form.destination_type = 'facility'; form.destination_id = null; selectedDestination = null">
                                            <span class="ml-2">Facility</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Destination Selection -->
                                <div>
                                    <InputLabel :value="`Select ${transferType === 'warehouse' ? 'Warehouse' : 'Facility'}`" />
                                    <Multiselect
                                        v-model="selectedDestination"
                                        :options="destinations"
                                        :searchable="true"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        :allow-empty="true"
                                        :placeholder="`Select ${transferType === 'warehouse' ? 'Warehouse' : 'Facility'}`"
                                        track-by="id"
                                        label="name"
                                        @select="handleDestinationSelect"
                                    >
                                        <template v-slot:option="{ option }">
                                            <span>{{ option.name }}</span>
                                        </template>
                                    </Multiselect>
                                    <InputError :message="errors.destination_id" class="mt-2" />
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <InputLabel value="Quantity to Transfer" />
                                    <TextInput
                                        type="number"
                                        v-model="form.quantity"
                                        class="mt-1 block w-full"
                                        :max="inventory.quantity"
                                        min="1"
                                        required
                                    />
                                    <InputError :message="errors.quantity" class="mt-2" />
                                </div>

                                <!-- Notes -->
                                <div>
                                    <InputLabel value="Notes" />
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Add any additional notes here..."
                                    ></textarea>
                                    <InputError :message="errors.notes" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4">
                                <SecondaryButton :href="route('expired.index')" as="a">
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton :disabled="loading">
                                    {{ loading ? 'Processing...' : 'Transfer Item' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>