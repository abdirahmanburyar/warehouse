<template>
  <AuthenticatedLayout>
    <!-- Order Header -->
    <div v-if="props.error">
      {{ props.error }}
    </div>
    <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Order #{{ props.order.order_number }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ formatDate(props.order.order_date) }}</p>
          </div>
          <div class="flex items-center space-x-4">
            <span :class="[statusClasses[props.order.status] || statusClasses.default]">
              {{ props.order.status.toUpperCase() }}
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <!-- Facility Information -->
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
          <h2 class="text-lg font-medium text-gray-900">Facility Details</h2>
          <div class="flex items-center">
            <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.name }}</span>
          </div>
          <div class="flex items-center">
            <EnvelopeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.email }}</span>
          </div>
          <div class="flex items-center">
            <PhoneIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.phone }}</span>
          </div>
          <div class="flex items-center">
            <MapPinIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.address }}, {{ props.order.facility.city
            }}</span>
          </div>
        </div>
        <div>
          <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <h2 class="text-lg font-medium text-gray-900">Order Details</h2>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Order Type</p>
                <p class="text-sm text-gray-900">{{ props.order.order_type }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Expected Date</p>
                <p class="text-sm text-gray-900">{{ formatDate(props.order.expected_date) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Created By</p>
                <p class="text-sm text-gray-900">{{ props.order.user.name }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Created At</p>
                <p class="text-sm text-gray-900">{{ formatDate(props.order.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Status Stage Timeline -->
      <div class="col-span-2 mb-6">
        <div class="relative">
          <!-- Timeline Track Background -->
          <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 z-0"></div>

          <!-- Timeline Progress -->
          <div class="absolute top-5 left-0 h-1 bg-orange-500 z-0 transition-all duration-500 ease-in-out" :style="{
            width: `${(statusOrder.indexOf(props.order.status) / (statusOrder.length - 1)) * 100}%`
          }"></div>

          <!-- Timeline Steps -->
          <div class="relative flex justify-between">
            <!-- Pending -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('pending') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/pending.svg" class="w-6 h-6" alt="Pending"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('pending') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('pending') ? 'text-orange-600' : 'text-gray-500'">Pending</span>
            </div>

            <!-- Approved -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('approved') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/approved.png" class="w-6 h-6" alt="Approved"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('approved') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('approved') ? 'text-orange-600' : 'text-gray-500'">Approved</span>
            </div>

            <!-- In Process -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('in_process') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/inprocess.png" class="w-6 h-6" alt="In Process"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('in_process') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('in_process') ? 'text-orange-600' : 'text-gray-500'">In Process</span>
            </div>

            <!-- Dispatch -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('dispatched') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/dispatch.png" class="w-6 h-6" alt="Dispatch"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('dispatched') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('dispatched') ? 'text-orange-600' : 'text-gray-500'">Dispatch</span>
            </div>

            <!-- Delivered -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('delivered') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/delivery.png" class="w-6 h-6" alt="Delivered"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('delivered') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('delivered') ? 'text-orange-600' : 'text-gray-500'">Delivered</span>
            </div>

            <!-- Received -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/received.png" class="w-6 h-6" alt="Received"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received') ? 'text-orange-600' : 'text-gray-500'">Received</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Order Items Table -->
      <div class="px-6 py-4 border border-gray-200 mb-[90px]">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Order Items</h2>
        <div class="overflow-x-auto shadow-sm rounded-lg">
          <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
            <thead>
              <tr class="bg-gray-50">
                <th class="w-[150px] px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Item
                </th>
                <th class="w-[120px] px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Inventory</th>
                <th class="w-[50px] px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Needed Quantity</th>
                <th class="w-[180px] px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Quantity to
                  release</th>
                <th class="w-[90px] px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">No. of Months
                </th>
                <th class="w-[100px] px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in form" :key="item.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">
                  <div class="flex flex-col">
                    <span class="font-medium text-sm">{{ item.product?.name }}</span>
                  </div>
                </td>
                <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">               
                  <div class="flex flex-col items-center text-sm">
                    <span class="font-medium">QOO:</span>
                    <span>{{ item.quantity_on_order }}</span>
                    <span class="font-medium">SOH:</span>
                    <span>{{ item.soh }}</span>
                    <span class="font-medium">QER:</span>
                    <input type="number" v-model="item.qer" class="w-12 h-6 text-sm rounded border-gray-300" />
                  </div>
                </td>
                <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">
                  <input 
                    type="number" 
                    v-model="item.quantity" 
                    class="w-16 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                  />
                </td>
                <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">
                  <div class="flex flex-between gap-2 items-center space-y-2">
                    <div class="flex items-center">
                      <input 
                        type="number" 
                        v-model="item.quantity_to_release"
                        class="w-16 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                      />
                    </div>
                    <div class="border rounded-md overflow-hidden text-sm">
                      <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr v-for="inv in item.inventory_allocations" :key="inv.id" class="hover:bg-gray-100">
                            <td class="px-2 py-1">
                              <div class="font-medium">QTY: {{ inv.allocated_quantity }}</div>
                              <div class="font-medium">Batch: {{ inv.batch_number }}</div>
                              <div class="font-medium">Barcode:{{ inv.barcode }}</div>
                              <div class="font-medium">WH:{{ inv.warehouse?.name }}</div>
                              <div class="font-medium">Loc: {{ inv.location?.location }}</div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    {{ item.no_of_days }}
                  </span>
                </td>
                <td class="px-3 py-3 text-sm text-gray-900">
                  <div class="flex flex-col space-y-2">
                    <button
                      type="button"
                      class="inline-flex items-center px-2 py-1 bg-red-100 border border-transparent rounded text-sm text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                      Delete
                    </button>
                    <!-- <button
                      type="button"
                      class="inline-flex items-center px-2 py-1 bg-green-100 border border-transparent rounded text-sm text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                      @click="submit(item, index)"
                    >
                      Submit
                    </button> -->
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';
import {
  BuildingOfficeIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon
} from '@heroicons/vue/24/outline';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  error: String,
  products: Array
});

const statusClasses = {
  pending: 'bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium',
  approved: 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium',
  'in process': 'bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium',
  dispatched: 'bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium',
  delivered: 'bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium',
  default: 'bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium'
};

const form = ref([]);

onMounted(() => {
  form.value = props.order.items || [];
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getTotalAllocated = (allocations) => {
  return allocations.reduce((sum, allocation) => sum + allocation.allocated_quantity, 0);
};

const getAllocationStatus = (item) => {
  const totalAllocated = getTotalAllocated(item.inventory_allocations);
  if (totalAllocated === 0) return 'bg-red-100 text-red-800';
  if (totalAllocated < item.quantity) return 'bg-yellow-100 text-yellow-800';
  return 'bg-green-100 text-green-800';
};

const statusOrder = ['pending', 'approved', 'in_process', 'dispatched', 'delivered', 'received'];

const getStatusProgress = (currentStatus) => {
  const currentIndex = statusOrder.indexOf(currentStatus);
  return statusOrder.map((status, index) => ({
    status,
    isActive: index <= currentIndex,
    isPast: index < currentIndex
  }));
};

async function submit(item, index){
  console.log(item)
}

const statusProgress = computed(() => getStatusProgress(props.order.status));


</script>