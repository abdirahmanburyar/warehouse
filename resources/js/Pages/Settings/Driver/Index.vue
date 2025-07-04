<template>
    <AuthenticatedLayout :title="'Drivers'" description="Manage your drivers" img="/assets/images/settings.png">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <Link 
                    :href="route('settings.index')" 
                    class="text-gray-500 hover:text-gray-700 transition-colors duration-150"
                >
                    <i class="fas fa-arrow-left text-xl"></i>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Drivers</h2>
            </div>
            <div class="flex space-x-4">
                <Link 
                    :href="route('settings.logistics.companies.index')" 
                    class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    <i class="fas fa-building mr-2"></i> Companies
                </Link>
                <button 
                    @click="openModal()" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    <i class="fas fa-plus mr-2"></i> Add Driver
                </button>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Search Bar -->
                <div class="mb-4 flex justify-between items-center">
                    <input 
                        type="text" 
                        v-model="search" 
                        placeholder="Search drivers..." 
                        class="w-full sm:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <select v-model="per_page" @change="props.filters.page = 1" class="w-[200px] rounded-3xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(driver, index) in props.drivers.data" :key="driver.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ driver.driver_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ driver.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ driver.phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ driver.company?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="[
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            driver.is_active 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ driver.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-3">
                                        <button 
                                            @click="confirmToggleStatus(driver, index)" 
                                            class="transition-colors duration-150"
                                            title="Toggle Status"
                                            :disabled="isTogglingStatus[index]"
                                        >
                                            <i class="fas fa-toggle-on text-lg" 
                                               :class="[
                                                   driver.is_active ? 'text-green-500' : 'text-gray-400',
                                                   { 'animate-pulse': isTogglingStatus[index] }
                                               ]"
                                            ></i>
                                        </button>
                                        <button 
                                            @click="openModal(driver)" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                                            title="Edit Driver"
                                        >
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                        <button 
                                            @click="confirmDelete(driver, index)" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150"
                                            title="Delete Driver"
                                        >
                                            <i class="fas fa-trash-alt text-lg" :class="{ 'animate-pulse': isDeleting[index] }"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <TailwindPagination 
                        :data="props.drivers" 
                        @pagination-change-page="handlePageChange" 
                    />
                </div>
            </div>
        </div>

        <!-- Driver Modal -->
        <Modal :show="showDriverModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ form.id ? 'Edit Driver' : 'Add New Driver' }}
                </h2>

                <div class="mt-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Driver ID</label>
                            <input 
                                type="text" 
                                v-model="form.driver_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input 
                                type="text" 
                                v-model="form.name" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input 
                                type="text" 
                                v-model="form.phone" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company</label>
                            <Multiselect
                                v-model="form.company"
                                :options="companyOptions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                placeholder="Select Company"
                                track-by="id"
                                label="name"
                                @select="handleCompanySelect"
                            >
                                <template v-slot:option="{ option }">
                                    <div
                                        :class="{
                                            'add-new-option': option.isAddNew,
                                        }"
                                    >
                                        <span
                                            v-if="option.isAddNew"
                                            class="text-indigo-600 font-medium"
                                        >+ Add New Company</span>
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeModal" 
                                :disabled="idDriverSubmiting"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="idDriverSubmiting"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-150"
                            >
                                {{ form.id ? idDriverSubmiting ? 'Updating...' : 'Update' : idDriverSubmiting ? 'Creating...' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Company Modal -->
        <Modal :show="showCompanyModal" @close="closeCompanyModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Company</h2>

                <div class="mt-6">
                    <form @submit.prevent="submitCompany" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input 
                                type="text" 
                                v-model="companyForm.name" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': errors.name }"
                            >
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input 
                                type="email" 
                                v-model="companyForm.email" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': errors.email }"
                            >
                            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea 
                                v-model="companyForm.address" 
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': errors.address }"
                            ></textarea>
                            <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <input 
                                type="text" 
                                v-model="companyForm.incharge_person" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': errors.incharge_person }"
                            >
                            <p v-if="errors.incharge_person" class="mt-1 text-sm text-red-600">{{ errors.incharge_person }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <input 
                                type="text" 
                                v-model="companyForm.incharge_phone" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': errors.incharge_phone }"
                            >
                            <p v-if="errors.incharge_phone" class="mt-1 text-sm text-red-600">{{ errors.incharge_phone }}</p>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeCompanyModal" 
                                :disabled="isSubmitting"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-150 flex items-center"
                            >
                                <span v-if="isSubmitting" class="mr-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                {{ isSubmitting ? 'Creating...' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from 'sweetalert2';
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    drivers: {
        type: Object,
        required: true,
    },
    companies: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
});

const showDriverModal = ref(false);
const showCompanyModal = ref(false);
const search = ref(props.filters.search || '');
const per_page = ref(props.filters.per_page || 25);
const company = ref(props.filters.company || '');

const form = ref({
    id: null,
    driver_id: '',
    name: '',
    phone: '',
    logistic_company_id: '',
    company: null,
    is_active: true
});

const companyForm = ref({
    name: '',
    email: '',
    address: '',
    incharge_person: '',
    incharge_phone: '',
    is_active: true
});

const companyOptions = computed(() => {
    const options = props.companies.map(company => ({
        id: company.id,
        name: company.name,
        isAddNew: false
    }));
    
    // Add the "Add New" option at the end
    options.push({
        id: 'new',
        name: 'Add New Company',
        isAddNew: true
    });
    
    return options;
});

const handleCompanySelect = (selected) => {
    if (selected && selected.isAddNew) {
        // Reset the selection
        form.value.company = null;
        form.value.logistic_company_id = '';
        // Open the company modal
        openCompanyModal();
    } else if (selected) {
        // Set the company ID for the driver form
        form.value.logistic_company_id = selected.id;
    }
};

const openModal = (driver = null) => {
    if (driver) {
        form.value = { 
            ...driver,
            logistic_company_id: driver.company?.id,
            company: driver.company ? {
                id: driver.company.id,
                name: driver.company.name,
                isAddNew: false
            } : null
        };
    } else {
        form.value = {
            id: null,
            driver_id: '',
            name: '',
            phone: '',
            logistic_company_id: '',
            company: null,
            is_active: true
        };
    }
    showDriverModal.value = true;
};

const closeModal = () => {
    showDriverModal.value = false;
    form.value = {
        id: null,
        driver_id: '',
        name: '',
        phone: '',
        logistic_company_id: '',
        company: null,
        is_active: true
    };
};

const openCompanyModal = () => {
    companyForm.value = {
        name: '',
        email: '',
        address: '',
        incharge_person: '',
        incharge_phone: '',
        is_active: true
    };
    showCompanyModal.value = true;
};

const closeCompanyModal = () => {
    showCompanyModal.value = false;
    companyForm.value = {
        name: '',
        email: '',
        address: '',
        incharge_person: '',
        incharge_phone: '',
        is_active: true
    };
};

watch([
    () => search.value,
    () => props.filters.page,
    () => props.filters.per_page,
    () => props.filters.company,
], () => {
    reloadDrivers();
});
function reloadDrivers() {
    const query = {}
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (company.value) query.company = company.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route('settings.drivers.index'), query, { preserveState: true, preserveScroll: true, only: ['drivers'] });
}

const idDriverSubmiting = ref(false);
const errors = ref({});
const isSubmitting = ref(false);

const submit = async () => {
    idDriverSubmiting.value = true;
    await axios.post(route('settings.drivers.store'), form.value)
        .then(response => {
            idDriverSubmiting.value = false;
            closeModal();
            reloadDrivers();
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        })
        .catch(error => {
            idDriverSubmiting.value = false;
            Swal.fire({
                title: 'Error!',
                text: error.response?.data || 'Something went wrong',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        });
       
};

const submitCompany = async () => {
    try {
        errors.value = {};
        isSubmitting.value = true;
        
        console.log('Submitting company data:', companyForm.value);
        
        const response = await axios.post(route('settings.logistics.companies.store'), companyForm.value);
        console.log('Response:', response.data);
        
        // Create a new company option and select it
        const newCompany = {
            id: response.data.company_id,
            name: companyForm.value.name,
            isAddNew: false
        };
        
        // Set both the company object and ID
        form.value.company = newCompany;
        form.value.logistic_company_id = newCompany.id;
        
        // Close modal and show success message
        closeCompanyModal();
        Swal.fire({
            title: 'Success!',
            text: response.data.message,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
        
        // Reload only the companies data
        router.get(
            route('settings.drivers.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                only: ['companies']
            }
        );
    } catch (error) {
        console.error('Error details:', error);
        console.error('Response data:', error.response?.data);
        console.error('Response status:', error.response?.status);
        
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
            Swal.fire({
                title: 'Validation Error',
                text: 'Please check the form for errors',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        } else {
            let errorMessage = 'Something went wrong';
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            } else if (typeof error.response?.data === 'string') {
                errorMessage = error.response.data;
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        }
    } finally {
        isSubmitting.value = false;
    }
};


const confirmDelete = (driver, index) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete driver ${driver.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteDriver(driver, index);
        }
    });
};

const isDeleting = ref([]);
const deleteDriver = async (driver, index) => {
    isDeleting.value[index] = true;
    await axios.delete(route('settings.drivers.destroy', driver.id))
        .then(response => {
            isDeleting.value[index] = false;
            reloadDrivers();
            Swal.fire({
                title: 'Deleted!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        })
        .catch(error => {
            isDeleting.value[index] = false;
            Swal.fire({
                title: 'Error!',
                text: error.response?.data || 'Something went wrong',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        });
};

const isTogglingStatus = ref([]);
const confirmToggleStatus = (driver, index) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${driver.is_active ? 'deactivate' : 'activate'} driver ${driver.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            toggleStatus(driver, index);
        }
    });
};

const toggleStatus = async (driver, index) => {
    isTogglingStatus.value[index] = true;
    await axios.put(route('settings.drivers.toggle-status', driver.id))
        .then(response => {
            isTogglingStatus.value[index] = false;
            reloadDrivers();
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        })
        .catch(error => {
            isTogglingStatus.value[index] = false;
            Swal.fire({
                title: 'Error!',
                text: error.response?.data || 'Something went wrong',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        });
};

const handlePageChange = (page) => {
    props.filters.page = page;
};

</script>