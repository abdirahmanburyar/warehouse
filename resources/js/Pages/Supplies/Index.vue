<template>

    <Head title="Suppliers" />
    <AuthenticatedLayout title="Manage Your Supplies" description="Ensuring an Optimcal Flow of Essentials Resource" img="/assets/images/supplies.png">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suppliers</h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Fixed Search and Add Button Section -->
                <div class="sticky top-0 z-10 bg-white pb-4 mb-4 border-b">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    v-model="search" 
                                    @input="debouncedSearch"
                                    placeholder="Search by name, email or phone..."
                                    class="pl-10 pr-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-80" 
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <button v-if="selectedSuppliers.length > 0"
                                @click="confirmBulkDelete"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                                Delete Selected ({{ selectedSuppliers.length }})
                            </button>
                        </div>
                        <button @click="openCreateSupplierModal"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add New Supplier
                        </button>
                    </div>
                </div>

                <!-- Suppliers Table with Fixed Header -->
                <div class="relative">
                    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-300px)]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" v-model="selectAllSuppliers" @change="selectAllSuppliers"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact Person</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Phone
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="supplier in props.suppliers?.data" :key="supplier.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="supplier.id" v-model="selectedSuppliers"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ supplier.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ supplier.contact_person }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ supplier.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ supplier.phone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editSupplier(supplier)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-2 transition-colors duration-200">
                                            Edit
                                        </button>
                                        <button @click="confirmDelete(supplier)" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 sticky bottom-0 bg-white pt-4 border-t">
                    <Pagination :links="props.suppliers?.links" />
                </div>

                <!-- Supplier Form Modal -->
                <Modal :show="showModal" @close="closeModal" :maxWidth="'2xl'">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">
                            {{ form.id ? 'Edit Supplier' : 'Add New Supplier' }}
                        </h2>
                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="name" value="Name" />
                                    <TextInput 
                                        id="name" 
                                        type="text" 
                                        v-model="form.name" 
                                        class="mt-1 block w-full"
                                        placeholder="Enter supplier name"
                                        required 
                                    />
                                </div>

                                <div>
                                    <InputLabel for="contact_person" value="Contact Person" />
                                    <TextInput 
                                        id="contact_person" 
                                        type="text" 
                                        v-model="form.contact_person"
                                        class="mt-1 block w-full" 
                                        placeholder="Enter contact person name"
                                        required 
                                    />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput 
                                        id="email" 
                                        type="email" 
                                        v-model="form.email" 
                                        class="mt-1 block w-full"
                                        placeholder="Enter email address"
                                        required 
                                    />
                                </div>

                                <div>
                                    <InputLabel for="phone" value="Phone" />
                                    <TextInput 
                                        id="phone" 
                                        type="text" 
                                        v-model="form.phone" 
                                        class="mt-1 block w-full"
                                        placeholder="Enter phone number"
                                        required 
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6 gap-4">
                                <SecondaryButton @click="closeModal" :disabled="isSubmitted">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="isSubmitted">
                                    {{ form.id ? isSubmitted ? 'Updating...' : 'Update' : isSubmitted ? 'Creating...' : 'Create' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import debounce from 'lodash/debounce';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    suppliers: Object,
    filters: Object
});


const errors = ref({});
const search = ref(props.filters?.search || '');
const showModal = ref(false);
const showDeleteModal = ref(false);
const selectedSupplier = ref(null);
const selectedSuppliers = ref([]);
const selectAllSuppliers = ref(false);

const form = ref({
    id: '',
    name: '',
    contact_person: '',
    email: '',
    phone: ''
});

const resetForm = () => {
    form.value = {
        id: '',
        name: '',
        contact_person: '',
        email: '',
        phone: ''
    };
    errors.value = {};
};

function reloadSupplier(){
    const query = {}
    if (search.value) {
        query.search = search.value;
    }
    router.get(route('supplies.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['suppliers']
    });
}


const openCreateSupplierModal = () => {
    resetForm();
    showModal.value = true;
};

const editSupplier = (supplier) => {
    form.value = { ...supplier };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

const isSubmitted = ref(false);

const submitForm = async () => {
    isSubmitted.value = true;
    await axios.post(route('suppliers.store'), form.value)
        .then((response) => {
            isSubmitted.value = false;
            toast.success(response.data);
            closeModal();
            reloadSupplier();
        })
        .catch(error => {
            console.log(error);
            isSubmitted.value = false;
            toast.error(error.response.data);
        });
};

const confirmDelete = async (supplier) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        deleteSupplier(supplier);
    }
};

const deleteSupplier = async (supplier) => {
    try {
        await axios.delete(`/suppliers/${supplier.id}`);
        
        await Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Supplier has been deleted.'
        });

        // Refresh the suppliers list
        const response = await axios.get('/suppliers');
        props.suppliers = response.data;
        
    } catch (error) {
        if (error.response?.data?.errors) {
            Object.keys(error.response.data.errors).forEach(key => {
                toast.error(error.response.data.errors[key][0]);
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete supplier'
            });
        }
    }
};

const confirmBulkDelete = async () => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete ${selectedSuppliers.value.length} suppliers. This cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete them!'
    });

    if (result.isConfirmed) {
        try {
            await axios.post('/suppliers/bulk-delete', {
                ids: selectedSuppliers.value
            });
            
            await Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Selected suppliers have been deleted.'
            });

            selectedSuppliers.value = [];
            selectAllSuppliers.value = false;

            // Refresh the suppliers list
            const response = await axios.get('/suppliers');
            props.suppliers = response.data;
            
        } catch (error) {
            if (error.response?.data?.errors) {
                Object.keys(error.response.data.errors).forEach(key => {
                    toast.error(error.response.data.errors[key][0]);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete suppliers'
                });
            }
        }
    }
};

watch([
    () => search.value
], () => {
    reloadSupplier();
})
</script>