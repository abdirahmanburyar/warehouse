<template>
    <Head title="Create Received Back Order" />
    <AuthenticatedLayout title="Create Received Back Order"
        description="Add a new received back order" img="/assets/images/supplies.png">
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
                                    <option v-for="location in locations" :key="location.id" :value="location.location">
                                        {{ location.location }}
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

                            <!-- Attachments -->
                            <div class="md:col-span-2">
                                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">Attachments</label>
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
                                <span v-if="processing">Creating...</span>
                                <span v-else>Create Received Back Order</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    products: Array,
    warehouses: Array,
    locations: Array,
    facilities: Array,
    errors: Object,
});

const processing = ref(false);

const form = useForm({
    product_id: '',
    barcode: '',
    expire_date: '',
    batch_number: '',
    uom: '',
    received_at: '',
    quantity: '',
    type: '',
    location: '',
    facility: '',
    warehouse: '',
    unit_cost: '',
    total_cost: '',
    note: '',
    attachments: [],
    // Additional fields for back order integration
    back_order_id: '',
    packing_list_id: '',
    packing_list_number: '',
    purchase_order_id: '',
    purchase_order_number: '',
    supplier_id: '',
    supplier_name: '',
});

// Handle URL parameters to pre-fill form from back order
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('product_id')) {
        form.product_id = urlParams.get('product_id');
        form.barcode = urlParams.get('barcode') || '';
        form.batch_number = urlParams.get('batch_number') || '';
        form.uom = urlParams.get('uom') || '';
        form.quantity = urlParams.get('quantity') || '';
        form.type = urlParams.get('type') || '';
        form.unit_cost = urlParams.get('cost_per_unit') || '';
        form.total_cost = urlParams.get('total_cost') || '';
        form.received_at = urlParams.get('received_at') || '';
        
        // Set additional fields
        form.back_order_id = urlParams.get('back_order_id') || '';
        form.packing_list_id = urlParams.get('packing_list_id') || '';
        form.packing_list_number = urlParams.get('packing_list_number') || '';
        form.purchase_order_id = urlParams.get('purchase_order_id') || '';
        form.purchase_order_number = urlParams.get('purchase_order_number') || '';
        form.supplier_id = urlParams.get('supplier_id') || '';
        form.supplier_name = urlParams.get('supplier_name') || '';
        
        // Set note with back order information
        form.note = `Received from Back Order: ${urlParams.get('packing_list_number') || 'N/A'}\nPurchase Order: ${urlParams.get('purchase_order_number') || 'N/A'}\nSupplier: ${urlParams.get('supplier_name') || 'N/A'}`;
    }
});

const handleFileUpload = (event) => {
    form.attachments = Array.from(event.target.files);
};

const submit = () => {
    processing.value = true;
    form.post(route('supplies.received-backorder.store'), {
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};
</script> 