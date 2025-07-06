<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import axios from "axios";
import { useToast } from "vue-toastification";
import Swal from "sweetalert2";
import moment from "moment";

const toast = useToast();

const props = defineProps({
    inventory: Object,
    warehouses: {
        type: Array,
        default: () => [],
    },
    facilities: {
        type: Array,
        default: () => [],
    },
    transferID: String,
});

const destinationType = ref("warehouse");
const loading = ref(false);
const selectedDestination = ref(null);
const quantityToTransfer = ref("");
const transfer_date = ref(moment().format("YYYY-MM-DD"));
const transfer_reason = ref("");

const form = ref({
    source_type: "warehouse", // Always warehouse for expired items
    source_id: props.inventory?.warehouse_id, // Automatically set from inventory
    destination_type: "warehouse",
    destination_id: null,
    transfer_date: moment().format("YYYY-MM-DD"),
    transferID: props.transferID,
    items: [
        {
            product_id: props.inventory?.product_id,
            quantity: 0,
            details: [
                {
                    id: props.inventory?.id,
                    quantity_to_transfer: 0,
                    transfer_reason: "",
                }
            ],
        },
    ],
});

const errors = ref({});

const destinationOptions = computed(() => {
    return destinationType.value === "warehouse"
        ? props.warehouses
        : props.facilities;
});

// Filter out the source warehouse from destination options
const filteredDestinationOptions = computed(() => {
    return destinationOptions.value.filter(
        (item) => item.id !== props.inventory?.warehouse_id
    );
});

const handleDestinationSelect = (selected) => {
    form.value.destination_id = selected ? selected.id : null;
    selectedDestination.value = selected;
    errors.value.destination_id = null;
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    // Validate destination
    if (!form.value.destination_id) {
        errors.value.destination_id = "Please select a destination.";
        isValid = false;
    }

    // Check if source and destination are the same
    if (form.value.source_id === form.value.destination_id) {
        errors.value.destination_id = "Source and destination cannot be the same.";
        isValid = false;
    }

    // Validate quantity
    if (!quantityToTransfer.value || parseInt(quantityToTransfer.value) < 1) {
        errors.value.quantity = "Quantity must be at least 1.";
        isValid = false;
    }

    if (parseInt(quantityToTransfer.value) > props.inventory.quantity) {
        errors.value.quantity = `Maximum available quantity is ${props.inventory.quantity}.`;
        isValid = false;
    }

    // Validate transfer reason
    if (!transfer_reason.value) {
        errors.value.transfer_reason = "Please select a transfer reason.";
        isValid = false;
    }

    return isValid;
};

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
};

const isExpiringSoon = (date) => {
    const expiryDate = moment(date);
    const now = moment();
    const daysUntilExpiry = expiryDate.diff(now, 'days');
    return daysUntilExpiry <= 180 && daysUntilExpiry > 0;
};

const handleSubmit = async () => {
    if (!validateForm()) {
        return;
    }

    // Update form with current values
    form.value.destination_type = destinationType.value;
    form.value.transfer_date = transfer_date.value;
    form.value.items[0].quantity = parseInt(quantityToTransfer.value);
    form.value.items[0].details[0].quantity_to_transfer = parseInt(quantityToTransfer.value);
    form.value.items[0].details[0].transfer_reason = transfer_reason.value;

    Swal.fire({
        title: "Confirm Transfer",
        html: `
            <div class="text-left">
                <p><strong>Item:</strong> ${props.inventory.product.name}</p>
                <p><strong>Quantity:</strong> ${quantityToTransfer.value}</p>
                <p><strong>Destination:</strong> ${selectedDestination.value.name}</p>
            </div>
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, transfer it!",
        cancelButtonText: "Cancel",
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        loading.value = true;

        await axios
            .post(route("transfers.store"), form.value)
            .then((response) => {
                loading.value = false;
                console.log(response.data);
                Swal.fire({
                    title: "Success!",
                    text: "Transfer completed successfully",
                    icon: "success",
                    confirmButtonText: "OK",
                }).then(() => {
                    router.visit(route("expired.index"));
                });
            })
            .catch((error) => {
                loading.value = false;
                console.log(error);
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data || "Failed to transfer",
                    icon: "error",
                    confirmButtonText: "OK",
                });
            });
    });
};
</script>

<template>
    <AuthenticatedLayout
        title="Transfer Expired Item"
        description="Transfer expired or soon to be expired items to warehouse or facility"
        img="/assets/images/transfer.png"
    >
        <div class="max-w-7xl mx-auto py-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Transfer Expired Item</h1>
                            <p class="mt-1 text-sm text-gray-600">Transfer ID: {{ props.transferID }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Transfer Date</p>
                                <p class="text-sm text-gray-600">{{ formatDate(transfer_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit" class="px-8 py-6">
                    <!-- Transfer Information Section -->
                    <div class="mb-8">
                        <div class="flex items-center space-x-2 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Transfer Information</h3>
                        </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                                <!-- Transfer Date -->
                                <div>
                                    <InputLabel for="transfer_date" value="Transfer Date" class="text-sm font-medium text-gray-700" />
                                    <input
                                        id="transfer_date"
                                        type="date"
                                        v-model="transfer_date"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    />
                                </div>
                            </div>
                    </div>

                    <!-- Source Information Section -->
                    <div class="mb-8">
                        <div class="flex items-center space-x-2 mb-6">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Transfer From</h3>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Source Type</p>
                                    <p class="text-sm text-gray-900 font-semibold">Warehouse</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Source Warehouse</p>
                                    <p class="text-sm text-gray-900 font-semibold">{{ props.inventory?.warehouse?.name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Location</p>
                                    <p class="text-sm text-gray-900 font-semibold">{{ props.inventory?.location?.location || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Destination Section -->
                    <div class="mb-8">
                        <div class="flex items-center space-x-2 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h14m-6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Transfer To</h3>
                        </div>
                        
                        <!-- Destination Type Selection -->
                        <div class="mb-6">
                            <InputLabel value="Destination Type" class="text-sm font-medium text-gray-700 mb-3" />
                            <div class="flex space-x-4">
                                <label class="flex items-center px-4 py-2 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200"
                                       :class="destinationType === 'warehouse' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300'">
                                    <input
                                        type="radio"
                                        v-model="destinationType"
                                        value="warehouse"
                                        class="sr-only"
                                    />
                                    <div class="w-4 h-4 rounded-full border-2 mr-2 flex items-center justify-center"
                                         :class="destinationType === 'warehouse' ? 'border-blue-500' : 'border-gray-400'">
                                        <div v-if="destinationType === 'warehouse'" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <span class="font-medium">Warehouse</span>
                                </label>
                                <label class="flex items-center px-4 py-2 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200"
                                       :class="destinationType === 'facility' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300'">
                                    <input
                                        type="radio"
                                        v-model="destinationType"
                                        value="facility"
                                        class="sr-only"
                                    />
                                    <div class="w-4 h-4 rounded-full border-2 mr-2 flex items-center justify-center"
                                         :class="destinationType === 'facility' ? 'border-blue-500' : 'border-gray-400'">
                                        <div v-if="destinationType === 'facility'" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <span class="font-medium">Facility</span>
                                </label>
                            </div>
                        </div>

                        <!-- Destination Selection -->
                        <div>
                            <InputLabel value="Select Destination" class="text-sm font-medium text-gray-700 mb-2" />
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
                                :class="{ 'border-red-500': errors.destination_id }"
                                @open="errors.destination_id = null"
                            >
                                <template v-slot:option="{ option }">
                                    <span>{{ option.name }}</span>
                                </template>
                            </Multiselect>
                            <InputError :message="errors.destination_id" class="mt-2" />
                        </div>
                    </div>

                    <!-- Items Table Section -->
                    <div class="mb-4 overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr class="bg-gray-50">
                                    <th class="min-w-[120px] px-2 py-2 text-left text-xs text-black capitalize border border-black" rowspan="2">
                                        Item Name
                                    </th>
                                    <th class="px-2 py-2 text-left text-xs text-black capitalize border border-black" rowspan="2">
                                        Category
                                    </th>
                                    <th class="px-2 py-2 text-left text-xs text-black capitalize border border-black" rowspan="2">
                                        UoM
                                    </th>
                                    <th class="px-2 py-2 text-center text-xs text-black capitalize border border-black" rowspan="2">
                                        Total Quantity on Hand Per Unit
                                    </th>
                                    <th class="px-2 py-2 text-center text-xs text-black capitalize border border-black" colspan="4">
                                        Item details
                                    </th>
                                    
                                    <th class="min-w-[150px] px-2 py-2 text-left text-xs text-black capitalize border border-black" rowspan="2">
                                        Reasons for Transfers
                                    </th>
                                    <th class="min-w-[110px] px-2 py-2 text-left text-xs text-black capitalize border border-black" rowspan="2">
                                        Quantity to be transferred
                                    </th>
                                    <th class="px-2 py-2 text-left text-xs text-black capitalize border border-black" rowspan="2">
                                        Total Quantity to be transferred
                                    </th>
                                </tr>
                                <tr class="bg-gray-50">
                                    <th class="px-2 py-1 text-xs border border-black text-left">
                                        QTY
                                    </th>
                                    <th class="px-2 py-1 text-xs border border-black text-left">
                                        Batch Number
                                    </th>
                                    <th class="px-2 py-1 text-xs border border-black text-left">
                                        Expiry Date
                                    </th>
                                    <th class="px-2 py-1 text-xs border border-black text-left">
                                        Location
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <!-- Item Name -->
                                    <td class="min-w-[200px] px-3 py-3 text-xs text-gray-900 border border-black align-top">
                                        {{ props.inventory.product.name }}
                                    </td>

                                    <!-- Category -->
                                    <td class="px-3 py-3 text-xs text-gray-900 border border-black text-center">
                                        {{ props.inventory.product?.category?.name || '' }}
                                    </td>

                                    <!-- UoM -->
                                    <td class="px-3 py-3 text-xs text-gray-900 border border-black text-center">
                                        {{ props.inventory.uom || '' }}
                                    </td>

                                    <!-- Total Quantity on Hand Per Unit -->
                                    <td class="px-3 py-3 text-xs text-center border border-black">
                                        {{ props.inventory.quantity }}
                                    </td>

                                    <!-- Item Details Columns -->
                                    <!-- Quantity -->
                                    <td class="px-2 py-1 text-xs border border-black text-center">
                                        {{ props.inventory.quantity }}
                                    </td>

                                    <!-- Batch Number -->
                                    <td class="px-2 py-1 text-xs border border-black text-center">
                                        {{ props.inventory.batch_number || '' }}
                                    </td>

                                    <!-- Expiry Date -->
                                    <td class="px-2 py-1 text-xs border border-black text-center" :class="{ 'text-red-600': isExpiringSoon(props.inventory.expiry_date) }">
                                        {{ props.inventory.expiry_date ? formatDate(props.inventory.expiry_date) : '' }}
                                    </td>

                                    <!-- Location -->
                                    <td class="px-2 py-1 text-xs border border-black text-center">
                                        {{ props.inventory.location?.location || '' }}
                                    </td>

                                    <!-- Reasons for Transfers -->
                                    <td class="px-2 py-1 text-xs border border-black text-center">
                                        <select
                                            v-model="transfer_reason"
                                            class="w-full text-xs border rounded px-2 py-1"
                                            :class="{ 'border-red-500': errors.transfer_reason }"
                                        >
                                            <option value="">Select reason...</option>
                                            <option value="Soon to expire">Soon to expire</option>
                                            <option value="Expired">Expired</option>
                                            <option value="Replenishment">Replenishment</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <InputError :message="errors.transfer_reason" class="mt-1" />
                                    </td>

                                    <!-- Quantity to be transferred -->
                                    <td class="px-2 py-1 text-xs border border-black text-center">
                                        <input
                                            type="number"
                                            v-model="quantityToTransfer"
                                            :max="props.inventory.quantity"
                                            min="0"
                                            class="w-full text-xs border rounded px-2 py-1 text-center"
                                            placeholder="0"
                                            :class="{ 'border-red-500': errors.quantity }"
                                        />
                                        <InputError :message="errors.quantity" class="mt-1" />
                                    </td>

                                    <!-- Total Quantity to be transferred -->
                                    <td class="px-3 py-3 text-xs text-center border border-black">
                                        <div class="text-sm font-medium text-blue-600">
                                            {{ quantityToTransfer || 0 }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-8 border-t border-gray-200">
                        <SecondaryButton
                            :href="route('expired.index')"
                            as="a"
                            :disabled="loading"
                            class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200"
                            :class="{
                                'opacity-50 cursor-not-allowed': loading,
                            }"
                        >
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            :disabled="loading" 
                            class="relative px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                        >
                            <span v-if="loading" class="absolute left-3">
                                <svg
                                    class="animate-spin h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                            </span>
                            <span :class="{ 'pl-7': loading }">{{
                                loading ? "Processing..." : "Create Transfer"
                            }}</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
