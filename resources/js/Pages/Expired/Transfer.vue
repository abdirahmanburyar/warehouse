<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
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

const transferType = ref("warehouse");
const loading = ref(false);
const selectedDestination = ref(null);
const quantityToTransfer = ref("");
const transfer_date = ref(moment().format("YYYY-MM-DD"));
const transfer_type = ref("");
const destinations = computed(() => {
    return transferType.value === "warehouse"
        ? props.warehouses
        : props.facilities;
});

const isSubmitting = ref(false);

const handleSubmit = async () => {
    if (!selectedDestination.value || !quantityToTransfer.value) {
        toast.error("Please fill in all required fields");
        return;
    }

    if (parseInt(quantityToTransfer.value) > props.inventory.quantity) {
        toast.error("Transfer quantity cannot exceed available quantity");
        return;
    }

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
        isSubmitting.value = true;

        await axios
            .post(route("transfers.store"), {
                source_type: "warehouse",
                source_id: props.inventory.warehouse_id,
                destination_type: transferType.value,
                destination_id: selectedDestination.value.id,
                items: [
                    {
                        id: props.inventory.id,
                        product_id: props.inventory.product_id,
                        quantity: parseInt(quantityToTransfer.value),
                        batch_number: props.inventory.batch_number,
                        barcode: props.inventory.barcode || null,
                        expiry_date: props.inventory.expiry_date || null,
                        uom: props.inventory.uom || null,
                    },
                ],
                transferID: props.transferID,
                transfer_date: transfer_date.value,
                transfer_type: transfer_type.value,
                notes: `Transferred ${quantityToTransfer.value} items to ${selectedDestination.value.name}`,
            })
            .then((response) => {
                isSubmitting.value = false;
                console.log(response.data);
                Swal.fire({
                    title: "Success!",
                    text: "Transfer completed successfully",
                    icon: "success",
                    confirmButtonText: "OK",
                }).then(() => {
                    router.visit(route("expired.transfer", props.inventory.id));
                });
            })
            .catch((error) => {
                isSubmitting.value = false;
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
        title="Transfer Item"
        description="Transfer expired or soon to be expired items to warehouse or facility"
        img="/assets/images/transfer.png"
    >
        <form @submit.prevent="handleSubmit">
            <div class="flex flex-col items-start w-full">
                <div class="flex flex-col">
                    Transfer ID: {{ props.transferID }}
                </div>
                <div class="flex flex-col">
                    <label class="inline-flex items-center">
                        Transfer Date
                    </label>
                    <input
                        type="date"
                        v-model="transfer_date"
                        class="form-input"
                    />
                </div>
                <div class="flex flex-col">
                    <label class="inline-flex items-center">
                        Transfer Type
                    </label>
                    <textarea
                        v-model="transfer_type"
                        class="form-input"
                    ></textarea>
                </div>
            </div>
            <div class="p-1 text-gray-900 mb-6">
                <div class="flex flex-col space-y-6">
                    <div class="flex items-start flex-col w-full gap-5 mb-6">
                        <div class="flex justify-between gap-3">
                            <div class="w-[400px]">
                                <div class="flex items-start gap-5">
                                    <label class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            v-model="transferType"
                                            value="warehouse"
                                            class="form-radio"
                                        />
                                        <span class="ml-2">Warehouse</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            v-model="transferType"
                                            value="facility"
                                            class="form-radio"
                                        />
                                        <span class="ml-2">Facility</span>
                                    </label>
                                </div>
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
                    </div>

                    <div class="overflow-auto">
                        <table class="w-full rounded-xl border border-gray-300">
                            <thead class="bg-blue-500 text-white">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border border-gray-300"
                                    >
                                        Item
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border border-gray-300"
                                    >
                                        UOM
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider border border-gray-300"
                                    >
                                        Item Detail
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border border-gray-300"
                                    >
                                        Available Quantity
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider border border-gray-300"
                                    >
                                        Quantity to Transfer
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td
                                        class="px-6 py-3 text-sm border border-gray-300"
                                    >
                                        {{ inventory.product.name }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm border border-gray-300"
                                    >
                                        {{ inventory.uom || "N/A" }}
                                    </td>
                                    <td class="text-sm border border-gray-300">
                                        <table
                                            class="w-full border border-gray-300"
                                        >
                                            <thead>
                                                <tr>
                                                    <th
                                                        class="text-xs border border-gray-300"
                                                    >
                                                        Batch No.
                                                    </th>
                                                    <th
                                                        class="text-xs border border-gray-300"
                                                    >
                                                        Barcode
                                                    </th>
                                                    <th
                                                        class="text-xs border border-gray-300"
                                                    >
                                                        Expire Date
                                                    </th>
                                                    <th
                                                        class="text-xs border border-gray-300"
                                                    >
                                                        Location
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        {{
                                                            inventory.batch_number
                                                        }}
                                                    </td>
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        {{ inventory.barcode }}
                                                    </td>
                                                    <td
                                                        class="border border-gray-300"
                                                    >
                                                        {{
                                                            moment(
                                                                inventory.expiry_date
                                                            ).format(
                                                                "DD/MM/YYYY"
                                                            )
                                                        }}
                                                    </td>
                                                    <td
                                                        class="border border-gray-300 flex flex-col"
                                                    >
                                                        <span>
                                                            WH:
                                                            {{
                                                                inventory
                                                                    .warehouse
                                                                    ?.name
                                                            }}
                                                        </span>
                                                        <span>
                                                            Loc:
                                                            {{
                                                                inventory
                                                                    .location
                                                                    ?.location
                                                            }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm font-medium border border-gray-300"
                                    >
                                        {{ inventory.quantity }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm border border-gray-300"
                                    >
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
                            :disabled="
                                isSubmitting ||
                                !selectedDestination ||
                                !quantityToTransfer
                            "
                            class="text-white bg-blue-600 px-4 py-2 rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50 min-w-[100px]"
                        >
                            <svg
                                v-if="isSubmitting"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
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
                            {{ isSubmitting ? "Processing..." : "Transfer" }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
