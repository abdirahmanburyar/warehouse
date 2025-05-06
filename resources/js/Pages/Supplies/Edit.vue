<template>
    <AuthenticatedLayout>
        <div class="containe mx-auto py-6">
            <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Suppliers
            </Link>

        <div class=" rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">Create Supplier</h2>
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" v-model="form.name" placeholder="Enter supplier name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" v-model="form.contact_person" placeholder="Enter contact person name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" v-model="form.email" placeholder="Enter email address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" v-model="form.phone" placeholder="Enter phone number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea v-model="form.address" placeholder="Enter complete address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('supplies.index')" :disabled="isSubmitting" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disable="isSubmitting" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        {{ isSubmitting ? 'Creating...' : 'Create Supplier' }} 
                    </button>
                </div>
            </form>
        </div>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from "axios";
import Swal from 'sweetalert2'

const props = defineProps({
    supplier: {
        required: true,
        type: Object
    }
});

const form = ref({
    id: props.supplier?.id || null,
    name: props.supplier?.name || '',
    contact_person: props.supplier?.contact_person || '',
    email: props.supplier?.email || '',
    phone: props.supplier?.phone || '',
    address: props.supplier?.address || '',
    is_active: props.supplier?.is_active || true,
})

function reloadSuppliers(){
    const query = {}
    router.get(route('suppliers.index'), {}, {
        preserveScroll: false,
        preserveState: false,  
    })
}

const tableHeaders = [
    'Name',
    'Contact Person',
    'Email',
    'Phone',
    'Address',
    'Status',
    'Actions',
]

const isSubmitting = ref(false)

async function submitForm(){
    isSubmitting.value = true;
    await axios.post(route('suppliers.store'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            console.log(response);
            Swal.fire({
                icon: 'success',
                title: 'Supplier Added',
                text: 'Supplier added successfully',
            }).then((result) => {
                if (result.isConfirmed) {
                    reloadSuppliers();
                }
            })
        })
        .catch((error) => {
            console.log(error);
        });
}
</script>