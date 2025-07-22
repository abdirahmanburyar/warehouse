<template>
    <AuthenticatedLayout title="Manage Facilities" description="Create New Facility" img="/assets/images/facility.png">
        <div class="p-6 bg-white border-b flex justify-between items-center">
            <h1 class="text-sm font-bold text-gray-900">
                Create New Facility
            </h1>
            <Link
                :href="route('facilities.index')"
                class="inline-flex items-center px-2 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <i class="fas fa-arrow-left mr-2"></i> Back to Facilities
            </Link>
        </div>

        <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg mb-[50px]">
            <form @submit.prevent="submitForm" class="space-y-6">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="Enter facility name"
                    />
                    <InputError :message="errors.name" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            placeholder="Enter facility email"
                        />
                        <InputError :message="errors.email" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone" />
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            placeholder="Enter phone number"
                        />
                        <InputError :message="errors.phone" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="address" value="Address" />
                        <TextInput
                            id="address"
                            v-model="form.address"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            placeholder="Enter facility address"
                        />
                        <InputError :message="errors.address" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="facility_type" value="Facility Type" />
                        <select
                            id="facility_type"
                            v-model="form.facility_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select Type</option>
                            <option value="District Hospital">
                                District Hospital
                            </option>
                            <option value="Regional Hospital">
                                Regional Hospital
                            </option>
                            <option value="Health Centre">Health Centre</option>
                            <option value="Primary Health Unit">
                                Primary Health Unit
                            </option>
                        </select>
                        <InputError
                            :message="errors.facility_type"
                            class="mt-2"
                        />
                    </div>
                    <!-- region -->
                    <div>
                        <label for="region">Region</label>
                        <Multiselect
                            v-model="form.region"
                            :options="[...regions, ADD_NEW_REGION_OPTION]"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            placeholder="Select Region"
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
                                    >
                                        + Add New Region
                                    </span>
                                    <span v-else>{{ option }}</span>
                                </div>
                            </template>
                        </Multiselect>
                    </div>
                    <div>
                        <label for="district">District</label>
                        <Multiselect
                            v-model="form.district"
                            :options="[...districts, ADD_NEW_DISTRICT_OPTION]"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            placeholder="Select District"
                            @select="handleDistrictSelect"
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
                                    >
                                        + Add New District
                                    </span>
                                    <span v-else>{{ option }}</span>
                                </div>
                            </template>
                        </Multiselect>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel
                            for="user_id"
                            value="Assigned User (Manager)"
                        />
                        <Multiselect
                            v-model="form.user_id"
                            :options="props.users"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            track-by="id"
                            label="name"
                            required
                            placeholder="Select Facility Manager"
                        ></Multiselect>
                        <InputError :message="errors.user_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="handled_by" value="Handled By" />
                        <Multiselect
                            v-model="form.handled_by"
                            :options="props.users"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            track-by="id"
                            label="name"
                            required
                            placeholder="Select Handled By"
                        ></Multiselect>
                    </div>
                </div>

                <div class="flex justify-start items-center space-x-8">
                    <label class="flex items-center">
                        <Checkbox
                            :checked="form.has_cold_storage"
                            :modelValue="form.has_cold_storage"
                            @update:modelValue="
                                (value) => (form.has_cold_storage = value)
                            "
                        />
                        <span class="ml-2 text-sm text-gray-600"
                            >Has Cold Storage</span
                        >
                    </label>

                    <label class="flex items-center">
                        <Checkbox
                            :checked="form.is_active"
                            :modelValue="form.is_active"
                            @update:modelValue="
                                (value) => (form.is_active = value)
                            "
                        />
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <Link :href="route('facilities.index')" class="mr-3">
                        <SecondaryButton :disabled="isSubmitting"
                            >Cancel</SecondaryButton
                        >
                    </Link>
                    <PrimaryButton :disabled="isSubmitting">
                        {{ isSubmitting ? "Creating..." : "Create Facility" }}
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- New Region Modal -->
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
                        placeholder="Enter region name"
                        v-model="newRegion"
                        required
                    />
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton
                        @click="showRegionModal = false"
                        :disabled="isNewRegion"
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isNewRegion"
                        @click="createRegion"
                    >
                        {{ isNewRegion ? "Waiting..." : "Create Region" }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- New District Modal -->
        <Modal :show="showDistrictModal" @close="showDistrictModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New District
                </h2>

                <div class="mt-6">
                    <InputLabel for="name" value="District Name" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Enter district name"
                        v-model="newDistrict"
                        required
                    />
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton
                        @click="showDistrictModal = false"
                        :disabled="isNewDistrict"
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="isNewDistrict"
                        @click="createDistrict"
                    >
                        {{ isNewDistrict ? "Waiting..." : "Create District" }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router, Link } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { useToast } from "vue-toastification";
import Modal from "@/Components/Modal.vue";

const toast = useToast();

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    regions: Array,
});

// Create reactive arrays (regions starts with props data, districts starts empty)
const regions = ref([...props.regions]);
const districts = ref([]);

const showRegionModal = ref(false);
const newRegion = ref("");
const isNewRegion = ref(false);
const showDistrictModal = ref(false);
const newDistrict = ref("");
const isNewDistrict = ref(false);

const handledBy = ref(null);

const ADD_NEW_REGION_OPTION = "+ Add New Region";
const ADD_NEW_DISTRICT_OPTION = "+ Add New District";

const isSubmitting = ref(false);
const errors = ref({});

const form = ref({
    name: "",
    email: "",
    phone: "",
    address: "",
    facility_type: "",
    has_cold_storage: false,
    is_active: true,
    user_id: "",
    district: "",
    region: "",
    handled_by: handledBy.value?.id,
});

async function handleRegionSelect(option) {
    if (option == ADD_NEW_REGION_OPTION) {
        form.value.region = null;
        showRegionModal.value = true;
    } else {
        form.value.region = option;
        await loadDistrict();
    }
}

async function handleDistrictSelect(option) {
    if (option == ADD_NEW_DISTRICT_OPTION) {
        form.value.district = null;
        showDistrictModal.value = true;
    } else {
        form.value.district = option;
    }
}

const createRegion = async () => {
    if (!newRegion.value) {
        toast.error("Please enter a region name");
        return;
    }
    isNewRegion.value = true;
    await axios
        .post(route("assets.regions.store"), { name: newRegion.value })
        .then((response) => {
            isNewRegion.value = false;
            // Add the new region to the reactive options array
            regions.value.push(response.data);
            // Set it as the selected value
            form.value.region = response.data;
            newRegion.value = "";
            showRegionModal.value = false;
            // Load districts for the newly created region
            loadDistrict();
        })
        .catch((error) => {
            isNewRegion.value = false;
            console.log(error);
        });
};

const createDistrict = async () => {
    if (!newDistrict.value || !form.value.region) {
        toast.error("Please select region");
        return;
    }
    isNewDistrict.value = true;
    await axios
        .post(route("districts.store"), {
            name: newDistrict.value,
            region: form.value.region,
        })
        .then((response) => {
            isNewDistrict.value = false;
            // Add the new district to the reactive options array
            districts.value.push(response.data);
            // Set it as the selected value
            form.value.district = response.data;
            newDistrict.value = "";
            showDistrictModal.value = false;
        })
        .catch((error) => {
            isNewDistrict.value = false;
            console.log(error);
        });
};

async function loadDistrict() {
    await axios
        .post(route("districts.get-districts"), { region: form.value.region })
        .then((response) => {
            districts.value = response.data;
        })
        .catch((error) => {
            console.log(error);
        });
}

const submitForm = async () => {
    console.log(form.value);
    isSubmitting.value = true;
    await axios
        .post(route("facilities.store"), form.value)
        .then((response) => {
            isSubmitting.value = false;
            Swal.fire({
                title: "Success!",
                text: "Facility created successfully.",
                icon: "success",
                confirmButtonText: "OK",
            }).then(() => {
                router.visit(route("facilities.index"));
            });
        })
        .catch((error) => {
            isSubmitting.value = false;
            Swal.fire({
                title: "Error!",
                text:
                    error.response?.data ||
                    "There was an error creating the facility",
                icon: "error",
                confirmButtonText: "OK",
            });
        });
};
</script>
