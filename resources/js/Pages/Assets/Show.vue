<template>
  <AuthenticatedLayout :title="`Asset: ${asset.asset_tag}`" description="Detailed asset information" img="/assets/images/asset-header.png">
    <div class="">
      <!-- Header Section -->
      <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-10">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center space-x-4">
              <div class="p-3 bg-white/20 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-white"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM6.24 5h11.52l.83 1H5.41l.83-1zM5 19V8h14v11H5zm3-5h8v-2H8v2zm0-3h8V9H8v2z"/></svg>
              </div>
              <div>
                <h1 class="text-3xl font-bold text-white">{{ asset.asset_tag || 'Asset Details' }}</h1>
                <p class="text-blue-100 text-sm mt-1">
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold', asset.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">
                    {{ formatStatus(asset.status) || 'N/A' }}
                  </span>
                </p>
              </div>
            </div>
            <div class="mt-6 sm:mt-0 flex space-x-3">
              <Link 
                :href="route('assets.edit', asset.id)" 
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-indigo-700 bg-white hover:bg-indigo-50 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg> Edit Asset
              </Link>
              <Link 
                :href="route('assets.index')" 
                class="inline-flex items-center justify-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg> Back to List
              </Link>
            </div>
          </div>
        </div>

        <!-- Main Details Sections -->
        <div class="p-6 sm:p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <!-- Asset Information -->
            <section>
              <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-blue-500 mr-2"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>Asset Information
              </h2>
              <dl class="space-y-3">
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Serial Number</dt>
                  <dd class="col-span-2 text-sm text-gray-900 font-mono">{{ asset.serial_number || '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Category</dt>
                  <dd class="col-span-2 text-sm text-gray-900">{{ asset.category?.name || '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Description</dt>
                  <dd class="col-span-2 text-sm text-gray-900">{{ asset.item_description || '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                  <dd class="col-span-2 text-sm text-gray-900">{{ asset.person_assigned || '—' }}</dd>
                </div>
              </dl>
            </section>

            <!-- Location & Financial Details -->
            <section>
              <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-green-500 mr-2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>Location & Financials
              </h2>
              <dl class="space-y-3">
                 <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Location</dt>
                  <dd class="col-span-2 text-sm text-gray-900">{{ asset.location?.name || '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Sub-Location</dt>
                  <dd class="col-span-2 text-sm text-gray-900">{{ asset.sub_location?.name || '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Acquisition Date</dt>
                  <dd class="col-span-2 text-sm text-gray-900">{{ formatDate(asset.acquisition_date) || '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Original Value</dt>
                  <dd class="col-span-2 text-sm text-gray-900 font-semibold">{{ formatMoney(asset.original_value) || '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                  <dt class="text-sm font-medium text-gray-500">Fund Source</dt>
                  <dd class="col-span-2 text-sm text-gray-900">{{ asset.fund_source?.name || '—' }}</dd>
                </div>
              </dl>
            </section>
          </div>
        </div>
      </div>

      <!-- Asset History Section -->
      <div v-if="asset.history && asset.history.length" class="bg-white shadow-xl rounded-2xl p-6 sm:p-8 mb-10">
        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-purple-500 mr-3"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.25 2.52.77-1.28-3.52-2.09V8H12z"/></svg>Asset History
        </h2>
        <div class="flow-root">
          <ul role="list" class="-mb-8">
            <li v-for="(event, eventIdx) in asset.history" :key="event.id">
              <div class="relative pb-8">
                <span v-if="eventIdx !== asset.history.length - 1" class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                <div class="relative flex items-start space-x-3">
                  <div class="relative">
                    <span class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center ring-8 ring-white">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-purple-500"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 12c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div>
                      <div class="text-sm">
                        <strong class="font-medium text-gray-900">{{ event.custodian || 'System Event' }}</strong>
                      </div>
                      <p class="mt-0.5 text-xs text-gray-500">Assigned on {{ formatDate(event.assigned_at) }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-700">
                      <p v-if="event.status_notes" class="bg-gray-50 p-3 rounded-md">{{ event.status_notes }}</p>
                      <p v-if="event.returned_at" class="mt-1 text-sm text-green-700 font-medium">Returned on: {{ formatDate(event.returned_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Attachments Section -->
      <div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-teal-500 mr-3"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 015 0v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H14v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/></svg>Attachments
        </h2>
        <div v-if="asset.attachments && asset.attachments.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <a v-for="attachment in asset.attachments" :key="attachment.id" :href="attachment.url" target="_blank" 
             class="group flex items-center space-x-3 bg-gray-50 hover:bg-gray-100 p-4 rounded-lg transition-colors border border-gray-200 hover:border-teal-300">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-gray-400 group-hover:text-teal-500"><path d="M6 2c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/></svg>
            <span class="text-sm font-medium text-gray-700 group-hover:text-teal-600 truncate">{{ attachment.name || 'View Attachment' }}</span>
          </a>
        </div>
        <div v-else>
          <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16 text-gray-300"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4c-1.48 0-2.85.43-4.01 1.17l1.46 1.46C10.21 6.23 11.08 6 12 6c3.04 0 5.5 2.46 5.5 5.5v.5H19c1.66 0 3 1.34 3 3 0 1.13-.64 2.11-1.56 2.62l1.45 1.45C23.16 18.16 24 16.68 24 15c0-2.64-2.05-4.78-4.65-4.96zM3 5.27l2.75 2.74C2.56 8.15 0 10.77 0 14c0 3.31 2.69 6 6 6h11.73l2 2L21 20.73 4.27 4 3 5.27zM7.73 10l8 8H6c-2.21 0-4-1.79-4-4s1.79-4 4-4h1.73z"/></svg>
            <p class="mt-4 text-sm font-medium text-gray-500">No attachments found for this asset.</p>
          </div>
        </div>
        <!-- Add Document Section -->
      <div class="bg-white shadow rounded-lg p-6 mt-8">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-teal-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Add Document(s)
        </h3>
        <form @submit.prevent="submitDocuments">
          <div v-for="(doc, idx) in newDocuments" :key="idx" class="flex flex-col md:flex-row md:items-center gap-3 mb-4">
            <input type="text" v-model="doc.type" placeholder="Document Type" class="border rounded px-3 py-2 w-full md:w-1/3" required />
            <input type="file" accept="application/pdf" @change="onFileChange($event, idx)" class="w-full md:w-1/3" required />
            <button type="button" @click="removeDocumentRow(idx)" v-if="newDocuments.length > 1" class="text-red-600 hover:text-red-800 ml-2">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
          <div class="flex gap-2 mb-4">
            <button type="button" @click="addDocumentRow" class="inline-flex items-center px-3 py-1 bg-teal-100 text-teal-700 rounded hover:bg-teal-200 transition">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              Add Row
            </button>
            <button type="submit" :disabled="uploading" class="inline-flex items-center px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition disabled:opacity-60">
              <svg v-if="uploading" class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Upload
            </button>
          </div>
          <div v-if="docErrors.length" class="mb-2 text-red-600 text-sm">
            <ul>
              <li v-for="(err, i) in docErrors" :key="i">{{ err }}</li>
            </ul>
          </div>
        </form>
      </div>
      </div>

      

      <!-- Loading State Overlay -->
      <div v-if="loading" class="fixed inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="flex flex-col items-center">
          <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="mt-3 text-sm font-medium text-gray-700">Loading asset details...</p>
        </div>
      </div>

      <!-- Error Toast -->
      <div v-if="error" aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50">
        <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
          <div class="max-w-sm w-full bg-red-50 shadow-lg rounded-lg pointer-events-auto ring-1 ring-red-400 ring-opacity-5 overflow-hidden">
            <div class="p-4">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-400"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                  <p class="text-sm font-medium text-red-700">An Error Occurred!</p>
                  <p class="mt-1 text-sm text-red-600">{{ error }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

const props = defineProps({
  asset: Object,
});

const loading = ref(false);
const error = ref(null);
const uploading = ref(false);
const newDocuments = ref([
  { type: '', file: null, asset_id: props.asset.id }
]);
const docErrors = ref([]);

function addDocumentRow() {
    newDocuments.value.push({ type: '', file: null, asset_id: props.asset.id });
}
function removeDocumentRow(idx) {
    newDocuments.value.splice(idx, 1);
    if (newDocuments.value.length === 0) addDocumentRow();
}
function onFileChange(e, idx) {
  const file = e.target.files[0];
  if (file && file.type !== 'application/pdf') {
    docErrors.value.push(`File at row ${idx + 1} must be a PDF.`);
    newDocuments.value[idx].file = null;
    e.target.value = '';
    return;
  }
  newDocuments.value[idx].file = file;
}
async function submitDocuments() {
  docErrors.value = [];
  let hasError = false;
  newDocuments.value.forEach((doc, idx) => {
    if (!doc.type || !doc.file) {
      docErrors.value.push(`Row ${idx + 1}: Both type and PDF file are required.`);
      hasError = true;
    } else if (doc.file.type !== 'application/pdf') {
      docErrors.value.push(`Row ${idx + 1}: File must be a PDF.`);
      hasError = true;
    }
  });
  if (hasError) return;

  const formData = new FormData();
  newDocuments.value.forEach((doc, idx) => {
    formData.append(`type`, doc.type);
    formData.append(`file`, doc.file);
    formData.append(`asset_id`, doc.asset_id);
  });

  uploading.value = true;
  await axios.post(route('assets.documents.store'), {documents: formData}, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
    .then(response => {
      // Success: reload or update attachments
      console.log(response);
      
    })
    .catch(error => {
     console.log(error);
    })
    .finally(() => {
      uploading.value = false;
      newDocuments.value = [{ type: '', file: null, asset_id: props.asset.id }];
    });
}

function formatDate(date) {
  if (!date) return '-';
  return moment(date).format('DD/MM/YYYY');
}
function formatMoney(amount) {
  if (amount == null) return '-';
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
}
function formatStatus(status) {
  if (!status) return '-';
  const map = { in_use: 'In Use', maintenance: 'Maintenance', disposed: 'Disposed', lost: 'Lost' };
  return map[status] || status;
}

</script>