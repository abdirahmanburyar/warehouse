<template>
    <AuthenticatedLayout title="Manage Facilities" description="Manage facilities" img="/assets/images/facility.png">
        <!-- Page Header -->
        <div class=" flex justify-between items-center">
            <h1 class="text-sm font-bold text-gray-900">Facilities</h1>
            <div class="flex space-x-4">
                <!-- Excel Upload Button -->
                <label class="inline-flex items-center rounded-2xl px-4 py-2 bg-green-600 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                    <i class="fas fa-file-excel mr-2"></i> Upload Excel
                    <input type="file" class="hidden" @change="handleFileUpload" accept=".xlsx,.xls"/>
                </label>
                
                <!-- Add Facility Button -->
                <Link :href="route('facilities.create')"
                    class="inline-flex rounded-2xl items-center px-4 py-2 bg-gray-800 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-plus mr-2"></i> Add Facility
                </Link>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2">
            <!-- Search Bar -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" v-model="search" placeholder="Search by name, type, manager..."
                    class="w-full border-gray-300 focus:border-gray-500 focus:ring-gray-500 py-2 px-4">
            </div>
            
            <!-- District Filter -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                <Multiselect
                    v-model="district"
                    :options="props.districts"
                    placeholder="Select district"
                    :searchable="true"
                    :allow-empty="true"
                    class="multiselect-indigo"
                />
            </div>
            
            
        </div>
        <!-- Per Page Selection -->
        <div class="flex justify-end">
            <select v-model="per_page"
                @change="props.filters.page = 1"
                class="w-[200px] border-black rounded-3xl">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
        <!-- Table Section -->
        <div class="overflow-x-auto mt-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-3 border border-black text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-building mr-2"></i>SN
                        </th>
                        <th class="px-2 py-3 border border-black text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-building mr-2"></i>Name
                        </th>
                        <th class="px-2 py-3 border border-black text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-tag mr-2"></i>Type
                        </th>
                        <th class="px-2 py-3 border border-black text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-user mr-2"></i>Manager
                        </th>
                        <th class="px-2 py-3 border border-black text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-user mr-2"></i>Handled By
                        </th>
                        <th class="px-2 py-3 border border-black text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-map-marker-alt mr-2"></i>District
                        </th>
                        <th class="px-2 py-3 border border-black text-left text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-check-circle mr-2"></i>Status
                        </th>
                        <th class="px-2 py-3 border border-black text-center text-xs font-medium text-gray-500 capitalize tracking-wider">
                            <i class="fas fa-cog mr-2"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(facility, index) in props.facilities.data" :key="facility.id" class="hover:bg-gray-50">
                        <td class="w-[50px] px-2 py-2 border border-black text-xs whitespace-nowrap">{{index + 1}}</td>
                            <td class="px-2 py-2 border border-black text-xs whitespace-nowrap">
                            <div class="flex items-center">
                                <Link :href="route('facilities.show', facility.id)">
                                    {{ facility.name }}
                                </Link>
                            </div>
                        </td>
                        <td class="px-2 py-2 border border-black text-xs whitespace-nowrap">
                            <div class="flex items-center">
                                <i :class="{
                                    'fas mr-2': true,
                                    'fa-hospital text-blue-500': facility.facility_type === 'hospital',
                                    'fa-clinic-medical text-green-500': facility.facility_type === 'clinic',
                                    'fa-prescription-bottle-alt text-purple-500': facility.facility_type === 'pharmacy'
                                }"></i>
                                <span class="capitalize">{{ facility.facility_type }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-2 border border-black text-xs whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2 text-gray-500"></i>
                                    <span>{{ facility.user?.name || 'Not assigned' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-envelope mr-2 text-gray-500"></i>
                                    <span class="text-sm text-gray-600">{{ facility.email || 'No email' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-phone mr-2 text-gray-500"></i>
                                    <span class="text-sm text-gray-600">{{ facility.phone || 'No phone' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-2 border border-black text-xs whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2 text-gray-500"></i>
                                    <span>{{ facility.handledby?.name || 'Not assigned' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-envelope mr-2 text-gray-500"></i>
                                    <span class="text-sm text-gray-600">{{ facility.handledby?.email || 'No email' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-phone mr-2 text-gray-500"></i>
                                    <span class="text-sm text-gray-600">{{ facility.handledby?.phone || 'No phone' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-2 border border-black text-xs whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-2 text-gray-500"></i>
                                <span>{{ facility.district || 'Not assigned' }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-2 border border-black text-xs whitespace-nowrap">
                            <span
                                :class="facility.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                <i :class="facility.is_active ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1'"></i>
                                {{ facility.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-2 py-2 border border-black text-xs whitespace-nowrap text-center">
                            <div class="flex space-x-3 justify-center">
                                <Link :href="route('facilities.edit', facility.id)"
                                    class="text-indigo-600 hover:text-indigo-900 p-1 rounded-full hover:bg-indigo-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                    </svg>
                                </Link>
                                <button
                                    @click="confirmToggleStatus(facility)"
                                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    :class="{
                                        'bg-red-600': facility.is_active,
                                        'bg-green-600': !facility.is_active,
                                        'opacity-50 cursor-wait': loadingProducts.has(facility.id)
                                    }"
                                    :disabled="loadingProducts.has(facility.id)"
                                >
                                    <span 
                                        class="inline-block h-4 w-4 transform rounded-full transition-transform duration-300"
                                        :class="{
                                            'translate-x-6': facility.is_active,
                                            'translate-x-1': !facility.is_active,
                                            'bg-white': !loadingProducts.has(facility.id),
                                            'bg-gray-200 animate-pulse': loadingProducts.has(facility.id)
                                        }"
                                    />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-end mt-3 mb-[80px]">
            <TailwindPagination :data="props.facilities" :limit="2" class="flex items-center space-x-3 text-sm" @pagination-change-page="getResults" />
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import '@/Components/multiselect.css'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'
import { TailwindPagination } from "laravel-vue-pagination";


const toast = useToast()

const selectedFile = ref(null)
const isUploading = ref(false)
const uploadErrors = ref([])

// Handle file selection
const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
            file.type === 'application/vnd.ms-excel') {
            selectedFile.value = file
            uploadErrors.value = []
        } else {
            toast.error('Please select a valid Excel file (.xlsx or .xls)')
        }
    }
}

// Upload the file
const uploadFile = async () => {
    if (!selectedFile.value) return

    isUploading.value = true
    uploadErrors.value = []

    const formData = new FormData()
    formData.append('file', selectedFile.value)

    try {
        const response = await axios.post(route('facilities.import'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        toast.success(response.data.message)
        closeUploadModal()
        
        // Show processing notification
        toast.info('Processing facilities in the background. The page will refresh in 10 seconds.')
        
        // Wait 10 seconds before reloading to allow some processing time
        setTimeout(() => {
            router.reload()
        }, 10000)
    } catch (error) {
        if (error.response?.data?.errors) {
            uploadErrors.value = Object.values(error.response.data.errors).flat()
        } else {
            uploadErrors.value = [error.response?.data?.message || 'Failed to import facilities']
        }
    } finally {
        isUploading.value = false
    }
}

const props = defineProps({
    facilities: {
        type: Object,
        required: true
    },
    users: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        required: true
    },
    districts: {
        type: Array,
        required: true
    }
})

const per_page = ref(props.filters.per_page || 25)
const search = ref(props.filters.search)
const district = ref(props.filters.district)
const loadingProducts = ref(new Set());

watch([
    () => per_page.value,
    () => props.filters.page,
    () => search.value,
    () => district.value
], () => {
    reloadFacility();
})



const reloadFacility = () => {
    const query = {}
    if (per_page.value) query.per_page = per_page.value
    if (props.filters.page) query.page = props.filters.page
    if (search.value) query.search = search.value
    if (district.value) query.district = district.value

    console.log(query)
    router.get(route('facilities.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: ['facilities']
    })
}

const getResults = (page) => {
   props.filters.page = page;
}

const confirmToggleStatus = (product) => {
    const action = product.is_active ? 'deactivate' : 'activate';
    
    Swal.fire({
        title: 'Are you sure?',
        html: `<p>Do you want to ${action} ${product.name}?</p>`,
        showConfirmButton: true,
        icon: undefined,
        showCancelButton: true,
        confirmButtonColor: product.is_active ? '#d33' : '#3085d6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: product.is_active ? 'Yes, deactivate!' : 'Yes, activate!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            loadingProducts.value.add(product.id);
            try {
                await axios.get(route('facilities.toggle-status', product.id));
                reloadFacility();
                Swal.fire(
                    action === 'activate' ? 'Activated!' : 'Deactivated!',
                    `Product has been ${action}d.`,
                    'success'
                );
            } catch (error) {
                toast.error(error.response?.data || 'An error occurred');
            } finally {
                loadingProducts.value.delete(product.id);
            }
        }
    });
};




const deleteFacility = async (id) => {
    if (confirm('Are you sure you want to delete this facility?')) {
        try {
            const response = await axios.delete(route('facilities.destroy', id))
            toast.success(response.data)
            reloadFacility()
        } catch (error) {
            toast.error(error.response.data);
        }
    }
}
</script>