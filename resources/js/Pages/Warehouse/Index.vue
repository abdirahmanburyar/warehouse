<template>

    <Head title="Warehouses" />

    <AuthenticatedLayout title="Warehouse Management">
        <!-- Page Header -->
        <div class="p-6 bg-white border-b flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Warehouse</h1>
            <Link :href="route('inventories.warehouses.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Add Warehouse
            </Link>
        </div>
        
        <!-- Filters Section -->
        <div class="p-6 bg-white border-b">
            <!-- Filter Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Search Bar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" v-model="search" placeholder="Name, code, manager..."
                            class="w-full border-black rounded-full focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2 px-4 pl-10" />
                    </div>
                </div>
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <select v-model="status"
                            class="w-full appearance-none border-black rounded-full focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2 px-4 pl-10 pr-8">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- State Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                        <Multiselect
                            v-model="selectedState"
                            :options="props.states || []"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select state"
                            label="name"
                            track-by="id"
                            @update:modelValue="handleStateChange"
                            class="multiselect--with-icon multiselect--rounded"
                        >
                            <template #noResult>No states found</template>
                        </Multiselect>
                    </div>
                </div>
                
                <!-- District Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <Multiselect
                            v-model="selectedDistrict"
                            :options="props.districts || []"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select district"
                            label="name"
                            track-by="id"
                            :allow-empty="true"
                            @update:modelValue="handleDistrictChange"
                            :disabled="!selectedState"
                            class="multiselect--with-icon multiselect--rounded"
                        >
                            <template #noResult>No districts found</template>
                        </Multiselect>
                    </div>
                </div>
                
                <!-- City Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <Multiselect
                            v-model="selectedCity"
                            :options="props.cities || []"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select city"
                            label="name"
                            track-by="id"
                            :allow-empty="true"
                            :disabled="!selectedDistrict"
                            class="multiselect--with-icon multiselect--rounded"
                        >
                            <template #noResult>No cities found</template>
                        </Multiselect>
                    </div>
                </div>
                
                <!-- Per Page Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <select v-model="perPage"
                            class="w-full appearance-none border-black rounded-full focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2 px-4 pl-10 pr-8">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                            <option value="500">500 per page</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                            </svg>
                        </div>
                    </div>
                </div>
                

            </div>
        </div>

        <div class="mt-4">
            <div class="bg-white mx-auto overflow-hidden">
                <table class="min-w-full border border-gray-200 divide-y divide-gray-200 rounded-xl overflow-hidden">
                    <thead style="background-color: #eef1f8" class="rounded-t-xl overflow-hidden sticky top-0 z-10">
                        <tr>
                            <th scope="col"
                                class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4 sticky left-0 bg-gray-50 z-20 border-2 border-black">
                                Name
                            </th>
                            <th scope="col"
                                class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4 border-2 border-black">
                                Location
                            </th>

                            <th scope="col"
                                class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4 border-2 border-black">
                                Manager
                            </th>

                            <th scope="col"
                                class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4 border-2 border-black">
                                Status
                            </th>

                            <th scope="col"
                                class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #636db4 border-2 border-black">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="warehouse in props.warehouses.data" :key="warehouse.id" class="hover:bg-gray-50 border-b">
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white z-10 border-2 border-black">
                                {{ warehouse.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-2 border-black">
                                {{ warehouse.code }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 border-2 border-black">
                                <div class="flex flex-col">
                                    <span class="font-semibold">State:</span> {{ warehouse.state ? warehouse.state.name : 'N/A' }}
                                    <span class="font-semibold">District:</span> {{ warehouse.district ? warehouse.district.name : 'N/A' }}
                                    <span class="font-semibold">City:</span> {{ warehouse.city ? warehouse.city.name : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-2 border-black">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ warehouse.manager_name || 'N/A' }}
                                </div>
                                <div v-if="warehouse.manager_email" class="text-xs text-gray-500">
                                    <span class="inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        {{ warehouse.manager_email }}
                                    </span>
                                </div>
                                <div v-if="warehouse.manager_phone" class="text-xs text-gray-500">
                                    <span class="inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ warehouse.manager_phone }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap border-2 border-black">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                    :class="warehouse.status_badge">
                                    {{ warehouse.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium border-2 border-black">
                                <div class="flex space-x-3 justify-center">
                                    <Link :href="route('inventories.warehouses.edit', warehouse.id)"
                                        class="text-indigo-600 hover:text-indigo-900 p-1 rounded-full hover:bg-indigo-100">
                                        <i class="fas fa-edit"></i>
                                    </Link>
                                    <button @click="confirmDelete(warehouse)"
                                        class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-100">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.warehouses.data.length === 0" class="hover:bg-gray-50 border-b">
                            <td colspan="8" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="text-lg font-medium text-gray-700">No warehouses found</p>
                                    <p class="text-sm text-gray-500">No data matches your current filter criteria. Try adjusting your filters or create a new warehouse.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch, onMounted } from 'vue';
import { debounce } from 'lodash';
import { useToast } from 'vue-toastification';

const toast = useToast();

// Props
const props = defineProps({
    warehouses: Object,
    filters: Object,
    errors: Object
});

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el._clickOutside = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener('click', el._clickOutside);
    },
    unmounted(el) {
        document.removeEventListener('click', el._clickOutside);
    }
};

// Register directives
const directives = {
    'click-outside': vClickOutside
};

// Reactive state
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const perPage = ref(props.filters?.perPage || 10);
const loading = ref(false);

// Multiselect variables
const selectedState = ref(null);
const selectedDistrict = ref(null);
const selectedCity = ref(null);

// Action menu state
const openActionMenuId = ref(null);

// Toggle action menu
const toggleActionMenu = (warehouseId) => {
    openActionMenuId.value = openActionMenuId.value === warehouseId ? null : warehouseId;
};

// Initialize selected values if filters exist
if (props.filters?.state_id) {
    const state = props.states.find(s => s.id == props.filters.state_id);
    if (state) selectedState.value = state;
}

if (props.filters?.district_id) {
    const district = props.districts.find(d => d.id == props.filters.district_id);
    if (district) selectedDistrict.value = district;
}

if (props.filters?.city_id) {
    const city = props.cities.find(c => c.id == props.filters.city_id);
    if (city) selectedCity.value = city;
}

// Debounced search function


// Watch for search and filter changes
watch([
    () => search.value,
    () => perPage.value,
    () => status.value,
    () => selectedState.value,
    () => selectedDistrict.value,
    () => selectedCity.value
], () => {
    reloadWarehouse();
});

// Handle state change - reset district and city
const handleStateChange = () => {
    selectedDistrict.value = null;
    selectedCity.value = null;
    reloadWarehouse();
};

// Handle district change - reset city
const handleDistrictChange = () => {
    selectedCity.value = null;
    reloadWarehouse();
};

// Clear all filters
const clearFilters = () => {
    search.value = '';
    status.value = '';
    selectedState.value = null;
    selectedDistrict.value = null;
    selectedCity.value = null;
    perPage.value = 10;
    reloadWarehouse();
};

// Format coordinates for display
const formatCoordinates = (lat, lng) => {
    if (!lat || !lng) return 'Not available';
    return `${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}`;
};

// Form for creating/editing warehouses
const form = ref({
    id: null,
    name: '',
    code: '',
    address: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    latitude: '',
    longitude: '',
    capacity: '',
    temperature_min: '',
    temperature_max: '',
    humidity_min: '',
    humidity_max: '',
    status: 'active',
    has_cold_storage: false,
    has_hazardous_storage: false,
    is_active: true,
    notes: '',
    manager_name: '',
    manager_email: '',
    manager_phone: ''
});

// UI state
const showModal = ref(false);
const editMode = ref(false);
const formSubmitting = ref(false);
const errors = ref({});
const showMapModal = ref(false);
const selectedWarehouse = ref(null);

// Google Maps
const mapLoaded = ref(false);
const mapError = ref(false);
const googleMapsCallback = 'initGoogleMaps_' + Math.random().toString(36).substring(2, 15);

// Load Google Maps API with recommended async pattern
const loadGoogleMapsAPI = () => {
    if (window.google && window.google.maps) {
        mapLoaded.value = true;
        return;
    }

    // Define the callback function in the global scope
    window[googleMapsCallback] = () => {
        mapLoaded.value = true;
        // Clean up the global callback
        setTimeout(() => {
            delete window[googleMapsCallback];
        }, 1000);
    };

    const script = document.createElement('script');
    // Use the provided Google Maps API key
    script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyCzF5z4VcAwypaYDE1k9Rqc4nQpbtVJRSY&callback=${googleMapsCallback}&loading=async`;
    script.async = true;
    script.defer = true;

    script.onerror = () => {
        mapError.value = true;
        // Clean up the global callback
        delete window[googleMapsCallback];
    };

    document.head.appendChild(script);
};

// Initialize map when modal is opened
const initMap = (warehouse) => {
    if (!mapLoaded.value || !window.google || !window.google.maps) {
        // If maps not loaded yet, try again after a delay
        setTimeout(() => {
            if (showMapModal.value) {
                initMap(warehouse);
            }
        }, 500);
        return;
    }

    try {
        const mapOptions = {
            center: {
                lat: parseFloat(warehouse.latitude) || 0,
                lng: parseFloat(warehouse.longitude) || 0
            },
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.SATELLITE
        };

        const map = new google.maps.Map(document.getElementById("map"), mapOptions);

        new google.maps.Marker({
            position: {
                lat: parseFloat(warehouse.latitude) || 0,
                lng: parseFloat(warehouse.longitude) || 0
            },
            map: map,
            title: warehouse.name
        });
    } catch (error) {
        console.error("Error initializing map:", error);
        mapError.value = true;
    }
};

// Open map modal
const openMapModal = (warehouse) => {
    selectedWarehouse.value = warehouse;
    showMapModal.value = true;

    // Initialize map after modal is shown
    setTimeout(() => {
        initMap(warehouse);
    }, 100);
};

// Close map modal
const closeMapModal = () => {
    showMapModal.value = false;
    selectedWarehouse.value = null;
};

// Open create/edit modal
const openModal = (warehouse = null) => {
    if (warehouse) {
        form.value.id = warehouse.id;
        form.value.name = warehouse.name || '';
        form.value.code = warehouse.code || '';
        form.value.address = warehouse.address || '';
        form.value.city = warehouse.city || '';
        form.value.state = warehouse.state || '';
        form.value.country = warehouse.country || '';
        form.value.postal_code = warehouse.postal_code || '';
        form.value.latitude = warehouse.latitude || '';
        form.value.longitude = warehouse.longitude || '';
        form.value.capacity = warehouse.capacity || '';
        form.value.temperature_min = warehouse.temperature_min || '';
        form.value.temperature_max = warehouse.temperature_max || '';
        form.value.humidity_min = warehouse.humidity_min || '';
        form.value.humidity_max = warehouse.humidity_max || '';
        form.value.status = warehouse.status || 'active';
        form.value.has_cold_storage = !!warehouse.has_cold_storage;
        form.value.has_hazardous_storage = !!warehouse.has_hazardous_storage;
        form.value.is_active = warehouse.is_active !== false;
        form.value.notes = warehouse.notes || '';
        form.value.manager_name = warehouse.manager_name || '';
        form.value.manager_email = warehouse.manager_email || '';
        form.value.manager_phone = warehouse.manager_phone || '';
        editMode.value = true;
    } else {
        form.value = {
            id: null,
            name: '',
            code: '',
            address: '',
            city: '',
            state: '',
            country: '',
            postal_code: '',
            latitude: '',
            longitude: '',
            capacity: '',
            temperature_min: '',
            temperature_max: '',
            humidity_min: '',
            humidity_max: '',
            status: 'active',
            has_cold_storage: false,
            has_hazardous_storage: false,
            is_active: true,
            notes: '',
            manager_name: '',
            manager_email: '',
            manager_phone: ''
        };
        editMode.value = false;
    }

    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
};

// Submit form for creating/editing warehouse
const submitForm = async () => {
    formSubmitting.value = true;

    await axios.post(route('warehouses.store'), form.value)
        .then(response => {
            showModal.value = false;
            formSubmitting.value = false;

            // Show success message
            toast.success(response.data);
            // Reset form
            reloadWarehouse();
        })
        .catch(error => {
            toast.error(error.response.data);
            formSubmitting.value = false;
        });
}

// Confirm delete warehouse
const confirmDelete = (warehouse) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you really want to delete the warehouse "${warehouse.name}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            deleteWarehouse(warehouse);
        }
    });
};

function reloadWarehouse() {
    loading.value = true;
    const query = {
        search: search.value,
        perPage: perPage.value,
        status: status.value,
        state_id: selectedState.value ? selectedState.value.id : '',
        district_id: selectedDistrict.value ? selectedDistrict.value.id : '',
        city_id: selectedCity.value ? selectedCity.value.id : ''
    };
    
    router.get(
        route('inventories.warehouses.index'),
        query,
        {
            preserveState: true,
            only: ['warehouses', 'districts', 'cities']
        }
    );
};

// Delete warehouse
const deleteWarehouse = (warehouse) => {
    axios.delete(route('warehouses.destroy', warehouse.id))
        .then(response => {
            Swal.fire({
                title: 'Deleted!',
                text: response.data,
                icon: 'success',
                confirmButtonColor: '#4F46E5'
            });

            // Refresh the page to update the warehouse list
            reloadWarehouse();
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: error.response?.data || 'Failed to delete the warehouse.',
                icon: 'error',
                confirmButtonColor: '#4F46E5'
            });
        });
};

// Retry loading the map
const retryLoadMap = () => {
    mapError.value = false;
    loadGoogleMapsAPI();

    if (selectedWarehouse.value) {
        setTimeout(() => {
            initMap(selectedWarehouse.value);
        }, 1000);
    }
};

// Load Google Maps API on component mount
onMounted(() => {
    loadGoogleMapsAPI();
});
</script>

<style>
.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%;
    /* 16:9 Aspect Ratio */
}

.aspect-w-16 iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Custom styles */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Make all filter inputs have border radius of 50% with black borders */
.multiselect__tags {
    border-radius: 9999px !important;
    border: 1px solid black !important;
}

/* Style the text input to match the rounded filter style */
input[type="text"] {
    border-radius: 9999px !important;
    border: 1px solid black !important;
    padding-left: 40px !important;
}

/* Style the dropdown menu to match */
.multiselect__content-wrapper {
    border-radius: 1rem !important;
    border: 1px solid black !important;
    margin-top: 5px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Add padding for icons in search input */
.search-with-icon {
    position: relative;
}

.search-with-icon input {
    padding-left: 40px !important;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: #6b7280;
}

/* Style for filter icons */
.filter-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: #6b7280;
}

.multiselect--with-icon .multiselect__tags {
    padding-left: 40px !important;
}

.multiselect--rounded .multiselect__tags {
    border-radius: 9999px !important;
    border: 1px solid black !important;
}

.multiselect--rounded .multiselect__content-wrapper {
    border-radius: 1rem !important;
    border: 1px solid black !important;
    margin-top: 5px;
    overflow: hidden;
}

/* Improve multiselect styling */
.multiselect {
    min-height: 40px;
}

.multiselect__tags {
    min-height: 40px;
    display: flex;
    align-items: center;
    padding: 8px 40px 8px 8px;
}

.multiselect__placeholder {
    padding-top: 0;
    margin-bottom: 0;
    color: #6b7280;
}

.multiselect__single {
    padding-top: 0;
    margin-bottom: 0;
}

.multiselect__input {
    margin-bottom: 0;
    padding: 0;
}

.multiselect__select {
    height: 38px;
}

.multiselect__option--highlight {
    background: #4f46e5;
}

.multiselect__option--selected.multiselect__option--highlight {
    background: #dc2626;
}
</style>