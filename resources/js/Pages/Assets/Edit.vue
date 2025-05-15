<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

const props = defineProps({
    asset: {
        type: Object,
        required: true
    },
    locations: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
});

const toast = useToast();

// Helper function to find item by id in array
const findById = (array, id) => array.find(item => item.id === id);

const form = ref({
    id: props.asset.id,
    asset_tag: props.asset.asset_tag,
    asset_category_id: findById(props.categories, props.asset.asset_category_id),
    serial_number: props.asset.serial_number,
    item_description: props.asset.item_description,
    person_assigned: props.asset.person_assigned,
    asset_location_id: findById(props.locations, props.asset.asset_location_id),
    sub_location_id: null, // Will be set after loading sub-locations
    acquisition_date: props.asset.acquisition_date,
    status: props.asset.status,
    original_value: props.asset.original_value,
    source_agency: props.asset.source_agency
});

const subLocations = ref([]);
const locationOptions = ref([]);
const categoryOptions = ref([]);

watch(() => props.locations, (newLocations) => {
    locationOptions.value = [...newLocations, { id: 'new', name: '+ Add New Location', isAddNew: true }];
}, { immediate: true, deep: true });

watch(() => props.categories, (newCategories) => {
    categoryOptions.value = [...newCategories, { id: 'new', name: '+ Add New Category', isAddNew: true }];
}, { immediate: true, deep: true });

const loadSubLocations = async (locationId) => {
    if (!locationId) {
        subLocations.value = [];
        form.value.sub_location_id = null;
        return;
    }
    try {
        const response = await axios.get(route('assets.locations.sub-locations', locationId));
        subLocations.value = [...response.data, { id: 'new', name: '+ Add New Sub-location', isAddNew: true }];
        
        // If we have a sub_location_id from the asset, set it now
        if (props.asset.sub_location_id) {
            const subLocation = response.data.find(sub => sub.id === props.asset.sub_location_id);
            if (subLocation) {
                form.value.sub_location_id = subLocation;
            }
        }
    } catch (error) {
        toast.error('Error loading sub-locations');
    }
};

onMounted(() => {
    if (form.value.asset_location_id?.id) {
        loadSubLocations(form.value.asset_location_id.id);
    }
});

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
const showCategoryModal = ref(false);
const newLocation = ref('');
const newSubLocation = ref('');
const newCategory = ref('');
const selectedLocationForSub = ref(null);
const isNewCategory = ref(false);

const handleLocationSelect = (selected) => {
    if (!selected) {
        form.value.asset_location_id = null;
        selectedLocationForSub.value = null;
        form.value.sub_location_id = null;
        subLocations.value = [];
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = form.value.asset_location_id;
        showLocationModal.value = true;
        form.value.asset_location_id = currentSelection;
        return;
    }

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
        const currentSelection = form.value.sub_location_id;
        selectedLocationForSub.value = form.value.asset_location_id;
        showSubLocationModal.value = true;
        form.value.sub_location_id = currentSelection;
        return;
    }

    form.value.sub_location_id = selected;
};

const handleCategorySelect = (selected) => {
    console.log('Category selected:', selected);
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

const createLocation = async () => {
    if (!newLocation.value) {
        toast.error('Please enter a location name');
        return;
    }

    try {
        const response = await axios.post(route('assets.locations.store'), {
            name: newLocation.value
        });

        const newLocationData = response.data;
        
        // Add the new location to locationOptions
        locationOptions.value = [...locationOptions.value.filter(loc => !loc.isAddNew), newLocationData, { id: 'new', name: '+ Add New Location', isAddNew: true }];
        
        // Update the form with the new location
        form.value.asset_location_id = newLocationData;
        selectedLocationForSub.value = newLocationData.id;
        
        // Clear modal data
        newLocation.value = '';
        showLocationModal.value = false;
        
        toast.success('Location created successfully');
        await loadSubLocations(newLocationData.id);
    } catch (error) {
        toast.error(error.response?.data || 'Error creating location');
    }
};

const createSubLocation = async () => {
    if (!newSubLocation.value || !selectedLocationForSub.value) {
        toast.error('Please enter a sub-location name');
        return;
    }

    try {
        const response = await axios.post(route('assets.locations.sub-locations.store'), {
            name: newSubLocation.value,
            asset_location_id: selectedLocationForSub.value
        });

        const newSubLocationData = response.data;
        
        // Add the new sub-location to the list
        subLocations.value = [...subLocations.value.filter(sub => !sub.isAddNew), newSubLocationData, { id: 'new', name: '+ Add New Sub-location', isAddNew: true }];
        
        // Update the form with the new sub-location
        form.value.sub_location_id = newSubLocationData;
        
        // Clear modal data
        newSubLocation.value = '';
        showSubLocationModal.value = false;
        
        toast.success('Sub-location created successfully');
    } catch (error) {
        toast.error(error.response?.data || 'Error creating sub-location');
    }
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

const submit = async () => {
    processing.value = true;
    const formData = { ...form.value };
    formData.asset_category_id = formData.asset_category_id?.id;
    formData.asset_location_id = formData.asset_location_id?.id;
    formData.sub_location_id = formData.sub_location_id?.id;
    
    await axios.put(route('assets.update', props.asset.id), formData)
        .then((response) => {
            processing.value =false;
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
    <AuthenticatedLayout>
        <Head title="Edit Asset" />

        <div class="overflow-hidden mb-[70px]">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Edit Asset</h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="asset_tag" value="Asset Tag" />
                            <TextInput
                                id="asset_tag"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.asset_tag"
                                required
                            />
                        </div>

                        <div>
                            <InputLabel for="asset_category" value="Asset Category" />
                            <div class="w-full">
                                <Multiselect v-model="form.asset_category_id"
                                    :value="form.asset_category_id"
                                    :options="categoryOptions"
                                    :searchable="true" :close-on-select="true" :show-labels="false"
                                    :allow-empty="true" placeholder="Select Category" track-by="id" label="name"
                                    @select="handleCategorySelect">
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
                            <TextInput
                                id="serial_number"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.serial_number"
                                required
                            />
                        </div>

                        <div>
                            <InputLabel for="item_description" value="Description" />
                            <TextInput
                                id="item_description"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.item_description"
                                required
                            />
                        </div>

                        <div>
                            <InputLabel for="person_assigned" value="Person Assigned" />
                            <TextInput
                                id="person_assigned"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.person_assigned"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <InputLabel for="location" value="Location" />
                                <div class="w-full">
                                    <Multiselect
                                        v-model="form.asset_location_id"
                                        :options="[...props.locations, { id: 'new', name: '+ Add New Location', isAddNew: true }]"
                                        :searchable="true"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        :allow-empty="true"
                                        placeholder="Select Location"
                                        track-by="id"
                                        label="name"
                                        @select="handleLocationSelect"
                                    >
                                        <template v-slot:option="{ option }">
                                            <div :class="{ 'add-new-option': option.isAddNew }">
                                                <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New Location</span>
                                                <span v-else>{{ option.name }}</span>
                                            </div>
                                        </template>
                                    </Multiselect>
                                </div>
                            </div>

                            <div>
                                <InputLabel for="sub_location" value="Sub Location" />
                                <div class="w-full">
                                    <Multiselect
                                        v-model="form.sub_location_id"
                                        :options="[...subLocations, { id: 'new', name: '+ Add New Sub-location', isAddNew: true }]"
                                        :searchable="true"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        :allow-empty="true"
                                        placeholder="Select Sub-location"
                                        track-by="id"
                                        label="name"
                                        :disabled="!form.asset_location_id"
                                        @select="handleSubLocationSelect"
                                    >
                                        <template v-slot:option="{ option }">
                                            <div :class="{ 'add-new-option': option.isAddNew }">
                                                <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New Sub-location</span>
                                                <span v-else>{{ option.name }}</span>
                                            </div>
                                        </template>
                                    </Multiselect>
                                </div>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="acquisition_date" value="Acquisition Date" />
                            <TextInput
                                id="acquisition_date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.acquisition_date"
                                required
                            />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status" />
                                <select
                                id="status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.status"
                                required
                            >
                                <option v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <InputLabel for="original_value" value="Original Value" />
                            <TextInput
                                id="original_value"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                                v-model="form.original_value"
                                required
                            />
                        </div>

                        <div>
                            <InputLabel for="source_agency" value="Source Agency" />
                            <TextInput
                                id="source_agency"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.source_agency"
                                required
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <Link :href="route('assets.index')" class="ml-4" :disabled="processing">
                            Exit
                        </Link>
                        <PrimaryButton class="ml-4" :disabled="processing">
                            {{  processing ? 'Saving...' : 'Update Asset' }}
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
                    <TextInput
                        id="new_location"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="newLocation"
                        required
                    />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showLocationModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="createLocation">Create Location</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- New Sub-Location Modal -->
        <Modal :show="showSubLocationModal" @close="showSubLocationModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Sub-Location</h2>
                <div class="mt-6">
                    <InputLabel for="new_sub_location" value="Sub-Location Name" />
                    <TextInput
                        id="new_sub_location"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="newSubLocation"
                        required
                    />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showSubLocationModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="createSubLocation">Create Sub-Location</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- New Category Modal -->
        <Modal :show="showCategoryModal" @close="showCategoryModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Category</h2>
                <div class="mt-6">
                    <InputLabel for="new_category" value="Category Name" />
                    <TextInput
                        id="new_category"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="newCategory"
                        required
                    />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showCategoryModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="createCategory" :disabled="isNewCategory">Create Category</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
