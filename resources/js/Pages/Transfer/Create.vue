<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
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
    items: [
        {
            id: null,
            product_id: "",
            product: null,
            quantity: 0,
            available_quantity: 0,
            details: [],
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
                console.log("[DEBUG] Error response status:", error.response.status);
                console.log("[DEBUG] Error response data:", error.response.data);
                
                if (typeof error.response.data === 'string') {
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

    console.log(form.value);
    await axios
        .post(route("transfers.store"), form.value)
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

                item.details = response.data;
                item.available_quantity = response.data?.reduce(
                    (sum, item) => sum + item.quantity,
                    0
                );
                item.product = selected;
                item.product_id = selected.id;
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
                })
            });
    }
}

function addNewItem() {
    form.value.items.push({
        id: null,
        product_id: null,
        product: null,
        available_quantity: 0,
        quantity: 0,
        details: []
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
</script>

<template>
    <AuthenticatedLayout title="Transfer Item">
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
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="w-[300px]">
                        <div class="flex flex-col">
                            Transfer ID: {{ props.transferID }}
                        </div>
                        <div class="flex flex-col">
                            <label for="transfer_date">Transfer Date</label>
                            <input
                                type="date"
                                v-model="form.transfer_date"
                                class="form-input"
                            />
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

                    <div class="mb-4">
                        <table class="min-w-full">
                            <thead class="w-full bg-gray-50">
                                <tr>
                                    <th
                                        class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Item
                                    </th>
                                    <th
                                        class="w-[300px] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-black tracking-wider"
                                    >
                                        Item Information
                                    </th>
                                    <th
                                        class="w-[100px] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-black tracking-wider"
                                    >
                                        Available quantity
                                    </th>
                                    <th
                                        class="w-[200px] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-black tracking-wider"
                                    >
                                        Quantity to release
                                    </th>
                                    <th
                                        class="w-[70px] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-black tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Inventory Items rows -->
                                <tr
                                    v-for="(item, index) in form.items"
                                    :key="index"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-3 py-2 border border-black">
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
                                            @select="
                                                handleProductSelect(
                                                    index,
                                                    $event
                                                )
                                            "
                                        />
                                    </td>
                                    <td
                                        class="whitespace-nowrap border border-black text-sm text-gray-500"
                                    >
                                        <table
                                            class="w-full text-xs border border-gray-300"
                                        >
                                            <thead class="bg-gray-50">
                                                <tr class="text-gray-600">
                                                    <th
                                                        class="border border-gray-300"
                                                    >
                                                        UoM
                                                    </th>
                                                    <th
                                                        class="border border-gray-300"
                                                    >
                                                        QTY
                                                    </th>
                                                    <th
                                                        class="border border-gray-300"
                                                    >
                                                        Batch
                                                    </th>
                                                    <th
                                                        class="border border-gray-300"
                                                    >
                                                        Expiry
                                                    </th>
                                                    <th
                                                        class="border border-gray-300"
                                                    >
                                                        Location
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="detail in item?.details"
                                                    :key="detail.id"
                                                    class="bg-white even:bg-gray-50"
                                                >
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        {{ detail.uom }}
                                                    </td>
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        {{ detail.quantity }}
                                                    </td>
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        {{
                                                            detail.batch_number
                                                        }}
                                                    </td>
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        {{
                                                            formatDate(
                                                                detail.expiry_date
                                                            )
                                                        }}
                                                    </td>
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        <div
                                                            class="text-xs flex flex-col space-y-0.5"
                                                        >
                                                            <span
                                                                class="text-xs"
                                                                >WH:
                                                                {{
                                                                    detail
                                                                        .warehouse
                                                                        ?.name
                                                                }}</span
                                                            >
                                                            <span
                                                                class="text-xs"
                                                                >LC:
                                                                {{
                                                                    detail.location
                                                                }}</span
                                                            >
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-black text-center text-sm text-black"
                                    >
                                        {{ item.available_quantity }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap border border-black text-sm text-black"
                                    >
                                        <input
                                            type="text"
                                            v-model.number="item.quantity"
                                            required
                                            :class="[
                                                'w-full text-sm',
                                                errors[`item_${index}_quantity`]
                                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                                    : '',
                                            ]"
                                            min="1"
                                            :max="item.available_quantity"
                                            :disabled="!item.product?.id"
                                            placeholder="0"
                                            @input="
                                                checkQuantity(index);
                                                errors[
                                                    `item_${index}_quantity`
                                                ] = null;
                                            "
                                        />
                                    </td>
                                    <td
                                        class="whitespace-nowrap border border-black text-sm text-gray-500"
                                    >
                                        <button
                                            type="button"
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-900"
                                            :disabled="form.items.length <= 1"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
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
