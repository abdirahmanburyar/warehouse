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
import { ref, onMounted, watch, computed } from 'vue';
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
    },
    regions: {
        type: Array,
        required: true
    },
    fundSources: {
        type: Array,
        required: true
    }
});

const toast = useToast();

// Assignee handling (parity with Create.vue)
const showAssigneeModal = ref(false);
const newAssignee = ref({ name: '', email: '', phone: '', department: '' });
const assigneeOptions = computed(() => [
    { id: 'new', name: '+ Add New Assignee', isAddNew: true },
]);

const onAssigneeSelect = (opt) => {
    if (!opt) return;
    if (opt.isAddNew) {
        showAssigneeModal.value = true;
        return;
    }
    form.value.assigned_to = opt.id;
    form.value.assignee_id = null;
};

const onAssigneeClear = () => {
    form.value.assigned_to = '';
    form.value.assignee_id = null;
};

const createAssignee = async () => {
    if (!newAssignee.value.name) {
        toast.error('Full name is required');
        return;
    }
    try {
        const { data } = await axios.post(route('assets.assignees.store'), newAssignee.value);
        form.value.assignee_id = data.id;
        form.value.assigned_to = '';
        newAssignee.value = { name: '', email: '', phone: '', department: '' };
        showAssigneeModal.value = false;
        toast.success('Assignee created');
    } catch (e) {
        toast.error(e.response?.data || 'Failed to create assignee');
    }
};

// Helper function to find item by id in array
const findById = (array, id) => (Array.isArray(array) ? array.find(item => item.id === id) : '');

// Helper function to format date for HTML5 date input (YYYY-MM-DD)
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    // Extract just the date part (YYYY-MM-DD) from datetime string
    return dateString.split(' ')[0];
};

// Warranty date validation function
const validateWarrantyDates = () => {
    warrantyDateError.value = "";
    
    if (form.value.asset_warranty_start && form.value.asset_warranty_end) {
        const startDate = new Date(form.value.asset_warranty_start);
        const endDate = new Date(form.value.asset_warranty_end);
        
        if (endDate <= startDate) {
            warrantyDateError.value = "End date must be after start date";
            return false;
        }
    }
    return true;
};

function handleFundSourceSelect(selected) {
    if (!selected) {
        form.value.fund_source_id = '';
        form.value.fund_source = '';
        return;
    }
    form.value.fund_source_id = selected.id;
    form.value.fund_source = selected;
}

function handleRegionSelect(selected) {
    if (!selected) {
        form.value.region_id = '';
        form.value.region = '';
        return;
    }
    form.value.region_id = selected.id;
    form.value.region = selected;
}

const warrantyDateError = ref("");

// Initialize form with properly formatted dates
const form = ref({
    ...props.asset,
    tag_no: props.asset?.tag_no || '',
    name: props.asset?.name || '',
    asset_category_id: props.asset?.asset_category_id || null,
    type_id: props.asset?.type_id || null,
    serial_number: props.asset?.serial_number || '',
    serial_no: props.asset?.serial_no || '',
    item_description: props.asset?.item_description || '',
    person_assigned: props.asset?.person_assigned || '',
    assigned_to: props.asset?.assigned_to || '',
    assignee_id: props.asset?.assignee_id || null,
    acquisition_date: formatDateForInput(props.asset?.acquisition_date),
    asset_warranty_start: formatDateForInput(props.asset?.asset_warranty_start),
    asset_warranty_end: formatDateForInput(props.asset?.asset_warranty_end),
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
    // Validate warranty dates if warranty is enabled
    if (form.value.has_warranty && !validateWarrantyDates()) {
        return;
    }

    processing.value = true;

    await axios.put(route('assets.update', props.asset.id), form.value)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                title: 'Success!',
                text: 'Asset updated successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.get(route('assets.index'));
            });

        })
        .catch((error) => {
            processing.value = false;
            console.log(error.response.data);
            toast.error(error.response?.data || 'Error updating asset');
        })
};
</script>

<template>
    <AuthenticatedLayout title="Assets management" description="Edit asset" img="/assets/images/asset-header.png">

        <Head title="Edit Asset" />

        <div class="overflow-hidden mb-[70px]">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Edit Asset</h2>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <InputLabel for="asset_tag" value="Asset Tag" />
                            <TextInput id="asset_tag" type="text" class="mt-1 block w-full" v-model="form.asset_tag"
                                required />
                        </div>
                        <div>
                            <InputLabel for="serial_number" value="Serial Number" />
                            <TextInput id="serial_number" type="text" class="mt-1 block w-full"
                                v-model="form.serial_number" required />
                        </div>
                        <div>
                            <InputLabel for="tag_no" value="Tag No" />
                            <TextInput id="tag_no" type="text" class="mt-1 block w-full" v-model="form.tag_no" />
                        </div>
                        <div>
                            <InputLabel for="asset_category" value="Asset Category" />
                            <div class="w-full">
                                <Multiselect v-model="form.category"
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
                    </div>
                    <!-- Assignee selector -->
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <InputLabel for="assignee" value="Assignee / User" />
                            <Multiselect
                                :options="assigneeOptions"
                                :searchable="false"
                                :close-on-select="true"
                                :show-labels="false"
                                placeholder="Select User or + Add New Assignee"
                                label="name"
                                track-by="id"
                                @select="onAssigneeSelect"
                                @remove="onAssigneeClear"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">

                        <div>
                            <InputLabel for="name" value="Asset Name" />
                            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" />
                        </div>

                        <div>
                            <InputLabel for="person_assigned" value="Person Assigned" />
                            <TextInput id="person_assigned" type="text" class="mt-1 block w-full"
                                v-model="form.person_assigned" />
                        </div>

                        <div>
                            <InputLabel for="acquisition_date" value="Acquisition Date" />
                            <input id="acquisition_date" type="date" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.acquisition_date" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <InputLabel for="region" value="Region" />
                            <Multiselect v-model="form.region" :options="props.regions" :searchable="true"
                                :close-on-select="true" :show-labels="false" :allow-empty="true"
                                placeholder="Select Region" track-by="id" label="name" @select="handleRegionSelect">
                                <template v-slot:option="{ option }">
                                    <div :class="{
                                        'add-new-option': option.isAddNew,
                                    }">
                                        <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New
                                            Region</span>
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                        </div>

                        <div>
                            <InputLabel for="location" value="Location" />
                            <div class="w-full">
                                <Multiselect v-model="form.location"
                                    :options="[...props.locations, { id: 'new', name: '+ Add New Location', isAddNew: true }]"
                                    :searchable="true" :close-on-select="true" :show-labels="false" :allow-empty="true"
                                    placeholder="Select Location" track-by="id" label="name"
                                    @select="handleLocationSelect">
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
                                <Multiselect v-model="form.sub_location"
                                    :options="[...subLocations, { id: 'new', name: '+ Add New Sub-location', isAddNew: true }]"
                                    :searchable="true" :close-on-select="true" :show-labels="false" :allow-empty="true"
                                    placeholder="Select Sub-location" track-by="id" label="name"
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
                    <div class="grid grid-cols-3 gap-1">
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
                            <InputLabel for="fund_source" value="Source Agency (Fund Source)" />
                            <Multiselect id="fund_source" v-model="form.fund_source" :options="props.fundSources"
                                :close-on-select="true" :show-labels="false" :allow-empty="true"
                                placeholder="Select Fund Source" track-by="id" label="name"
                                @select="handleFundSourceSelect">
                                <template v-slot:option="{ option }">
                                    <div :class="{
                                        'add-new-option': option.isAddNew,
                                    }">
                                        <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New Fund
                                            Source</span>
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                        </div>
                    </div>
                    <!-- Warranty Section -->
                    <div class="mt-4 bg-white rounded-xl shadow border">
                        <div class="flex items-center justify-between p-4 border-b">
                            <div class="font-semibold">Warranty</div>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.has_warranty" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                <span class="text-sm text-gray-700">Has warranty</span>
                            </label>
                        </div>
                        <div v-if="form.has_warranty" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="asset_warranty_start" value="Start Date" />
                                <input type="date" v-model="form.asset_warranty_start" id="asset_warranty_start"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <InputLabel for="asset_warranty_end" value="End Date" />
                                <input type="date" v-model="form.asset_warranty_end" id="asset_warranty_end"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                <div v-if="warrantyDateError" class="text-red-500 text-sm mt-1">{{ warrantyDateError }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-6">
                        <Link :href="route('assets.index')" class="ml-4" :disabled="processing">
                        Exit
                        </Link>
                        <PrimaryButton class="ml-4" :disabled="processing">
                            {{ processing ? 'Saving...' : 'Update Asset' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
        <!-- New Assignee Modal -->
        <Modal :show="showAssigneeModal" @close="showAssigneeModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Assignee</h2>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="new_assignee_name" value="Full Name" />
                        <TextInput id="new_assignee_name" name="new_assignee_name" type="text" class="mt-1 block w-full" placeholder="e.g., John Doe" v-model="newAssignee.name" />
                    </div>
                    <div>
                        <InputLabel for="new_assignee_email" value="Email (optional)" />
                        <TextInput id="new_assignee_email" type="email" class="mt-1 block w-full" placeholder="name@example.com" v-model="newAssignee.email" />
                    </div>
                    <div>
                        <InputLabel for="new_assignee_phone" value="Phone" />
                        <TextInput id="new_assignee_phone" name="new_assignee_phone" type="text" class="mt-1 block w-full" placeholder="e.g., +1 555 123 4567" v-model="newAssignee.phone" />
                    </div>
                    <div>
                        <InputLabel for="new_assignee_department" value="Department" />
                        <TextInput id="new_assignee_department" name="new_assignee_department" type="text" class="mt-1 block w-full" placeholder="e.g., IT" v-model="newAssignee.department" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showAssigneeModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="createAssignee">Save</PrimaryButton>
                </div>
            </div>
        </Modal>
        <!-- New Location Modal -->
        <Modal :show="showLocationModal" @close="showLocationModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Location</h2>
                <div class="mt-6">
                    <InputLabel for="new_location" value="Location Name" />
                    <TextInput id="new_location" type="text" class="mt-1 block w-full" v-model="newLocation" required />
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
                    <TextInput id="new_sub_location" type="text" class="mt-1 block w-full" v-model="newSubLocation"
                        required />
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
                    <TextInput id="new_category" type="text" class="mt-1 block w-full" v-model="newCategory" required />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showCategoryModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="createCategory" :disabled="isNewCategory">Create Category</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
