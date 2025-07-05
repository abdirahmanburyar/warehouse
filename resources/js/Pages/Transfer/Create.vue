<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed, watch } from "vue";
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
    transferID: {
        type: String,
        required: true,
    },
});

const sourceType = ref("warehouse");
const destinationType = ref("warehouse");
const loading = ref(false);
const selectedSource = ref(null);
const selectedDestination = ref(null);
const selectedInventory = ref(null);
const filteredInventories = ref([]);
const availableInventories = ref([]);
const searchQuery = ref("");
const loadingInventories = ref(false);

const form = ref({
    source_type: "warehouse", // Default source type is warehouse since it's an expired item
    source_id: null,
    destination_type: "warehouse",
    destination_id: null,
    transfer_date: moment().format("YYYY-MM-DD"),
    transferID: props.transferID,
    transfer_type: "",
    items: [
        {
            id: null,
            product_id: "",
            product: null,
            quantity: 0,
            available_quantity: 0,
            details: [],
            transfer_reason: "",
        },
    ],
});

const errors = ref({});

const sourceOptions = computed(() => {
    return sourceType.value === "warehouse"
        ? props.warehouses
        : props.facilities;
});

const destinationOptions = computed(() => {
    return destinationType.value === "warehouse"
        ? props.warehouses
        : props.facilities;
});

// Filter out the selected source from destination options if they are of the same type
const filteredDestinationOptions = computed(() => {
    if (sourceType.value === destinationType.value && selectedSource.value) {
        return destinationOptions.value.filter(
            (item) => item.id !== selectedSource.value.id
        );
    }
    return destinationOptions.value;
});

const handleSourceSelect = async (selected) => {
    form.value.source_id = selected ? selected.id : null;
    selectedInventory.value = null;

    // Reset the items array to have only one empty item when source changes
    form.value.items = [
        {
            id: null,
            product_id: null,
            product: null,
            quantity: 0,
            batch_number: "",
            barcode: "",
            expiry_date: null,
            uom: "",
            available_quantity: 0,
            transfer_reason: "",
        },
    ];

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

const fetchInventories = async () => {
    // If no source selected, reset and exit
    if (!selectedSource.value || !form.value.source_id) {
        availableInventories.value = [];
        filteredInventories.value = [];
        return;
    }

    // Set loading state
    loadingInventories.value = true;
    filteredInventories.value = [];
    searchQuery.value = "";

    // Show Swal loading counter
    let timerInterval;
    const swalLoading = Swal.fire({
        title: "Loading Inventory",
        html: "Fetching inventory items... <b></b>",
        timer: 30000, // 30 seconds max timeout
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
            const timer = Swal.getPopup().querySelector("b");
            timerInterval = setInterval(() => {
                timer.textContent = `${Math.ceil(Swal.getTimerLeft() / 1000)}s`;
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        },
    });

    try {
        // Use GET request to fetch available inventories
        const response = await axios.get(route("transfers.getInventories"), {
            params: {
                source_type: sourceType.value,
                source_id: form.value.source_id,
            },
        });

        console.log("[SUCCESS] Fetch inventories response:", response.data);
        swalLoading.close();
        availableInventories.value = response.data;
        filteredInventories.value = availableInventories.value;
        loadingInventories.value = false;
    } catch (error) {
        console.error("[ERROR] Fetch inventories failed:", error);
        console.error("[ERROR] Full error object:", error.response);

        // Close loading dialog first
        swalLoading.close();

        // Reset states
        loadingInventories.value = false;
        availableInventories.value = [];
        filteredInventories.value = [];

        // Add small delay before showing error dialog to avoid timing conflicts
        setTimeout(() => {
            let errorMessage = "Failed to fetch inventories";

            if (error.response) {
                console.log(
                    "[DEBUG] Error response status:",
                    error.response.status
                );
                console.log(
                    "[DEBUG] Error response data:",
                    error.response.data
                );

                if (typeof error.response.data === "string") {
                    errorMessage = error.response.data;
                } else if (error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response.data && error.response.data.error) {
                    errorMessage = error.response.data.error;
                } else {
                    errorMessage = `Server error (${error.response.status})`;
                }
            } else if (error.message) {
                errorMessage = error.message;
            }

            console.log("[DEBUG] Final error message:", errorMessage);

            Swal.fire({
                title: "Error!",
                text: errorMessage,
                icon: "error",
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 5000,
            });
        }, 100); // 100ms delay
    }
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    // Validate source and destination
    if (!form.value.source_id) {
        errors.value.source_id = "Please select a source.";
        isValid = false;
    }

    if (!form.value.destination_id) {
        errors.value.destination_id = "Please select a destination.";
        isValid = false;
    }

    // Check if source and destination are the same
    if (
        form.value.source_id === form.value.destination_id &&
        form.value.source_type === form.value.destination_type
    ) {
        errors.value.destination_id =
            "Source and destination cannot be the same.";
        isValid = false;
    }

    // Validate that all items are properly filled
    let hasValidItems = false;

    form.value.items.forEach((item, index) => {
        // Check if inventory item is selected
        if (!item.inventory_id) {
            errors.value[`item_${index}_inventory`] =
                "Please select an inventory item.";
            isValid = false;
        }

        // Check if quantity is valid (must be at least 1)
        if (item.inventory_id && (!item.quantity || item.quantity < 1)) {
            errors.value[`item_${index}_quantity`] =
                "Quantity must be at least 1.";
            isValid = false;
        }

        // Check if quantity exceeds available quantity
        if (item.inventory_id && item.quantity > item.available_quantity) {
            errors.value[
                `item_${index}_quantity`
            ] = `Maximum available quantity is ${item.available_quantity}.`;
            isValid = false;
        }

        // Track if we have at least one valid item
        if (
            item.inventory_id &&
            item.quantity >= 1 &&
            item.quantity <= item.available_quantity
        ) {
            hasValidItems = true;
        }
    });

    // Ensure at least one valid item exists
    if (!hasValidItems) {
        errors.value.items =
            "At least one item must be selected with a valid quantity.";
        isValid = false;
    }

    return isValid;
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
    loading.value = true;

    // Validate that at least one item has quantity to transfer
    const hasItemsToTransfer = form.value.items.some(item => 
        item.details.some(detail => 
            parseFloat(detail.quantity_to_transfer) > 0
        )
    );

    if (!hasItemsToTransfer) {
        loading.value = false;
        Swal.fire({
            title: "No Items to Transfer",
            text: "Please specify quantities to transfer for at least one item.",
            icon: "warning",
            confirmButtonColor: "#4F46E5",
        });
        return;
    }

    // Filter out items with no quantity to transfer
    const filteredItems = form.value.items.filter(item => 
        item.product_id && item.details.some(detail => 
            parseFloat(detail.quantity_to_transfer) > 0
        )
    );

    const submitData = {
        ...form.value,
        items: filteredItems
    };

    console.log("Submitting data:", submitData);
    
    await axios
        .post(route("transfers.store"), submitData)
        .then((response) => {
            loading.value = false;
            Swal.fire({
                title: "Success!",
                text: response.data,
                icon: "success",
                confirmButtonColor: "#4F46E5",
            }).then(() => {
                router.get(route("transfers.index"));
            });
        })
        .catch((error) => {
            console.error(error.response);
            loading.value = false;
            Swal.fire({
                title: "Error!",
                text: error.response?.data || "Failed to create transfer",
                icon: "error",
                confirmButtonColor: "#4F46E5",
            });
        });
};

const isLoading = ref([]);
async function handleProductSelect(index, selected) {
    isLoading.value[index] = true;
    const item = form.value.items[index];
    item.details = [];
    if (selected) {
        await axios
            .post(route("transfers.inventory"), {
                product_id: selected.id,
                source_type: sourceType.value,
                source_id: form.value.source_id,
            })
            .then((response) => {
                isLoading.value[index] = false;
                console.log(response.data);

                // Initialize quantity_to_transfer for each detail item
                item.details = response.data.map(detail => ({
                    ...detail,
                    quantity_to_transfer: 0,
                    transfer_reason: ''
                }));
                
                item.available_quantity = response.data?.reduce(
                    (sum, item) => sum + item.quantity,
                    0
                );
                item.product = selected;
                item.product_id = selected.id;
                item.quantity = 0; // Reset main quantity
                addNewItem();
            })
            .catch((error) => {
                isLoading.value[index] = false;
                console.log(error);

                // Clear product fields on error
                item.product_id = null;
                item.product = null;
                item.details = [];
                item.available_quantity = 0;

                Swal.fire({
                    title: "Error!",
                    text: error.response.data,
                    icon: "error",
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 5000,
                });
            });
    }
}

// Function to update main item quantity based on detail quantities
function updateItemQuantity(index) {
    const item = form.value.items[index];
    const totalQuantity = item.details.reduce((sum, detail) => {
        return sum + (parseFloat(detail.quantity_to_transfer) || 0);
    }, 0);
    
    item.quantity = totalQuantity;
    
    // Clear any existing quantity errors
    if (errors.value[`item_${index}_quantity`]) {
        errors.value[`item_${index}_quantity`] = null;
    }
}

function addNewItem() {
    form.value.items.push({
        id: null,
        product_id: null,
        product: null,
        available_quantity: 0,
        quantity: 0,
        details: [],
        transfer_reason: "",
    });
}

function removeItem(index) {
    // Check if we have more than one item before removing
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
}

function checkQuantity(index) {
    const item = form.value.items[index];

    // Ensure quantity is at least 1
    if (item.quantity < 1) {
        item.quantity = 1;
        toast.info("Minimum quantity is 1");
    }

    // Ensure quantity doesn't exceed available quantity
    if (item.quantity > item.available_quantity) {
        // Reset to available quantity if exceeded
        item.quantity = item.available_quantity;
        toast.warning(
            `Quantity reset to maximum available (${item.available_quantity})`
        );
    }
}

function formatDate(date) {
    return moment(date).format("DD/MM/YYYY");
}

// Function to check if date is expiring soon (within 6 months)
function isExpiringSoon(expiryDate) {
    if (!expiryDate) return false;
    const today = moment();
    const expiry = moment(expiryDate);
    const monthsDiff = expiry.diff(today, 'months');
    return monthsDiff <= 6 && monthsDiff >= 0;
}
</script>

<template>
    <AuthenticatedLayout
        title="Optimize Your Transfers"
        description="Moving Supplies, Bridging needs"
        img="/assets/images/transfer.png"
    >
        <div class="mb-5">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Transfer Item</h2>
                <div class="mb-4">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-blue-400"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Select source and destination locations,
                                    then choose inventory items to transfer. The
                                    quantity must not exceed available quantity.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
               <div class="flex justify-between mb-6">
                    <h2 class="text-xs">Transfer Item</h2>
                    <div class="flex flex-col">
                        Transfer ID: {{ props.transferID }}
                    </div>
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="flex gap-2">
                        <div>
                            <div class="flex flex-col">
                                <label for="transfer_date">Transfer Date</label>
                                <input
                                    type="date"
                                    v-model="form.transfer_date"
                                    class="form-input"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <label for="transfer_type">Transfer Type</label>
                            <textarea
                                name="transfer_type"
                                id="transfer_type"
                                v-model="form.transfer_type"
                                class="form-input w-[300px]"
                                placeholder="Enter transfer type [Soon to expire, Replenishment, ...]"
                            ></textarea>
                        </div>
                    </div>
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
                                        class="form-radio"
                                    />
                                    <span class="ml-2">Warehouse</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        v-model="sourceType"
                                        value="facility"
                                        class="form-radio"
                                    />
                                    <span class="ml-2">Facility</span>
                                </label>
                            </div>
                        </div>

                        <!-- Source Selection -->
                        <div>
                            <InputLabel
                                :value="`Select Source ${
                                    sourceType === 'warehouse'
                                        ? 'Warehouse'
                                        : 'Facility'
                                }`"
                            />
                            <Multiselect
                                v-model="selectedSource"
                                :options="sourceOptions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                :placeholder="`Select source ${
                                    sourceType === 'warehouse'
                                        ? 'warehouse'
                                        : 'facility'
                                }`"
                                track-by="id"
                                label="name"
                                @select="handleSourceSelect"
                                :class="{ 'border-red-500': errors.source_id }"
                                @open="errors.source_id = null"
                            >
                                <template v-slot:option="{ option }">
                                    <span>{{ option.name }}</span>
                                </template>
                            </Multiselect>
                            <InputError
                                :message="errors.source_id"
                                class="mt-2"
                            />
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
                                        class="form-radio"
                                    />
                                    <span class="ml-2">Warehouse</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        v-model="destinationType"
                                        value="facility"
                                        class="form-radio"
                                    />
                                    <span class="ml-2">Facility</span>
                                </label>
                            </div>
                        </div>

                        <!-- Destination Selection -->
                        <div>
                            <InputLabel
                                :value="`Select Destination ${
                                    destinationType === 'warehouse'
                                        ? 'Warehouse'
                                        : 'Facility'
                                }`"
                            />
                            <Multiselect
                                v-model="selectedDestination"
                                :options="filteredDestinationOptions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                :placeholder="`Select destination ${
                                    destinationType === 'warehouse'
                                        ? 'warehouse'
                                        : 'facility'
                                }`"
                                track-by="id"
                                label="name"
                                @select="handleDestinationSelect"
                                required
                                :class="{
                                    'border-red-500': errors.destination_id,
                                }"
                                @open="errors.destination_id = null"
                            >
                                <template v-slot:option="{ option }">
                                    <span>{{ option.name }}</span>
                                </template>
                            </Multiselect>
                            <InputError
                                :message="errors.destination_id"
                                class="mt-2"
                            />
                        </div>
                        <!-- here for items -->
                    </div>
                    <!-- here for items -->

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
                                    <th class="px-2 py-2 text-left text-xs text-black capitalize border border-black" rowspan="2">
                                        Action
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
                                <template v-for="(item, index) in form.items" :key="index">
                                    <!-- Show details if they exist, otherwise show one row with main item data -->
                                    <tr v-for="(detail, detailIndex) in (item.details?.length > 0 ? item.details : [{}])"
                                        :key="`${index}-${detail.id || detailIndex}`"
                                        class="hover:bg-gray-50 transition-colors duration-150">
                                        
                                        <!-- Item Name - only on first row for this item -->
                                        <td v-if="detailIndex === 0" :rowspan="Math.max(item.details?.length || 1, 1)"
                                            class="min-w-[200px] px-3 py-3 text-xs text-gray-900 border border-black align-top">
                                            <div class="w-full">
                                                <Multiselect
                                                    v-model="item.product"
                                                    :value="item.product_id"
                                                    :options="availableInventories"
                                                    placeholder="Search for an item..."
                                                    required
                                                    track-by="id"
                                                    label="name"
                                                    :searchable="true"
                                                    :allow-empty="true"
                                                    :loading="isLoading[index]"
                                                    @select="handleProductSelect(index, $event)"
                                                />
                                            </div>
                                        </td>

                                        <!-- Category - only on first row for this item -->
                                        <td v-if="detailIndex === 0" :rowspan="Math.max(item.details?.length || 1, 1)"
                                            class="px-3 py-3 text-xs text-gray-900 border border-black text-center">
                                            {{ item.product?.category?.name || '' }}
                                        </td>

                                        <!-- UoM - only on first row for this item -->
                                        <td v-if="detailIndex === 0" :rowspan="Math.max(item.details?.length || 1, 1)"
                                            class="px-3 py-3 text-xs text-gray-900 border border-black text-center">
                                            {{ item.details?.[0]?.uom || '' }}
                                        </td>

                                        <!-- Total Quantity on Hand Per Unit - only on first row for this item -->
                                        <td v-if="detailIndex === 0" :rowspan="Math.max(item.details?.length || 1, 1)"
                                            class="px-3 py-3 text-xs text-center border border-black">
                                            {{ item.available_quantity || 0 }}
                                        </td>

                                        <!-- Item Details Columns -->
                                        <!-- Quantity -->
                                        <td class="px-2 py-1 text-xs border border-black text-center">
                                            {{ detail.quantity || '' }}
                                        </td>

                                        <!-- Batch Number -->
                                        <td class="px-2 py-1 text-xs border border-black text-center">
                                            {{ detail.batch_number || '' }}
                                        </td>

                                        <!-- Expiry Date -->
                                        <td class="px-2 py-1 text-xs border border-black text-center" :class="{ 'text-red-600': detail.expiry_date && isExpiringSoon(detail.expiry_date) }">
                                            {{ detail.expiry_date ? formatDate(detail.expiry_date) : '' }}
                                        </td>

                                        <!-- Location -->
                                        <td class="px-2 py-1 text-xs border border-black text-center">
                                            {{ detail.location || '' }}
                                        </td>

                                        <!-- Reasons for Transfers - only on first row for this item -->
                                        <td 
                                            class="px-3 min-w-[120px] py-3 text-xs border border-black text-center">
                                            <select
                                                v-model="item.transfer_reason"
                                                class="w-full text-xs border rounded px-2 py-1 resize-none"
                                                rows="3"
                                                placeholder="Enter transfer reason..."
                                            >
                                                <option value="Soon to expire">Soon to expire</option>
                                                <option value="Replenishment">Replenishment</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </td>

                                        <!-- Quantity to be transferred - per detail -->
                                        <td class="px-2 py-1 text-xs border border-black text-center">
                                            <input
                                                v-if="item.product && detail.quantity"
                                                type="number"
                                                v-model.number="detail.quantity_to_transfer"
                                                :max="detail.quantity"
                                                min="0"
                                                class="w-full text-xs border rounded px-2 py-1 text-center"
                                                placeholder="0"
                                                @input="updateItemQuantity(index)"
                                            />
                                        </td>

                                        <!-- Total Quantity to be transferred - only on first row for this item -->
                                        <td v-if="detailIndex === 0" :rowspan="Math.max(item.details?.length || 1, 1)"
                                            class="px-3 py-3 text-xs text-center border border-black align-top">
                                            <div class="text-sm font-medium text-blue-600" v-if="item.product">
                                                {{ item.quantity || 0 }}
                                            </div>
                                        </td>

                                        <!-- Action - only on first row for this item -->
                                        <td v-if="detailIndex === 0" :rowspan="Math.max(item.details?.length || 1, 1)"
                                            class="px-3 py-3 text-xs text-center border border-black align-top">
                                            <button
                                                type="button"
                                                @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-800"
                                                :disabled="form.items.length <= 1"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="flex items-center justify-between space-x-4 mb-4"
                    >
                        <button
                            type="button"
                            @click="addNewItem"
                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            Add Another Item
                        </button>
                        <div>
                            <SecondaryButton
                                :href="route('transfers.index')"
                                as="a"
                                :disabled="loading"
                                class="opacity-75"
                                :class="{
                                    'opacity-50 cursor-not-allowed': loading,
                                }"
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton :disabled="loading" class="relative">
                                <span v-if="loading" class="absolute left-2">
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
                                    loading ? "Processing..." : "Transfer Item"
                                }}</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
