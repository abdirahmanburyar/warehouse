<template>
    <AuthenticatedLayout title="Assets" description="Manage your assets" img="/assets/images/asset.png">
        <div class="">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Assets</h1>
                <div class="flex gap-2">
                    <button
                        @click="exportToExcel"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Export to Excel
                    </button>

                    <Link 
                    :href="route('assets.create')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Asset
                </Link>
                </div>
            </div>
            <!-- Filters Row -->
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-1">
                <!-- Search Filter -->
                <input 
                    type="text" 
                    v-model="search" 
                    placeholder="Search assets by tag, serial number, or description" 
                    class="w-full"
                >
                <!-- Region Filter -->
                <div class="">
                    <Multiselect
                        v-model="regionFilter"
                        :options="regionOptions"
                        label="name"
                        track-by="id"
                        placeholder="Region"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :allow-empty="true"
                        class=""
                        @input="onRegionChange"
                    />
                </div>
                <!-- Fund Source Filter -->
                <div>
                    <Multiselect
                        v-model="fundSourceFilter"
                        :options="props.fundSources"
                        label="name"
                        track-by="id"
                        placeholder="Fund Source"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :allow-empty="true"
                        class=""
                    />
                </div>
                <!-- Location Filter -->
                <div class="">
                    <Multiselect
                        v-model="locationFilter"
                        :options="locationOptions"
                        label="name"
                        track-by="id"
                        placeholder="Location"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :allow-empty="true"
                        class=""
                        @input="onLocationChange"
                    />
                </div>
                <!-- SubLocation Filter -->
                <div class="">
                    <Multiselect
                        v-model="selectedSubLocations"
                        :options="filteredSubLocations"
                        label="name"
                        track-by="id"
                        placeholder="Sub Location(s)"
                        :multiple="true"
                        :show-labels="false"
                        :allow-empty="true"
                        class="text-xs"
                    />
                </div>
            </div>
            <!-- Create Asset Button -->
            <!-- Per Page Row -->
            <div class="flex justify-end mt-2">
                <div class="flex items-center gap-2">
                    <select 
                        v-model="per_page" 
                        @change="props.filters.page = 1"
                        class="border border-gray-300 rounded-lg py-2 pl-3 pr-8 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="1">1 per page</option>
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
            <!-- Main content area -->
            <div class="flex-grow overflow-auto p-4">
                <!-- Asset cards will go here -->
                <!-- Dashboard Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
                        <span class="text-3xl font-bold text-indigo-600">{{ props.assets.data.length }}</span>
                        <span class="text-xs text-gray-500 mt-1">Total Assets</span>
                    </div>
                    <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
                        <span class="text-3xl font-bold text-green-600">{{ props.assets.data.filter(a => a.status === 'in_use').length }}</span>
                        <span class="text-xs text-gray-500 mt-1">In Use</span>
                    </div>
                    <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
                        <span class="text-3xl font-bold text-orange-500">{{ props.assets.data.filter(a => a.status === 'maintenance').length }}</span>
                        <span class="text-xs text-gray-500 mt-1">Maintenance</span>
                    </div>
                </div>
                <!-- Asset Table -->
                <div class="overflow-auto rounded-xl">
                  <table class="min-w-full text-left">
                    <thead class="p-6 text-black" style="background-color:rgb(167, 204, 240)">
                      <tr>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Tag</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Category</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Serial</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Description</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Assigned</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Location</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Sub-Location</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Status</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Acquired</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Value</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Fund Source</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">Attachments</th>
                        <th class="px-4 py-2 text-xs font-bold text-gray-600">History</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                      <tr v-for="asset in props.assets.data" :key="asset.id" class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap font-semibold">{{ asset.asset_tag }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ asset.category?.name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ asset.serial_number }}</td>
                        <td class="px-4 py-2 whitespace-nowrap max-w-xs truncate" :title="asset.item_description">{{ asset.item_description }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ asset.person_assigned }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ asset.location?.name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ asset.sub_location?.name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                          <span :class="{
                            'bg-green-100 text-green-700': asset.status === 'in_use',
                            'bg-orange-100 text-orange-700': asset.status === 'maintenance',
                            'bg-gray-100 text-gray-600': asset.status !== 'in_use' && asset.status !== 'maintenance'
                          }" class="px-2 py-1 rounded-full text-xs font-bold">
                            {{ asset.status.replace('_', ' ').toUpperCase() }}
                          </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-xs">{{ formatDate(asset.acquisition_date) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-right font-semibold text-indigo-600">${{ parseFloat(asset.original_value).toLocaleString() }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-right font-semibold text-indigo-600">{{ asset.fund_source?.name || 'N/A' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-center">
                          <button v-if="asset.attachments && asset.attachments.length" @click="openAttachmentsModal(asset.attachments)" class="text-green-600 hover:underline text-xs">
                            {{ asset.attachments.length }} file(s)
                          </button>
                          <span v-else class="text-gray-400 text-xs">None</span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-center">
                          <button v-if="asset.history && asset.history.length" @click="openHistoryModal(asset)" class="text-indigo-600 hover:underline text-xs">
                            {{ asset.history.length }} entr{{ asset.history.length === 1 ? 'y' : 'ies' }}
                          </button>
                          <span v-else class="text-gray-400 text-xs">None</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
            </div>
            
            <!-- Pagination footer -->
            <div class="flex justify-end mb-5 mt-3">
                <TailwindPagination
                    :data="props.assets"
                    @pagination-change-page="getResults"
                    :limit="2"
                />
            </div>
        </div>
        <!-- Attachments Modal -->
        <TransitionRoot as="template" :show="isAttachmentsModalOpen">
          <Dialog as="div" class="fixed z-[99] inset-0 overflow-y-auto" @close="closeAttachmentsModal">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
              <TransitionChild
                as="template"
                enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
              >
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" />
              </TransitionChild>
              <!-- Modal panel -->
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
              <TransitionChild
                as="template"
                enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100"
                leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              >
                <DialogPanel class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                  <div class="flex justify-between items-center mb-4">
                    <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900">Asset Attachments</DialogTitle>
                    <button @click="closeAttachmentsModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                  <div v-if="selectedAttachments.length">
                    <ul class="divide-y divide-gray-200">
                      <li v-for="file in selectedAttachments" :key="file.id" class="py-2 flex items-center justify-between">
                        <span class="truncate max-w-xs" :title="file.file">{{ file.type || 'Attachment' }}</span>
                        <a :href="'/' + file.file" target="_blank" class="ml-4 px-3 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold">View</a>
                      </li>
                    </ul>
                  </div>
                  <div v-else class="text-gray-400 text-sm">No attachments found.</div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </Dialog>
        </TransitionRoot>
        <!-- History Modal -->
        <TransitionRoot as="template" :show="isHistoryModalOpen">
          <Dialog as="div" class="fixed z-50 inset-0 overflow-y-auto" @close="closeHistoryModal">
            <div class="flex items-center justify-center min-h-screen px-0">
              <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
              >
                <DialogOverlay class="fixed inset-0 bg-black bg-opacity-40 transition-opacity" />
              </TransitionChild>

              <span class="inline-block align-middle h-screen" aria-hidden="true">&#8203;</span>
              <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0 scale-95"
                enter-to="opacity-100 scale-100"
                leave="ease-in duration-200"
                leave-from="opacity-100 scale-100"
                leave-to="opacity-0 scale-95"
              >
                <div
                  class="fixed inset-0 w-screen h-screen p-0 m-0 bg-white shadow-xl z-50 flex flex-col rounded-none"
                  style="max-width:100vw;max-height:100vh;"
                >
                  <div class="flex justify-between items-center mb-4">
                    <DialogTitle as="h3" class="text-lg leading-6 font-medium text-gray-900">Asset History</DialogTitle>
                    <button @click="closeHistoryModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                  <div v-if="selectedAssetHistory.length">
                    <div class="overflow-x-auto">
                      <table class="min-w-full text-left text-xs border border-black">
                        <thead>
                          <tr>
                            <th class="px-2 py-1 border border-black font-bold text-left">Date</th>
                            <th class="px-2 py-1 border border-black font-bold text-left">Custodian</th>
                            <th class="px-2 py-1 border border-black font-bold text-left">Status</th>
                            <th class="px-2 py-1 border border-black font-bold text-left">Assigned At</th>
                            <th class="px-2 py-1 border border-black font-bold text-left">Returned At</th>
                            <th class="px-2 py-1 border border-black font-bold text-left">Status Notes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="entry in selectedAssetHistory" :key="entry.id">
                            <td class="px-2 py-1 border border-black text-left">{{ formatDate(entry.created_at) }}</td>
                            <td class="px-2 py-1 border border-black text-left">{{ entry.custodian }}</td>
                            <td class="px-2 py-1 border border-black text-left">
                              <span :class="{
                                'bg-green-100 text-green-700': entry.status === 'in_use',
                                'bg-orange-100 text-orange-700': entry.status === 'maintenance',
                                'bg-gray-100 text-gray-600': entry.status !== 'in_use' && entry.status !== 'maintenance'
                              }" class="px-2 py-1 rounded-full text-xs font-bold">
                                {{ entry.status.replace('_', ' ').toUpperCase() }}
                              </span>
                            </td>
                            <td class="px-2 py-1 border border-black text-left">{{ entry.assigned_at ? moment(entry.assigned_at).format('DD/MM/YYYY HH:mm') : '-' }}</td>
                            <td class="px-2 py-1 border border-black text-left">{{ entry.returned_at || '-' }}</td>
                            <td class="px-2 py-1 border border-black text-left">{{ entry.status_notes }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div v-else class="text-gray-400 text-sm">No history found.</div>
                </div>
              </TransitionChild>
            </div>
          </Dialog>
        </TransitionRoot>
    </AuthenticatedLayout>
</template>

<script setup>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { debounce } from 'lodash';
import axios from 'axios';
import * as XLSX from 'xlsx';
import moment from 'moment';

const props = defineProps({
    locations: {
        type: Array,
        required: true
    },
    assets: {
        type: Object,
        required: true
    },
    filters: {
        type: Object
    },
    regions: {
        type: Array,
        required: true
    },
    fundSources: {
        type: Array,
        required: true
    }
});

function formatDate(date){
    return moment(date).format('DD/MM/YYYY');
}

const assets = ref([]);
const loading = ref(false);
const selectedLocations = ref([]);
const selectedSubLocations = ref([]);
const collapsedLocations = ref([]);
const isHistoryModalOpen = ref(false);
const selectedAssetHistory = ref([]);

function openHistoryModal(asset) {
    selectedAssetHistory.value = [...asset.history].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    isHistoryModalOpen.value = true;
}
function closeHistoryModal() {
    isHistoryModalOpen.value = false;
    selectedAssetHistory.value = [];
}
const isAttachmentsModalOpen = ref(false);
const selectedAttachments = ref([]);

function openAttachmentsModal(attachments) {
    selectedAttachments.value = attachments;
    isAttachmentsModalOpen.value = true;
}
function closeAttachmentsModal() {
    isAttachmentsModalOpen.value = false;
    selectedAttachments.value = [];
}

const search = ref(props.filters.search);
const per_page = ref(props.filters.per_page);

// Region, Location, SubLocation filter state
const regionFilter = ref(null);
const locationFilter = ref(null);
const subLocationFilter = ref(null);
const subLocationOptions = ref([]);
const fundSourceFilter = ref(null);

const regionOptions = computed(() => props.regions || []);
const locationOptions = computed(() => props.locations || []);
// subLocationOptions is now a ref, loaded dynamically

const filteredSubLocations = computed(() => subLocationOptions.value);

function onRegionChange(selected) {
    locationFilter.value = null;
    subLocationFilter.value = null;
    subLocationOptions.value = [];
}

async function onLocationChange(selected) {
    subLocationFilter.value = null;
    subLocationOptions.value = [];
    if (!selected) return;
    const locationId = selected.id || selected;
    try {
        const response = await axios.get(route('assets.locations.sub-locations', { location: locationId }));
        subLocationOptions.value = response.data;
    } catch (error) {
        subLocationOptions.value = [];
    }
}

// Computed property for filtered assets
const filteredAssets = computed(() => {
    return assets.value.filter(asset => {
        const regionMatch = !regionFilter.value || asset.region_id === regionFilter.value.id;
        const locationMatch = !locationFilter.value || asset.asset_location_id === locationFilter.value.id;
        const subLocationMatch = !subLocationFilter.value || asset.sub_location_id === subLocationFilter.value.id;
        const searchMatch = !search.value || [asset.asset_tag, asset.serial_number, asset.item_description].some(
            field => field && field.toLowerCase().includes(search.value.toLowerCase())
        );
        return regionMatch && locationMatch && subLocationMatch && searchMatch;
    });
});

function getResults(page = 1){
    props.filters.page = page;
}

// Watch for location change to load sub-locations
watch(
  () => locationFilter.value,
  async (newLocation) => {
    if (newLocation && newLocation.id) {
      // Fetch sub-locations for the selected location
      try {
        const response = await axios.get(route('assets.locations.sub-locations', newLocation.id));
        subLocationOptions.value = response.data;
      } catch (e) {
        subLocationOptions.value = [];
      }
    } else {
      subLocationOptions.value = [];
      subLocationFilter.value = null;
    }
  },
  { immediate: true }
);

watch([
    () => props.filters.page,
    () => search.value,
    () => per_page.value,
    () => regionFilter.value ? regionFilter.value.id : null,
    () => locationFilter.value ? locationFilter.value.id : null,
    () => fundSourceFilter.value ? fundSourceFilter.value.id : null
], () => {
    reloadAssets();
})

// Watch for changes in selectedSubLocations and reload assets
watch(
  selectedSubLocations,
  () => {
    reloadAssets();
  },
  { deep: true }
);

function reloadAssets(){
    const query = {};
    if(props.filters.page) query.page = props.filters.page;
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (regionFilter.value && regionFilter.value.id) query.region_id = regionFilter.value.id;
    if (locationFilter.value && locationFilter.value.id) query.location_id = locationFilter.value.id;
    if (selectedSubLocations.value && selectedSubLocations.value.length) {
        query['sub_location_ids'] = selectedSubLocations.value.map(x => x.id);
    }
    if (fundSourceFilter.value && fundSourceFilter.value.id) query.fund_source_id = fundSourceFilter.value.id;
    router.get(route('assets.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['assets', 'locations']
    });
}

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

// Excel export function
const exportToExcel = () => {
    // Prepare the data
    const exportData = props.assets.data.map(asset => ({
        'Asset Tag': asset.asset_tag,
        'Category': asset.category.name,
        'Serial Number': asset.serial_number,
        'Description': asset.item_description,
        'Assigned To': asset.person_assigned,
        'Region': asset.region?.name,
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

</script>