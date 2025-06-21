<template>
    <AuthenticatedLayout title="Warehouse Management" description="Edit warehouse" img="/assets/images/facility.png">
        <Head title="Edit Warehouse" />        <div class="p-6 bg-white border-b flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Edit Warehouse</h1>
        </div>

        <div class=" overflow-hidden sm:rounded-lg p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" id="name" v-model="form.name" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
                        <input type="text" id="code" v-model="form.code"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <input type="text" id="address" v-model="form.address"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
                
                <!-- State, District, City Selection -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                        <Multiselect
                            v-model="selectedState"
                            :options="states"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select state"
                            label="name"
                            track-by="id"
                            class="multiselect-blue"
                        >
                            <template #noResult>No states found</template>
                        </Multiselect>
                    </div>
                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700">District</label>
                        <Multiselect
                            v-model="selectedDistrict"
                            :options="filteredDistricts"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select district"
                            label="name"
                            track-by="id"
                            :disabled="!selectedState"
                            class="multiselect-blue"
                        >
                            <template #noResult>No districts found</template>
                        </Multiselect>
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <Multiselect
                            v-model="selectedCity"
                            :options="filteredCities"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select city"
                            label="name"
                            track-by="id"
                            :disabled="!selectedDistrict"
                            class="multiselect-blue"
                        >
                            <template #noResult>No cities found</template>
                        </Multiselect>
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

                <!-- Warehouse Status -->
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
                        {{ loading ? 'Saving...' : 'Save Changes' }}
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
    warehouse: {
        required: true,
        type: Object
    },
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

// Selected values
const selectedState = ref(null);
const selectedDistrict = ref(null);
const selectedCity = ref(null);

// Filter districts based on selected state
const filteredDistricts = computed(() => {
    if (!selectedState.value) return [];
    return districts.value.filter(d => d.state_id === selectedState.value.id);
});

// Filter cities based on selected district
const filteredCities = computed(() => {
    if (!selectedDistrict.value) return [];
    return cities.value.filter(c => c.district_id === selectedDistrict.value.id);
});

// Initialize selected values if they exist
onMounted(() => {
    console.log('Warehouse data:', props.warehouse);
    console.log('States data:', states.value);
    
    // Set state
    if (props.warehouse.state_id && states.value.length > 0) {
        selectedState.value = states.value.find(s => s.id === props.warehouse.state_id);
    }
    
    // Set district
    if (props.warehouse.district_id && districts.value.length > 0) {
        selectedDistrict.value = districts.value.find(d => d.id === props.warehouse.district_id);
    }
    
    // Set city
    if (props.warehouse.city_id && cities.value.length > 0) {
        selectedCity.value = cities.value.find(c => c.id === props.warehouse.city_id);
    }
});

// Form data
const loading = ref(false);
const form = ref({
    id: props.warehouse.id,
    name: props.warehouse.name,
    code: props.warehouse.code,
    address: props.warehouse.address,
    state_id: props.warehouse.state_id,
    district_id: props.warehouse.district_id,
    city_id: props.warehouse.city_id,
    manager_name: props.warehouse.manager_name,
    manager_email: props.warehouse.manager_email,
    manager_phone: props.warehouse.manager_phone,
    status: props.warehouse.status || 'active',
});

// Watch for state changes
watch(selectedState, (newState) => {
    form.value.state_id = newState ? newState.id : null;
    // Reset district and city when state changes
    if (selectedDistrict.value && selectedDistrict.value.state_id !== form.value.state_id) {
        selectedDistrict.value = null;
        form.value.district_id = null;
    }
    // Also reset city
    if (selectedCity.value) {
        selectedCity.value = null;
        form.value.city_id = null;
    }
});

// Watch for district changes
watch(selectedDistrict, (newDistrict) => {
    form.value.district_id = newDistrict ? newDistrict.id : null;
    // Reset city when district changes
    if (selectedCity.value && selectedCity.value.district_id !== form.value.district_id) {
        selectedCity.value = null;
        form.value.city_id = null;
    }
});

// Watch for city changes
watch(selectedCity, (newCity) => {
    form.value.city_id = newCity ? newCity.id : null;
});

// Submit form
const submit = async () => {
    loading.value = true;
    try {
        const response = await axios.post(route('inventories.warehouses.store'), form.value);
        Swal.fire({
            title: 'Success!',
            text: response.data,
            icon: 'success',
            confirmButtonColor: '#4F46E5',
        }).then(() => {
            router.get(route('inventories.warehouses.index'));
        });
    } catch (error) {
        console.log(error);
        loading.value = false;
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'An error occurred',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
    }
};
</script>