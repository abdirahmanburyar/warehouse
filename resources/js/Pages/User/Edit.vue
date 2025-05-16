<template>
    <UserAuthTab>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Edit User</h2>
            <Link
                :href="route('settings.users.index')"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Users
            </Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm">
            <form @submit.prevent="submit" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>
                    <div>
                        <InputLabel for="username" value="Username" />
                        <TextInput
                            id="username"
                            type="text"
                            v-model="form.username"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>
                </div>


                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <!-- Password (Optional for edit) -->
                    <div>
                        <InputLabel for="password" value="New Password (leave blank to keep current)" />
                        <TextInput
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm New Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <!-- Role -->
                    <div>
                        <InputLabel value="Role" />
                        <Multiselect
                            v-model="form.role"
                            :options="roles"
                            track-by="id"                            
                            label="name"
                            placeholder="Select role"
                            :searchable="true"
                            :allow-empty="false"
                            class="mt-1"
                        />
                    </div>

                    <!-- Warehouse -->
                    <div>
                        <InputLabel value="Warehouse" />
                        <Multiselect
                            v-model="form.warehouse"
                            :options="warehouses"
                            track-by="id"
                            label="name"
                            placeholder="Select warehouse"
                            :searchable="true"
                            :allow-empty="false"
                            class="mt-1"
                        />
                    </div>

                    <!-- Status -->
                    <div>
                        <InputLabel value="Status" />
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <SecondaryButton
                        class="mr-3"
                        :disabled="processing"
                        :href="route('settings.users.index')"
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="processing">
                        {{ processing ? 'Saving...' : " Update User" }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </UserAuthTab>
</template>

<script setup>
import { ref } from 'vue';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import Swal from 'sweetalert2';

const toast = useToast();
const processing = ref(false);

const props = defineProps({
    user: Object,
    roles: Array,
    warehouses: Array,
});

const form = ref({
    id: props.user?.id || null,
    name: props.user?.name || '',
    email: props.user?.email || '',
    username: props.user?.username || '',
    password: '',
    password_confirmation: '',
    role: props.user?.roles || [],
    warehouse: props.user?.warehouse || null,
    is_active: props.user?.is_active || false,
});

const submit = async () => {
    processing.value = true;


    await axios.post(route('settings.users.store', props.user.id), form.value)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                toast.success('User updated successfully');
                router.visit(route('settings.users.index'));
            });
        })
        .catch((error) => {
            processing.value = false;
            toast.error(error.response.data);
        });
};
</script>
