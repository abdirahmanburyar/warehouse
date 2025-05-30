<template>
    <AuthenticatedLayout title="Edit Facility">
        <div class="p-6 bg-white border-b flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Edit Facility</h1>
            <Link :href="route('facilities.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i> Back to Facilities
            </Link>
        </div>

        <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <form @submit.prevent="submitForm" class="space-y-6">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                        placeholder="Enter facility name" />
                    <InputError :message="errors.name" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <InputLabel for="district" value="District" />
                        <select id="district" v-model="form.district"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required>
                            <option value="">Select District</option>
                            <option v-for="district in districts" :key="district" :value="district">{{ district }}</option>
                        </select>
                        <InputError :message="errors.district" class="mt-2" />
                    </div>
                </div>

                <div>
                    <InputLabel for="user_id" value="Assigned User (Manager)" />
                    <select id="user_id" v-model="form.user_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required>
                        <option value="">Select User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                    <InputError :message="errors.user_id" class="mt-2" />
                </div>

                <div class="flex justify-start items-center space-x-8">
                    <label class="flex items-center">
                        <Checkbox :checked="form.has_cold_storage" :modelValue="form.has_cold_storage"
                            @update:modelValue="(value) => form.has_cold_storage = value" />
                        <span class="ml-2 text-sm text-gray-600">Has Cold Storage</span>
                    </label>

                    <label class="flex items-center">
                       <input type="checkbox" v-model="form.is_active" />
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <Link :href="route('facilities.index')" class="mr-3">
                        <SecondaryButton :disabled="isSubmitting">Cancel</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="isSubmitting">
                        {{ isSubmitting ? 'Updating...' : 'Update Facility' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Checkbox from '@/Components/Checkbox.vue'

const props = defineProps({
    facility: {
        type: Object,
        required: true
    },
    users: {
        type: Array,
        required: true
    },
    districts: {
        type: Array,
        required: true
    }
})

const isSubmitting = ref(false)
const errors = ref({})

const form = ref({
    id: props.facility.id,
    name: props.facility.name,
    email: props.facility.email,
    phone: props.facility.phone,
    address: props.facility.address,
    facility_type: props.facility.facility_type,
    has_cold_storage: props.facility.has_cold_storage,
    is_active: !!props.facility.is_active,
    user_id: props.facility.user_id,
    district: props.facility.district
})


const submitForm = async () => {
    isSubmitting.value = true
       await axios.post(route('facilities.store'), form.value)
        .then((response) => {
            isSubmitting.value = false
            Swal.fire({
                title: 'Success!',
                text: 'Facility created successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.visit(route('facilities.index'))
            })
        })
        .catch((error) => {
            isSubmitting.value = false
            Swal.fire({
                title: 'Error!',
                text: error.response?.data || 'There was an error creating the facility',
                icon: 'error',
                confirmButtonText: 'OK'
            })
        })
}
</script>
