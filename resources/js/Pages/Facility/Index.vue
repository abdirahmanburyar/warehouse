<template>
    <AuthenticatedLayout title="Facilities" :data="{
        pageTitle: 'Facility Management',
        description: 'Manage and monitor all healthcare facilities',
        image: '/assets/images/facility.png'
    }">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Facilities</h2>
            <button @click="openModal('create')"
                class="bg-gray-900 text-white rounded-full px-6 py-2.5 text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-700 transition-colors duration-200 ease-in-out flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                Add Facility
            </button>
        </div>
        <div class="flex justify-between items-center mt-2 m-2">
            <div class="w-full items-center gap-2">
                <input type="text" v-model="search" placeholder="Search"
                    class="w-full rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <select v-model="per_page"
                class="w-1/5 px-4  rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
        <div class="overflow-hidden sm:rounded-lg p-6">
            <div class="overflow-x-auto">
                <table v-if="props.facilities.data.length > 0" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fa-solid fa-building mr-2"></i>Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fa-solid fa-tag mr-2"></i>Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fa-solid fa-user mr-2"></i>Manager
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fa-solid fa-location-dot mr-2"></i>District
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fa-solid fa-circle-check mr-2"></i>Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fa-solid fa-gear mr-2"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="facility in props.facilities.data" :key="facility.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-building-circle-check mr-2 text-gray-400"></i>
                                    {{ facility.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i :class="{
                                        'fa-solid mr-2': true,
                                        'fa-hospital text-blue-400': facility.facility_type === 'hospital',
                                        'fa-clinic-medical text-green-400': facility.facility_type === 'clinic',
                                        'fa-prescription text-purple-400': facility.facility_type === 'pharmacy'
                                    }"></i>
                                    {{ facility.facility_type }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-user mr-2 text-gray-400"></i>
                                    {{ facility.user?.name }}
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-solid fa-envelope mr-2 text-gray-400"></i>
                                    {{ facility.email }}
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-solid fa-phone mr-2 text-gray-400"></i>
                                    {{ facility.phone }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-location-dot mr-2 text-gray-400"></i>
                                    {{ facility.district?.district_name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="facility.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    <i
                                        :class="facility.is_active ? 'fa-solid fa-circle-check mr-1' : 'fa-solid fa-circle-xmark mr-1'"></i>
                                    {{ facility.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button @click="openModal('edit', facility)"
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="deleteFacility(facility.id)" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty state indicator -->
                <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fa-solid fa-building-circle-exclamation text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No facilities found</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        {{ search ? 'No facilities match your search criteria.' : 'There are no facilities in the system yet.' }}
                    </p>
                    <button @click="openModal('create')"
                        class="bg-gray-900 text-white rounded-full px-6 py-2.5 text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-700 transition-colors duration-200 ease-in-out inline-flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        Add Your First Facility
                    </button>
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500" v-if="props.facilities.meta.total">
                        Showing {{ props.facilities.meta.from }} to {{ props.facilities.meta.to }} of {{
                            props.facilities.meta.total }} results
                    </div>
                    <Pagination :links="props.facilities.meta.links" />
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal" :max-width="'2xl'">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ isEditing ? 'Edit Facility' : 'Add New Facility' }}
                </h2>
                <form @submit.prevent="submitForm" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                            placeholder="Enter facility name" />
                        <InputError :message="errors.name" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                                placeholder="Enter facility email" />
                            <InputError :message="errors.email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="phone" value="Phone" />
                            <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" required
                                placeholder="Enter phone number" />
                            <InputError :message="errors.phone" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="address" value="Address" />
                        <TextInput id="address" v-model="form.address" type="text" class="mt-1 block w-full" required
                            placeholder="Enter facility address" />
                        <InputError :message="errors.address" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="city" value="City" />
                            <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" required
                                placeholder="Enter city" />
                            <InputError :message="errors.city" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="state" value="State" />
                            <TextInput id="state" v-model="form.state" type="text" class="mt-1 block w-full"
                                placeholder="Enter state" />
                            <InputError :message="errors.state" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="facility_type" value="Facility Type" />
                            <select id="facility_type" v-model="form.facility_type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">Select Type</option>
                                <option value="District Hospital">District Hospital</option>
                                <option value="Regional Hospital">Regional Hospital</option>
                                <option value="Health Centre">Health Centre</option>
                                <option value="Primary Health Unit">Primary Health Unit</option>
                            </select>
                            <InputError :message="errors.facility_type" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="district_id" value="District" />
                            <select id="district_id" v-model="form.district_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">Select District</option>
                                <option v-for="district in props.districts" :key="district.id" :value="district.id">{{
                                    district.district_name }}</option>
                            </select>
                            <InputError :message="errors.district_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="user_id" value="Assigned User" />
                            <select id="user_id" v-model="form.user_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">Select User</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                            <InputError :message="errors.user_id" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-center items-center space-x-8">
                        <label class="flex items-center">
                            <Checkbox :checked="form.has_cold_storage" :modelValue="form.has_cold_storage"
                                @update:modelValue="(value) => form.has_cold_storage = value" />
                            <span class="ml-2 text-sm text-gray-600">Has Cold Storage</span>
                        </label>

                        <label class="flex items-center">
                            <Checkbox :checked="form.is_24_hour_service" :modelValue="form.is_24_hour_service"
                                @update:modelValue="(value) => form.is_24_hour_service = value" />
                            <span class="ml-2 text-sm text-gray-600">24 Hour Service</span>
                        </label>

                        <label class="flex items-center">
                            <Checkbox :checked="form.is_active" :modelValue="form.is_active"
                                @update:modelValue="(value) => form.is_active = value" />
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>

                    <div>
                        <InputLabel for="special_handling_capabilities" value="Special Handling Capabilities" />
                        <textarea id="special_handling_capabilities" v-model="form.special_handling_capabilities"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            rows="3"></textarea>
                        <InputError :message="errors.special_handling_capabilities" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <SecondaryButton @click="closeModal" class="mr-3" :disabled="isSubmitting">Cancel
                        </SecondaryButton>
                        <PrimaryButton :disabled="isSubmitting">{{ isEditing ? isSubmitting ? 'Updating...' : 'Update' :
                            isSubmitting ? 'Creating...' : 'Create' }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Checkbox from '@/Components/Checkbox.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    facilities: {
        type: Object,
        required: true
    },
    users: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        required: true
    },
    districts: {
        type: Array,
        required: true
    }
})

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const isSubmitting = ref(false)
const errors = ref({})
const page = ref(props.filters.page)
const per_page = ref(props.filters.per_page || 10)
const search = ref(props.filters.search || '')

watch([
    () => per_page.value,
    () => page.value,
    () => search.value
], () => {
    reloadFacility();
})

const form = ref({
    id: null,
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    facility_type: '',
    has_cold_storage: false,
    is_24_hour_service: false,
    is_active: true,
    user_id: '',
    district_id: ''
})

const resetForm = () => {
    form.value = {
        id: null,
        name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        facility_type: '',
        has_cold_storage: false,
        is_24_hour_service: false,
        is_active: true,
        user_id: '',
        district_id: ''
    }
    errors.value = {}
}

const openModal = (mode, facility = null) => {
    isEditing.value = mode === 'edit'
    if (isEditing.value && facility) {
        editingId.value = facility.id
        form.value = { ...facility }
    } else {
        editingId.value = null
        resetForm()
    }
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    resetForm()
    isEditing.value = false
    editingId.value = null
}

const reloadFacility = () => {
    const query = {}
    if (per_page.value) query.per_page = per_page.value
    if (page.value) query.page = page.value
    if (search.value) query.search = search.value
    router.get(route('facilities.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: ['facilities']
    })
}


const submitForm = async () => {
    console.log(form.value);
    isSubmitting.value = true
    await axios.post(route('facilities.store'), form.value)
        .then((response) => {
            isSubmitting.value = false
            toast.success(response.data)
            closeModal()
            reloadFacility()
        })
        .catch((error) => {
            isSubmitting.value = false
            toast.error(error.response.data)
        })
}

const deleteFacility = async (id) => {
    if (confirm('Are you sure you want to delete this facility?')) {
        try {
            const response = await axios.delete(route('facilities.destroy', id))
            toast.success(response.data)
            reloadFacility()
        } catch (error) {
            toast.error(error.response.data);
        }
    }
}
</script>