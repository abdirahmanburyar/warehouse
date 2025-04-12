<template>

    <Head title="Expired" />
    <AuthenticatedLayout title="Manage Expiring Items" description="saving Stock, Securing Quality" img="/assets/images/expires.png">
        <div class="bg-white overflow-hidden sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Flash messages -->
                <div v-if="$page.props.flash && $page.props.flash.success"
                    class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash && $page.props.flash.error"
                    class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Main content container - flex on large screens -->
                <div class="flex flex-col lg:flex-row lg:gap-6">
                    <!-- Stats cards - horizontal on small screens, vertical on right side for large screens -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4 lg:hidden">
                        <div class="bg-orange-400 p-4 rounded-xl hover:shadow-md transition-all duration-200 cursor-pointer text-white"
                            @click="navigateToTab('all')">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium mb-1">Expiring within next<br />1 Year</div>
                                    <div class="text-xl font-bold mb-2">{{ counts.all }} items</div>
                                    <div class="text-xs opacity-90">{{ oneYearFromNow }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-pink-500 p-4 rounded-xl hover:shadow-md transition-all duration-200 cursor-pointer text-white"
                            @click="navigateToTab('near')">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium mb-1">Expiring within next<br />6 months</div>
                                    <div class="text-xl font-bold mb-2">{{ counts.near }} items</div>
                                    <div class="text-xs opacity-90">{{ sixMonthsFromNow }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gray-500 p-4 rounded-xl hover:shadow-md transition-all duration-200 cursor-pointer text-white"
                            @click="navigateToTab('expired')">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium mb-1">Expired items</div>
                                    <div class="text-xl font-bold mb-2">{{ counts.expired }} items</div>
                                    <div class="text-xs opacity-90">{{ oneYearAgo }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-emerald-400 p-4 rounded-xl hover:shadow-md transition-all duration-200 cursor-pointer text-white"
                            @click="navigateToTab('disposed')">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium mb-1">Disposed items</div>
                                    <div class="text-xl font-bold mb-2">{{ counts.disposed }} items</div>
                                    <div class="text-xs opacity-90">{{ disposalDate }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Table section - 85% width on large screens -->
                    <div class="lg:w-10/12 lg:order-1">
                        <!-- Tabs -->
                        <div class="border-b border-gray-200 mb-4">
                            <nav class="-mb-px flex w-full">
                                <button @click="navigateToTab('all')"
                                    class="flex-1 text-center whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                    :class="[currentTab === 'all' ? 'border-blue-600 text-blue-700 border-b-4' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                                    All Expiration
                                </button>
                                <button @click="navigateToTab('near')"
                                    class="flex-1 text-center whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                    :class="[currentTab === 'near' ? 'border-yellow-600 text-yellow-700 border-b-4' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                                    Near Expiry
                                </button>
                                <button @click="navigateToTab('expired')"
                                    class="flex-1 text-center whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                    :class="[currentTab === 'expired' ? 'border-red-600 text-red-700 border-b-4' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                                    Expired
                                </button>
                                <button @click="navigateToTab('disposed')"
                                    class="flex-1 text-center whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                    :class="[currentTab === 'disposed' ? 'border-emerald-600 text-emerald-700 border-b-4' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                                    Disposed
                                </button>
                            </nav>
                        </div>

                        <!-- Search and Filter Bar -->
                        <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="w-full md:w-auto">
                                <input type="text" v-model="search" placeholder="Search products, batch numbers..."
                                    class=" border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                            </div>
                            <div class="flex items-center space-x-4">
                                <select v-model="warehouseFilter"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Warehouses</option>
                                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                        {{ warehouse.name }}
                                    </option>
                                </select>

                                <div v-if="currentTab === 'expired' || currentTab === 'near'" class="inline-block">
                                    <button @click="showDisposeModal = true"
                                        :disabled="selectedInventories.length === 0"
                                        :class="[selectedInventories.length === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700', 'text-white py-2 px-4 rounded']">
                                        Mark as Disposed
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-2 border-black divide-y divide-black">
                                <thead class="bg-gray-50 border-b-2 border-black">
                                    <tr>
                                        <th v-if="currentTab === 'expired' || currentTab === 'near'" scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider border-r-2 border-black">
                                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                                        </th>
                                        <th @click="sort('product_name')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer border-r-2 border-black">
                                            Product
                                            <span v-if="sort_field === 'product_name'" class="ml-1">
                                                {{ sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th @click="sort('batch_number')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer border-r-2 border-black">
                                            Batch Number
                                            <span v-if="sort_field === 'batch_number'" class="ml-1">
                                                {{ sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th @click="sort('expiry_date')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer border-r-2 border-black">
                                            Expiry Date
                                            <span v-if="sort_field === 'expiry_date'" class="ml-1">
                                                {{ sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th @click="sort('quantity')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer border-r-2 border-black">
                                            Quantity
                                            <span v-if="sort_field === 'quantity'" class="ml-1">
                                                {{ sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider border-r-2 border-black">
                                            Status
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-black">
                                    <tr v-if="inventories.data.length === 0">
                                        <td :colspan="currentTab === 'expired' || currentTab === 'near' ? 7 : 6" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-lg font-medium">No {{ currentTab }} inventory items found</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="inventory in inventories.data" :key="inventory.id" :class="{
                                        'bg-red-50': inventory.is_expired,
                                        'bg-yellow-50': inventory.is_near_expiry
                                    }">
                                        <td v-if="currentTab === 'expired' || currentTab === 'near'"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r-2 border-black">
                                            <input type="checkbox" v-model="selectedInventories" :value="inventory.id">
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r-2 border-black">
                                            {{ inventory.product_name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r-2 border-black">
                                            {{ inventory.batch_number }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r-2 border-black">
                                            {{ formatDate(inventory.expiry_date) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r-2 border-black">
                                            {{ inventory.quantity }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r-2 border-black">
                                            <span :class="getStatusClass(inventory)">
                                                {{ getStatusText(inventory) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <button
                                                v-if="(currentTab === 'expired' || currentTab === 'near') && !inventory.is_disposed"
                                                @click="openDisposeModal([inventory.id])"
                                                class="text-red-600 hover:text-red-900">
                                                Mark as Disposed
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Pagination -->
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Pagination :links="inventories.meta.links" />
                        </div>

                    </div>

                    <!-- Stats cards - only visible on large screens, vertical on right side -->
                    <div
                        class="hidden lg:grid lg:grid-cols-1 gap-3 lg:w-2/12 lg:order-2 lg:sticky lg:top-0 lg:self-start">
                        <div class="bg-gradient-to-r from-orange-400 to-orange-300 p-4 rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer"
                            @click="navigateToTab('all')">
                            <div class="flex items-center justify-between">
                                <div class="text-white">
                                    <div class="text-sm font-medium">Expiring within next 1 year</div>
                                    <div class="text-2xl font-bold">{{ counts.all }}</div>
                                    <div class="text-xs mt-1 opacity-80">{{ oneYearFromNow }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-pink-500 to-pink-400 p-4 rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer"
                            @click="navigateToTab('near')">
                            <div class="flex items-center justify-between">
                                <div class="text-white">
                                    <div class="text-sm font-medium">Expiring within 6 months</div>
                                    <div class="text-2xl font-bold">{{ counts.near }}</div>
                                    <div class="text-xs mt-1 opacity-80">{{ sixMonthsFromNow }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-gray-600 to-gray-500 p-4 rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer"
                            @click="navigateToTab('expired')">
                            <div class="flex items-center justify-between">
                                <div class="text-white">
                                    <div class="text-sm font-medium">Expired items</div>
                                    <div class="text-2xl font-bold">{{ counts.expired }}</div>
                                    <div class="text-xs mt-1 opacity-80">{{ oneYearAgo }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-emerald-400 to-emerald-300 p-4 rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer"
                            @click="navigateToTab('disposed')">
                            <div class="flex items-center justify-between">
                                <div class="text-white">
                                    <div class="text-sm font-medium">Disposed items</div>
                                    <div class="text-2xl font-bold">{{ counts.disposed }}</div>
                                    <div class="text-xs mt-1 opacity-80">{{ disposalDate }}</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white opacity-80"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dispose Modal -->
                <Modal :show="showDisposeModal" @close="showDisposeModal = false">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">
                            Mark Items as Disposed
                        </h2>

                        <p class="mb-4 text-gray-600">
                            You are about to mark {{ selectedInventories.length }} item(s) as disposed. This action
                            cannot be undone.
                        </p>

                        <div class="mb-4">
                            <InputLabel for="disposal_notes" value="Notes (Optional)" />
                            <TextArea id="disposal_notes" v-model="disposalNotes" class="mt-1 block w-full"
                                placeholder="Reason for disposal" />
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <SecondaryButton @click="showDisposeModal = false">
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton @click="confirmDisposal" :class="{ 'opacity-25': processing }"
                                :disabled="processing">
                                {{ processing ? 'Processing...' : 'Confirm Disposal' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import debounce from 'lodash/debounce';
import moment from 'moment';

const props = defineProps({
    inventories: Object,
    warehouses: Array,
    activeTab: {
        type: String,
        default: 'all'
    },
    filters: Object,
    counts: Object
});

// Initialize state values
const currentTab = ref(props.activeTab);
const search = ref(props.filters?.search || '');
const sort_field = ref(props.filters?.sort_field || 'expiry_date');
const sort_direction = ref(props.filters?.sort_direction || 'asc');
const warehouseFilter = ref('');
const selectedInventories = ref([]);
const selectAll = ref(false);
const showDisposeModal = ref(false);
const disposalNotes = ref('');
const processing = ref(false);

// Calculate dates for the cards
const oneYearFromNow = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    return formatDateShort(date);
});

const sixMonthsFromNow = computed(() => {
    const date = new Date();
    date.setMonth(date.getMonth() + 6);
    return formatDateShort(date);
});

const oneYearAgo = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 1);
    return formatDateShort(date);
});

const disposalDate = computed(() => {
    const date = new Date();
    date.setMonth(date.getMonth() + 2);
    return formatDateShort(date);
});

// Simple date formatter for card display (MM/DD/YYYY)
function formatDateShort(date) {
    if (!date) return 'N/A';
    return moment(date).format('LL');
}

// Watch for search input changes
watch(search, debounce(() => {
    debouncedSearch();
}, 300));

// Watch for warehouse filter changes
watch(warehouseFilter, () => {
    debouncedSearch();
});

// Navigate to a different tab
function navigateToTab(tab) {
    currentTab.value = tab;

    const params = {};
    if (search.value) params.search = search.value;
    if (sort_field.value) params.sort_field = sort_field.value;
    if (sort_direction.value) params.sort_direction = sort_direction.value;
    if (warehouseFilter.value) params.warehouse_id = warehouseFilter.value;

    router.visit(route('expired.index', { tab: tab, ...params }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['inventories', 'filters', 'counts']
    });
}

// Search function
function debouncedSearch() {
    const params = {};

    // Only include non-empty values
    if (search.value) params.search = search.value;
    if (sort_field.value) params.sort_field = sort_field.value;
    if (sort_direction.value) params.sort_direction = sort_direction.value;
    if (warehouseFilter.value) params.warehouse_id = warehouseFilter.value;

    router.visit(route('expired.index', { tab: currentTab.value, ...params }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['inventories', 'filters', 'counts']
    });
}

// Sort function
function sort(field) {
    if (sort_field.value === field) {
        sort_direction.value = sort_direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort_field.value = field;
        sort_direction.value = 'asc';
    }

    debouncedSearch();
}

// Toggle select all
function toggleSelectAll() {
    if (selectAll.value) {
        selectedInventories.value = props.inventories.data.map(item => item.id);
    } else {
        selectedInventories.value = [];
    }
}

// Open the dispose modal for specific items
function openDisposeModal(inventoryIds = []) {
    if (inventoryIds.length > 0) {
        selectedInventories.value = inventoryIds;
    }
    showDisposeModal.value = true;
}

// Confirm disposal of selected items
function confirmDisposal() {
    processing.value = true;

    const form = useForm({
        inventory_ids: selectedInventories.value,
        notes: disposalNotes.value
    });

    form.post(route('expired.dispose'), {
        preserveScroll: true,
        onSuccess: () => {
            showDisposeModal.value = false;
            selectedInventories.value = [];
            selectAll.value = false;
            disposalNotes.value = '';
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
}

// Helper functions for date formatting and status
const formatDate = (date) => {
    if (!date) return 'N/A';
    return moment(date).format('LL');
};

// Functions using pre-computed values from the resource
function getExpiryClass(inventory) {
    if (inventory.is_expired) return 'text-red-600';
    if (inventory.is_near_expiry) return 'text-yellow-600';
    return 'text-gray-900';
}

function getStatusText(inventory) {
    switch (inventory.status) {
        case 'disposed': return 'Disposed';
        case 'expired': return 'Expired';
        case 'near': return 'Near Expiry';
        default: return 'Active';
    }
}

function getStatusClass(inventory) {
    switch (inventory.status) {
        case 'disposed': return 'bg-green-100 text-green-800 border border-green-400 font-semibold';
        case 'expired': return 'bg-red-100 text-red-800 border border-red-400 font-semibold';
        case 'near': return 'bg-yellow-100 text-yellow-800 border border-yellow-400 font-semibold';
        default: return 'bg-blue-100 text-blue-800 border border-blue-400 font-semibold';
    }
}

// Add pagination handling
const handlePageChange = (page) => {
    const query = {
        page,
        tab: currentTab.value,
        search: search.value,
        sort_field: sort_field.value,
        sort_direction: sort_direction.value
    };

    if (warehouseFilter.value) {
        query.warehouse_id = warehouseFilter.value;
    }

    router.get(route('expired.index'), query, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>