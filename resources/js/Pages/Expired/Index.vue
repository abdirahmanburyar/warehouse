<template>
    <AuthenticatedLayout description="Expired" title="Expired" img="/assets/images/expires.png">
        <div class="p-2 mb-[100px]">
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2">
                    <div class="col-span-1 md:col-span-2 min-w-0">
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <TextInput v-model="search" type="text" class="w-full"
                            placeholder="Search by item name, barcode" />
                    </div>
                    <div class="col-span-1 min-w-0">
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <Multiselect v-model="category" :options="props.categories" :searchable="true"
                            :close-on-select="true" :show-labels="false" placeholder="Select a category"
                            :allow-empty="true" class="multiselect--with-icon w-full">
                        </Multiselect>
                    </div>
                    <div class="col-span-1 min-w-0">
                        <label class="block text-sm font-medium text-gray-700">Dosage Form</label>
                        <Multiselect v-model="dosage" :options="props.dosage" :searchable="true" :close-on-select="true"
                            :show-labels="false" placeholder="Select a dosage form" :allow-empty="true"
                            class="multiselect--with-icon w-full">
                        </Multiselect>
                    </div>
                    <div class="col-span-1 min-w-0">
                        <label class="block text-sm font-medium text-gray-700">Warehouse</label>
                        <Multiselect v-model="warehouse" :options="props.warehouses" :searchable="true"
                            :close-on-select="true" :show-labels="false" placeholder="Select a warehouse"
                            :allow-empty="true" class="multiselect--with-icon multiselect--rounded w-full">
                        </Multiselect>
                    </div>
                    <div class="col-span-1 min-w-0">
                        <label class="block text-sm font-medium text-gray-700">Storage Location</label>
                        <Multiselect v-model="location" :options="loadedLocation" :searchable="true"
                            :close-on-select="true" :show-labels="false" placeholder="Select a S. Location"
                            :allow-empty="true" :disabled="warehouse == null"
                            class="multiselect--with-icon multiselect--rounded w-full">
                        </Multiselect>
                    </div>
                </div>
                <div class="flex justify-end">
                    <select v-model="per_page"
                        class="rounded-full border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-[200px] mb-1"
                        @change="props.filters.page = 1">
                        <option :value="6">6 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                        <option :value="100">100 per page</option>
                        <option :value="200">200 per page</option>
                    </select>
                </div>
            </div>
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                        activeTab === tab.id
                            ? 'border-green-500 text-green-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs'
                    ]">
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-12 gap-4">
                <!-- LEFT COLUMN: Table (8/12) -->
                <div class="col-span-12 md:col-span-9 overflow-auto">
                    <table class="min-w-full border border-collapse border-black">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-2 text-left text-xs font-medium uppercase border border-black">Product</th>
                                <th class="p-2 text-left text-xs font-medium uppercase border border-black">Quantity</th>
                                <th class="p-2 text-left text-xs font-medium uppercase border border-black">Batch Number</th>
                                <th class="p-2 text-left text-xs font-medium uppercase border border-black">Location</th>
                                <th class="p-2 text-left text-xs font-medium uppercase border border-black">Expiry Date</th>
                                <th class="p-2 text-left text-xs font-medium uppercase border border-black">Days Until Expiry</th>
                                <th class="p-2 w-[150px] text-left text-xs font-medium uppercase border border-black">Status</th>
                                <th class="p-2 text-left text-xs font-medium uppercase border border-black">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in props.inventories.data" :key="item.id" 
                                :class="{
                                    'bg-orange-50': !item.expired && item.days_until_expiry > 180 && item.days_until_expiry <= 365,
                                    'bg-pink-50': !item.expired && item.days_until_expiry <= 180 && item.days_until_expiry > 0,
                                    'bg-gray-50': item.expired
                                }">
                                <td class="p-2 text-xs text-gray-900 border border-black">{{ item.product?.name }}</td>
                                <td class="p-2 text-xs text-gray-500 border border-black">{{ item.quantity }}</td>
                                <td class="p-2 text-xs text-gray-500 border border-black">{{ item.batch_number }}</td>
                                <td class="p-2 text-xs text-gray-500 border border-black">{{ item.location || 'N/A' }}</td>
                                <td class="p-2 text-xs text-gray-500 border border-black">{{ formatDate(item.expiry_date) }}</td>
                                <td class="p-2 border border-black">
                                    <div :class="{
                                        'text-xs font-medium': true,
                                        'text-gray-600': item.expired,
                                        'text-orange-600': !item.expired && item.days_until_expiry > 180 && item.days_until_expiry <= 365,
                                        'text-pink-600': !item.expired && item.days_until_expiry <= 180 && item.days_until_expiry > 0
                                    }">
                                        {{ item.days_until_expiry }} days
                                    </div>
                                </td>
                                <td class="p-2 border border-black">
                                    <span v-if="item.expired"
                                        class="p-[5px] rounded-xl inline-flex items-center bg-gray-600 text-xs font-medium text-white">
                                        Expired
                                    </span>
                                    <span v-else-if="item.days_until_expiry <= 180 && item.days_until_expiry > 0"
                                        class="p-[5px] rounded-xl inline-flex items-center bg-pink-500 text-xs font-medium text-white">
                                        Expiring Very Soon
                                    </span>
                                    <span v-else-if="item.days_until_expiry > 180 && item.days_until_expiry <= 365"
                                        class="p-[5px] rounded-xl inline-flex items-center bg-orange-400 text-xs font-medium text-white">
                                        Expiring Soon
                                    </span>
                                </td>
                                <td class="p-2 whitespace-nowrap text-xs text-gray-500 border border-black">
                                    <template v-if="item.expired">
                                        <button class="text-red-600 hover:text-red-900"
                                            @click="disposeItem(item)">
                                            <img src="/assets/images/Disposal.png" alt="Dispose" class="w-10">
                                        </button>
                                    </template>
                                    <template v-else>
                                        <Link class="text-blue-600 hover:text-blue-900"
                                            :href="route('expired.transfer', item.id)">
                                            <img src="/assets/images/facility.png" alt="Transfer" class="w-10">
                                        </Link>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-6 px-4 py-3 flex items-center justify-end">
                        <TailwindPagination :data="props.inventories" @pagination-change-page="getResults" :limit="3" />
                    </div>
                </div>

                <!-- RIGHT COLUMN: Summary cards (4/12) -->
                <div class="col-span-12 md:col-span-3">
                    <div v-if="activeTab === 'all' || activeTab === 'year'"
                        class="bg-orange-400 rounded-lg p-4 text-white shadow-lg mb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expiring within next 1 Year</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.year }} Item</div>
                                <div class="text-xs mt-1">{{ formatDate(oneYearFromNow) }}</div>
                            </div>
                            <img src="/assets/images/soon_expire.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>

                    <div v-if="activeTab === 'all' || activeTab === 'six_months'"
                        class="bg-pink-500 rounded-lg p-4 text-white shadow-lg mb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expiring within next 6 months</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.six_months }} Items</div>
                                <div class="text-xs mt-1">{{ formatDate(sixMonthsFromNow) }}</div>
                            </div>
                            <img src="/assets/images/Near Expiration Alert.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>

                    <div v-if="activeTab === 'all' || activeTab === 'expired'"
                        class="bg-gray-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expired Items</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.expired }} Items</div>
                            </div>
                            <img src="/assets/images/expired_stock.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>
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
                        <p class="text-xs font-medium text-gray-500">Product Name</p>
                        <p class="text-xs text-gray-900">{{ selectedItem.product?.name }}</p>
                        <p class="text-xs font-medium text-gray-500">Batch Number</p>
                        <p class="text-xs text-gray-900">{{ selectedItem.batch_number }}</p>
                        <p class="text-xs font-medium text-gray-500">Barcode</p>
                        <p class="text-xs text-gray-900">{{ selectedItem.barcode || 'N/A' }}</p>
                        <p class="text-xs font-medium text-gray-500">UOM</p>
                        <p class="text-xs text-gray-900">{{ selectedItem.uom }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Expiry Date</p>
                        <p class="text-xs text-gray-900">{{ formatDate(selectedItem.expiry_date) }}</p>
                        <p class="text-xs"
                            :class="{ 'text-red-600': selectedItem.expired, 'text-green-600': !selectedItem.expired }">
                            {{
                                selectedItem.expired ? 'Expired' : 'Not Expired' }}</p>
                    </div>
                </div>
            </div>

            <!-- Quantity -->
            <div>
                <label for="quantity" class="block text-xs font-medium text-gray-700">Quantity</label>
                <input type="number" id="quantity" v-model="disposeForm.quantity" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
                    :min="1" :max="selectedItem?.quantity" required>
            </div>

            <!-- Note -->
            <div>
                <label for="note" class="block text-xs font-medium text-gray-700">Note</label>
                <textarea id="note" v-model="disposeForm.note"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
                    rows="3" required></textarea>
            </div>

            <!-- Attachments -->
            <div>
                <label class="block text-xs font-medium text-gray-700">Attachments (PDF files)</label>
                <input type="file" @change="handleFileChange"
                    class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    multiple accept=".pdf" required>
            </div>

            <!-- Selected Files Preview -->
            <div v-if="disposeForm.attachments.length > 0" class="mt-2">
                <h4 class="text-xs font-medium text-gray-700 mb-2">Selected Files:</h4>
                <ul class="space-y-2">
                    <li v-for="(file, index) in disposeForm.attachments" :key="index"
                        class="flex items-center justify-between text-xs text-gray-500 bg-gray-50 p-2 rounded">
                        <span>{{ file.name }}</span>
                        <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">
                            Remove
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" :disabled="isDisposing"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    @click="showDisposeModal = false">
                    Cancel
                </button>
                <button type="submit" :disabled="isDisposing"
                    class="inline-flex justify-center rounded-md border border-transparent bg-yellow-500 px-4 py-2 text-xs font-medium text-white shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                    {{ isDisposing ? "Disposing..." : "Dispose" }}
                </button>
            </div>
        </form>
    </Modal>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { format, addMonths, addYears } from 'date-fns'
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'
import Transfer from './Transfer.vue'
import Modal from '@/Components/Modal.vue'
import { useToast } from 'vue-toastification'
import moment from 'moment'
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { TailwindPagination } from "laravel-vue-pagination";

import TextInput from "@/Components/TextInput.vue";

const toast = useToast()

const props = defineProps({
    inventories: Object,
    products: Array,
    dosage: Array,
    categories: Array,
    warehouses: Array,
    filters: Object,
    summary: Object,
})

const activeTab = ref('all')

const tabs = [
    { id: 'all', name: 'All Items' },
    { id: 'year', name: 'Expiring within next 1 Year' },
    { id: 'six_months', name: 'Expiring within next 6 months' },
    { id: 'expired', name: 'Expired' },
]

const now = new Date()
const sixMonthsFromNow = addMonths(now, 6)
const oneYearFromNow = addYears(now, 1)

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
}

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

const search = ref(props.filters.search || "");
const location = ref(props.filters.location || "");
const dosage = ref(props.filters.dosage || "");
const category = ref(props.filters.category || "");
const warehouse = ref(props.filters.warehouse || "");
const per_page = ref(props.filters.per_page || 25);

const loadedLocation = ref([]);

// Apply filters
const applyFilters = () => {
    const query = {};
    // Add all filter values to query object
    if (search.value) query.search = search.value;
    if (location.value) query.location = location.value;
    if (warehouse.value) query.warehouse = warehouse.value;
    if (dosage.value) query.dosage = dosage.value;
    if (category.value) query.category = category.value;

    // Always include per_page in query if it exists
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("expired.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            "inventories",
            "products",
            "warehouses",
            "filters",
            "summary",
            "locations",
            "dosage",
            "category",
        ],
    });
};

// Watch for changes in search input
watch(
    [
        () => search.value,
        () => location.value,
        () => per_page.value,
        () => warehouse.value,
        () => dosage.value,
        () => category.value,
        () => props.filters.page,
    ],
    () => {
        applyFilters();
    }
);

function getResults(page = 1) {
    props.filters.page = page;
}

const isDisposing = ref(false)

const submitDisposal = async () => {
    isDisposing.value = true
    const formData = new FormData();
    formData.append('id', selectedItem.value.id);
    formData.append('quantity', disposeForm.value.quantity);
    formData.append('note', disposeForm.value.note);
    formData.append('type', 'Expired');
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
    const yearItems = props.summary.expiring_within_1_year
    const sixMonthItems = props.summary.expiring_within_6_months
    const expiredItems = props.summary.expired
    const disposedItems = props.summary.disposed

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


watch([() => warehouse.value], () => {
    if (warehouse.value == null) {
        location.value = null;
        return;
    }
    loadLocations();
});
async function loadLocations() {
    await axios
        .post(route("invetnories.getLocations"), {
            warehouse: warehouse.value,
        })
        .then((response) => {
            loadedLocation.value = response.data;
        })
        .catch((error) => {
            console.log(error);
            toast.error(error.response.data);
        });
}

</script>