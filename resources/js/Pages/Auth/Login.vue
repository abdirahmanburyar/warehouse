<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const isLoading = ref(false);
const showPassword = ref(false);
const loginAnimation = ref(false);

const submit = () => {
    if (form.processing) return;
    
    isLoading.value = true;
    loginAnimation.value = true;
    
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
            isLoading.value = false;
            setTimeout(() => {
                loginAnimation.value = false;
            }, 500);
        },
    });
};

// Toggle password visibility
const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="min-h-screen flex items-center justify-center overflow-hidden">
            <div 
                class="w-full md:w-2/4 max-w-md bg-white p-8 transition-all duration-300"
            >
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Sign In</h1>
                    <p class="text-gray-600 text-sm">Let's get you Dive in to VISTA</p>
                </div>
                
                <div v-if="status" class="bg-green-50 border-l-4 border-green-400 p-3 mb-4 rounded text-green-700 font-medium">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <div>
                        <InputLabel for="username" value="Username" class="font-semibold text-gray-700 mb-1 block" />
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-user"></i></span>
                            <TextInput
                                id="username"
                                type="text"
                                class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-300 bg-gray-50 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 transition disabled:bg-gray-100"
                                v-model="form.username"
                                required
                                autofocus
                                autocomplete="username"
                                :disabled="isLoading"
                                placeholder="Enter your username"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.username" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="font-semibold text-gray-700 mb-1 block" />
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-lock"></i></span>
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="pl-10 pr-10 py-2 w-full rounded-lg border border-gray-300 bg-gray-50 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 transition disabled:bg-gray-100"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                :disabled="isLoading"
                                placeholder="Enter your password"
                            />
                            <button 
                                type="button" 
                                @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            >
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between mt-2">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" :disabled="isLoading" />
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs text-blue-600 hover:underline ml-2"
                        >
                            Forgot your password?
                        </Link>
                    </div>

                    <PrimaryButton
                        class="w-full py-3 rounded-lg font-semibold bg-gradient-to-r from-blue-500 to-blue-400 text-white shadow hover:from-blue-600 hover:to-blue-500 transition disabled:opacity-60 flex items-center justify-center"
                        :disabled="form.processing || isLoading"
                    >
                        <span v-if="isLoading" class="flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Logging in...
                        </span>
                        <span v-else>Log in</span>
                    </PrimaryButton>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

