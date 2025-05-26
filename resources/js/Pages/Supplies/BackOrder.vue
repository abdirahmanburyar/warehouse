<template>
    <AuthenticatedLayout>
        <div class="bg-white">
            <div class="p-6 text-gray-900">
                <div class="mb-4">
                    <label for="po" class="block text-sm font-medium text-gray-700">Select Purchase Order</label>
                    <Multiselect
                        v-model="selectedPo"
                        :options="props.po"
                        :searchable="true"
                        :create-option="false"
                        class="mt-1"
                        placeholder="Select Purchase Order"
                        label="po_number"
                        track-by="id"
                        @select="handlePoChange"
                    />
                </div>

                <div class="mt-6" v-if="selectedPo">
                    <h3 class="text-lg font-medium text-gray-900">Back Order Items</h3>
                    <div class="mt-4 flex flex-col">
                        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Packing List</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template v-if="isLoading">
                                                <tr v-for="i in 3" :key="i">
                                                    <td v-for="j in 6" :key="j" class="px-6 py-4 whitespace-nowrap">
                                                        <div class="animate-pulse h-4 bg-gray-200 rounded"></div>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template v-else>
                                                <tr v-for="item in items" :key="item.id">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.product.productID }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.product.name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.packing_list.packing_list_number }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ moment(item.created_at).format('DD/MM/YYYY') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.quantity }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <span :class="{
                                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                            'bg-yellow-100 text-yellow-800': item.status === 'Missing',
                                                            'bg-red-100 text-red-800': ['Damaged', 'Expired'].includes(item.status)
                                                        }">
                                                            {{ item.status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                        <button
                                                            v-if="['Missing', 'Damaged', 'Expired'].includes(item.status)"
                                                            @click="handleReceive(item)"
                                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                            :disabled="isLoading"
                                                        >
                                                            Receive
                                                        </button>
                                                        <button
                                                            v-if="item.status === 'Missing'"
                                                            @click="handleLiquidate(item)"
                                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                            :disabled="isLoading"
                                                        >
                                                            Liquidate
                                                        </button>
                                                        <button
                                                            v-if="['Damaged', 'Expired'].includes(item.status)"
                                                            @click="handleDispose(item)"
                                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                            :disabled="isLoading"
                                                        >
                                                            Dispose
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr v-if="items.length === 0">
                                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No back order items found</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liquidation Modal -->
        <Modal :show="showLiquidateModal" max-width="xl" @close="showLiquidateModal = false">
            <form id="liquidationForm" class="p-6 space-y-4" @submit.prevent="submitLiquidation">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Liquidate Item</h2>
                
                <!-- Product Info -->
                <div v-if="selectedItem" class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Product ID</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.product.productID }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Product Name</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.product.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Packing List</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.packing_list.packing_list_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.status }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input 
                        type="number" 
                        id="quantity" 
                        v-model="liquidateForm.quantity"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :min="1"
                        :max="selectedItem?.quantity"
                        required
                    >
                </div>

                <!-- Note -->
                <div>
                    <label for="note" class="block text-sm font-medium text-gray-700">Note</label>
                    <textarea 
                        id="note" 
                        v-model="liquidateForm.note"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        rows="3"
                        required
                    ></textarea>
                </div>

                <!-- Attachments -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attachments (PDF files)</label>
                    <input 
                        type="file" 
                        ref="attachments"
                        @change="(e) => handleFileChange('liquidate', e)"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        multiple
                        accept=".pdf"
                        required
                    >
                </div>

                <!-- Selected Files Preview -->
                <div v-if="liquidateForm.attachments.length > 0" class="mt-2">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h4>
                    <ul class="space-y-2">
                        <li v-for="(file, index) in liquidateForm.attachments" :key="index" class="flex items-center justify-between text-sm text-gray-500 bg-gray-50 p-2 rounded">
                            <span>{{ file.name }}</span>
                            <button 
                                type="button"
                                @click="removeLiquidateFile(index)" 
                                class="text-red-500 hover:text-red-700"
                            >
                                Remove
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        @click="showLiquidateModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-yellow-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? 'Liquidating...' : 'Liquidate' }}
                    </button>
                </div>
            </form>
        </Modal>


          <!-- Dispose Modal -->
          <Modal :show="showDisposeModal" max-width="xl" @close="showDisposeModal = false">
            <form id="disposeForm" class="p-6 space-y-4" @submit.prevent="submitDisposal">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Dispose Item</h2>
                
                <!-- Product Info -->
                <div v-if="selectedItem" class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Product ID</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.product.productID }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Product Name</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.product.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Packing List</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.packing_list.packing_list_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="text-sm text-gray-900">{{ selectedItem.status }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input 
                        type="number" 
                        id="quantity" 
                        v-model="disposeForm.quantity"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :min="1"
                        :max="selectedItem?.quantity"
                        required
                    >
                </div>

                <!-- Note -->
                <div>
                    <label for="note" class="block text-sm font-medium text-gray-700">Note</label>
                    <textarea 
                        id="note" 
                        v-model="disposeForm.note"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        rows="3"
                        required
                    ></textarea>
                </div>

                <!-- Attachments -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attachments (PDF files)</label>
                    <input 
                        type="file" 
                        ref="attachments"
                        @change="(e) => handleFileChange('dispose', e)"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        multiple
                        accept=".pdf"
                        required
                    >
                </div>

                <!-- Selected Files Preview -->
                <div v-if="disposeForm.attachments.length > 0" class="mt-2">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h4>
                    <ul class="space-y-2">
                        <li v-for="(file, index) in disposeForm.attachments" :key="index" class="flex items-center justify-between text-sm text-gray-500 bg-gray-50 p-2 rounded">
                            <span>{{ file.name }}</span>
                            <button 
                                type="button"
                                @click="removeDisposeFile(index)" 
                                class="text-red-500 hover:text-red-700"
                            >
                                Remove
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        @click="showDisposeModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-yellow-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? 'Disposing...' : 'Dispose' }}
                    </button>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import moment from 'moment';
import Modal from '@/Components/Modal.vue';

// Component state
const selectedPo = ref(null);
const items = ref([]);
const isLoading = ref(false);
const isSubmitting = ref(false);
const showLiquidateModal = ref(false);
const showDisposeModal = ref(false);
const selectedItem = ref(null);
const liquidateForm = ref({
    quantity: 0,
    note: '',
    attachments: []
});

const disposeForm = ref({
    quantity: 0,
    note: '',
    attachments: []
});

const handleFileChange = (formType, e) => {
    const files = Array.from(e.target.files || []);
    if (formType === 'liquidate') {
        liquidateForm.value.attachments = files;
    } else {
        disposeForm.value.attachments = files;
    }
};

const removeLiquidateFile = (index) => {
    liquidateForm.value.attachments.splice(index, 1);
};

const removeDisposeFile = (index) => {
    disposeForm.value.attachments.splice(index, 1);
};

// Component props
const props = defineProps({
    po: {
        required: true,
        type: Array
    }
});

// Action handlers
const receiveItems = async (item) => {
    const { value: quantity } = await Swal.fire({
        title: 'Enter Quantity to Receive',
        input: 'number',
        inputLabel: `Maximum quantity: ${item.quantity}`,
        inputValue: item.quantity,
        inputAttributes: {
            min: '1',
            max: item.quantity.toString(),
            step: '1'
        },
        showCancelButton: true,
        confirmButtonText: 'Receive',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6B7280',
        showLoaderOnConfirm: true,
        preConfirm: async (value) => {
            const num = parseInt(value);
            if (!value || num < 1) {
                Swal.showValidationMessage('Please enter a quantity greater than 0');
                return false;
            }
            if (num > item.quantity) {
                Swal.showValidationMessage(`Cannot receive more than ${item.quantity} items`);
                return false;
            }
            
            try {
                isLoading.value = true;
                await axios.post(route('back-order.receive'), {
                    id: item.id,
                    product_id: item.product.id,
                    packing_list_id: item.packing_list.id,
                    purchase_order_id: selectedPo.value?.id,
                    quantity: num,
                    original_quantity: item.quantity
                })
                .then(response => {
                    Swal.fire({
                        title: 'Success!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonColor: '#10B981',
                    });
                })
                .catch(error => {
                    console.error('Failed to receive items:', error);
                    Swal.showValidationMessage(error.response?.data?.message || 'Failed to receive items');
                });
                await handlePoChange(selectedPo.value);
                return true;
            } catch (error) {
                console.error('Failed to receive items:', error);
                Swal.showValidationMessage(error.response?.data?.message || 'Failed to receive items');
                return false;
            } finally {
                isLoading.value = false;
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
};

const liquidateItems = async (item) => {
    const { value: formValues } = await Swal.fire({
        title: 'Liquidate Items',
        html: `
            <form id="liquidationForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input 
                        type="number" 
                        id="quantity" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        min="1"
                        max="${item.quantity}"
                        value="${item.quantity}"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Note</label>
                    <textarea 
                        id="note" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        rows="3"
                        required
                    ></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attachments</label>
                    <input 
                        type="file" 
                        id="attachments" 
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        required
                    >
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Liquidate',
        confirmButtonColor: '#EAB308',
        cancelButtonColor: '#6B7280',
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            const form = document.getElementById('liquidationForm');
            const quantity = parseInt(form.querySelector('#quantity').value);
            const note = form.querySelector('#note').value;
            const attachments = form.querySelector('#attachments').files;
            
            if (!quantity || quantity < 1) {
                Swal.showValidationMessage('Please enter a quantity greater than 0');
                return false;
            }
            if (quantity > item.quantity) {
                Swal.showValidationMessage(`Cannot liquidate more than ${item.quantity} items`);
                return false;
            }
            if (!note.trim()) {
                Swal.showValidationMessage('Please enter a note');
                return false;
            }
            if (attachments.length === 0) {
                Swal.showValidationMessage('Please upload at least one attachment');
                return false;
            }
            
            try {
                isLoading.value = true;
                const formData = new FormData();
                formData.append('id', item.id);
                formData.append('product_id', item.product.id);
                formData.append('packing_list_id', item.packing_list.id);
                formData.append('purchase_order_id', selectedPo.value?.id);
                formData.append('quantity', quantity);
                formData.append('original_quantity', item.quantity);
                formData.append('note', note);
                
                for (let i = 0; i < attachments.length; i++) {
                    formData.append('attachments[]', attachments[i]);
                }
                
                await axios.post(route('back-order.liquidate'), formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                await handlePoChange(selectedPo.value);
                return true;
            } catch (error) {
                console.error('Failed to liquidate items:', error);
                Swal.showValidationMessage(error.response?.data?.message || 'Failed to liquidate items');
                return false;
            } finally {
                isLoading.value = false;
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
};


// Event handlers
const handlePoChange = async (po) => {
    try {
        if (!po) {
            items.value = [];
            return;
        }

        isLoading.value = true;
        const response = await axios.get(route('supplies.get-packingList', po.id));
        items.value = response.data;
    } catch (error) {
        console.error('Failed to fetch items:', error);
        Swal.fire('Error!', error.response?.data?.message || 'Failed to fetch items', 'error');
        items.value = [];
    } finally {
        isLoading.value = false;
    }
};

const handleReceive = async (item) => {
    await receiveItems(item);
};

const handleLiquidate = (item) => {
    selectedItem.value = item;
    liquidateForm.value = {
        quantity: item.quantity,
        note: '',
        attachments: []
    };
    showLiquidateModal.value = true;
};

const submitLiquidation = async () => {
    try {
        console.log(selectedItem.value);
        isSubmitting.value = true;
        const formData = new FormData();
        formData.append('id', selectedItem.value.id);
        formData.append('product_id', selectedItem.value.product.id);
        formData.append('packing_list_id', selectedItem.value.packing_list.id);
        formData.append('purchase_order_id', selectedPo.value?.id);
        formData.append('quantity', liquidateForm.value.quantity);
        formData.append('original_quantity', selectedItem.value.quantity);
        formData.append('barcode', selectedItem.value.packing_list.barcode);
        formData.append('expire_date', selectedItem.value.packing_list.expire_date);
        formData.append('batch_number', selectedItem.value.packing_list.batch_number);
        formData.append('uom', selectedItem.value.packing_list.uom);
        formData.append('status', selectedItem.value.status);
        formData.append('note', liquidateForm.value.note);
        
        // Append each attachment
        for (let i = 0; i < liquidateForm.value.attachments.length; i++) {
            formData.append('attachments[]', liquidateForm.value.attachments[i]);
        }
        
        await axios.post(route('back-order.liquidate'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        await handlePoChange(selectedPo.value);
        showLiquidateModal.value = false;
        alert('Item has been liquidated successfully');
    } catch (error) {
        console.error('Failed to liquidate items:', error);
        alert(error.response?.data?.message || 'Failed to liquidate items');
    } finally {
        isSubmitting.value = false;
    }
};


const handleDispose = async (item) => {
    selectedItem.value = item;
    disposeForm.value = {
        quantity: item.quantity,
        note: '',
        attachments: []
    };
    showDisposeModal.value = true;
};

const submitDisposal = async () => {
    try {
        isSubmitting.value = true;
        const formData = new FormData();
        formData.append('id', selectedItem.value.id);
        formData.append('product_id', selectedItem.value.product.id);
        formData.append('packing_list_id', selectedItem.value.packing_list.id);
        formData.append('purchase_order_id', selectedPo.value?.id);
        formData.append('quantity', disposeForm.value.quantity);
        formData.append('original_quantity', selectedItem.value.quantity);
        formData.append('barcode', selectedItem.value.packing_list.barcode);
        formData.append('expire_date', selectedItem.value.packing_list.expire_date);
        formData.append('batch_number', selectedItem.value.packing_list.batch_number);
        formData.append('uom', selectedItem.value.packing_list.uom);
        formData.append('status', selectedItem.value.status);
        formData.append('note', disposeForm.value.note);
        
        // Append each attachment
        for (let i = 0; i < disposeForm.value.attachments.length; i++) {
            formData.append('attachments[]', disposeForm.value.attachments[i]);
        }
        
        await axios.post(route('back-order.dispose'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        await handlePoChange(selectedPo.value);
        showDisposeModal.value = false;
        alert('Item has been disposed successfully');
    } catch (error) {
        console.error('Failed to dispose items:', error);
        alert(error.response?.data?.message || 'Failed to dispose items');
    } finally {
        isSubmitting.value = false;
    }
};

</script>