<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { ref, onMounted, watch } from 'vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import Swal from 'sweetalert2';

const toast = useToast();

const props = defineProps({
    locations: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true,
        default: () => []
    }
});

const locationOptions = ref([]);
const categoryOptions = ref([]);

watch(() => props.categories, (newCategories) => {
    categoryOptions.value = [{ id: 'new', name: '+ Add New Category', isAddNew: true }, ...newCategories];
}, { immediate: true, deep: true });

watch(() => props.locations, (newLocations) => {
    locationOptions.value = [{ id: 'new', name: '+ Add New Location', isAddNew: true }, ...newLocations];
}, { immediate: true, deep: true });


const form = ref({
    asset_tag: '',
    asset_category_id: null,
    serial_number: '',
    item_description: '',
    person_assigned: '',
    asset_location_id: '',
    sub_location_id: '',
    acquisition_date: '',
    status: 'active',
    original_value: '',
    source_agency: ''
});

const subLocations = ref([]);

const loadSubLocations = async (locationId) => {
    if (!locationId) {
        subLocations.value = [];
        form.value.sub_location_id = '';
        return;
    }
    try {
        const response = await axios.get(route('assets.locations.sub-locations', locationId));
        subLocations.value = response.data;
    } catch (error) {
        toast.error(error.response.data);
    }
};

watch(() => form.value.asset_location_id, (newValue) => {
    loadSubLocations(newValue);
});

const statuses = ref([
    { value: 'active', label: 'Active' },
    { value: 'in_use', label: 'In Use' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'retired', label: 'Retired' },
    { value: 'disposed', label: 'Disposed' }
]);

const processing = ref(false);
const showLocationModal = ref(false);
const showSubLocationModal = ref(false);
const newLocation = ref('');
const newSubLocation = ref('');
const newCategory = ref('');
const showCategoryModal = ref(false);
const isNewCategory = ref(false);
const selectedLocationForSub = ref(null);

const handleLocationSelect = (selected) => {
    console.log(selected);
    if (!selected) {
        // Handle clearing the selection
        form.value.asset_location_id = null;
        selectedLocationForSub.value = null;
        form.value.sub_location_id = null;
        subLocations.value = [];
        return;
    }

    if (selected.isAddNew) {
        // Show the modal and keep the previous selection
        const currentSelection = form.value.asset_location_id;
        showLocationModal.value = true;
        form.value.asset_location_id = currentSelection;
        return;
    }

    // Handle normal selection
    form.value.asset_location_id = selected;
    selectedLocationForSub.value = selected.id;
    loadSubLocations(selected.id);
    form.value.sub_location_id = null;
};

const handleSubLocationSelect = (selected) => {
    if (!selected) {
        form.value.sub_location_id = null;
        return;
    }

    if (selected.isAddNew) {
        selectedLocationForSub.value = form.value.asset_location_id.id;
        showSubLocationModal.value = true;
        return;
    }

    form.value.sub_location_id = selected;
};
const isNewLocation = ref(false);
const createLocation = async () => {
    if (!newLocation.value) return;
    isNewLocation.value = true;
    try {
        const response = await axios.post(route('assets.locations.store'), { name: newLocation.value });
        const newLocationData = response.data;

        // Update the form with the new location
        form.value.asset_location_id = newLocationData;
        selectedLocationForSub.value = newLocationData.id;

        // Clear modal data
        newLocation.value = '';
        showLocationModal.value = false;

        // Add the new location to locationOptions
        locationOptions.value = [...locationOptions.value.filter(loc => !loc.isAddNew), newLocationData, { id: 'new', name: '+ Add New Location', isAddNew: true }];

        // Load sub-locations for the new location
        await loadSubLocations(newLocationData.id);

        toast.success('Location created successfully');
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data || 'Error creating location');
    } finally {
        isNewLocation.value = false;
    }
};

const handleCategorySelect = (selected) => {
    if (!selected) {
        form.value.asset_category_id = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = form.value.asset_category_id;
        showCategoryModal.value = true;
        form.value.asset_category_id = currentSelection;
        return;
    }

    form.value.asset_category_id = selected;
};

const createCategory = async () => {
    if (!newCategory.value) {
        toast.error('Please enter a category name');
        return;
    }

    isNewCategory.value = true;
    try {
        const response = await axios.post(route('assets.categories.store'), {
            name: newCategory.value
        });

        const newCategoryData = response.data;

        // Add the new category to categoryOptions
        categoryOptions.value = [...categoryOptions.value.filter(cat => !cat.isAddNew), newCategoryData, { id: 'new', name: '+ Add New Category', isAddNew: true }];

        // Update the form with the new category
        form.value.asset_category_id = newCategoryData;

        // Clear modal data
        newCategory.value = '';
        showCategoryModal.value = false;

        toast.success('Category created successfully');
    } catch (error) {
        console.error('Error creating category:', error);
        toast.error(error.response?.data || 'Error creating category');
    } finally {
        isNewCategory.value = false;
    }
};

const createSubLocation = async () => {
    if (!newSubLocation.value || !selectedLocationForSub.value) {
        toast.error('Please enter a sub-location name and select a location');
        return;
    }

    try {
        const response = await axios.post(route('assets.locations.sub-locations.store'), {
            name: newSubLocation.value,
            asset_location_id: selectedLocationForSub.value
        });

        const newSubLocationData = response.data;

        // Add the new sub-location to the list
        subLocations.value = [...subLocations.value, newSubLocationData];

        // Set it as the selected sub-location
        form.value.sub_location_id = newSubLocationData;

        // Clear the form and close modal
        newSubLocation.value = '';
        showSubLocationModal.value = false;

        toast.success('Sub-location created successfully');
    } catch (error) {
        console.error('Error creating sub-location:', error);
        toast.error(error.response?.data || 'Error creating sub-location');
    }
};

const submit = async () => {
    processing.value = true;
    form.value.asset_category_id = form.value.asset_category_id?.id;
    form.value.asset_location_id = form.value.asset_location_id?.id;
    form.value.sub_location_id = form.value.sub_location_id?.id;
    console.log(form.value);
    await axios.post(route('assets.store'), form.value)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                title: 'Success!',
                text: 'Asset created successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.get(route('assets.index'));
            });

        })
        .catch((error) => {
            processing.value = false;
            console.log(error.response.data);
            toast.error(error.response?.data || 'Error creating asset');
        })
};
</script>

<template>
    <AuthenticatedLayout title="Assets management">

        <Head title="Create Asset" />
        <div class="">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Create New Asset</h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="asset_tag" value="Asset Tag" />
                            <TextInput id="asset_tag" type="text" class="mt-1 block w-full" v-model="form.asset_tag"
                                required />
                        </div>

                        <div>
                            <InputLabel for="asset_category" value="Asset Category" />
                            <div class="w-full">
                                <Multiselect v-model="form.asset_category_id" :value="form.asset_category_id"
                                    :options="categoryOptions" :searchable="true" :close-on-select="true"
                                    :show-labels="false" :allow-empty="true" placeholder="Select Category" track-by="id"
                                    label="name" @select="handleCategorySelect">
                                    <template v-slot:option="{ option }">
                                        <div :class="{ 'add-new-option': option.isAddNew }">
                                            <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add
                                                New Category</span>
                                            <span v-else>{{ option.name }}</span>
                                        </div>
                                    </template>
                                </Multiselect>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="serial_number" value="Serial Number" />
                            <TextInput id="serial_number" type="text" class="mt-1 block w-full"
                                v-model="form.serial_number" required />
                        </div>

                        <div>
                            <InputLabel for="item_description" value="Description" />
                            <TextInput id="item_description" type="text" class="mt-1 block w-full"
                                v-model="form.item_description" required />
                        </div>

                        <div>
                            <InputLabel for="person_assigned" value="Person Assigned" />
                            <TextInput id="person_assigned" type="text" class="mt-1 block w-full"
                                v-model="form.person_assigned" required />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="location" value="Location" />
                                <div class="w-full">
                                    <Multiselect v-model="form.asset_location_id" :value="form.asset_location_id"
                                        :options="locationOptions" :searchable="true" :close-on-select="true"
                                        :show-labels="false" :allow-empty="true" placeholder="Select Location"
                                        track-by="id" label="name" @select="handleLocationSelect">
                                        <template v-slot:option="{ option }">
                                            <div :class="{ 'add-new-option': option.isAddNew }">
                                                <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add
                                                    New Location</span>
                                                <span v-else>{{ option.name }}</span>
                                            </div>
                                        </template>
                                    </Multiselect>
                                </div>
                            </div>

                            <div>
                                <InputLabel for="sub_location" value="Sub Location" />
                                <div class="w-full">
                                    <Multiselect v-model="form.sub_location_id"
                                        :options="[...subLocations, { id: 'new', name: '+ Add New Sub-location', isAddNew: true }]"
                                        :searchable="true" :close-on-select="true" :show-labels="false"
                                        :allow-empty="true" placeholder="Select Sub-location" track-by="id" label="name"
                                        :disabled="!form.asset_location_id" @select="handleSubLocationSelect">
                                        <template v-slot:option="{ option }">
                                            <div :class="{ 'add-new-option': option.isAddNew }">
                                                <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add
                                                    New Sub-location</span>
                                                <span v-else>{{ option.name }}</span>
                                            </div>
                                        </template>
                                    </Multiselect>
                                </div>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="acquisition_date" value="Acquisition Date" />
                            <TextInput id="acquisition_date" type="date" class="mt-1 block w-full"
                                v-model="form.acquisition_date" required />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status" />
                            <select id="status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.status" required>
                                <option v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <InputLabel for="original_value" value="Original Value" />
                            <TextInput id="original_value" type="number" step="0.01" class="mt-1 block w-full"
                                v-model="form.original_value" required />
                        </div>

                        <div>
                            <InputLabel for="source_agency" value="Source Agency" />
                            <TextInput id="source_agency" type="text" class="mt-1 block w-full"
                                v-model="form.source_agency" required />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <Link :href="route('assets.index')" :disabled="processing">Exit</Link>
                        <PrimaryButton class="ml-4" :disabled="processing">
                            {{ processing ? 'Saving...' : 'Create Asset' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
        <!-- New Location Modal -->
        <Modal :show="showLocationModal" @close="showLocationModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Location</h2>
                <div class="mt-6">
                    <InputLabel for="new_location" value="Location Name" />
                    <TextInput id="new_location" type="text" class="mt-1 block w-full" v-model="newLocation" required />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showLocationModal = false" :disabled="isNewLocation">Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="isNewLocation" @click="createLocation">{{ isNewLocation ? "Waiting..." :
                        "Create new location" }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- New Sub-Location Modal -->
        <Modal :show="showSubLocationModal" @close="showSubLocationModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Sub-Location</h2>
                <div class="mt-6">
                    <InputLabel for="new_sub_location" value="Sub-Location Name" />
                    <TextInput id="new_sub_location" type="text" class="mt-1 block w-full" v-model="newSubLocation"
                        required />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showSubLocationModal = false" :disabled="isNewLocation">Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="isNewLocation" @click="createSubLocation">{{ isNewLocation ? "Waiting..."
                        :
                        "Create Sub-Location" }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- New Category Modal -->
        <Modal :show="showCategoryModal" @close="showCategoryModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Category</h2>
                <div class="mt-6">
                    <InputLabel for="new_category" value="Category Name" />
                    <TextInput id="new_category" type="text" class="mt-1 block w-full" v-model="newCategory" required />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showCategoryModal = false" :disabled="isNewCategory">Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="isNewCategory" @click="createCategory">{{ isNewCategory ? "Waiting..." :
                        "Create Category" }}</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
