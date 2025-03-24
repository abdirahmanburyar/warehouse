<template>
    <GuestLayout>
        <Head title="Two-Factor Authentication" />

        <div class="bg-white p-4">
            <div class="mb-4 text-sm text-gray-600">
                Please enter the 6-digit verification code sent to your email address.
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="bg-white p-4">
                <div>
                    <InputLabel for="code" value="Verification Code" />

                <TextInput
                    id="code"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.code"
                    required
                    autofocus
                    autocomplete="off"
                    maxlength="6"
                    placeholder="Enter 6-digit code"
                />

                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button 
                    type="button" 
                    class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
                    @click="resendCode"
                    :disabled="resending"
                >
                    {{ resending ? 'Sending...' : 'Resend Code' }}
                </button>

                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ form.processing ? 'Verifying...' : 'Verify' }}
                </PrimaryButton>
            </div>
        </form>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ToastService from '@/Services/ToastService';

const props = defineProps({
    status: String,
});

const form = useForm({
    code: '',
});

const resending = ref(false);

const submit = () => {
    form.post(route('two-factor.verify'), {
        onSuccess: () => {
            ToastService.success('Successfully verified. Redirecting...');
        },
        onError: () => {
            ToastService.error('Verification failed. Please try again.');
        }
    });
};

const resendCode = () => {
    resending.value = true;
    
    axios.post(route('two-factor.resend'))
        .then(() => {
            ToastService.success('A new verification code has been sent to your email.');
        })
        .catch(() => {
            ToastService.error('Failed to resend code. Please try again.');
        })
        .finally(() => {
            resending.value = false;
        });
};
</script>
