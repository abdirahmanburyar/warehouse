<template>
    <AuthenticatedLayout title="Facilities">
        <!-- Page Header -->
        <div class="p-6 bg-white border-b flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Facilities</h1>
            <Link :href="route('facilities.create')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Add Facility
            </Link>
        </div>
        
        <!-- Filters Section -->
        <div class="p-6 bg-white border-b">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Search Bar -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" v-model="search" placeholder="Search by name, type, manager..."
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 px-4">
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
                
                <!-- Per Page Selection -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Items Per Page</label>
                    <select v-model="per_page"
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Table Section -->
        <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table v-if="props.facilities.data.length > 0" class="min-w-full divide-y divide-gray-200 border-2 border-black">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-2 border-black">
                                <i class="fas fa-building mr-2"></i>Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-2 border-black">
                                <i class="fas fa-tag mr-2"></i>Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-2 border-black">
                                <i class="fas fa-user mr-2"></i>Manager
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-2 border-black">
                                <i class="fas fa-map-marker-alt mr-2"></i>District
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-2 border-black">
                                <i class="fas fa-check-circle mr-2"></i>Status
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-2 border-black">
                                <i class="fas fa-cog mr-2"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="facility in props.facilities.data" :key="facility.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap border-2 border-black">
                                <div class="flex items-center">
                                    <i class="fas fa-building mr-2 text-indigo-500"></i>
                                    <span class="font-medium text-gray-900">{{ facility.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border-2 border-black">
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
                            <td class="px-6 py-4 whitespace-nowrap border-2 border-black">
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
                            <td class="px-6 py-4 whitespace-nowrap border-2 border-black">
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-gray-500"></i>
                                    <span>{{ facility.district || 'Not assigned' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border-2 border-black">
                                <span
                                    :class="facility.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    <i :class="facility.is_active ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1'"></i>
                                    {{ facility.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center border-2 border-black">
                                <div class="flex space-x-3 justify-center">
                                    <Link :href="route('facilities.edit', facility.id)"
                                        class="text-indigo-600 hover:text-indigo-900 p-1 rounded-full hover:bg-indigo-100">
                                        <i class="fas fa-edit"></i>
                                    </Link>
                                    <button @click="deleteFacility(facility.id)" 
                                        class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-100">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty state indicator -->
                <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fa-solid fa-building-circle-exclamation text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No facilities found</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        {{ search ? 'No facilities match your search criteria.' : 'There are no facilities in the system yet.' }}
                    </p>
                    <Link :href="route('facilities.create')"
                        class="bg-gray-900 text-white rounded-full px-6 py-2.5 text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-700 transition-colors duration-200 ease-in-out inline-flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        Add Your First Facility
                    </Link>
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500" v-if="props.facilities.meta.total">
                        Showing {{ props.facilities.meta.from }} to {{ props.facilities.meta.to }} of {{
                            props.facilities.meta.total }} results
                    </div>
                    <Pagination :links="props.facilities.meta.links" />
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import '@/Components/multiselect.css'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

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

const page = ref(props.filters.page)
const per_page = ref(props.filters.per_page || 10)
const search = ref(props.filters.search || '')
const district = ref(props.filters.district || null)

watch([
    () => per_page.value,
    () => page.value,
    () => search.value,
    () => district.value
], () => {
    reloadFacility();
})



const reloadFacility = () => {
    const query = {}
    if (per_page.value) query.per_page = per_page.value
    if (page.value) query.page = page.value
    if (search.value) query.search = search.value
    if (district.value) query.district = district.value
    router.get(route('facilities.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: ['facilities']
    })
}




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