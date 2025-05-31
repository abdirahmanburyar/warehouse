<template>
  <Head title="Physical Count Report" />
  <AuthenticatedLayout title="Physical Count Report" description="Inventory Verification Tool" img="/assets/images/report.png">
    <div class="py-6 px-4 mb-[100px]">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Physical Count Report</h1>
      </div>

      <!-- Results Table -->
      <div class="overflow-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 border-b border-black">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black w-16">
                SN
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black w-1/5">
                Item
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Expiry date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                UOM
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                QTY on Report
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Physical Count
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Difference
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Remarks
              </th>
            </tr>
          </thead>
          <tbody class="bg-white border-b border-black">
            <tr v-if="props.inventories.length === 0">
              <td colspan="9" class="px-6 py-4 text-center text-sm text-black border border-black">
                No products found matching the criteria.
              </td>
            </tr>
            <tr v-for="(item, index) in props.inventories" :key="item.id" class="hover:bg-gray-50 border border-black">
              <td class="border px-4 py-2 text-center">{{ index + 1 }}</td>
              <td class="border px-4 py-2">{{ item.product ? item.product.name : 'Unknown Product' }}</td>
              <td class="border px-4 py-2 text-center">{{ item.expiry_date ? formatDate(item.expiry_date) : 'N/A' }}</td>
              <td class="border px-4 py-2 text-center">{{ item.uom || 'N/A' }}</td>
              <td class="border px-4 py-2 text-left">{{ item.quantity || 0 }}</td>
              <td class="border px-4 py-2 text-left">
                <input 
                  type="number" 
                  v-model="item.physical_count" 
                  @input="calculateDifference(item)" 
                  class="w-full text-left border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  min="0"
                  placeholder="Enter count"
                />
              </td>
              <td class="border px-4 py-2" :class="getDifferenceClass(item)">
                <span>{{ calculateDifferenceValue(item) }}</span>
              </td>
              <td class="border px-4 py-2 text-center">
                <span v-if="calculateDifferenceValue(item) === 0" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                  Matched
                </span>
                <span v-else-if="calculateDifferenceValue(item) > 0" class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                  Surplus
                </span>
                <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                  Shortage
                </span>
              </td>
              <td class="border px-4 py-2 text-left">
                <input 
                  type="text" 
                  v-model="item.remarks" 
                  class="w-full text-left border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  placeholder="Add remarks"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Submit Button Below Table -->
      <div class="mt-3 flex justify-end">
        <button 
          @click="savePhysicalCount" 
          :disabled="saving"
          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
        >
          <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
          </svg>
          {{ saveButtonText }}
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import moment from 'moment';
import Swal from "sweetalert2";
import axios from "axios";

const props = defineProps({
    inventories: Array,
});


// Reactive data
const saving = ref(false);
const saveButtonText = ref('Save Physical Count');

// Calculate difference between system quantity and physical count
const calculateDifference = (item) => {
  // Ensure physical_count is a number
  if (item.physical_count === '' || item.physical_count === null) {
    item.physical_count = 0;
  }
};

// Calculate and return the difference value
const calculateDifferenceValue = (item) => {
  const systemQty = parseInt(item.quantity || 0);
  const physicalQty = parseInt(item.physical_count || 0);
  const difference = physicalQty - systemQty;
  
  // Ensure difference is 0 as integer when there's no mismatch
  return difference === 0 ? 0 : difference;
};

// Get CSS class for difference cell based on value
const getDifferenceClass = (item) => {
  const difference = calculateDifferenceValue(item);
  if (difference > 0) return 'text-blue-700 font-medium';
  if (difference < 0) return 'text-red-700 font-medium';
  return 'text-green-700 font-medium';
};

// Helper functions for date formatting
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return moment(dateString).format('DD/MM/YYYY');
};

// Save physical count data
const savePhysicalCount = () => {
  // Confirm before saving
  Swal.fire({
    title: 'Save Physical Count?',
    text: 'This will adjust the inventory based on your physical count data.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, save it!'
  }).then(async (result) => {
    if (result.isConfirmed) {
      saving.value = true;
      saveButtonText.value = 'Saving...';
      
      console.log(props.inventories);
      
      // Show loading state with Swal
      Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we process your data.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      // Submit data using axios
      await axios.post(route('reports.adjustInventory'), {
        items: props.inventories,
      })
      .then(response => {
        saving.value = false;
        saveButtonText.value = 'Save Physical Count';

        Swal.fire({
          title: 'Success!',
          text: response.data,
          icon: 'success',
          confirmButtonColor: '#3085d6',
        });
      })
      .catch(error => {
        saving.value = false;
        saveButtonText.value = 'Save Physical Count';
        
        console.error('Error saving physical count data:', error);
        
        Swal.fire({
          title: 'Error!',
          text: error.response?.data?.message || 'Failed to save physical count data. Please try again.',
          icon: 'error',
          confirmButtonColor: '#3085d6',
        });
      });
    }
  });
};
</script>