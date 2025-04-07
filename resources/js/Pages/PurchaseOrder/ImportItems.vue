<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" class="relative z-[60]" @close="closeModal">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                            <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                Import Items from Excel
                            </DialogTitle>

                            <div class="mt-4">
                                <form @submit.prevent="importItems" enctype="multipart/form-data" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Excel File</label>
                                        <div class="mt-1 flex items-center justify-center w-full">
                                            <label
                                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                                    </svg>
                                                    <p class="mb-2 text-sm text-gray-500" v-if="!selectedFile"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                                    <p class="text-xs text-gray-500" v-if="!selectedFile">Excel</p>
                                                    <p class="text-sm text-gray-800 font-medium" v-else>{{ selectedFile.name }}</p>
                                                </div>
                                                <input 
                                                    type="file" 
                                                    class="hidden" 
                                                    accept=".xlsx"
                                                    @change="handleFileChange"
                                                    ref="fileInput"
                                                    name="file"
                                                />
                                            </label>
                                        </div>
                                        <div v-if="errors.file" class="mt-1 text-sm text-red-600">{{ errors.file }}</div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            Please upload an Excel file (.xlsx) with the following columns: Item Code, Item Description, UoM, Quantity, Unit Cost, Total Cost
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button
                                            type="button"
                                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            @click="closeModal"
                                            :disabled="isSubmitting"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="!selectedFile || isSubmitting"
                                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                        >
                                            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ isSubmitting ? 'Importing...' : 'Import' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    isOpen: Boolean,
    purchaseOrderId: Number
});

const emit = defineEmits(['close', 'imported', 'uploaded']);

const fileInput = ref(null);
const selectedFile = ref(null);
const errors = ref({});
const isSubmitting = ref(false);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file && file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        selectedFile.value = file;
        errors.value = {};
    } else {
        toast.error('Please select a valid Excel file (.xlsx)');
        event.target.value = '';
        selectedFile.value = null;
    }
};

const closeModal = () => {
    selectedFile.value = null;
    errors.value = {};
    emit('close');
};

const importItems = async () => {
    if (!selectedFile.value || isSubmitting.value) return;

    isSubmitting.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('file', selectedFile.value, selectedFile.value.name);
    formData.append('purchase_order_id', props.purchaseOrderId);

    try {
        const response = await axios.post(
            route('purchase-orders.import-items', props.purchaseOrderId), 
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json',
                }
            }
        );

        if (response.data.success) {
            toast.success(response.data.message || 'Items imported successfully');
            emit('imported');
            emit('uploaded');
            closeModal();
        } else {
            toast.error(response.data.message || 'Failed to import items');
        }
    } catch (error) {
        console.error('Import error:', error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            toast.error(error.response?.data?.message || 'Error importing items');
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>
