<template>
  <Head title="Facility Compliance Report" />
  <AuthenticatedLayout title="Facility Compliance Report" description="Facility reporting compliance and submission status" img="/assets/images/report.png">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Compliance</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Facility</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Month</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Submitted At</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Compliance Rate</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-if="rows.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-gray-500">No data found.</td>
            </tr>
            <tr v-for="row in rows" :key="row.facility + row.month" class="hover:bg-gray-50 transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ row.facility }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ row.month }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-700">{{ row.status }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-green-700">{{ row.submitted_at }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-700">{{ row.compliance_rate }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const props = defineProps({ rows: Array });
const rows = props.rows || [];
</script> 