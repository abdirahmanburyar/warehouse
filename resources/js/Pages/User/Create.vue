<template>
    <UserAuthTab>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Create New User</h2>
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
                                id="name"
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

                    <!-- Password -->
                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <!-- Role -->
                    <div>
                        <InputLabel value="Role" />
                        <Multiselect
                            v-model="form.role_id"
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
                            :select="handleSelectWarehouse"
                            class="mt-1"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6 gap-4">
                    <Link :href="route('settings.users.index')" :disabled="processing">
                        Exit
                    </Link>
                    <PrimaryButton :disabled="processing">
                        {{ processing ? 'Creating...' : 'Create User' }}
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
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
    roles: Array,
    warehouses: Array,
});

const form = ref({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: null,
    warehouse_id: null,
    warehouse: null
});

function handleSelectWarehouse(selected){
    form.value.warehouse_id = selected.id;
}

const submit = async () => {
    processing.value = true;

    console.log(form.value);
    

    await axios.post(route('settings.users.store'), form.value)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                toast.success('User created successfully');
                router.visit(route('settings.users.index'));
            });
        })
        .catch((error) => {
            processing.value = false;
            toast.error(error.response.data);
        });
};
</script>
