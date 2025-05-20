<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';
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
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

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
    },

});

const sourceType = ref('warehouse');
const destinationType = ref('warehouse');
const loading = ref(false);
const selectedSource = ref(null);
const selectedDestination = ref(null);
const selectedInventory = ref(null);
const filteredInventories = ref([]);
const availableInventories = ref([]);
const searchQuery = ref('');
const loadingInventories = ref(false);

const form = ref({
    source_type: 'warehouse', // Default source type is warehouse since it's an expired item
    source_id: null,
    destination_type: 'warehouse',
    destination_id: null,
    notes: '',
    items: [
        {
            inventory_id: null,
            product_id: null,
            quantity: 0,
            batch_number: '',
            barcode: '',
            expire_date: null,
            uom: ''
        }
    ]
});

const errors = ref({});

const sourceOptions = computed(() => {
    return sourceType.value === 'warehouse' ? props.warehouses : props.facilities;
});

const destinationOptions = computed(() => {
    return destinationType.value === 'warehouse' ? props.warehouses : props.facilities;
});

// Filter out the selected source from destination options if they are of the same type
const filteredDestinationOptions = computed(() => {
    if (sourceType.value === destinationType.value && selectedSource.value) {
        return destinationOptions.value.filter(item => item.id !== selectedSource.value.id);
    }
    return destinationOptions.value;
});

const handleSourceSelect = async (selected) => {
    form.value.source_id = selected ? selected.id : null;
    selectedInventory.value = null;
    
    // Reset the items array to have only one empty item when source changes
    form.value.items = [{
        inventory_id: null,
        product_id: null,
        selected_inventory: null,
        product_name: '',
        quantity: 0,
        batch_number: '',
        barcode: '',
        expire_date: null,
        uom: '',
        available_quantity: 0
    }];
    
    if (selected) {
        await fetchInventories();
    } else {
        availableInventories.value = [];
        filteredInventories.value = [];
    }
};

const handleDestinationSelect = (selected) => {
    form.value.destination_id = selected ? selected.id : null;
};

const handleInventorySelect = (inventory) => {
    form.value.inventory_id = inventory ? inventory.id : null;
    selectedInventory.value = inventory;
};

const fetchInventories = async () => {
    if (!selectedSource.value || !form.value.source_id) {
        return;
    }
    
    loadingInventories.value = true;
    filteredInventories.value = [];
    searchQuery.value = '';
    
    try {
        const sourceId = form.value.source_id;
        const response = await axios.get(route('transfers.getInventories'), {
            params: {
                source_type: sourceType.value,
                source_id: sourceId
            }
        });

        console.log(response.data);

        
        availableInventories.value = response.data;
    } catch (error) {
        console.error('Failed to fetch inventories:', error);
    } finally {
        loadingInventories.value = false;
    }
};

const searchInventories = () => {
    if (!searchQuery.value || searchQuery.value.trim() === '') {
        filteredInventories.value = [];
        return;
    }
    
    const query = searchQuery.value.toLowerCase();
    filteredInventories.value = availableInventories.value.filter(item => {
        const productName = item.product?.name?.toLowerCase() || '';
        const batchNumber = item.batch_number?.toLowerCase() || '';
        const barcode = item.product?.barcode?.toLowerCase() || '';
        
        return productName.includes(query) || 
               batchNumber.includes(query) || 
               barcode.includes(query);
    });
};

const validateForm = () => {
    errors.value = {};
    if (!form.value.source_id) errors.value.source_id = 'Please select a source.';
    if (!form.value.destination_id) errors.value.destination_id = 'Please select a destination.';
    
    // Validate that at least one item is selected
    let validItems = true;
    let hasItems = false;
    
    form.value.items.forEach((item, index) => {
        if (!item.inventory_id) {
            errors.value[`item_${index}_inventory`] = 'Please select an inventory item.';
            validItems = false;
        }
        
        if (item.inventory_id && (!item.quantity || item.quantity <= 0)) {
            errors.value[`item_${index}_quantity`] = 'Quantity must be greater than 0.';
            validItems = false;
        }
        
        // Removed the constraint that prevents quantity from exceeding available quantity
        
        if (item.inventory_id && item.quantity > 0) {
            hasItems = true;
        }
    });
    
    if (!hasItems) {
        errors.value.items = 'At least one item must be selected with a quantity greater than 0.';
    }
    
    return Object.keys(errors.value).length === 0 && validItems && hasItems;
};

// Watch for changes in source type and update form
watch(sourceType, (newValue) => {
    form.value.source_type = newValue;
    form.value.source_id = null;
    selectedSource.value = null;
    selectedInventory.value = null;
    form.value.inventory_id = null;
    availableInventories.value = [];
    filteredInventories.value = [];
});

// Watch for changes in destination type and update form
watch(destinationType, (newValue) => {
    form.value.destination_type = newValue;
    form.value.destination_id = null;
    selectedDestination.value = null;
});

const submit = async () => {
    if (!validateForm()) {
        toast.error('Please correct the errors in the form.');
        return;
    }
    
    // Filter out items with no quantity or inventory_id
    const validItems = form.value.items.filter(item => item.inventory_id && item.quantity > 0);
    const totalItems = validItems.length;
    const totalQuantity = validItems.reduce((sum, item) => sum + item.quantity, 0);
    
    if (totalItems === 0) {
        toast.error('Please select at least one item to transfer.');
        return;
    }
    
    // Create confirmation message based on number of items
    let confirmMessage = totalItems === 1 
        ? `Are you sure you want to transfer ${validItems[0].quantity} units of ${validItems[0].product_name}?`
        : `Are you sure you want to transfer ${totalItems} different items (total quantity: ${totalQuantity})?`;

    const result = await Swal.fire({
        title: 'Confirm Transfer',
        text: confirmMessage,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, transfer it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        loading.value = true;
        
        // Create payload with only valid items
        const payload = {
            source_type: form.value.source_type,
            source_id: form.value.source_id,
            destination_type: form.value.destination_type,
            destination_id: form.value.destination_id,
            notes: form.value.notes,
            items: validItems
        };

        try {
            const response = await axios.post(route('transfers.store'), payload);
            
            Swal.fire({
                title: 'Transfer Successful!',
                text: `Transfer ID: ${response.data.transfer_id}`,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.visit(route('transfers.index'));
            });
        } catch (error) {
            console.error('Transfer error:', error);
            if (error.response && error.response.data) {
                if (typeof error.response.data === 'string') {
                    toast.error(error.response.data);
                } else if (error.response.data.message) {
                    toast.error(error.response.data.message);
                } else {
                    toast.error('An error occurred while creating the transfer.');
                }
                
                if (error.response.data.errors) {
                    errors.value = error.response.data.errors;
                }
            } else {
                toast.error('An error occurred while creating the transfer.');
            }
        } finally {
            loading.value = false;
        }
    }
};

function handleProductSelect(index, selected) {
    const item = form.value.items[index];
    if (selected) {
        // Store the full inventory object in selectedInventory for reference
        console.log('Selected inventory item:', selected);
        
        item.inventory_id = selected.id;
        item.product_id = selected.product_id;
        item.selected_inventory = selected; // Store the full inventory object
        item.product_name = selected.product?.name || ''; 
        item.batch_number = selected.batch_number || '';
        item.barcode = selected.product?.barcode || '';
        item.expire_date = selected.expiry_date || selected.expire_date || null;
        item.uom = selected.product?.uom || '';
        item.available_quantity = selected.quantity || 0;
        item.quantity = 0; // Reset quantity when new item selected
    } else {
        // Reset item properties if selection is cleared
        item.inventory_id = null;
        item.product_id = null;
        item.selected_inventory = null;
        item.product_name = '';
        item.batch_number = '';
        item.barcode = '';
        item.expire_date = null;
        item.uom = '';
        item.available_quantity = 0;
        item.quantity = 0;
    }
}

function addNewItem() {
    form.value.items.push({
        inventory_id: null,
        product_id: null,
        selected_inventory: null,
        product_name: '',
        quantity: 0,
        batch_number: '',
        barcode: '',
        expire_date: null,
        uom: '',
        available_quantity: 0
    });
}

function removeItem(index) {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
}

function checkQuantity(index) {
    const item = form.value.items[index];
    if (item.quantity > item.available_quantity) {
        // Reset to available quantity if exceeded
        item.quantity = item.available_quantity;
        toast.warning(`Quantity reset to maximum available (${item.available_quantity})`);
    }
}
</script>

<template>
    <AuthenticatedLayout title="Transfer Item">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Transfer Item</h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Source Type Selection -->
                        <div>
                            <InputLabel value="Transfer From" />
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input 
                                        type="radio" 
                                        v-model="sourceType" 
                                        value="warehouse" 
                                        class="form-radio">
                                    <span class="ml-2">Warehouse</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input 
                                        type="radio" 
                                        v-model="sourceType" 
                                        value="facility" 
                                        class="form-radio">
                                    <span class="ml-2">Facility</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Source Selection -->
                        <div>
                            <InputLabel :value="`Select Source ${sourceType === 'warehouse' ? 'Warehouse' : 'Facility'}`" />
                            <Multiselect
                                v-model="selectedSource"
                                :options="sourceOptions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                :placeholder="`Select source ${sourceType === 'warehouse' ? 'warehouse' : 'facility'}`"
                                track-by="id"
                                label="name"
                                @select="handleSourceSelect"
                            >
                                <template v-slot:option="{ option }">
                                    <span>{{ option.name }}</span>
                                </template>
                            </Multiselect>
                            <InputError :message="errors.source_id" class="mt-2" />
                        </div>
                        
                        <!-- Destination Type Selection -->
                        <div>
                            <InputLabel value="Transfer To" />
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input 
                                        type="radio" 
                                        v-model="destinationType" 
                                        value="warehouse" 
                                        class="form-radio">
                                    <span class="ml-2">Warehouse</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input 
                                        type="radio" 
                                        v-model="destinationType" 
                                        value="facility" 
                                        class="form-radio">
                                    <span class="ml-2">Facility</span>
                                </label>
                            </div>
                        </div>

                        <!-- Destination Selection -->
                        <div>
                            <InputLabel :value="`Select Destination ${destinationType === 'warehouse' ? 'Warehouse' : 'Facility'}`" />
                            <Multiselect
                                v-model="selectedDestination"
                                :options="filteredDestinationOptions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                :placeholder="`Select destination ${destinationType === 'warehouse' ? 'warehouse' : 'facility'}`"
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
                        
                       <!-- here for items -->


                       
                       
                       
                    </div>                    
                    <div class="mb-4">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="w-full bg-gray-50">
                                <tr>
                                    <th class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Batch number
                                    </th> 
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity to release</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="loadingInventories">
                                </tr>
                                <tr v-else-if="!selectedSource">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Select a source {{ sourceType === 'warehouse' ? 'warehouse' : 'facility' }} first</td>
                                </tr>
                                <tr v-else-if="filteredInventories.length === 0 && searchQuery">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No matching inventory items found</td>
                                </tr>
                                <tr v-else-if="filteredInventories.length === 0 && !searchQuery && availableInventories.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No inventory items available in this {{ sourceType === 'warehouse' ? 'warehouse' : 'facility' }}</td>
                                </tr>
                                
                                <!-- Inventory Items rows -->
                                <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <Multiselect 
                                            v-model="item.selected_inventory"
                                            :options="filteredInventories.length ? filteredInventories : availableInventories"
                                            :searchable="true"
                                            :close-on-select="true"
                                            :show-labels="false"
                                            :allow-empty="true"
                                            placeholder="Select product"
                                            track-by="id"
                                            @select="handleProductSelect(index, $event)"
                                        >
                                            <template v-slot:option="{ option }">
                                                <span>{{ option.product?.name }}</span>
                                            </template>
                                            <template v-slot:singleLabel="{ option }">
                                                <span>{{ option.product?.name }}</span>
                                            </template>
                                        </Multiselect>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div>{{ item.batch_number }}</div>
                                        <div>Barcode: {{ item.barcode }}</div>
                                        <div>Expire Date: {{ item.expire_date ? new Date(item.expire_date).toLocaleDateString() : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                        {{ item.uom }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                        {{ item.available_quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                        <TextInput 
                                            type="number" 
                                            v-model.number="item.quantity" 
                                            class="w-24 text-sm" 
                                            min="1"
                                            :max="item.available_quantity"
                                            :disabled="!item.inventory_id"
                                            placeholder="0"
                                            @input="checkQuantity(index)"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button 
                                            type="button" 
                                            @click="removeItem(index)" 
                                            class="text-red-600 hover:text-red-900"
                                            :disabled="form.items.length <= 1"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mb-4">
                        <button 
                            type="button" 
                            @click="addNewItem" 
                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            Add Another Item
                        </button>
                    </div>
                    
                    <!-- Notes Field -->
                    <div class="mb-4">
                        <InputLabel value="Notes" />
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="Add any additional notes here..."
                        ></textarea>
                        <InputError :message="errors.notes" class="mt-2" />
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
    </AuthenticatedLayout>
</template>