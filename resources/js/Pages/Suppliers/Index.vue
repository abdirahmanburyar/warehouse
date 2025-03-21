<template>
    <Head title="Suppliers Management" />
    <AuthenticatedLayout>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suppliers Management</h2>
            <Link :href="route('suppliers.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Add New Supplier
            </Link>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Filters -->
                        <div class="mb-6 flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <TextInput
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search by name, contact, email or phone..."
                                    class="w-full"
                                    @keyup.enter="getSuppliers"
                                />
                            </div>
                            <div class="w-full md:w-auto">
                                <SelectInput
                                    v-model="filters.active"
                                    :options="[
                                        { value: '', label: 'All Status' },
                                        { value: '1', label: 'Active' },
                                        { value: '0', label: 'Inactive' }
                                    ]"
                                    class="w-full md:w-48"
                                />
                            </div>
                            <div class="flex items-end">
                                <PrimaryButton @click="getSuppliers">Filter</PrimaryButton>
                                <SecondaryButton class="ml-2" @click="resetFilters">Reset</SecondaryButton>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact Person
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Phone
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No suppliers found
                                        </td>
                                    </tr>
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ supplier.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.contact_person || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.email || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.phone || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${supplier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`">
                                                {{ supplier.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('suppliers.show', supplier.id)" class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </Link>
                                                <Link :href="route('suppliers.edit', supplier.id)" class="text-blue-600 hover:text-blue-900">
                                                    Edit
                                                </Link>
                                                <button @click="confirmDelete(supplier)" class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="suppliers.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="deleteModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this supplier?
                </h2>
                <p class="mt-1 text-sm text-gray-600" v-if="supplierToDelete && supplierToDelete.supplies_count > 0">
                    This supplier has {{ supplierToDelete.supplies_count }} supplies associated with it. You cannot delete a supplier with associated supplies.
                </p>
                <p class="mt-1 text-sm text-gray-600" v-else>
                    This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal" class="mr-3">
                        Cancel
                    </SecondaryButton>
                    <DangerButton 
                        @click="deleteSupplier" 
                        :class="{ 'opacity-25': processing || (supplierToDelete && supplierToDelete.supplies_count > 0) }" 
                        :disabled="processing || (supplierToDelete && supplierToDelete.supplies_count > 0)"
                    >
                        Delete Supplier
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    suppliers: Object,
    filters: Object,
});

// Set up reactive state
const filters = ref({
    search: props.filters.search || '',
    active: props.filters.active || '',
});

// Delete modal state
const deleteModal = ref(false);
const supplierToDelete = ref(null);
const processing = ref(false);

// Methods
const getSuppliers = () => {
    router.get(route('suppliers.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        active: '',
    };
    getSuppliers();
};

const confirmDelete = (supplier) => {
    supplierToDelete.value = supplier;
    deleteModal.value = true;
};

const closeModal = () => {
    deleteModal.value = false;
    supplierToDelete.value = null;
};

const deleteSupplier = () => {
    if (!supplierToDelete.value || supplierToDelete.value.supplies_count > 0) return;
    
    processing.value = true;
    router.delete(route('suppliers.destroy', supplierToDelete.value.id), {
        onSuccess: () => {
            closeModal();
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

// Watch for filter changes
watch(
    () => [filters.value.active],
    () => getSuppliers(),
    { deep: true }
);
</script>
