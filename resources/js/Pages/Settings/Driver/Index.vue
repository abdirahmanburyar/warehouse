<template>
    <AuthenticatedLayout :title="'Drivers'" description="Manage your drivers" img="/assets/images/settings.png">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Drivers</h2>
            <button @click="openModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i> Add Driver
            </button>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Search Bar -->
                <div class="mb-4">
                    <input 
                        type="text" 
                        v-model="search" 
                        placeholder="Search drivers..." 
                        class="w-full sm:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
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
                            <tr v-for="driver in props.drivers.data" :key="driver.id">
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
                                            @click="toggleStatus(driver)" 
                                            class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150"
                                            title="Toggle Status"
                                        >
                                            <i class="fas fa-toggle-on text-lg"></i>
                                        </button>
                                        <button 
                                            @click="openModal(driver)" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                                            title="Edit Driver"
                                        >
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                        <button 
                                            @click="confirmDelete(driver)" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150"
                                            title="Delete Driver"
                                        >
                                            <i class="fas fa-trash-alt text-lg"></i>
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
                            <div class="flex space-x-2">
                                <select 
                                    v-model="form.logistic_company_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                    <option value="">Select Company</option>
                                    <option v-for="company in props.companies" :key="company.id" :value="company.id">
                                        {{ company.name }}
                                    </option>
                                </select>
                                <button 
                                    type="button"
                                    @click="openCompanyModal"
                                    class="mt-1 px-3 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-150"
                                    title="Add New Company"
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeModal" 
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-150"
                            >
                                {{ form.id ? 'Update' : 'Create' }}
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
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input 
                                type="email" 
                                v-model="companyForm.email" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea 
                                v-model="companyForm.address" 
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <input 
                                type="text" 
                                v-model="companyForm.incharge_person" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <input 
                                type="text" 
                                v-model="companyForm.incharge_phone" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeCompanyModal" 
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-150"
                            >
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from 'sweetalert2';

const showDriverModal = ref(false);
const showCompanyModal = ref(false);
const search = ref('');

const form = ref({
    id: null,
    driver_id: '',
    name: '',
    phone: '',
    logistic_company_id: '',
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

const props = defineProps({
    drivers: {
        type: Object,
        required: true,
    },
    companies: {
        type: Array,
        required: true
    }
});

const openModal = (driver = null) => {
    if (driver) {
        form.value = { 
            ...driver,
            logistic_company_id: driver.company?.id
        };
    } else {
        form.value = {
            id: null,
            driver_id: '',
            name: '',
            phone: '',
            logistic_company_id: '',
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
        is_active: true
    };
};

const openCompanyModal = () => {
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

const submit = async () => {
    try {
        const response = await axios.post(route('settings.logistics.drivers.store'), form.value);
        closeModal();
        Swal.fire({
            title: 'Success!',
            text: response.data,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
        router.reload();
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Something went wrong',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
    }
};

const submitCompany = async () => {
    try {
        const response = await axios.post(route('settings.logistics.companies.store'), companyForm.value);
        closeCompanyModal();
        Swal.fire({
            title: 'Success!',
            text: response.data,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
        router.reload();
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Something went wrong',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
    }
};

const confirmDelete = (driver) => {
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
            deleteDriver(driver);
        }
    });
};

const deleteDriver = async (driver) => {
    try {
        const response = await axios.delete(route('settings.logistics.drivers.destroy', driver.id));
        Swal.fire({
            title: 'Deleted!',
            text: response.data,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
        router.reload();
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Something went wrong',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
    }
};

const toggleStatus = async (driver) => {
    try {
        const response = await axios.patch(route('settings.logistics.drivers.toggle-status', driver.id));
        Swal.fire({
            title: 'Success!',
            text: response.data,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
        router.reload();
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Something went wrong',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
    }
};

const handlePageChange = (page) => {
    router.get(route('settings.logistics.drivers.index'), { 
        page: page,
        search: search.value,
        per_page: props.drivers.per_page 
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['drivers']
    });
};

watch(search, debounce((value) => {
    router.get(route('settings.logistics.drivers.index'), { 
        search: value,
        page: 1,
        per_page: props.drivers.per_page 
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['drivers']
    });
}, 300));
</script>

<style scoped>
.fa-edit {
    color: #3b82f6;
}
.fa-trash-alt {
    color: #ef4444;
}
.fa-toggle-on {
    color: #eab308;
}
</style>