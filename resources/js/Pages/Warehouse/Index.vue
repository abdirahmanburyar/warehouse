<template>

    <Head title="Warehouses" />

    <AuthenticatedTabs>
        <div class="flex justify-between items-center p-4 sticky top-0 bg-white z-20 border-b">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Warehouses</h2>
            <div class="flex items-center gap-2">
                <div class="relative mr-4">
                    <input type="text" v-model="search" placeholder="Search warehouses..."
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 px-4 pl-10 w-64" />
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <select v-model="perPage"
                    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-sm">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
                <Link :href="route('inventories.warehouses.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-plus mr-2"></i> Add Warehouse
                </Link>
            </div>
        </div>

        <div class="mt-4">
            <div class="bg-white mx-auto overflow-hidden">
                <div class="text-gray-900 overflow-hidden">
                    <div
                        class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-180px)] scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 rounded-lg">
                        <table class="w-full divide-y divide-black border-collapse table-auto border border-black">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer sticky left-0 bg-gray-50 z-20 border border-black"
                                        @click="updateSort('name')">
                                        Name
                                        <span v-if="sort === 'name' && direction === 'asc'" class="ml-1">↑</span>
                                        <span v-else-if="sort === 'name' && direction === 'desc'" class="ml-1">↓</span>
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                        @click="updateSort('code')">
                                        Code
                                        <span v-if="sort === 'code' && direction === 'asc'" class="ml-1">↑</span>
                                        <span v-else-if="sort === 'code' && direction === 'desc'" class="ml-1">↓</span>
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Address
                                    </th>

                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Manager
                                    </th>

                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Special Handling
                                    </th>

                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                        @click="updateSort('capacity')">
                                        Capacity
                                        <span v-if="sort === 'capacity' && direction === 'asc'" class="ml-1">↑</span>
                                        <span v-else-if="sort === 'capacity' && direction === 'desc'"
                                            class="ml-1">↓</span>
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="warehouse in props.warehouses.data" :key="warehouse.id"
                                    class="hover:bg-gray-50">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white z-10 border border-black">
                                        <div class="font-medium text-gray-900">{{ warehouse.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border border-black">
                                        <div class="text-sm text-gray-500">{{ warehouse.code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border border-black">
                                        <div class="text-sm text-gray-900">
                                            {{ warehouse.city }}, {{ warehouse.country || 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ warehouse.address || 'No address' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border border-black">
                                        <div class="text-sm text-gray-900">
                                            {{ warehouse.manager_name || 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ warehouse.manager_email || 'No email' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ warehouse.manager_phone || 'No phone' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap border border-black">
                                        <div class="text-sm text-gray-900">
                                            {{ warehouse.special_handling_capabilities || 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border border-black">
                                        <div class="text-sm text-gray-900">{{ warehouse.capacity ?
                                            `${warehouse.capacity} m³` : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border border-black">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="warehouse.status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                            {{ warehouse.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium border border-black">
                                        <Link :href="route('inventories.warehouses.edit', warehouse.id)" class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </Link>
                                        <button @click="confirmDelete(warehouse)"
                                            class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="props.warehouses.data.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No warehouses found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedTabs>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedTabs from '@/Layouts/AuthenticatedTabs.vue';
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
import axios from 'axios';
import Swal from 'sweetalert2';

const toast = useToast();

// Props
const props = defineProps({
    warehouses: Object,
    filters: Object,
    errors: Object
});

// Reactive state
const search = ref(props.filters?.search || '');
const sort = ref(props.filters?.sort || 'name');
const direction = ref(props.filters?.direction || 'asc');
const perPage = ref(props.filters?.perPage || 10);
const loading = ref(false);

// Debounced search function


// Watch for search and filter changes
watch([
    () => search.value,
    () => sort.value,
    () => direction.value,
    () => perPage.value
], () => {
    reloadWarehouse();
});

// Update sorting
const updateSort = (column) => {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = column;
        direction.value = 'asc';
    }
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
    const query = {}
    if (search.value) query.search = search.value
    if (perPage.value) query.perPage = perPage.value
    router.get(
        route('inventories.warehouses.index'),
        query,
        {
            preserveState: true,
            replace: true,
            only: ['warehouses']
        }
    );
}

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

<style scoped>
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
</style>