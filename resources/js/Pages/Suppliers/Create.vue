<template>
    <Head title="Add New Supplier" />
    <AuthenticatedLayout>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add New Supplier</h2>
            <Link :href="route('supplies.index', { tab: 'suppliers' })" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back to Suppliers
            </Link>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div>
                                <InputLabel for="name" value="Supplier Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="contact_person" value="Contact Person" />
                                <TextInput
                                    id="contact_person"
                                    type="text"
                                    v-model="form.contact_person"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.contact_person" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Phone" />
                                <TextInput
                                    id="phone"
                                    type="text"
                                    v-model="form.phone"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.phone" class="mt-2" />
                            </div>

                            <!-- Address Information -->
                            <div>
                                <InputLabel for="address" value="Address" />
                                <TextInput
                                    id="address"
                                    type="text"
                                    v-model="form.address"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.address" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="city" value="City" />
                                <TextInput
                                    id="city"
                                    type="text"
                                    v-model="form.city"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.city" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="state" value="State/Province" />
                                <TextInput
                                    id="state"
                                    type="text"
                                    v-model="form.state"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.state" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="postal_code" value="Postal Code" />
                                <TextInput
                                    id="postal_code"
                                    type="text"
                                    v-model="form.postal_code"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.postal_code" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="country" value="Country" />
                                <TextInput
                                    id="country"
                                    type="text"
                                    v-model="form.country"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.country" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="is_active" value="Status" />
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-600">Active</span>
                                    </label>
                                </div>
                                <InputError :message="form.errors.is_active" class="mt-2" />
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <InputLabel for="notes" value="Notes" />
                            <TextareaInput
                                id="notes"
                                v-model="form.notes"
                                class="mt-1 block w-full"
                                rows="3"
                            />
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-6">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Add Supplier
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Initialize form with default values
const form = useForm({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    notes: '',
    is_active: true,
});

// Submit form
const submit = () => {
    form.post(route('suppliers.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
