<template>
  <Head title="Physical Count Report" />
  <AuthenticatedLayout title="Physical Count Report" description="Inventory Verification Tool" img="/assets/images/report.png">
    <div class="py-6 px-4 mb-[100px]">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Physical Count Report</h1>
        <div class="flex gap-3">
          <button 
            @click="savePhysicalCount" 
            :disabled="saving"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            {{ saveButtonText }}
          </button>
          <button 
            @click="exportToExcel" 
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            {{ exportButtonText }}
          </button>
        </div>
      </div>
      
      <form @submit.prevent="applyFilters" class="mb-6">
        <div class="flex flex-wrap items-end gap-3 pb-1">
          <div class="w-1/3">
            <Multiselect
              v-model="selectedProduct"
              :options="props.products"
              :searchable="true"
              :close-on-select="true"
              :show-labels="false"
              placeholder="All Items"
              label="name"
              track-by="id"
              :allow-empty="true"
              @select="handleSelectProduct"
              @remove="() => { form.product_id = ''; }"
              class="w-full"
            >
              <template #singleLabel="{ option }">
                <span>{{ option.name }}</span>
              </template>
            </Multiselect>
          </div>

          <div class="w-1/3">
            <Multiselect
              v-model="selectedCategory"
              :options="props.categories"
              :searchable="true"
              :close-on-select="true"
              :show-labels="false"
              placeholder="All Categories"
              label="name"
              track-by="id"
              :allow-empty="true"
              @select="handleSelectCategory"
              @remove="() => { form.category_id = ''; }"
              class="w-full"
            >
              <template #singleLabel="{ option }">
                <span>{{ option.name }}</span>
              </template>
            </Multiselect>
          </div>

          <div class="w-48">
            <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input
              type="date"
              id="expiry_date"
              v-model="form.expiry_date"
              @change="applyFiltersWithPageReset"
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

          <div class="ml-auto">
            <label for="per_page" class="block text-sm font-medium text-gray-700">Items per page</label>
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
                Remarks
              </th>
            </tr>
          </thead>
          <tbody class="bg-white border-b border-black">
            <tr v-if="props.inventories.data.length === 0">
              <td colspan="8" class="px-6 py-4 text-center text-sm text-black border border-black">
                No products found matching the criteria.
              </td>
            </tr>
            <tr v-for="(item, index) in props.inventories.data" :key="item.id" class="hover:bg-gray-50 border border-black">
              <td class="border px-4 py-2 text-center">{{ index + 1 }}</td>
              <td class="border px-4 py-2">{{ item.product ? item.product.name : 'Unknown Product' }}</td>
              <td class="border px-4 py-2 text-center">{{ item.expiry_date ? formatDate(item.expiry_date) : 'N/A' }}</td>
              <td class="border px-4 py-2 text-center">{{ item.product && item.product.uom ? item.product.uom : 'N/A' }}</td>
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
              <td class="border px-4 py-2 text-left">
                {{ calculateDifferenceValue(item) }}
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

      <!-- Pagination -->
      <div class="mt-4 flex items-center justify-end">
        <TailwindPagination :data="props.inventories" @pagination-change-page="getResult"/>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from 'laravel-vue-pagination';
import moment from 'moment';

const props = defineProps({
  inventories: Object,
  products: Array,
  categories: Array,
  filters: Object,
});

// Reactive data
const loading = ref(false);
const saving = ref(false);
const exportButtonText = ref('Export to Excel');
const saveButtonText = ref('Save Physical Count');

// Form for filters
const form = ref({
  product_id: props.filters?.product_id || '',
  category_id: props.filters?.category_id || '',
  expiry_date: props.filters?.expiry_date || '',
  per_page: props.filters?.per_page || '100',
});

// Reference variables for multiselect components
const selectedProduct = ref(props.filters?.product_id ? 
  props.products.find(p => p.id == props.filters.product_id) : null);
const selectedCategory = ref(props.filters?.category_id ? 
  props.categories.find(c => c.id == props.filters.category_id) : null);

// Initialize physical_count and remarks fields for each inventory item
if (props.inventories && props.inventories.data) {
  props.inventories.data.forEach(item => {
    // Set default values if not already set
    if (item.physical_count === undefined) {
      item.physical_count = item.quantity || 0;
    }
    if (item.remarks === undefined) {
      item.remarks = '';
    }
  });
}

// Calculate difference between system quantity and physical count
const calculateDifference = (item) => {
  // Ensure physical_count is a number
  if (item.physical_count === '' || item.physical_count === null) {
    item.physical_count = 0;
  }
};

// Calculate and return the difference value
const calculateDifferenceValue = (item) => {
  const systemQty = item.quantity || 0;
  const physicalQty = item.physical_count || 0;
  return physicalQty - systemQty;
};

// Watch for changes in props.filters and update the form and selected values
watch(() => props.filters, (newFilters) => {
  if (newFilters) {
    form.value = {
      product_id: newFilters.product_id || '',
      category_id: newFilters.category_id || '',
      expiry_date: newFilters.expiry_date || '',
      per_page: newFilters.per_page || '100',
    };
    
    // Update selected values for multiselect components
    selectedProduct.value = newFilters.product_id ? props.products.find(p => p.id == newFilters.product_id) : null;
    selectedCategory.value = newFilters.category_id ? props.categories.find(c => c.id == newFilters.category_id) : null;
  }
}, { deep: true });

const handleSelectProduct = (option) => {
  form.value.product_id = option.id;
};

const handleSelectCategory = (option) => {
  form.value.category_id = option.id;
};  

// Update the selected values when form changes
watch(() => form.value.product_id, (newValue) => {
  selectedProduct.value = newValue ? props.products.find(p => p.id == newValue) : null;
});

watch(() => form.value.category_id, (newValue) => {
  selectedCategory.value = newValue ? props.categories.find(c => c.id == newValue) : null;
});

// Methods
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
  router.get(route('reports.physicalCount'), filters, {
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
  if (form.value.product_id && form.value.product_id !== '') params.product_id = form.value.product_id;
  if (form.value.category_id && form.value.category_id !== '') params.category_id = form.value.category_id;
  if (form.value.expiry_date && form.value.expiry_date !== '') params.expiry_date = form.value.expiry_date;
  if (form.value.per_page) params.per_page = form.value.per_page;
  
  // Always include page number
  params.page = page;
  
  router.get(route('reports.physicalCount'), params, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  form.value = {
    product_id: '',
    category_id: '',
    expiry_date: '',
    per_page: '100',
  };
  
  // Reset selected values for multiselect components
  selectedProduct.value = null;
  selectedCategory.value = null;
  
  applyFilters();
};

// Helper functions for date formatting
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return moment(dateString).format('DD/MM/YYYY');
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  return moment(dateTimeString).format('DD/MM/YYYY HH:mm:ss');
};

// Save physical count data
const savePhysicalCount = () => {
  saving.value = true;
  saveButtonText.value = 'Saving...';

  // Prepare the data to be saved
  const physicalCountData = props.inventories.data.map(item => ({
    inventory_id: item.id,
    physical_count: item.physical_count || 0,
    remarks: item.remarks || '',
    difference: calculateDifferenceValue(item)
  }));

  // Send the data to the server
  router.post(route('reports.savePhysicalCount'), {
    physical_count_data: physicalCountData,
    filters: form.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      saving.value = false;
      saveButtonText.value = 'Save Physical Count';
      // Show success message or handle success
    },
    onError: () => {
      saving.value = false;
      saveButtonText.value = 'Save Physical Count';
      // Show error message or handle error
    }
  });
};

// Export to Excel
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
  exportButtonText.value = 'Exporting...';

  // Fetch all data for export
  router.visit(route('reports.physicalCount'), { data: filters, only: ['inventories'], preserveState: true, onSuccess: (page) => {
      // Map the data to the format needed for Excel
      const exportData = page.props.inventories.data.map((item, index) => ({
        'SN': index + 1,
        'Item': item.product ? item.product.name : 'N/A',
        'Category': item.product && item.product.category ? item.product.category.name : 'N/A',
        'Batch #': item.batch_number || 'N/A',
        'Expiry Date': item.expiry_date ? formatDate(item.expiry_date) : 'N/A',
        'UOM': item.product && item.product.uom ? item.product.uom : 'N/A',
        'System Quantity': item.quantity || 0,
        'Physical Count': item.physical_count || 0,
        'Difference': calculateDifferenceValue(item),
        'Remarks': item.remarks || '',
      }));

      // Create summary data
      const summaryData = [
        ['Physical Count Report'],
        ['Generated on:', formatDateTime(new Date())],
        ['Filters:'],
        ['Product:', selectedProduct.value ? selectedProduct.value.name : 'All Products'],
        ['Category:', selectedCategory.value ? selectedCategory.value.name : 'All Categories'],
        ['Expiry Date:', form.value.expiry_date ? formatDate(form.value.expiry_date) : 'All Dates'],
        [''],
        ['Summary:'],
        ['Total Items:', page.props.inventories.data.length],
        ['Items with Differences:', page.props.inventories.data.filter(item => calculateDifferenceValue(item) !== 0).length],
        [''],
        ['Report Data:'],
      ];

      // Add date filter if applicable
      if (form.value.expiry_date) {
        summaryData.push(['Expiry Date:', formatDate(form.value.expiry_date), '', '', '', '', '', '']);
        summaryData.push(['', '', '', '', '', '', '', '']);
      }

      // Add product filter if applicable
      if (form.value.product_id) {
        const product = props.products.find(p => p.id == form.value.product_id);
        if (product) {
          summaryData.push(['Item:', product.name, '', '', '', '', '', '']);
        }
      }
      
      // Add category filter if applicable
      if (form.value.category_id) {
        const category = props.categories.find(c => c.id == form.value.category_id);
        if (category) {
          summaryData.push(['Category:', category.name, '', '', '', '', '', '']);
        }
      }
      
      // Add empty row before data
      summaryData.push(['', '', '', '', '', '', '', '']);
      summaryData.push(['', '', '', '', '', '', '', '']);
      
      // Add headers row
      const headers = [
        'SN', 'Item', 'Expiry Date', 'UOM', 'QTY on Report', 'Physical Count', 'Difference', 'Remarks'
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
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Physical Count');

      // Generate file name with current date and applied filters
      const date = new Date().toISOString().split('T')[0];
      let fileName = `physical_count_${date}`;
      
      if (form.value.product_id) {
        const product = props.products.find(p => p.id == form.value.product_id);
        if (product) fileName += `_${product.name.replace(/\s+/g, '_')}`;
      }
      
      if (form.value.category_id) {
        const category = props.categories.find(c => c.id == form.value.category_id);
        if (category) fileName += `_${category.name.replace(/\s+/g, '_')}`;
      }
      
      if (form.value.expiry_date) fileName += `_expiry_${form.value.expiry_date}`;
      
      fileName += '.xlsx';

      // Save the file
      XLSX.writeFile(workbook, fileName);
      loading.value = false;
      exportButtonText.value = 'Export to Excel';
    },
    onError: (errors) => {
      console.error('Error exporting data:', errors);
      loading.value = false;
      exportButtonText.value = 'Export to Excel';
    }
  });
};
</script>