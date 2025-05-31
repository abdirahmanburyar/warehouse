<template>
  <Head title="Received Quantities Report" />
  <AuthenticatedLayout title="Received Quantities Report" description="Track all received inventory" img="/assets/images/report.png">

    <div>
      <!-- Filter Form -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Received Quantities Report</h2>
        <button
          type="button"
          @click="exportToExcel"
          class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
          {{ exportButtonText }}
        </button>
      </div>
      <form @submit.prevent="applyFilters" class="mb-6">
        <div class="flex flex-wrap items-end gap-3 pb-1">
          <div class="w-1/3">
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

          <!-- Warehouse filter removed as warehouse_id doesn't exist in the product table -->

          <div class="w-48">
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
            <input
              type="date"
              v-model="form.start_date"
              placeholder="Start Date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="w-40">
            <input
              type="date"
              v-model="form.end_date"
              placeholder="End Date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="flex items-end space-x-3">
            <button
              type="submit"
              class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-6 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 min-w-[120px] h-10"
            >
              Apply Filter
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-6 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 min-w-[120px] h-10"
            >
              Reset
            </button>
          </div>
          
          <div class="w-40 ml-2">
            <select
              v-model="form.per_page"
              @change="applyFiltersWithPageReset"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="100">100 per page</option>
              <option value="200">200 per page</option>
              <option value="500">500 per page</option>
              <option value="1000">1000 per page</option>
            </select>
          </div>
        </div>
      </form>

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
                Dosage Form
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Category
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                UOM
              </th>
              <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-black uppercase tracking-wider border border-black">
                Quantity
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
              <td colspan="11" class="px-6 py-4 text-center text-sm text-black border border-black">
                No received quantities found matching the criteria.
              </td>
            </tr>
            <tr v-for="(item, index) in receivedQuantities.data" :key="item.id" class="hover:bg-gray-50 border border-black">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border border-black text-center w-16">
                {{ (receivedQuantities.meta.current_page - 1) * receivedQuantities.meta.per_page + index + 1 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border border-black text-left w-1/5">
                {{ item.product ? item.product.name : 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                {{ item.product?.dosage?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                {{ item.product?.category?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                {{ item.uom || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-smwrap text-sm text-black border border-black">
                {{ item.quantity }}
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
import { ref, watch, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import moment from 'moment';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from 'laravel-vue-pagination';
import * as XLSX from 'xlsx';

// Loading state for export
const loading = ref(false);

const props = defineProps({
  receivedQuantities: Object,
  warehouses: Array,
  products: Array,
  filters: Object,
});

// Extract warehouses and products from props for easier access
const warehouses = ref(props.warehouses || []);
const products = ref(props.products || []);

// We'll use props.receivedQuantities directly as it's already reactive through Inertia

// Add a loading indicator to the export button
const exportButtonText = computed(() => loading.value ? 'Exporting...' : 'Export to Excel');

// Calculate total quantity received
const totalQuantity = computed(() => {
  if (!props.receivedQuantities || !props.receivedQuantities.data) return 0;
  return props.receivedQuantities.data.reduce((sum, item) => sum + Number(item.quantity || 0), 0).toLocaleString();
});

// Get the selected warehouse and product for display
const selectedWarehouseComputed = computed(() => {
  if (!form.value.warehouse_id) return null;
  return warehouses.value.find(w => w.id == form.value.warehouse_id);
});

const selectedProductComputed = computed(() => {
  if (!form.value.product_id) return null;
  return products.value.find(p => p.id == form.value.product_id);
});

const form = ref({
  product_id: props.filters?.product_id || '',
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
  source: props.filters?.source || '',
  per_page: props.filters?.per_page || '100',
});

// Reference variables for multiselect components
const selectedProduct = ref(props.filters?.product_id ? products.value.find(p => p.id == props.filters.product_id) : null);
const selectedSource = ref(props.filters?.source ? {id: props.filters.source, name: props.filters.source === 'transfer' ? 'Transfer' : 'Packing List'} : null);

// No need to watch props.receivedQuantities as we're using it directly

// Update the selected values when form changes
watch(() => form.value.product_id, (newValue) => {
  selectedProduct.value = newValue ? products.value.find(p => p.id == newValue) : null;
});

// Watch for changes in props.filters and update the form and selected values
watch(() => props.filters, (newFilters) => {
  if (newFilters) {
    form.value = {
      product_id: newFilters.product_id || '',
      start_date: newFilters.start_date || '',
      end_date: newFilters.end_date || '',
      source: newFilters.source || '',
      per_page: newFilters.per_page || '1000',
    };
    
    // Update selected values for multiselect components
    selectedProduct.value = newFilters.product_id ? products.value.find(p => p.id == newFilters.product_id) : null;
    selectedSource.value = newFilters.source ? {id: newFilters.source, name: newFilters.source === 'transfer' ? 'Transfer' : 'Packing List'} : null;
  }
}, { deep: true });

const applyFiltersWithPageReset = () => {
  // Apply filters and reset to page 1
  const filters = {};
  Object.keys(form.value).forEach(key => {
    // Only add non-empty values to the filters object
    if (form.value[key] !== '' && form.value[key] !== null) {
      filters[key] = form.value[key];
    }
  });
  
  // Explicitly set page to 1 when changing filters
  router.get(route('reports.receivedQuantities'), filters, {
    preserveState: true,
    replace: true,
  });
};

const applyFilters = () => {
  // Use the page reset function
  applyFiltersWithPageReset();
};

const getResult = (page) => {
  // Only include non-empty filter values
  const params = {};
  
  // Process form values
  // Warehouse filter removed as warehouse_id doesn't exist in the product table
  if (form.value.product_id && form.value.product_id !== '') params.product_id = form.value.product_id;
  if (form.value.source && form.value.source !== '') params.source = form.value.source;
  if (form.value.start_date && form.value.start_date !== '') params.start_date = form.value.start_date;
  if (form.value.end_date && form.value.end_date !== '') params.end_date = form.value.end_date;
  if (form.value.per_page) params.per_page = form.value.per_page;
  
  // Always include page number
  params.page = page;
  
  router.get(route('reports.receivedQuantities'), params, {
    preserveState: true,
    replace: true,
  });
}

const resetFilters = () => {
  form.value = {
    product_id: '',
    start_date: '',
    end_date: '',
    source: '',
    per_page: '100',
  };
  
  // Reset selected values for multiselect components
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

// Excel export function
const exportToExcel = () => {
  // Get all data (not just current page) by removing pagination
  const filters = {};
  Object.keys(form.value).forEach(key => {
    // Only add non-empty values to the filters object
    if (form.value[key] !== '' && form.value[key] !== null && key !== 'per_page') {
      filters[key] = form.value[key];
    }
  });
  
  // Add a large per_page value to get all data
  filters.per_page = 10000;
  
  // Set loading state
  loading.value = true;
  
  // Fetch all data for export
  router.visit(route('reports.receivedQuantities'), { data: filters, only: ['receivedQuantities'], preserveState: true, onSuccess: (page) => {
      // Prepare the data for export
      const exportData = page.props.receivedQuantities.data.map((item, index) => ({
        'SN': index + 1,
        'Item': item.product ? item.product.name : 'N/A',
        'Dosage Form': item.product?.dosage?.name || 'N/A',
        'Category': item.product?.category?.name || 'N/A',
        'UOM': item.uom || 'N/A',
        'Quantity': item.quantity,
        'Batch #': item.batch_number || 'N/A',
        'Expiry Date': item.expiry_date ? formatDate(item.expiry_date) : 'N/A',
        'Source': item.transfer_id ? `Transfer #${item.transfer_id}` : 
                 item.packing_list_id ? `Packing List #${item.packing_list_id}` : 'N/A',
        'Received By': item.receiver ? item.receiver.name : 'N/A',
        'Received At': formatReceivedAt(item.received_at)
      }));

      // Calculate total quantity
      const totalQty = exportData.reduce((sum, item) => sum + Number(item['Quantity'] || 0), 0);
      
      // Create summary data
      const summaryData = [
        ['Received Quantities Report', '', '', '', '', '', '', '', '', '', ''],
        ['Generated on:', formatDateTime(new Date()), '', '', '', '', '', '', '', '', ''],
        ['', '', '', '', '', '', '', '', '', '', ''],
        ['Total Quantity Received:', totalQty.toLocaleString(), '', '', '', '', '', '', '', '', ''],
        ['', '', '', '', '', '', '', '', '', '', '']
      ];
      
      // Add date range if applicable
      if (form.value.start_date || form.value.end_date) {
        summaryData.push([
          'Date Range:',
          form.value.start_date ? formatDate(form.value.start_date) : 'All time',
          'to',
          form.value.end_date ? formatDate(form.value.end_date) : 'Present',
          '', '', '', '', '', '', ''
        ]);
        summaryData.push(['', '', '', '', '', '', '', '', '', '', '']);
      }
      
      // Warehouse filter removed as warehouse_id doesn't exist in the product table
      
      // Add product filter if applicable
      if (form.value.product_id) {
        const product = products.value.find(p => p.id == form.value.product_id);
        if (product) {
          summaryData.push(['Item:', product.name, '', '', '', '', '', '', '', '', '']);
        }
      }
      
      // Add source filter if applicable
      if (form.value.source) {
        summaryData.push(['Source:', form.value.source === 'transfer' ? 'Transfer' : 'Packing List', '', '', '', '', '', '', '', '', '']);
      }
      
      // Add empty row before data
      summaryData.push(['', '', '', '', '', '', '', '', '', '', '']);
      summaryData.push(['', '', '', '', '', '', '', '', '', '', '']);
      
      // Add headers row
      const headers = [
        'SN', 'Item', 'Dosage Form', 'Category', 'UOM', 'Quantity', 'Batch #', 'Expiry Date', 'Source', 'Received By', 'Received At'
      ];
      summaryData.push(headers);
      
      // Create worksheet with summary data first
      const worksheet = XLSX.utils.aoa_to_sheet(summaryData);
      
      // Append the actual data starting after the summary (skipHeader=true to avoid duplicate headers)
      XLSX.utils.sheet_add_json(worksheet, exportData, { 
        origin: summaryData.length,
        skipHeader: true,
        header: headers
      });
      
      // Style the headers (make them bold)
      const headerRowIndex = summaryData.length - 1;
      const headerRange = XLSX.utils.decode_range(worksheet['!ref']);
      for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
        const cellRef = XLSX.utils.encode_cell({ r: 0, c: col }); // Title row
        if (!worksheet[cellRef]) worksheet[cellRef] = {};
        worksheet[cellRef].s = { font: { bold: true, sz: 14 } };
        
        const headerCellRef = XLSX.utils.encode_cell({ r: headerRowIndex, c: col }); // Headers row
        if (!worksheet[headerCellRef]) worksheet[headerCellRef] = {};
        worksheet[headerCellRef].s = { font: { bold: true } };
      }
      
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Received Quantities');

      // Generate file name with current date and applied filters
      const date = new Date().toISOString().split('T')[0];
      let fileName = `received_quantities_${date}`;
      
      // Warehouse filter removed as warehouse_id doesn't exist in the product table
      
      if (form.value.product_id) {
        const product = products.value.find(p => p.id == form.value.product_id);
        if (product) fileName += `_${product.name.replace(/\s+/g, '_')}`;
      }
      
      if (form.value.start_date) fileName += `_from_${form.value.start_date}`;
      if (form.value.end_date) fileName += `_to_${form.value.end_date}`;
      if (form.value.source) fileName += `_${form.value.source}`;
      
      fileName += '.xlsx';

      // Save the file
      XLSX.writeFile(workbook, fileName);
      loading.value = false;
    },
    onError: (errors) => {
      console.error('Error exporting data:', errors);
      loading.value = false;
    }
  });
};
</script>
