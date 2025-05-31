<template>
  <Head title="Received Quantities Report" />
  <AuthenticatedLayout title="Received Quantities Report" description="Track all received inventory" img="/assets/images/report.png">

    <div>
      <!-- Filter Form -->
      <form @submit.prevent="applyFilters" class="mb-6">
        <div class="flex items-end space-x-2 pb-1">
          <div class="w-1/4">
            <label class="block text-sm font-medium text-gray-700">Item</label>
            <Multiselect
              v-model="selectedProduct"
              :options="products"
              :searchable="true"
              :close-on-select="true"
              :show-labels="false"
              placeholder="All Items"
              label="name"
              track-by="id"
              :allow-empty="true"
              @select="option => { form.product_id = option.id; }"
              @remove="() => { form.product_id = ''; }"
              class="w-full"
            >
              <template #singleLabel="{ option }">
                <span>{{ option.name }}</span>
              </template>
            </Multiselect>
          </div>

          <div class="w-48">
            <label class="block text-sm font-medium text-gray-700">Warehouse</label>
            <Multiselect
              v-model="selectedWarehouse"
              :options="warehouses"
              :searchable="true"
              :close-on-select="true"
              :show-labels="false"
              placeholder="All Warehouses"
              label="name"
              track-by="id"
              :allow-empty="true"
              @select="option => { form.warehouse_id = option.id; }"
              @remove="() => { form.warehouse_id = ''; }"
              class="w-full"
            >
              <template #singleLabel="{ option }">
                <span>{{ option.name }}</span>
              </template>
            </Multiselect>
          </div>

          <div class="w-48">
            <label class="block text-sm font-medium text-gray-700">Source</label>
            <Multiselect
              v-model="selectedSource"
              :options="[{id: 'transfer', name: 'Transfer'}, {id: 'packing_list', name: 'Packing List'}]"
              :searchable="false"
              :close-on-select="true"
              :show-labels="false"
              placeholder="All Sources"
              label="name"
              track-by="id"
              :allow-empty="true"
              @select="option => { form.source = option.id; }"
              @remove="() => { form.source = ''; }"
              class="w-full"
            >
              <template #singleLabel="{ option }">
                <span>{{ option.name }}</span>
              </template>
            </Multiselect>
          </div>

          <div class="w-40">
            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
            <input
              type="date"
              id="start_date"
              v-model="form.start_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="w-40">
            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
            <input
              type="date"
              id="end_date"
              v-model="form.end_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="w-52">
            <div class="flex gap-2 w-full">
              <button
                type="submit"
                class="flex-1 justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Apply Filters
              </button>
              <button
                type="button"
                @click="resetFilters"
                class="flex-1 justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Reset
              </button>
            </div>
          </div>
          
          <div class="w-40">
            <label for="per_page" class="block text-sm font-medium text-gray-700">Items per page</label>
            <select 
              id="per_page" 
              v-model="form.per_page" 
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>
      </form>

      <!-- Results Table -->
      <div class="overflow-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 border-b border-black">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Product
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Quantity
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                UOM
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Batch #
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Expiry Date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Source
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Received By
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Received At
              </th>
            </tr>
          </thead>
          <tbody class="bg-white border-b border-black">
            <tr v-if="receivedQuantities.data.length === 0">
              <td colspan="8" class="px-6 py-4 text-smnter text-sm text-black border border-black">
                No received quantities found matching the criteria.
              </td>
            </tr>
            <tr v-for="item in receivedQuantities.data" :key="item.id" class="hover:bg-gray-50 border border-black">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border border-black">
                {{ item.product ? item.product.name : 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                {{ item.quantity }}
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                {{ item.uom || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                {{ item.batch_number || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                {{ item.expiry_date ? formatDate(item.expiry_date) : 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                <span v-if="item.transfer_id">
                  Transfer #{{ item.transfer_id }}
                </span>
                <span v-else-if="item.packing_list_id">
                  Packing List #{{ item.packing_list_id }}
                </span>
                <span v-else>N/A</span>
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                {{ item.receiver ? item.receiver.name : 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                {{ formatReceivedAt(item.received_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex items-center justify-end">
        <TailwindPagination :data="receivedQuantities" @pagination-change-page="getResult"/>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import moment from 'moment';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from 'laravel-vue-pagination';

const props = defineProps({
  receivedQuantities: Object,
  warehouses: Array,
  products: Array,
  filters: Object,
});

const form = ref({
  warehouse_id: props.filters?.warehouse_id || '',
  product_id: props.filters?.product_id || '',
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
  source: props.filters?.source || '',
  per_page: props.filters?.per_page || '15',
});

// Reference variables for multiselect components
const selectedWarehouse = ref(props.filters?.warehouse_id ? warehouses.find(w => w.id == props.filters.warehouse_id) : null);
const selectedProduct = ref(props.filters?.product_id ? products.find(p => p.id == props.filters.product_id) : null);
const selectedSource = ref(props.filters?.source ? {id: props.filters.source, name: props.filters.source === 'transfer' ? 'Transfer' : 'Packing List'} : null);

// Watch for changes in props.filters and update the form and selected values
watch(() => props.filters, (newFilters) => {
  if (newFilters) {
    form.value = {
      warehouse_id: newFilters.warehouse_id || '',
      product_id: newFilters.product_id || '',
      start_date: newFilters.start_date || '',
      end_date: newFilters.end_date || '',
      source: newFilters.source || '',
      per_page: newFilters.per_page || '15',
    };
    
    // Update selected values for multiselect components
    selectedWarehouse.value = newFilters.warehouse_id ? warehouses.find(w => w.id == newFilters.warehouse_id) : null;
    selectedProduct.value = newFilters.product_id ? products.find(p => p.id == newFilters.product_id) : null;
    selectedSource.value = newFilters.source ? {id: newFilters.source, name: newFilters.source === 'transfer' ? 'Transfer' : 'Packing List'} : null;
  }
}, { deep: true });

const applyFilters = () => {
  // Convert empty string values to null to ensure proper filtering
  const filters = {};
  Object.keys(form.value).forEach(key => {
    filters[key] = form.value[key] === '' ? null : form.value[key];
  });
  
  router.get(route('reports.receivedQuantities'), filters, {
    preserveState: true,
    replace: true,
  });
};

const getResult = (page) => {
  // Convert empty string values to null to ensure proper filtering
  const filters = {};
  Object.keys(form.value).forEach(key => {
    filters[key] = form.value[key] === '' ? null : form.value[key];
  });
  
  router.get(route('reports.receivedQuantities'), {
    ...filters,
    page: page
  }, {
    preserveState: true,
    replace: true,
  });
}

const resetFilters = () => {
  form.value = {
    warehouse_id: '',
    product_id: '',
    start_date: '',
    end_date: '',
    source: '',
    per_page: '15',
  };
  
  // Reset selected values for multiselect components
  selectedWarehouse.value = null;
  selectedProduct.value = null;
  selectedSource.value = null;
  
  applyFilters();
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return moment(dateString).format('DD/MM/YYYY');
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  return moment(dateTimeString).format('DD/MM/YYYY HH:mm:ss');
};

const formatReceivedAt = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  return moment(dateTimeString).format('DD/MM/YYYY');
};
</script>
