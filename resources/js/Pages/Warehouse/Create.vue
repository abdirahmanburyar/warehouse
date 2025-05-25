<template>
    <AuthenticatedLayout>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Warehouse</h2>

        <div class=" overflow-hidden sm:rounded-lg p-6 mb-[60px]">
            <!-- State Modal -->
            <Teleport to="body" v-if="showStateModal">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New State</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">State Name</label>
                            <input type="text" v-model="newState.name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">State Code</label>
                            <input type="text" v-model="newState.code" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button @click="showStateModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" :disabled="savingState">
                                Cancel
                            </button>
                            <button @click="saveNewState" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 flex items-center" :disabled="savingState">
                                <svg v-if="savingState" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ savingState ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- District Modal -->
            <Teleport to="body" v-if="showDistrictModal">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New District</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">District Name</label>
                            <input type="text" v-model="newDistrict.name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Region Name</label>
                            <input type="text" v-model="newDistrict.region_name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button @click="showDistrictModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" :disabled="savingDistrict">
                                Cancel
                            </button>
                            <button @click="saveNewDistrict" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 flex items-center" :disabled="savingDistrict">
                                <svg v-if="savingDistrict" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ savingDistrict ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- City Modal -->
            <Teleport to="body" v-if="showCityModal">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New City</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">City Name</label>
                            <input type="text" v-model="newCity.name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button @click="showCityModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" :disabled="savingCity">
                                Cancel
                            </button>
                            <button @click="saveNewCity" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 flex items-center" :disabled="savingCity">
                                <svg v-if="savingCity" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ savingCity ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" v-model="form.name" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Code (Auto-generated)</label>
                        <input type="text" id="code" v-model="form.code" disabled
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="streetname" class="block text-sm font-medium text-gray-700">Street Name</label>
                        <input type="text" id="streetname" v-model="form.address"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Location Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                        <div class="relative mt-1">
                            <Multiselect 
                                v-model="selectedState" 
                                :options="states" 
                                placeholder="Select State" 
                                :searchable="true" 
                                :close-on-select="true"
                                :show-labels="false" 
                                :allow-empty="true"
                                label="name" 
                                track-by="id"
                                @select="handleStateSelect"
                                class="mt-1"
                            >
                                <template v-slot:beforeList>
                                    <div @click="showStateModal = true" class="multiselect__add-new-button bg-indigo-600 text-white py-2 px-4 text-center cursor-pointer hover:bg-indigo-700 transition-colors">
                                        + Add New State
                                    </div>
                                </template>
                            </Multiselect>
                        </div>
                    </div>
                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700">District</label>
                        <div class="relative mt-1">
                            <Multiselect 
                                v-model="selectedDistrict" 
                                :options="filteredDistricts" 
                                placeholder="Select District" 
                                :searchable="true" 
                                :close-on-select="true"
                                :show-labels="false" 
                                :allow-empty="true"
                                label="name" 
                                track-by="id"
                                @select="handleDistrictSelect"
                                class="mt-1"
                                :disabled="!selectedState"
                            >
                                <template v-slot:beforeList>
                                    <div @click="showDistrictModal = true" class="multiselect__add-new-button bg-indigo-600 text-white py-2 px-4 text-center cursor-pointer hover:bg-indigo-700 transition-colors">
                                        + Add New District
                                    </div>
                                </template>
                            </Multiselect>
                        </div>
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <div class="relative mt-1">
                            <Multiselect 
                                v-model="selectedCity" 
                                :options="filteredCities" 
                                placeholder="Select City" 
                                :searchable="true" 
                                :close-on-select="true"
                                :show-labels="false" 
                                :allow-empty="true"
                                label="name" 
                                track-by="id"
                                @select="handleCitySelect"
                                class="mt-1"
                                :disabled="!selectedDistrict"
                            >
                                <template v-slot:beforeList>
                                    <div @click="showCityModal = true" class="multiselect__add-new-button bg-indigo-600 text-white py-2 px-4 text-center cursor-pointer hover:bg-indigo-700 transition-colors">
                                        + Add New City
                                    </div>
                                </template>
                            </Multiselect>
                        </div>
                    </div>
                </div>

                <!-- Manager Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="manager_name" class="block text-sm font-medium text-gray-700">Manager Name</label>
                        <input type="text" id="manager_name" v-model="form.manager_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="manager_email" class="block text-sm font-medium text-gray-700">Manager Email</label>
                        <input type="email" id="manager_email" v-model="form.manager_email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="manager_phone" class="block text-sm font-medium text-gray-700">Manager Phone</label>
                        <input type="tel" id="manager_phone" v-model="form.manager_phone"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Warehouse Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" v-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <Link :href="route('inventories.warehouses.index')" 
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Exit
                    </Link>
                    <button type="submit"
                        :disabled="loading"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ loading ? 'Creating...' : 'Create Warehouse' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

const props = defineProps({
    states: {
        type: Array,
        default: () => []
    },
    districts: {
        type: Array,
        default: () => []
    },
    cities: {
        type: Array,
        default: () => []
    }
});

// Use the data passed from the controller
const states = ref(props.states || []);
const districts = ref(props.districts || []);
const cities = ref(props.cities || []);

// Generate a unique warehouse code
function generateWarehouseCode() {
    const prefix = 'WH';
    const timestamp = new Date().getTime().toString().slice(-6);
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    return `${prefix}-${timestamp}-${random}`;
}

// Form data
const form = ref({
    name: '',
    code: generateWarehouseCode(),
    address: '',
    manager_name: '',
    manager_phone: '',
    manager_email: '',
    status: 'active',
    state: '',
    state_id: null,
    district: '',
    district_id: null,
    city: '',
    city_id: null,
    postal_code: '',
    capacity: '',
    special_handling_capabilities: '',
    notes: ''
});

// Selected location items
const selectedState = ref(null);
const selectedDistrict = ref(null);
const selectedCity = ref(null);
const loading = ref(false);

// Modal visibility states
const showStateModal = ref(false);
const showDistrictModal = ref(false);
const showCityModal = ref(false);

// New item form data
const newState = ref({ name: '', code: '' });
const newDistrict = ref({ name: '', region_name: '', state_id: null });
const newCity = ref({ name: '', district_id: null });

// Filtered options based on selection
const filteredDistricts = computed(() => {
    if (!selectedState.value) return [];
    return districts.value.filter(district => 
        district.state_id === selectedState.value.id
    );
});

const filteredCities = computed(() => {
    if (!selectedDistrict.value) return [];
    return cities.value.filter(city => 
        city.district_id === selectedDistrict.value.id
    );
});

// Loading states for save operations
const savingState = ref(false);
const savingDistrict = ref(false);
const savingCity = ref(false);

// Methods for saving new locations
const saveNewState = async () => {
    if (!newState.value.name) {
        Swal.fire({
            title: 'Error!',
            text: 'State name is required',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }

    savingState.value = true;
    try {
        const response = await axios.post(route('locations.states.store'), newState.value);
        const savedState = response.data;
        
        // Add to states array
        states.value.push(savedState);
        
        // Select the new state
        selectedState.value = savedState;
        form.value.state = savedState.name;
        form.value.state_id = savedState.id;
        
        // Reset form and close modal
        newState.value = { name: '', code: '' };
        showStateModal.value = false;
        
        Swal.fire({
            title: 'Success!',
            text: 'State added successfully',
            icon: 'success',
            confirmButtonColor: '#4F46E5',
        });
    } catch (error) {
        console.error('Error saving state:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'An error occurred',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
    } finally {
        savingState.value = false;
    }
};

const saveNewDistrict = async () => {
    if (!newDistrict.value.name) {
        Swal.fire({
            title: 'Error!',
            text: 'District name is required',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }

    if (!selectedState.value) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a state first',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }

    // Set the state_id
    newDistrict.value.state_id = selectedState.value.id;

    savingDistrict.value = true;
    try {
        const response = await axios.post(route('locations.districts.store'), newDistrict.value);
        const savedDistrict = response.data;
        
        // Add to districts array
        districts.value.push(savedDistrict);
        
        // Select the new district
        selectedDistrict.value = savedDistrict;
        form.value.district = savedDistrict.name;
        form.value.district_id = savedDistrict.id;
        
        // Reset form and close modal
        newDistrict.value = { name: '', region_name: '', state_id: null };
        showDistrictModal.value = false;
        
        Swal.fire({
            title: 'Success!',
            text: 'District added successfully',
            icon: 'success',
            confirmButtonColor: '#4F46E5',
        });
    } catch (error) {
        console.error('Error saving district:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'An error occurred',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
    } finally {
        savingDistrict.value = false;
    }
};

const saveNewCity = async () => {
    if (!newCity.value.name) {
        Swal.fire({
            title: 'Error!',
            text: 'City name is required',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }

    if (!selectedDistrict.value) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a district first',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }

    // Set the district_id
    newCity.value.district_id = selectedDistrict.value.id;

    savingCity.value = true;
    try {
        const response = await axios.post(route('locations.cities.store'), newCity.value);
        const savedCity = response.data;
        
        // Add to cities array
        cities.value.push(savedCity);
        
        // Select the new city
        selectedCity.value = savedCity;
        form.value.city = savedCity.name;
        form.value.city_id = savedCity.id;
        
        // Reset form and close modal
        newCity.value = { name: '', district_id: null };
        showCityModal.value = false;
        
        Swal.fire({
            title: 'Success!',
            text: 'City added successfully',
            icon: 'success',
            confirmButtonColor: '#4F46E5',
        });
    } catch (error) {
        console.error('Error saving city:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'An error occurred',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
    } finally {
        savingCity.value = false;
    }
};

// Handler functions for select events
const handleStateSelect = (selected) => {
    if (!selected) {
        selectedState.value = null;
        form.value.state = '';
        form.value.state_id = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = selectedState.value;
        showStateModal.value = true;
        selectedState.value = currentSelection;
        return;
    }

    selectedState.value = selected;
    form.value.state = selected.name;
    form.value.state_id = selected.id;
    
    // Reset dependent fields
    selectedDistrict.value = null;
    selectedCity.value = null;
    form.value.district = '';
    form.value.district_id = null;
    form.value.city = '';
    form.value.city_id = null;
};

const handleDistrictSelect = (selected) => {
    if (!selected) {
        selectedDistrict.value = null;
        form.value.district = '';
        form.value.district_id = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = selectedDistrict.value;
        newDistrict.value.state_id = selectedState.value.id;
        showDistrictModal.value = true;
        selectedDistrict.value = currentSelection;
        return;
    }

    selectedDistrict.value = selected;
    form.value.district = selected.name;
    form.value.district_id = selected.id;
    
    // Reset dependent fields
    selectedCity.value = null;
    form.value.city = '';
    form.value.city_id = null;
};

const handleCitySelect = (selected) => {
    if (!selected) {
        selectedCity.value = null;
        form.value.city = '';
        form.value.city_id = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = selectedCity.value;
        newCity.value.district_id = selectedDistrict.value.id;
        showCityModal.value = true;
        selectedCity.value = currentSelection;
        return;
    }

    selectedCity.value = selected;
    form.value.city = selected.name;
    form.value.city_id = selected.id;
};

// Watch for city selection changes
watch(selectedCity, (newCity) => {
    if (newCity) {
        form.value.city = newCity.name;
    } else {
        form.value.city = '';
    }
});

// Watch for name changes to update code if name is changed and code is empty
watch(() => form.value.name, (newName) => {
    if (!form.value.code && newName) {
        form.value.code = generateWarehouseCode();
    }
});

const submit = async () => {
    if (!form.value.name) {
        Swal.fire({
            title: 'Error!',
            text: 'Warehouse name is required',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }
    
    loading.value = true;
    try {
        // Prepare form data with location information
        const formData = {
            ...form.value,
            state_id: selectedState.value?.id || null,
            district_id: selectedDistrict.value?.id || null,
            city_id: selectedCity.value?.id || null
        };
        
        const response = await axios.post(route('inventories.warehouses.store'), formData);
        Swal.fire({
            title: 'Success!',
            text: 'Warehouse created successfully',
            icon: 'success',
            confirmButtonColor: '#4F46E5',
        }).then(() => {
            router.visit(route('inventories.warehouses.index'));
        });
    } catch (error) {
        loading.value = false;
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'An error occurred',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
    }
};

// Generate code on component mount
onMounted(() => {
    form.value.code = generateWarehouseCode();
});
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
