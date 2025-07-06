<template>
    <Head title="Edit Received Back Order" />
    <AuthenticatedLayout title="Edit Received Back Order"
        description="Update received back order details" img="/assets/images/supplies.png">
        <div class="overflow-hidden mb-[80px]">
            <div class="text-gray-900">
                <Link :href="route('supplies.received-backorder.index')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Received Back Orders
                </Link>

                <div class="max-w-4xl mx-auto">
                    <form @submit.prevent="submit" class="bg-white shadow-sm rounded-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Selection -->
                            <div class="md:col-span-2">
                                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Product <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.product_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Product</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} ({{ product.productID }})
                                    </option>
                                </select>
                                <div v-if="errors.product_id" class="text-red-500 text-sm mt-1">{{ errors.product_id }}</div>
                            </div>

                            <!-- Basic Information -->
                            <div>
                                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                                <input type="text" v-model="form.barcode"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.barcode" class="text-red-500 text-sm mt-1">{{ errors.barcode }}</div>
                            </div>

                            <div>
                                <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                                <input type="text" v-model="form.batch_number"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.batch_number" class="text-red-500 text-sm mt-1">{{ errors.batch_number }}</div>
                            </div>

                            <div>
                                <label for="expire_date" class="block text-sm font-medium text-gray-700 mb-2">Expire Date</label>
                                <input type="date" v-model="form.expire_date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.expire_date" class="text-red-500 text-sm mt-1">{{ errors.expire_date }}</div>
                            </div>

                            <div>
                                <label for="uom" class="block text-sm font-medium text-gray-700 mb-2">Unit of Measure</label>
                                <input type="text" v-model="form.uom"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.uom" class="text-red-500 text-sm mt-1">{{ errors.uom }}</div>
                            </div>

                            <div>
                                <label for="received_at" class="block text-sm font-medium text-gray-700 mb-2">
                                    Received Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" v-model="form.received_at" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.received_at" class="text-red-500 text-sm mt-1">{{ errors.received_at }}</div>
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                    Quantity <span class="text-red-500">*</span>
                                </label>
                                <input type="number" v-model="form.quantity" min="1" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.quantity" class="text-red-500 text-sm mt-1">{{ errors.quantity }}</div>
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Type <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.type" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Type</option>
                                    <option value="backorder">Back Order</option>
                                    <option value="return">Return</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="expired">Expired</option>
                                </select>
                                <div v-if="errors.type" class="text-red-500 text-sm mt-1">{{ errors.type }}</div>
                            </div>

                            <div>
                                <label for="unit_cost" class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                                <input type="number" v-model="form.unit_cost" min="0" step="0.01"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.unit_cost" class="text-red-500 text-sm mt-1">{{ errors.unit_cost }}</div>
                            </div>

                            <div>
                                <label for="total_cost" class="block text-sm font-medium text-gray-700 mb-2">Total Cost</label>
                                <input type="number" v-model="form.total_cost" min="0" step="0.01"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="errors.total_cost" class="text-red-500 text-sm mt-1">{{ errors.total_cost }}</div>
                            </div>

                            <!-- Location Information -->
                            <div>
                                <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-2">Warehouse</label>
                                <select v-model="form.warehouse"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Warehouse</option>
                                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.name">
                                        {{ warehouse.name }}
                                    </option>
                                </select>
                                <div v-if="errors.warehouse" class="text-red-500 text-sm mt-1">{{ errors.warehouse }}</div>
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <select v-model="form.location"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Location</option>
                                    <option v-for="location in locations" :key="location.id" :value="location.name">
                                        {{ location.name }}
                                    </option>
                                </select>
                                <div v-if="errors.location" class="text-red-500 text-sm mt-1">{{ errors.location }}</div>
                            </div>

                            <div>
                                <label for="facility" class="block text-sm font-medium text-gray-700 mb-2">Facility</label>
                                <select v-model="form.facility"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Facility</option>
                                    <option v-for="facility in facilities" :key="facility.id" :value="facility.name">
                                        {{ facility.name }}
                                    </option>
                                </select>
                                <div v-if="errors.facility" class="text-red-500 text-sm mt-1">{{ errors.facility }}</div>
                            </div>

                            <!-- Existing Attachments -->
                            <div class="md:col-span-2" v-if="receivedBackorder.attachments && receivedBackorder.attachments.length">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Existing Attachments</label>
                                <div class="space-y-2">
                                    <div v-for="(attachment, index) in receivedBackorder.attachments" :key="index" 
                                        class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-sm text-gray-700">{{ attachment.name }}</span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a :href="`/storage/${attachment.path}`" target="_blank"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                View
                                            </a>
                                            <button type="button" @click="deleteAttachment(index)"
                                                class="text-red-600 hover:text-red-900 text-sm">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- New Attachments -->
                            <div class="md:col-span-2">
                                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">Add New Attachments</label>
                                <input type="file" @change="handleFileUpload" multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <p class="text-sm text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (max 2MB each)</p>
                                <div v-if="errors.attachments" class="text-red-500 text-sm mt-1">{{ errors.attachments }}</div>
                            </div>

                            <!-- Note -->
                            <div class="md:col-span-2">
                                <label for="note" class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                                <textarea v-model="form.note" rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    placeholder="Enter any additional notes..."></textarea>
                                <div v-if="errors.note" class="text-red-500 text-sm mt-1">{{ errors.note }}</div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end mt-6">
                            <Link :href="route('supplies.received-backorder.index')"
                                class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </Link>
                            <button type="submit" :disabled="processing"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                <span v-if="processing">Updating...</span>
                                <span v-else>Update Received Back Order</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    receivedBackorder: Object,
    products: Array,
    warehouses: Array,
    locations: Array,
    facilities: Array,
    errors: Object,
});

const processing = ref(false);

const form = useForm({
    product_id: props.receivedBackorder.product_id || '',
    barcode: props.receivedBackorder.barcode || '',
    expire_date: props.receivedBackorder.expire_date || '',
    batch_number: props.receivedBackorder.batch_number || '',
    uom: props.receivedBackorder.uom || '',
    received_at: props.receivedBackorder.received_at || '',
    quantity: props.receivedBackorder.quantity || '',
    type: props.receivedBackorder.type || '',
    location: props.receivedBackorder.location || '',
    facility: props.receivedBackorder.facility || '',
    warehouse: props.receivedBackorder.warehouse || '',
    unit_cost: props.receivedBackorder.unit_cost || '',
    total_cost: props.receivedBackorder.total_cost || '',
    note: props.receivedBackorder.note || '',
    attachments: [],
});

const handleFileUpload = (event) => {
    form.attachments = Array.from(event.target.files);
};

const deleteAttachment = (index) => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this attachment?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('supplies.received-backorder.deleteAttachment', props.receivedBackorder.id), {
                data: { attachment_index: index },
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Attachment has been deleted.',
                        'success'
                    );
                },
                onError: (errors) => {
                    Swal.fire(
                        'Error!',
                        errors.message || 'Failed to delete attachment.',
                        'error'
                    );
                }
            });
        }
    });
};

const submit = () => {
    processing.value = true;
    form.put(route('supplies.received-backorder.update', props.receivedBackorder.id), {
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};
</script> 