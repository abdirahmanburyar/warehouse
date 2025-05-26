<template>
    <AuthenticatedLayout description="Expired" title="Expired" img="/assets/images/expires.png">
        <div class="p-6 relative">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'border-green-500 text-green-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 pr-72">
                <div class="bg-white rounded-lg shadow overflow-hidden">

            <!-- Statistics Panel -->
            <div class="fixed right-6 top-32 w-64 space-y-3">
                    <div v-if="activeTab === 'all' || activeTab === 'year'" class="bg-orange-400 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expiring within next 1 Year</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.year }} Item</div>
                                <div class="text-sm mt-1">{{ formatDate(oneYearFromNow) }}</div>
                            </div>
                            <img src="/assets/images/box.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>

                    <div v-if="activeTab === 'all' || activeTab === 'six_months'" class="bg-pink-500 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expiring within next 6 months</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.six_months }} Items</div>
                                <div class="text-sm mt-1">{{ formatDate(sixMonthsFromNow) }}</div>
                            </div>
                            <img src="/assets/images/box.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>

                    <div v-if="activeTab === 'all' || activeTab === 'expired'" class="bg-gray-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expired Items</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.expired }} Items</div>
                            </div>
                            <img src="/assets/images/box.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Until Expiry</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in filteredInventories" :key="item.id" :class="{ 'bg-yellow-50': item.expiring_soon }">
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ item.product_name }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.quantity }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(item.expiry_date) }}</td>
                            <td class="px-6 py-4">
                                <div :class="{
                                    'text-sm font-medium': true,
                                    'text-red-600': item.days_until_expiry <= 30,
                                    'text-yellow-600': item.days_until_expiry <= 180 && item.days_until_expiry > 30,
                                    'text-gray-900': item.days_until_expiry > 180
                                }">
                                    {{ item.days_until_expiry }} days
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="item.expired" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Expired
                                </span>
                                <span v-else-if="item.expiring_soon" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Expiring Very Soon
                                </span>
                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Expiring Soon
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <template v-if="item.expired">
                                    <button
                                        class="text-red-600 hover:text-red-900"
                                        @click="disposeItem(item)"
                                    >
                                        Dispose
                                    </button>
                                </template>
                                <template v-else>
                                    <Link
                                        class="text-blue-600 hover:text-blue-900"
                                        :href="route('expired.transfer', item.id)"
                                    >
                                        Transfer
                                </Link>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Dispose Modal -->
    <Modal :show="showDisposeModal" @close="showDisposeModal = false">
        <form @submit.prevent="submitDisposal" class="p-6 space-y-4">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Dispose Item</h2>
            
            <!-- Product Info -->
            <div v-if="selectedItem" class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Product ID</p>
                        <p class="text-sm text-gray-900">{{ selectedItem.product_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Product Name</p>
                        <p class="text-sm text-gray-900">{{ selectedItem.product_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Batch Number</p>
                        <p class="text-sm text-gray-900">{{ selectedItem.batch_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Barcode</p>
                        <p class="text-sm text-gray-900">{{ selectedItem.barcode }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">UOM</p>
                        <p class="text-sm text-gray-900">{{ selectedItem.uom }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Expiry Date</p>
                        <p class="text-sm text-gray-900">{{ formatDate(selectedItem.expiry_date) }}</p>
                        <p class="text-sm" :class="{'text-red-600': selectedItem.expired, 'text-green-600': !selectedItem.expired}">{{ selectedItem.expired ? 'Expired' : 'Not Expired' }}</p>
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
                    @change="handleFileChange"
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
                            @click="removeFile(index)" 
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
                    :disabled="isDisposing"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    @click="showDisposeModal = false"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="isDisposing"
                    class="inline-flex justify-center rounded-md border border-transparent bg-yellow-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                >
                    {{ isDisposing ? "Disposing..." : "Dispose"}}
                </button>
            </div>
        </form>
    </Modal>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { format, addMonths, addYears } from 'date-fns'
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'
import Transfer from './Transfer.vue'
import Modal from '@/Components/Modal.vue'
import { useToast } from 'vue-toastification'

const toast = useToast()

const props = defineProps({
    inventories: Array
})

const activeTab = ref('all')

const tabs = [
    { id: 'all', name: 'All Items' },
    { id: 'six_months', name: 'Expiring within next 6 months' },
    { id: 'year', name: 'Expiring within next 1 Year' },
    { id: 'expired', name: 'Expired' },
]

const filteredInventories = computed(() => {
    if (activeTab.value === 'all') return props.inventories
    if (activeTab.value === 'year') return props.inventories.filter(item => !item.expired && item.days_until_expiry <= 365)
    if (activeTab.value === 'six_months') return props.inventories.filter(item => !item.expired && item.days_until_expiry <= 180)
    if (activeTab.value === 'expired') return props.inventories.filter(item => item.expired)
    return props.inventories
})

const now = new Date()
const sixMonthsFromNow = addMonths(now, 6)
const oneYearFromNow = addYears(now, 1)

const formatDate = (date) => {
    return format(new Date(date), 'MMM d, yyyy')
}

const showTransferModal = ref(false)
const selectedInventory = ref(null)

const showDisposeModal = ref(false);
const selectedItem = ref(null);
const disposeForm = ref({
    quantity: 0,
    note: '',
    attachments: []
});

const handleFileChange = (e) => {
    const files = Array.from(e.target.files || []);
    disposeForm.value.attachments = files;
};

const removeFile = (index) => {
    disposeForm.value.attachments.splice(index, 1);
};

const disposeItem = (item) => {
    selectedItem.value = item;
    disposeForm.value = {
        quantity: item.quantity || 0,
        note: '',
        attachments: []
    };
    showDisposeModal.value = true;
};

const isDisposing = ref(false)

const submitDisposal = async () => {
    isDisposing.value = true
    const formData = new FormData();
    formData.append('id', selectedItem.value.id);
    formData.append('quantity', disposeForm.value.quantity);
    formData.append('note', disposeForm.value.note);
    formData.append('barcode', selectedItem.value.barcode);
    formData.append('batch_number', selectedItem.value.batch_number);
    formData.append('uom', selectedItem.value.uom);
    formData.append('expiry_date', selectedItem.value.expiry_date);
    formData.append('status', 'Expired');
    formData.append('product_id', selectedItem.value.product_id);
    
    // Append each attachment
    for (let i = 0; i < disposeForm.value.attachments.length; i++) {
        formData.append('attachments[]', disposeForm.value.attachments[i]);
    }
    
    await axios.post(route('expired.dispose', selectedItem.value.id), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then((response) => {
        isDisposing.value = false
        Swal.fire({
            icon: 'success',
            title: response.data.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            isDisposing.value = false
            showDisposeModal.value = false;
            disposeForm.value = {
                quantity: 0,
                note: '',
                attachments: []
            };
            router.get(route('expired.index'), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['inventories']
            });
        });
    })
    .catch(error => {
        console.error('Error disposing item:', error);
        isDisposing.value = false
        Swal.fire({
            icon: 'error',
            title: error.response.data,
            showConfirmButton: false,
            timer: 1500
        });
    });
};

const filteredStats = computed(() => {
    const yearItems = props.inventories.filter(item => !item.expired && item.days_until_expiry <= 365).length
    const sixMonthItems = props.inventories.filter(item => !item.expired && item.days_until_expiry <= 180).length
    const expiredItems = props.inventories.filter(item => item.expired).length
    const disposedItems = props.inventories.filter(item => item.disposed).length

    if (activeTab.value === 'all') {
        return {
            year: yearItems,
            six_months: sixMonthItems,
            expired: expiredItems,
            disposed: disposedItems
        }
    } else if (activeTab.value === 'year') {
        return {
            year: yearItems,
            six_months: sixMonthItems,
            expired: 0,
            disposed: 0
        }
    } else if (activeTab.value === 'six_months') {
        return {
            year: 0,
            six_months: sixMonthItems,
            expired: 0,
            disposed: 0
        }
    } else if (activeTab.value === 'expired') {
        return {
            year: 0,
            six_months: 0,
            expired: expiredItems,
            disposed: disposedItems
        }
    }
    return { year: 0, six_months: 0, expired: 0, disposed: 0 }
})
</script>