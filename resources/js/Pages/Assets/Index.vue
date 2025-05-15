<template>
    <AuthenticatedLayout title="Assets" description="Manage your assets" img="/assets/images/asset.png">
        <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-[300px] bg-white shadow-md flex-shrink-0 flex flex-col h-full">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center flex-shrink-0">
                    <h2 class="text-lg font-semibold">Filter by Location</h2>
                    <Link :href="route('assets.create')"
                        class="bg-indigo-500 text-white hover:bg-indigo-700 rounded-full w-8 h-8 flex items-center justify-center">
                    +
                    </Link>
                </div>
                <div class="overflow-y-auto flex-1 p-4">
                    <div v-for="location in props.locations" :key="location.id" class="pl-2">
                        <div class="flex items-center p-2 hover:bg-gray-100 rounded cursor-pointer"
                            @click="toggleLocation(location.id)">
                            <div class="flex items-center flex-1">
                                <input type="checkbox" :checked="selectedLocations.includes(location.id)" @click.stop
                                    @change="toggleLocation(location.id)"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2">{{ location.name }}</span>
                            </div>
                            <button @click.stop="toggleCollapse(location.id)"
                                class="ml-2 p-1 hover:bg-gray-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform"
                                    :class="{ 'rotate-90': !collapsedLocations.includes(location.id) }"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <!-- Sub-locations -->
                        <div v-if="location.sub_locations?.length && !collapsedLocations.includes(location.id)"
                            class="pl-8 space-y-1 mt-1">
                            <div v-for="sub in location.sub_locations" :key="sub.id"
                                class="flex items-center p-2 hover:bg-gray-100 rounded cursor-pointer">
                                <input type="checkbox" :checked="selectedSubLocations.includes(sub.id)"
                                    @change="toggleSubLocation(sub.id, location.id)"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm">{{ sub.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col overflow-hidden bg-gray-50">
                <div class="p-6 flex-1 overflow-auto space-y-6">
                    <div class="flex justify-end">
                        <Link :href="route('assets.locations.index')">Locations</Link>
                        <Link :href="route('assets.sub-locations.index')">Sub-Locations</Link>
                    </div>
                    <!-- Loading State -->
                    <div v-if="loading" class="flex justify-center items-center h-32">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    </div>

                    <!-- No Filter Message -->
                    <div v-else-if="!selectedLocations.length && !selectedSubLocations.length"
                        class="flex flex-col items-center justify-center h-32 text-gray-500 bg-white rounded-lg shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <p class="text-lg">Please select a location to view assets</p>
                    </div>

                    <!-- Stats -->
                    <div v-else class="grid grid-cols-3 gap-3">
                        <!-- Total Assets Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-2">Total Assets</h3>
                            <div class="flex items-center justify-between">
                                <span class="text-3xl font-bold">{{ assets.length }}</span>
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Active Assets Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-2">Active Assets</h3>
                            <div class="flex items-center justify-between">
                                <span class="text-3xl font-bold">{{ activeAssets }}</span>
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Assets Card -->
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold mb-2">In Maintenance</h3>
                            <div class="flex items-center justify-between">
                                <span class="text-3xl font-bold">{{ maintenanceAssets }}</span>
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assets Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden" v-if="filteredAssets">
                        <div class="p-4 border-b border-gray-200 sticky top-0 bg-white z-10">
                            <div class="flex justify-between items-center">
                                <h2 class="text-lg font-semibold">Asset List</h2>
                                <button @click="exportToExcel" 
                                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 flex items-center gap-2 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Export to Excel
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            ID</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Asset Tag</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Category</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Serial Number</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Description</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Assigned To</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Location</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Sub Location</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Acquisition Date</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Status</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Original Value</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Source Agency</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 whitespace-nowrap">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(asset, i) in filteredAssets" :key="asset.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ i + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.asset_tag }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.category.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.serial_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.item_description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.person_assigned }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.location.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.sub_location?.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.acquisition_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-green-100 text-green-800': asset.status === 'active',
                                                'bg-yellow-100 text-yellow-800': asset.status === 'maintenance',
                                                'bg-blue-100 text-blue-800': asset.status === 'in_use',
                                                'bg-red-100 text-red-800': asset.status === 'retired' || asset.status === 'disposed'
                                            }">
                                                {{ asset.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatMoney(asset.original_value) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ asset.source_agency }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <button @click="openHistoryModal(asset)"
                                                class="text-blue-600 hover:text-blue-900 mr-3 text-sm font-medium">
                                                History
                                            </button>
                                            <Link :href="route('assets.edit', asset.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3 text-sm font-medium">
                                            Edit
                                            </Link>

                                            <!-- History Modal -->
                                            <TransitionRoot appear :show="isHistoryModalOpen" as="template">
                                                <Dialog as="div" @close="closeHistoryModal" class="relative z-50">
                                                    <TransitionChild as="template" enter="duration-300 ease-out"
                                                        enter-from="opacity-0" enter-to="opacity-100"
                                                        leave="duration-200 ease-in" leave-from="opacity-100"
                                                        leave-to="opacity-0">
                                                        <div class="fixed inset-0 bg-black/25" />
                                                    </TransitionChild>

                                                    <div class="fixed inset-0 overflow-y-auto">
                                                        <div class="flex min-h-full items-center justify-center p-4">
                                                            <TransitionChild as="template" enter="duration-300 ease-out"
                                                                enter-from="opacity-0 scale-95"
                                                                enter-to="opacity-100 scale-100"
                                                                leave="duration-200 ease-in"
                                                                leave-from="opacity-100 scale-100"
                                                                leave-to="opacity-0 scale-95">
                                                                <DialogPanel
                                                                    class="w-full max-w-2xl transform overflow-hidden rounded-xl bg-white p-6 shadow-xl transition-all">
                                                                    <div class="flex items-center justify-between mb-4">
                                                                        <DialogTitle as="h3"
                                                                            class="text-lg font-medium text-gray-900">
                                                                            Asset History
                                                                        </DialogTitle>
                                                                        <button @click="closeHistoryModal"
                                                                            class="text-gray-400 hover:text-gray-500">
                                                                            <XMarkIcon class="h-6 w-6" />
                                                                        </button>
                                                                    </div>

                                                                    <div class="overflow-hidden">
                                                                        <div class="max-h-[60vh] overflow-auto">
                                                                            <table
                                                                                class="min-w-full divide-y divide-gray-200 relative">
                                                                                <thead
                                                                                    class="bg-gray-50 sticky top-0 z-10">
                                                                                    <tr>
                                                                                        <th scope="col"
                                                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                                            Status</th>
                                                                                        <th scope="col"
                                                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                                            Custodian</th>
                                                                                        <th scope="col"
                                                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                                            Date</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody
                                                                                    class="bg-white divide-y divide-gray-200">
                                                                                    <tr v-for="(record, index) in selectedAssetHistory"
                                                                                        :key="index"
                                                                                        class="hover:bg-gray-50">
                                                                                        <td
                                                                                            class="px-6 py-4 whitespace-nowrap">
                                                                                            <span :class="{
                                                                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                                                                'bg-green-100 text-green-800': record.status === 'active',
                                                                                                'bg-yellow-100 text-yellow-800': record.status === 'maintenance',
                                                                                                'bg-blue-100 text-blue-800': record.status === 'in_use',
                                                                                                'bg-red-100 text-red-800': record.status === 'retired' || record.status === 'disposed'
                                                                                            }">
                                                                                                {{ record.status }}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td
                                                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                                            {{ record.custodian }}
                                                                                        </td>
                                                                                        <td
                                                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                                            {{
                                                                                            formatDate(record.created_at)
                                                                                            }}
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </DialogPanel>
                                                            </TransitionChild>
                                                        </div>
                                                    </div>
                                                </Dialog>
                                            </TransitionRoot>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { debounce } from 'lodash';
import axios from 'axios';
import * as XLSX from 'xlsx';

const props = defineProps({
    locations: {
        type: Array,
        required: true
    }
});

const assets = ref([]);
const loading = ref(false);
const selectedLocations = ref([]);
const selectedSubLocations = ref([]);
const collapsedLocations = ref([]);
const isHistoryModalOpen = ref(false);
const selectedAssetHistory = ref([]);

// Computed property for filtered assets
const filteredAssets = computed(() => assets.value);

// Function to fetch assets
const fetchAssets = async () => {
    // Only fetch if filters are selected
    if (!selectedLocations.value.length && !selectedSubLocations.value.length) {
        assets.value = [];
        return;
    }

    try {
        loading.value = true;
        const params = {
            locations: selectedLocations.value,
            sub_locations: selectedSubLocations.value
        };

        const response = await axios.get(route('assets.get'), { params });
        assets.value = response.data;
    } catch (error) {
        console.error('Error fetching assets:', error);
        assets.value = [];
    } finally {
        loading.value = false;
    }
};

// Toggle location selection
const toggleLocation = (locationId) => {
    const index = selectedLocations.value.indexOf(locationId);
    if (index === -1) {
        selectedLocations.value.push(locationId);
        // Auto-select all sub-locations
        const location = props.locations.find(l => l.id === locationId);
        if (location?.sub_locations) {
            location.sub_locations.forEach(sub => {
                if (!selectedSubLocations.value.includes(sub.id)) {
                    selectedSubLocations.value.push(sub.id);
                }
            });
        }
    } else {
        selectedLocations.value.splice(index, 1);
        // Deselect all sub-locations of this location
        const location = props.locations.find(l => l.id === locationId);
        if (location?.sub_locations) {
            location.sub_locations.forEach(sub => {
                const subIndex = selectedSubLocations.value.indexOf(sub.id);
                if (subIndex !== -1) {
                    selectedSubLocations.value.splice(subIndex, 1);
                }
            });
        }
    }
};

// Format money
const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};


// Toggle sub-location selection
const toggleSubLocation = (subLocationId, parentLocationId) => {
    const index = selectedSubLocations.value.indexOf(subLocationId);
    if (index === -1) {
        selectedSubLocations.value.push(subLocationId);
        // Ensure parent location is selected
        if (!selectedLocations.value.includes(parentLocationId)) {
            selectedLocations.value.push(parentLocationId);
        }
    } else {
        selectedSubLocations.value.splice(index, 1);
        // Check if all sub-locations are deselected
        const location = props.locations.find(l => l.id === parentLocationId);
        const anySubSelected = location?.sub_locations.some(sub =>
            selectedSubLocations.value.includes(sub.id)
        );
        if (!anySubSelected) {
            const parentIndex = selectedLocations.value.indexOf(parentLocationId);
            if (parentIndex !== -1) {
                selectedLocations.value.splice(parentIndex, 1);
            }
        }
    }
};

// Toggle location collapse state
const toggleCollapse = (locationId) => {
    const index = collapsedLocations.value.indexOf(locationId);
    if (index === -1) {
        collapsedLocations.value.push(locationId);
    } else {
        collapsedLocations.value.splice(index, 1);
    }
};

// Computed properties for the cards
const totalAssets = computed(() => assets.value.length);
const activeAssets = computed(() => assets.value.filter(asset => asset.status === 'in_use').length);
const maintenanceAssets = computed(() => assets.value.filter(asset => asset.status === 'maintenance').length);

// Modal functions
const openHistoryModal = (asset) => {
    selectedAssetHistory.value = [...asset.history].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    isHistoryModalOpen.value = true;
};

const closeHistoryModal = () => {
    isHistoryModalOpen.value = false;
    selectedAssetHistory.value = [];
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(date);
};

// Excel export function
const exportToExcel = () => {
    // Prepare the data
    const exportData = assets.value.map(asset => ({
        'Asset Tag': asset.asset_tag,
        'Category': asset.category.name,
        'Serial Number': asset.serial_number,
        'Description': asset.item_description,
        'Assigned To': asset.person_assigned,
        'Location': asset.location.name,
        'Sub Location': asset.sub_location?.name || '',
        'Acquisition Date': formatDate(asset.acquisition_date),
        'Status': asset.status,
        'Original Value': asset.original_value,
        'Source Agency': asset.source_agency
    }));

    // Create worksheet
    const worksheet = XLSX.utils.json_to_sheet(exportData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Assets');

    // Generate file name with current date
    const date = new Date().toISOString().split('T')[0];
    const fileName = `assets_${date}.xlsx`;

    // Save the file
    XLSX.writeFile(workbook, fileName);
};

// Watch for changes in location and sub-location selections
const debouncedFilter = debounce(() => {
    fetchAssets();
}, 300);

watch([selectedLocations, selectedSubLocations], debouncedFilter, { deep: true });

// No initial fetch since we want to wait for filter selection
</script>