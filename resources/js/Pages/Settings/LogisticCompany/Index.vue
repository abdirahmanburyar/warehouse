<template>
    <AuthenticatedLayout :title="'Logistic Companies'" description="Manage your logistic partners" img="/assets/images/settings.png">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Logistics Companies</h2>
            <div class="flex space-x-4">
                <Link 
                    :href="route('settings.drivers.index')" 
                    class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    <i class="fas fa-users mr-2"></i> Drivers
                </Link>
                <button 
                    @click="openModal()" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    <i class="fas fa-plus mr-2"></i> Add Company
                </button>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="text-gray-900">
                <!-- Search Bar -->
                <div class="mb-4 flex justify-between items-center">
                    <input 
                        type="text" 
                        v-model="search" 
                        placeholder="Search companies..." 
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Drivers</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(company, index) in props.companies.data" :key="company.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ company.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ company.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ company.incharge_person }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ company.incharge_phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ company.address }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ company.drivers_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="[
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            company.is_active 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ company.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-3">
                                        <button 
                                            @click="confirmToggleStatus(company, index)"
                                            class="transition-colors duration-150"
                                            title="Toggle Status"
                                            :disabled="isTogglingStatus[index]"
                                        >
                                            <i class="fas fa-toggle-on text-lg" 
                                               :class="[
                                                   company.is_active ? 'text-green-500' : 'text-gray-400',
                                                   { 'animate-pulse': isTogglingStatus[index] }
                                               ]"
                                            ></i>
                                        </button>
                                        <button 
                                            @click="openModal(company)" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                                            title="Edit Company"
                                        >
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                        <button 
                                            @click="confirmDelete(company, index)" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150"
                                            title="Delete Company"
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
                        :data="props.companies" 
                        :limit="2"
                        @pagination-change-page="getResult" 
                    />
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ form.id ? 'Edit Company' : 'Add New Company' }}
                </h2>

                <div class="mt-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input 
                                type="text" 
                                v-model="form.name" 
                                placeholder="Enter company name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input 
                                type="email" 
                                v-model="form.email" 
                                placeholder="Enter company email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea 
                                v-model="form.address" 
                                placeholder="Enter company address"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                rows="3"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <input 
                                type="text" 
                                placeholder="Enter contact person"
                                v-model="form.incharge_person" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <input 
                                type="text" 
                                placeholder="Enter contact phone"
                                v-model="form.incharge_phone" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeModal" 
                                :disabled="isLoading"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="isLoading"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-150"
                            >
                                {{ form.id ? isLoading ? 'Updating...' : 'Update' : isLoading ? 'Creating...' : 'Create' }}
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from 'sweetalert2';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    companies: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const showModal = ref(false);
const search = ref(props.filters.search || '');
const per_page = ref(props.filters.per_page || 25);

watch([
    () => search.value,
    () => per_page.value,
], () => {
    reloadCompanies();
});

function reloadCompanies() {
    const query = {}
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    router.get(route('settings.logistics.companies.index'), query, { preserveState: true, preserveScroll: true, only: ['companies'] });
}

function getResult(page) {
    props.filters.page = page;
}

const form = ref({
    id: null,
    name: '',
    email: '',
    address: '',
    incharge_person: '',
    incharge_phone: '',
    is_active: true
});

const openModal = (company = null) => {
    if (company) {
        form.value = { ...company };
    } else {
        form.value = {
            id: null,
            name: '',
            email: '',
            address: '',
            incharge_person: '',
            incharge_phone: '',
            is_active: true
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = {
        id: null,
        name: '',
        email: '',
        address: '',
        incharge_person: '',
        incharge_phone: '',
        is_active: true
    };
};

const isLoading = ref(false);
const submit = async () => {
    isLoading.value = true;
    await axios.post(route('settings.logistics.companies.store'), form.value)
        .then(response => {
            isLoading.value = false;
            closeModal();
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
            reloadCompanies();
        })
        .catch(error => {
            isLoading.value = false;
            Swal.fire({
                title: 'Error!',
                text: error.response?.data || 'Something went wrong',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        });
};

const confirmDelete = (company, index) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete ${company.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteCompany(company, index);
        }
    });
};

const isDeleting = ref([]);
const deleteCompany = async (company, index) => {
    isDeleting.value[index] = true;
    await axios.delete(route('settings.logistics.companies.destroy', company.id))
        .then(response => {
            isDeleting.value[index] = false;
            Swal.fire({
                title: 'Deleted!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
            reloadCompanies();
        })
        .catch((error) => {
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

const confirmToggleStatus = (company, index) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${company.is_active ? 'deactivate' : 'activate'} company ${company.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            toggleStatus(company, index);
        }
    });
};

const toggleStatus = async (company, index) => {
    isTogglingStatus.value[index] = true;
    await axios.put(route('settings.logistics.companies.toggle-status', company.id))
        .then(response => {
            isTogglingStatus.value[index] = false;
            reloadCompanies();
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

</script>

<style scoped>
.fa-edit {
    color: #3b82f6;
}
.fa-trash-alt {
    color: #ef4444;
}
</style> 