<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, Link } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ref, watch, computed } from "vue";
import { useToast } from "vue-toastification";
import axios from "axios";
import Swal from "sweetalert2";
import moment from "moment";


const toast = useToast();
const processing = ref(false);
const isSubmitting = ref(false);

const props = defineProps({
    locations: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
        default: () => [],
    },
    types: {
        type: Array,
        required: false,
        default: () => [],
    },
    fundSources: {
        type: Array,
        required: true,
        default: () => [],
    },
    regions: {
        type: Array,
        required: true,
        default: () => [],
    },
    users: {
        type: Array,
        required: false,
        default: () => [],
    },
    assignees: {
        type: Array,
        required: false,
        default: () => [],
    },
});

const locationOptions = ref([]);
const categoryOptions = ref([]);
const fundSourceOptions = ref([]);
const regionOptions = ref([]);

watch(
    () => props.categories,
    (newCategories) => {
        categoryOptions.value = [
            { id: "new", name: "+ Add New Category", isAddNew: true },
            ...newCategories,
        ];
    },
    { immediate: true, deep: true }
);
const typeOptions = ref([]);
watch(
    () => props.types,
    (newTypes) => {
        typeOptions.value = [
            { id: "new", name: "+ Add New Type", isAddNew: true },
            ...newTypes,
        ];
    },
    { immediate: true, deep: true }
);

const filteredTypeOptions = computed(() => {
    const categoryId = form.value.asset_category_id || form.value.asset_category?.id;
    if (!categoryId) {
        return [{ id: "new", name: "+ Add New Type", isAddNew: true }];
    }
    const base = typeOptions.value.filter(
        t => !t.isAddNew && (!t.asset_category_id || t.asset_category_id === categoryId)
    );
    // Always append the add-new option at the end
    return [
        ...base,
        { id: "new", name: "+ Add New Type", isAddNew: true },
    ];
});

// Will be attached after form is defined to avoid temporal dead zone
let detachCategoryWatch = null;

const usersAsOptions = computed(() => (props.users || []).map(u => ({ id: u.id, name: u.name })));

const assigneeOptions = computed(() => [
    { id: 'new', name: '+ Add New Assignee', isAddNew: true },
    ...((props.assignees || []).map(a => ({ id: a.id, name: a.name })))
]);

const showAssigneeModal = ref(false);
const newAssignee = ref({ name: '', email: '', phone: '', department: '' });

const onAssigneeSelect = (opt) => {
    if (!opt) return;
    if (opt.isAddNew) {
        showAssigneeModal.value = true;
        return;
    }
    // Selecting from this dropdown chooses an Assignee, not a User
    form.value.assignee_id = opt.id;
    form.value.assigned_to = '';
    // clear manual assignee fields
    form.value.assignee_name = '';
    form.value.assignee_email = '';
    form.value.assignee_phone = '';
    form.value.assignee_department = '';
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
        const { data } = await axios.post(route('assets.assignees.store'), {
            name: newAssignee.value.name,
            email: newAssignee.value.email || null,
            phone: newAssignee.value.phone || null,
            department: newAssignee.value.department || null,
        });
        form.value.assigned_user = { id: null, name: data.name };
        form.value.assignee_id = data.id;
        form.value.assigned_to = '';
        newAssignee.value = { name: '', email: '', phone: '', department: '' };
        showAssigneeModal.value = false;
        toast.success('Assignee created');
    } catch (e) {
        toast.error(e.response?.data || 'Failed to create assignee');
    }
};

const showTypeModal = ref(false);
const newType = ref("");
const isNewType = ref(false);

const handleTypeSelect = (selected) => {
    if (!selected) {
        form.value.type_id = null;
        form.value.type = null;
        return;
    }
    if (selected.isAddNew) {
        showTypeModal.value = true;
        return;
    }
    form.value.type_id = selected.id;
    form.value.type = selected;
};

const createType = async () => {
    if (!newType.value) {
        toast.error("Please enter a type name");
        return;
    }
    isNewType.value = true;
    try {
        const response = await axios.post(route('assets.types.store'), {
            name: newType.value,
            asset_category_id: form.value.asset_category_id || (form.value.asset_category?.id ?? null),
        });

        const newTypeData = response.data;
        typeOptions.value = [
            ...typeOptions.value.filter(t => !t.isAddNew),
            newTypeData,
            { id: "new", name: "+ Add New Type", isAddNew: true },
        ];
        form.value.type = newTypeData;
        form.value.type_id = newTypeData.id;
        newType.value = "";
        showTypeModal.value = false;
        toast.success("Type created successfully");
    } catch (error) {
        console.error("Error creating type:", error);
        toast.error(error.response?.data || "Error creating type");
    } finally {
        isNewType.value = false;
    }
};

watch(
    () => props.locations,
    (newLocations) => {
        locationOptions.value = [
            { id: "new", name: "+ Add New Location", isAddNew: true },
            ...newLocations,
        ];
    },
    { immediate: true, deep: true }
);

watch(
    () => props.fundSources,
    (newFundSources) => {
        fundSourceOptions.value = [
            { id: "new", name: "+ Add New Fund Source", isAddNew: true },
            ...newFundSources,
        ];
    },
    { immediate: true, deep: true }
);

watch(
    () => props.regions,
    (newRegions) => {
        regionOptions.value = [
            { id: "new", name: "+ Add New Region", isAddNew: true },
            ...newRegions,
        ];
    },
    { immediate: true, deep: true }
);

const form = ref({
    tag_no: "",
    name: "",
    asset_tag: "",
    asset_category_id: null,
    asset_category: null,
    type_id: "",
    type: null,
    serial_number: "",
    serial_no: "",
    item_description: "",
    person_assigned: "",
    assigned_to: "",
    assignee_id: null,
    assigned_user: null,
    assignee_name: "",
    assignee_email: "",
    assignee_phone: "",
    assignee_department: "",
    asset_location_id: "",
    asset_location: null,
    sub_location_id: "",
    sub_location: null,
    acquisition_date: "",
    cost: "",
    supplier: "",
    has_warranty: false,
    asset_warranty_start: "",
    asset_warranty_end: "",
    warranty_months: "",
    maintenance_interval_months: 0,
    last_maintenance_at: "",
    has_documents: false,
    status: "active",
    original_value: "",
    fund_source_id: "",
    region_id: "",
    region: null,
    fund_source: null,
    documents: [{ type: "", file: "" }],
});

// Attach watcher after form is initialized
detachCategoryWatch = watch(() => form.value.asset_category, (newVal) => {
    if (!newVal) {
        form.value.type = null;
        form.value.type_id = null;
    }
});

const subLocations = ref([]);

const filteredSubLocations = computed(() => {
    if (!form.value.asset_location) return [];
    // If asset_location is a Multiselect object, use its id
    const locId = form.value.asset_location.id || form.value.asset_location;
    return subLocations.value.filter((sub) => sub.location_id === locId);
});

function onLocationChange(selected) {
    // Clear sub-location if location changes
    form.value.sub_location = null;
    // If you want to trigger loading sublocations via API, uncomment next line:
    // loadSubLocations(selected ? selected.id : null);
}

const loadSubLocations = async (locationId) => {
    if (!locationId) {
        subLocations.value = [];
        form.value.sub_location_id = "";
        return;
    }
    try {
        const response = await axios.get(
            route("assets.locations.sub-locations", { location: locationId })
        );
        subLocations.value = response.data;
    } catch (error) {
        console.log(error);
        toast.error("Error loading sub-locations");
    }
};

watch(
    () => form.value.asset_location_id,
    (newValue) => {
        loadSubLocations(newValue);
    }
);

const statuses = ref([
    { value: "active", label: "Active" },
    { value: "in_use", label: "In Use" },
    { value: "maintenance", label: "Maintenance" },
    { value: "retired", label: "Retired" },
    { value: "disposed", label: "Disposed" },
]);

const showLocationModal = ref(false);
const showSubLocationModal = ref(false);
const newLocation = ref("");
const newSubLocation = ref("");
const newCategory = ref("");
const newRegion = ref("");
const newFundSource = ref("");
const showCategoryModal = ref(false);
const showFundSourceModal = ref(false);
const showRegionModal = ref(false);
const isNewRegion = ref(false);
const isNewCategory = ref(false);
const isNewFundSource = ref(false);
const selectedLocationForSub = ref(null);
const warrantyDateError = ref("");

const handleLocationSelect = (selected) => {
    if (!selected) {
        // Handle clearing the selection
        form.value.asset_location_id = null;
        selectedLocationForSub.value = null;
        form.value.sub_location_id = null;
        form.value.sub_location = null;
        subLocations.value = [];
        return;
    }

    if (selected.isAddNew) {
        // Show the modal and keep the previous selection
        const currentSelection = form.value.asset_location;
        showLocationModal.value = true;
        form.value.asset_location = currentSelection;
        form.value.asset_location_id = currentSelection.id;
        return;
    }

    // Handle normal selection
    form.value.asset_location_id = selected.id;
    form.value.asset_location = selected;
    selectedLocationForSub.value = selected.id;
    loadSubLocations(selected.id);
    form.value.sub_location_id = null;
    form.value.sub_location = null;
};

const handleSubLocationSelect = (selected) => {
    if (!selected) {
        form.value.sub_location_id = null;
        form.value.sub_location = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = form.value.asset_location;
        showSubLocationModal.value = true;
        form.value.asset_location = currentSelection;
        form.value.asset_location_id = currentSelection.id;
        return;
    }

    form.value.sub_location_id = selected.id;
    form.value.sub_location = selected;
};

const isNewLocation = ref(false);
const createLocation = async () => {
    if (!newLocation.value) return;
    isNewLocation.value = true;
    try {
        const response = await axios.post(route("assets.locations.store"), {
            name: newLocation.value,
        });
        const newLocationData = response.data;

        // Add the new location to locationOptions
        locationOptions.value = [
            ...locationOptions.value.filter((loc) => !loc.isAddNew),
            newLocationData,
            { id: "new", name: "+ Add New Location", isAddNew: true },
        ];

        // Select the new location in the multiselect
        form.value.asset_location = newLocationData;
        form.value.asset_location_id = newLocationData.id;
        selectedLocationForSub.value = newLocationData.id;

        // Clear modal data
        newLocation.value = "";
        showLocationModal.value = false;

        // Load sub-locations for the new location
        await loadSubLocations(newLocationData.id);

        toast.success("Location created successfully");
    } catch (error) {
        console.error(error);
        // toast.error(error.response?.data || 'Error creating location');
    } finally {
        isNewLocation.value = false;
    }
};

const createRegion = async () => {
    if (!newRegion.value) {
        toast.error("Please enter a region name");
        return;
    }

    isNewRegion.value = true;

    try {
        const response = await axios.post(route("assets.regions.store"), {
            name: newRegion.value,
        });

        isNewRegion.value = false;

        const newRegionData = response.data;

        // Add the new region to regionOptions
        regionOptions.value = [
            ...regionOptions.value.filter((reg) => !reg.isAddNew),
            newRegionData,
            { id: "new", name: "+ Add New Region", isAddNew: true },
        ];

        // Select the new region in the multiselect
        form.value.region = newRegionData;
        form.value.region_id = newRegionData.id;

        // Clear modal data
        newRegion.value = "";
        showRegionModal.value = false;

        toast.success("Region created successfully");
    } catch (error) {
        isNewRegion.value = false;
        console.error("Error creating region:", error);
        toast.error(error.response?.data || "Error creating region");
    } finally {
        isNewRegion.value = false;
    }
};

const handleCategorySelect = (selected) => {
    if (!selected) {
        form.value.asset_category_id = null;
        form.value.asset_category = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = form.value.asset_category;
        showCategoryModal.value = true;
        form.value.asset_category_id = currentSelection.id;
        form.value.asset_category = currentSelection;
        return;
    }

    form.value.asset_category_id = selected.id;
    form.value.asset_category = selected;
};

const handleRegionSelect = (selected) => {
    if (!selected) {
        form.value.region_id = null;
        form.value.region = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = form.value.region;
        showRegionModal.value = true;
        form.value.region_id = currentSelection.id;
        return;
    }

    form.value.region_id = selected.id;
    form.value.region = selected;
};

const handleFundSourceSelect = (selected) => {
    if (!selected) {
        form.value.fund_source_id = null;
        form.value.fund_source = null;
        return;
    }

    if (selected.isAddNew) {
        const currentSelection = form.value.fund_source;
        showFundSourceModal.value = true;
        form.value.fund_source_id = currentSelection.id;
        return;
    }

    form.value.fund_source_id = selected.id;
    form.value.fund_source = selected;
};

const createCategory = async () => {
    if (!newCategory.value) {
        toast.error("Please enter a category name");
        return;
    }

    isNewCategory.value = true;
    try {
        const response = await axios.post(route('assets.categories.store'), {
            name: newCategory.value,
        });

        const newCategoryData = response.data;

        // Add the new category to categoryOptions
        categoryOptions.value = [
            ...categoryOptions.value.filter((cat) => !cat.isAddNew),
            newCategoryData,
            { id: "new", name: "+ Add New Category", isAddNew: true },
        ];

        // Select the new category in the multiselect
        form.value.asset_category = newCategoryData;
        form.value.asset_category_id = newCategoryData.id;
        
        // Clear modal data
        newCategory.value = "";
        showCategoryModal.value = false;

        toast.success("Category created successfully");
    } catch (error) {
        console.error("Error creating category:", error);
        toast.error(error.response?.data || "Error creating category");
    } finally {
        isNewCategory.value = false;
    }
};

const validateWarrantyDates = () => {
    warrantyDateError.value = "";

    if (
        form.value.has_warranty &&
        form.value.asset_warranty_start &&
        form.value.asset_warranty_end
    ) {
        const startDate = moment(form.value.asset_warranty_start);
        const endDate = moment(form.value.asset_warranty_end);

        if (!startDate.isValid() || !endDate.isValid()) {
            warrantyDateError.value = "Invalid date format";
            return false;
        }

        if (endDate.isBefore(startDate)) {
            warrantyDateError.value =
                "Warranty end date must be equal to or after start date";
            return false;
        }
    }
    return true;
};

const createFundSource = async () => {
    if (!newFundSource.value) {
        toast.error("Please enter a fund source name");
        return;
    }

    isNewFundSource.value = true;
    try {
        const response = await axios.post(route("assets.fund-sources.store"), {
            name: newFundSource.value,
        });

        isNewFundSource.value = false;

        const newFundSourceData = response.data;

        // Add the new fund source to fundSourceOptions
        fundSourceOptions.value = [
            ...fundSourceOptions.value.filter((fs) => !fs.isAddNew),
            newFundSourceData,
            { id: "new", name: "+ Add New Fund Source", isAddNew: true },
        ];

        // Select the new fund source in the multiselect
        form.value.fund_source = newFundSourceData;
        form.value.fund_source_id = newFundSourceData.id;

        // Clear modal data
        newFundSource.value = "";
        showFundSourceModal.value = false;

        toast.success("Fund Source created successfully");
    } catch (error) {
        isNewFundSource.value = false;
        console.error("Error creating fund source:", error);
        toast.error(error.response?.data || "Error creating fund source");
    } finally {
        isNewFundSource.value = false;
    }
};

const isSubLocationLoadint = ref(false);

const createSubLocation = async () => {
    if (!newSubLocation.value || !selectedLocationForSub.value) {
        toast.error("Please enter a sub-location name and select a location");
        return;
    }
    isNewLocation.value = true;

    try {
        const response = await axios.post(
            route("assets.locations.sub-locations.store"),
            {
                name: newSubLocation.value,
                asset_location_id: selectedLocationForSub.value,
            }
        );
        isNewLocation.value = false;

        const newSubLocationData = response.data;

        // Add the new sub-location to the list
        subLocations.value = [...subLocations.value, newSubLocationData];

        // Select the new sub-location in the multiselect
        form.value.sub_location = newSubLocationData;
        form.value.sub_location_id = newSubLocationData.id;

        // Clear the form and close modal
        newSubLocation.value = "";
        showSubLocationModal.value = false;

        toast.success("Sub-location created successfully");
    } catch (error) {
        isNewLocation.value = true;
        console.error("Error creating sub-location:", error);
        toast.error(error.response?.data || "Error creating sub-location");
    }
};

const submit = async () => {
    isSubmitting.value = true;

    // Debug location values
    console.log("Location values before submit:", {
        asset_location_id: form.value.asset_location_id,
        asset_location: form.value.asset_location,
        sub_location_id: form.value.sub_location_id,
        sub_location: form.value.sub_location,
    });

    // Validate warranty dates if has_warranty is checked
    if (form.value.has_warranty) {
        if (!validateWarrantyDates()) {
            isSubmitting.value = false;
            toast.error(
                "Please fix the warranty date issues before submitting"
            );
            return;
        }
    }
    processing.value = true;

    // Create a FormData object to handle file uploads
    const formData = new FormData();

    // Add all form fields to FormData
    Object.keys(form.value).forEach((key) => {
        // Skip documents as we'll handle them separately
        if (key !== "documents") {
            // Handle nested objects (like asset_category, region, etc.)
            if (
                typeof form.value[key] === "object" &&
                form.value[key] !== null
            ) {
                formData.append(key, form.value[key].id);
            } else if (typeof form.value[key] === "boolean") {
                // Convert boolean to 1/0 for PHP
                formData.append(key, form.value[key] ? "1" : "0");
            } else {
                formData.append(key, form.value[key]);
            }
        }
    });

    // Handle documents if has_documents is true
    if (form.value.has_documents) {
        form.value.documents.forEach((doc, index) => {
            if (doc.type) {
                formData.append(`documents[${index}][type]`, doc.type);
            }
            if (doc.file instanceof File) {
                formData.append(`documents[${index}][file]`, doc.file);
            }
        });
    }

    formData.append("asset_tag", form.value.asset_tag);
    formData.append("asset_category_id", form.value.asset_category_id);
    formData.append("serial_number", form.value.serial_number);
    formData.append("item_description", form.value.item_description);
    formData.append("region_id", form.value.region_id);
    formData.append("fund_source_id", form.value.fund_source_id);

    // Ensure location ID is properly set (use the ID from the object if available)
    const locationId =
        form.value.asset_location_id ||
        (form.value.asset_location ? form.value.asset_location.id : "");
    formData.append("asset_location_id", locationId);

    // Ensure sub-location ID is properly set
    const subLocationId =
        form.value.sub_location_id ||
        (form.value.sub_location ? form.value.sub_location.id : "");
    formData.append("sub_location_id", subLocationId);
    if (form.value.assigned_user?.id) formData.append("assigned_to", form.value.assigned_user.id);
    if (form.value.assignee_id) formData.append("assignee_id", form.value.assignee_id);
    formData.append("acquisition_date", form.value.acquisition_date);
    if (form.value.purchase_date) formData.append("purchase_date", form.value.purchase_date);
    if (form.value.cost) formData.append("cost", form.value.cost);
    if (form.value.supplier) formData.append("supplier", form.value.supplier);
    formData.append("status", form.value.status);
    formData.append("original_value", form.value.original_value);
    // Convert boolean values to 0/1 format for Laravel
    formData.append("has_warranty", form.value.has_warranty ? "1" : "0");
    formData.append("has_documents", form.value.has_documents ? "1" : "0");
    formData.append("asset_warranty_start", form.value.asset_warranty_start);
    formData.append("asset_warranty_end", form.value.asset_warranty_end);
    if (form.value.warranty_months !== "") formData.append("warranty_months", form.value.warranty_months);
    formData.append("maintenance_interval_months", form.value.maintenance_interval_months ?? 0);
    if (form.value.last_maintenance_at) formData.append("last_maintenance_at", form.value.last_maintenance_at);

    try {
        const response = await axios.post(route("assets.store"), formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        processing.value = false;
        isSubmitting.value = false;
        Swal.fire({
            title: "Success!",
            text: "Asset created successfully",
            icon: "success",
            confirmButtonText: "OK",
        }).then(() => {
            router.get(route("assets.index"));
        });
    } catch (error) {
        processing.value = false;
        isSubmitting.value = false;
        console.error("Error creating asset:", error);
        toast.error(error.response?.data || "Error creating asset");
    }
};

function addDocument() {
    form.value.documents.push({ type: "", file: "" });
}

function removeDocument(index) {
    form.value.documents.splice(index, 1);
}

function handleDocumentUpload(index, event) {
    form.value.documents[index].file = event.target.files[0];
}
</script>

<template>
    <AuthenticatedLayout title="Assets management" description="Create new asset" img="/assets/images/asset-header.png">
        <Head title="Create Asset" />
        <div class="mb-[80px]">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Create New Asset</h2>

                <form @submit.prevent="submit" novalidate class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-800 mt-4">Basic Details</h3>
                    <!-- Region, Location, SubLocation Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <InputLabel for="tag_no" value="Tag No" />
                            <TextInput id="tag_no" type="text" class="mt-1 block w-full" placeholder="e.g., AST-2025-001" v-model="form.tag_no" />
                        </div>
                        <div>
                            <InputLabel for="name" value="Asset Name" />
                            <TextInput id="name" type="text" class="mt-1 block w-full" placeholder="e.g., Dell Latitude 7420" v-model="form.name" />
                        </div>
                        <div>
                            <InputLabel for="asset_tag" value="Asset Tag" />
                            <TextInput
                                id="asset_tag"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Internal tag, e.g., INV-000123"
                                v-model="form.asset_tag"
                                required
                            />
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel
                                    for="asset_category"
                                    value="Asset Category"
                                />
                                <div class="w-full">
                                    <Multiselect
                                        v-model="form.asset_category"
                                        :options="categoryOptions"
                                        :searchable="true"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        :allow-empty="true"
                                        placeholder="Select Category"
                                        track-by="id"
                                        label="name"
                                        @select="handleCategorySelect"
                                    >
                                        <template v-slot:option="{ option }">
                                            <div
                                                :class="{
                                                    'add-new-option': option.isAddNew,
                                                }"
                                            >
                                                <span
                                                    v-if="option.isAddNew"
                                                    class="text-indigo-600 font-medium"
                                                    >+ Add New Category</span
                                                >
                                                <span v-else>{{ option.name }}</span>
                                            </div>
                                        </template>
                                    </Multiselect>
                                </div>
                            </div>
                            <div>
                                <InputLabel for="type" value="Asset Type" />
                                <Multiselect
                                    v-model="form.type"
                                    :options="filteredTypeOptions"
                                    :searchable="true"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    :allow-empty="true"
                                    placeholder="Select Type"
                                    track-by="id"
                                    label="name"
                                    :disabled="!form.asset_category && !form.asset_category_id"
                                    @select="handleTypeSelect"
                                >
                                    <template v-slot:option="{ option }">
                                        <div :class="{ 'add-new-option': option.isAddNew }">
                                            <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New Type</span>
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
                                placeholder="Manufacturer serial no"
                                v-model="form.serial_number"
                                required
                            />
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mt-8">Assignment</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="assigned_to" value="Assigned User (optional)" />
                            <Multiselect
                                v-model="form.assigned_user"
                                :options="assigneeOptions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                placeholder="Select user"
                                track-by="id"
                                label="name"
                                @select="onAssigneeSelect"
                                @clear="onAssigneeClear"
                            />
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
                    </div>
                    
                    <!-- New Assignee Modal -->
                    <Modal :show="showAssigneeModal" @close="showAssigneeModal = false">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900">Add New Assignee</h2>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="new_assignee_name" value="Full Name" />
                                    <TextInput id="new_assignee_name" name="new_assignee_name" type="text" class="mt-1 block w-full" placeholder="e.g., John Doe" :required="showAssigneeModal" v-model="newAssignee.name" />
                                </div>
                                <div>
                                    <InputLabel for="new_assignee_email" value="Email (optional)" />
                                    <TextInput id="new_assignee_email" type="email" class="mt-1 block w-full" placeholder="name@example.com" v-model="newAssignee.email" />
                                </div>
                                <div>
                                    <InputLabel for="new_assignee_phone" value="Phone" />
                                    <TextInput id="new_assignee_phone" name="new_assignee_phone" type="text" class="mt-1 block w-full" placeholder="e.g., +1 555 123 4567" :required="showAssigneeModal" v-model="newAssignee.phone" />
                                </div>
                                <div>
                                    <InputLabel for="new_assignee_department" value="Department" />
                                    <TextInput id="new_assignee_department" name="new_assignee_department" type="text" class="mt-1 block w-full" placeholder="e.g., IT" :required="showAssigneeModal" v-model="newAssignee.department" />
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end space-x-3">
                                <SecondaryButton @click="showAssigneeModal = false">Cancel</SecondaryButton>
                                <PrimaryButton @click="createAssignee">Save</PrimaryButton>
                            </div>
                        </div>
                    </Modal>
                    
                    
                    <h3 class="text-lg font-semibold text-gray-800 mt-8">Location & Classification</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="region" value="Region" />
                            <Multiselect
                                v-model="form.region"
                                :options="regionOptions"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                placeholder="Select Region"
                                track-by="id"
                                label="name"
                                @select="handleRegionSelect"
                            >
                                <template v-slot:option="{ option }">
                                    <div
                                        :class="{
                                            'add-new-option': option.isAddNew,
                                        }"
                                    >
                                        <span
                                            v-if="option.isAddNew"
                                            class="text-indigo-600 font-medium"
                                            >+ Add New Region</span
                                        >
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                            <div class="w-full"></div>
                        </div>
                        
                        <div>
                            <InputLabel for="location" value="Location" />
                            <div class="w-full">
                                <Multiselect
                                    v-model="form.asset_location"
                                    :options="locationOptions"
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
                                    v-model="form.sub_location"
                                    :options="[
                                        ...subLocations,
                                        {
                                            id: 'new',
                                            name: '+ Add New Sub-location',
                                            isAddNew: true,
                                        },
                                    ]"
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

                    <!-- Acquisition Date, Status, Original Value, and Fund Source -->
                    <div class="grid grid-cols-3 gap-3 mt-4">
                        
                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                class="mt-1 block w-full rounded border-gray-300"
                                v-model="form.status"
                                required
                            >
                                <option v-for="option in statuses" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="original_value" value="Original Value" />
                            <TextInput
                                id="original_value"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.original_value"
                                required
                            />
                        </div>
                        <div>
                            <InputLabel for="fund_source" value="Fund Source" />
                            <Multiselect
                                id="fund_source"
                                v-model="form.fund_source"
                                :options="fundSourceOptions"
                                :close-on-select="true"
                                :show-labels="false"
                                :allow-empty="true"
                                placeholder="Select Fund Source"
                                track-by="id"
                                label="name"
                                @select="handleFundSourceSelect"
                            >
                                <template v-slot:option="{ option }">
                                    <div
                                        :class="{
                                            'add-new-option': option.isAddNew,
                                        }"
                                    >
                                        <span
                                            v-if="option.isAddNew"
                                            class="text-indigo-600 font-medium"
                                            >+ Add New Fund Source</span
                                        >
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                        </div>
                    </div>
                    <div>
                        <input
                            type="checkbox"
                            v-model="form.has_warranty"
                            id="has_warranty"
                            class="mt-1"
                        />
                        <InputLabel for="has_warranty" value="Has Warrantee" />
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4" v-if="form.has_warranty">
                        <div>
                            <InputLabel
                                for="asset_warranty_start"
                                value="Start Date"
                            />
                            <input
                                type="date"
                                v-model="form.asset_warranty_start"
                                id="asset_warranty_start"
                                class="mt-1 w-full"
                                @change="validateWarrantyDates"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="asset_warranty_end"
                                value="End Date"
                            />
                            <input
                                type="date"
                                v-model="form.asset_warranty_end"
                                id="asset_warranty_end"
                                class="mt-1 w-full"
                                @change="validateWarrantyDates"
                            />
                            <div
                                v-if="warrantyDateError"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ warrantyDateError }}
                            </div>
                        </div>
                        <div>
                            <InputLabel for="warranty_months" value="Warranty (months)" />
                            <TextInput id="warranty_months" type="number" class="mt-1 w-full" placeholder="e.g., 12" v-model="form.warranty_months" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="maintenance_interval_months" value="Maintenance interval (months)" />
                            <TextInput id="maintenance_interval_months" type="number" class="mt-1 w-full" placeholder="e.g., 3" v-model="form.maintenance_interval_months" />
                        </div>
                        <div>
                            <InputLabel for="last_maintenance_at" value="Last maintenance date" />
                            <TextInput id="last_maintenance_at" type="date" class="mt-1 w-full" v-model="form.last_maintenance_at" />
                        </div>
                    </div>
                    <div>
                        <input
                            type="checkbox"
                            v-model="form.has_documents"
                            id="has_documents"
                            class="mt-1"
                        />
                        <InputLabel for="has_documents" value="Has Documents" />
                    </div>
                    <div
                        class="grid grid-cols-3 gap-4"
                        v-if="form.has_documents"
                        v-for="(document, index) in form.documents"
                    >
                        <div>
                            <InputLabel for="type" value="Document Type" />
                            <input
                                type="text"
                                v-model="document.type"
                                id="type"
                                class="mt-1 w-full"
                                placeholder="Document Type, e.g. Warranty, Receipt"
                            />
                        </div>
                        <div>
                            <InputLabel for="file" value="Document Date" />
                            <input
                                type="file"
                                accept=".pdf"
                                @change="(e) => handleDocumentUpload(index, e)"
                                id="file"
                                class="mt-1 w-full"
                            />
                        </div>
                        <div>
                            <button
                                @click.prevent="removeDocument(index)"
                                class="mt-4"
                            >
                                Remove document
                            </button>
                        </div>
                    </div>
                    <button @click.prevent="addDocument" class="mt-4">
                        Add document
                    </button>
                    <div class="flex items-center justify-end mt-6">
                        <Link
                            :href="route('assets.index')"
                            :disabled="processing"
                            >Exit</Link
                        >
                        <PrimaryButton class="ml-4" :disabled="processing">
                            {{ processing ? "Saving..." : "Create Asset" }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- New Location Modal -->
        <Modal :show="showLocationModal" @close="showLocationModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Location
                </h2>
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
                    <SecondaryButton
                        @click="showLocationModal = false"
                        :disabled="isNewLocation"
                        >Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isNewLocation"
                        @click="createLocation"
                        >{{
                            isNewLocation ? "Waiting..." : "Create new location"
                        }}</PrimaryButton
                    >
                </div>
            </div>
        </Modal>

        <!-- New Sub-Location Modal -->
        <Modal
            :show="showSubLocationModal"
            @close="showSubLocationModal = false"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Sub-Location
                </h2>
                <div class="mt-6">
                    <InputLabel
                        for="new_sub_location"
                        value="Sub-Location Name"
                    />
                    <TextInput
                        id="new_sub_location"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="newSubLocation"
                        required
                    />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton
                        @click="showSubLocationModal = false"
                        :disabled="isNewLocation"
                        >Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isNewLocation"
                        @click="createSubLocation"
                        >{{
                            isNewLocation ? "Waiting..." : "Create Sub-Location"
                        }}</PrimaryButton
                    >
                </div>
            </div>
        </Modal>

        <!-- New Category Modal -->
        <Modal :show="showCategoryModal" @close="showCategoryModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Category
                </h2>
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
                    <SecondaryButton
                        @click="showCategoryModal = false"
                        :disabled="isNewCategory"
                        >Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isNewCategory"
                        @click="createCategory"
                        >{{
                            isNewCategory ? "Waiting..." : "Create Category"
                        }}</PrimaryButton
                    >
                </div>
            </div>
        </Modal>

        <!-- New Category Modal -->
        <Modal :show="showRegionModal" @close="showRegionModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Region
                </h2>
                <div class="mt-6">
                    <InputLabel for="new_region" value="Region Name" />
                    <TextInput
                        id="new_region"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="newRegion"
                        required
                    />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton
                        @click="showRegionModal = false"
                        :disabled="isNewRegion"
                        >Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isNewRegion"
                        @click="createRegion"
                        >{{
                            isNewRegion ? "Waiting..." : "Create Region"
                        }}</PrimaryButton
                    >
                </div>
            </div>
        </Modal>

        <!-- New Category Modal -->
        <Modal :show="showFundSourceModal" @close="showFundSourceModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Fund Source
                </h2>
                <div class="mt-6">
                    <InputLabel
                        for="new_fund_source"
                        value="Fund Source Name"
                    />
                    <TextInput
                        id="new_fund_source"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="newFundSource"
                        required
                    />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton
                        @click="showFundSourceModal = false"
                        :disabled="isNewFundSource"
                        >Exit
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isNewFundSource"
                        @click="createFundSource"
                        >{{
                            isNewFundSource
                                ? "Waiting..."
                                : "Create Fund Source"
                        }}</PrimaryButton
                    >
                </div>
            </div>
        </Modal>
        
        <!-- New Type Modal -->
        <Modal :show="showTypeModal" @close="showTypeModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Type</h2>
                <div class="mt-6">
                    <InputLabel for="new_type" value="Type Name" />
                    <TextInput id="new_type" type="text" class="mt-1 block w-full" v-model="newType" required />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showTypeModal = false" :disabled="isNewType">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="isNewType" @click="createType">{{ isNewType ? 'Waiting...' : 'Create Type' }}</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
