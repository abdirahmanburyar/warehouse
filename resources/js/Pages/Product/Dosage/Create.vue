<template>
    <AuthenticatedLayout title="Create Dosage Form" description="Create a new dosage form">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Dosage Form</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    placeholder="Dosage Form"
                                />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <TextArea
                                    id="description"
                                    class="mt-1 block w-full"
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Dosage Form Description"
                                />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :href="route('products.dosages.index')"
                                    :disabled="isSubmitting"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3"
                                >
                                    Exit
                                </Link>
                                <PrimaryButton
                                    class="ml-4"
                                    :class="{ 'opacity-25': isSubmitting }"
                                    :disabled="isSubmitting"
                                >
                                    {{ isSubmitting ? 'Creating...' : 'Create Dosage Form' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import axios from 'axios';

const toast = useToast();

const form = ref({
    name: '',
    description: ''
});
const isSubmitting = ref(false);

const submit = async () => {
    isSubmitting.value = true;
    await axios.post(route('products.dosages.store'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.get(route('products.dosages.index'));
            });            
        })
        .catch((error) => {
            isSubmitting.value = false;
            console.log(error);
            toast.error(error.response.data);
        });
};
</script>
