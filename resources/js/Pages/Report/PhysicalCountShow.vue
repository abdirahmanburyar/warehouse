<template>
    <AuthenticatedLayout title="Physical Count - Report" description="Inventory Verification Tool" img="/assets/images/report.png">
        <Head title="Physical Count Reports" />
        <div class="flex justify-between items-center mb-6">
            <Link :href="route('reports.physicalCount')">
                <div class="flex items-center">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight ml-2">
                        Physical Count Reports
                    </h2>
                </div>
            </Link>
        </div>

        <div class="flex justify-between items-center mb-6">
            <input type="month" v-model="month" class="border-black rounded-3xl w-[300px]">
            <div class="w-[300px]">
                <label for="per_page" class="block text-sm font-medium text-gray-700">Per Page</label>
                <select v-model="per_page" class="border-black rounded-3xl w-full">
                    <option value="100">100 per page</option>
                    <option value="200">200 per page</option>
                    <option value="500">500 per page</option>
                </select>
            </div>
        </div>

        <!-- Reports List -->
        <div>
            <div v-if="physicalCountReport.data.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjustment Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Counted Items</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="report in physicalCountReport.data" :key="report.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ formatDate(report.month_year) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ formatDateTime(report.adjustment_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ report.items.length }} items</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(report.status)">
                                    {{ report.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button 
                                    @click="openModal(report)"
                                    class="text-indigo-600 hover:text-indigo-900 font-medium"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
               <div class="mt-3 flex justify-end">
                <TailwindPagination
                    :data="props.physicalCountReport"
                    @pagination-change-page="getResult"
                />
               </div>
            </div>
            <div v-else class="text-center py-12">
                <p class="text-gray-500 text-lg">No physical count report data available.</p>
            </div>
        </div>

        <!-- Modal for Report Details -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-gray-500 bg-opacity-75" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full w-full p-4">
                <div class="w-full bg-white p-6 shadow-xl overflow-auto" id="reportContent">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Physical Count Report Details</h2>
                        <div class="flex gap-2">
                            <button
                                @click="downloadPDF"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                            >
                                Download PDF
                            </button>
                            <button
                                @click="closeModal"
                                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition"
                            >
                                Close
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedReport" class="space-y-6">
                        <!-- Report Status Information -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Reviewer Information -->
                            <div v-if="selectedReport.reviewer" class="bg-blue-50 p-4 rounded-lg">
                                <h5 class="font-semibold text-blue-800 mb-2">Reviewed By</h5>
                                <div class="space-y-1">
                                    <p><span class="text-gray-600">Name:</span> {{ selectedReport.reviewer.name }}</p>
                                    <p><span class="text-gray-600">Username:</span> {{ selectedReport.reviewer.username }}</p>
                                    <p><span class="text-gray-600">Date:</span> {{ formatDateTime(selectedReport.reviewed_at) }}</p>
                                </div>
                            </div>
                            
                            <!-- Approver Information -->
                            <div v-if="selectedReport.approver" class="bg-green-50 p-4 rounded-lg">
                                <h5 class="font-semibold text-green-800 mb-2">Approved By</h5>
                                <div class="space-y-1">
                                    <p><span class="text-gray-600">Name:</span> {{ selectedReport.approver.name }}</p>
                                    <p><span class="text-gray-600">Username:</span> {{ selectedReport.approver.username }}</p>
                                    <p><span class="text-gray-600">Date:</span> {{ formatDateTime(selectedReport.approved_at) }}</p>
                                </div>
                            </div>

                            <!-- Rejection Information -->
                            <div v-if="selectedReport.rejecter" class="bg-red-50 p-4 rounded-lg">
                                <h5 class="font-semibold text-red-800 mb-2">Rejected By</h5>
                                <div class="space-y-1">
                                    <p><span class="text-gray-600">Name:</span> {{ selectedReport.rejecter.name }}</p>
                                    <p><span class="text-gray-600">Username:</span> {{ selectedReport.rejecter.username }}</p>
                                    <p><span class="text-gray-600">Date:</span> {{ formatDateTime(selectedReport.rejected_at) }}</p>
                                    <p><span class="text-gray-600">Reason:</span> {{ selectedReport.rejection_reason || 'No reason provided' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Report Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold mb-3">Report Information</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Month/Year</p>
                                    <p class="font-medium">{{ formatDate(selectedReport.month_year) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Adjustment Date</p>
                                    <p class="font-medium">{{ formatDateTime(selectedReport.adjustment_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status</p>
                                    <span :class="getStatusClass(selectedReport.status)">
                                        {{ selectedReport.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <h4 class="text-md font-semibold p-4 bg-gray-50">Physical Count Items</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosage</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch Number</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Physical Count</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Difference</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in selectedReport.items" :key="item.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm">
                                                <div class="font-medium text-gray-900">{{ item.product?.name }}</div>
                                                <div class="text-gray-500 text-xs">ID: {{ item.product?.productID }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ item.uom || 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm">{{ item.product?.category?.name }}</td>
                                            <td class="px-4 py-3 text-sm">{{ item.product?.dosage?.name }}</td>
                                            <td class="px-4 py-3 text-sm">{{ item.barcode || 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm">{{ item.batch_number }}</td>
                                            <td class="px-4 py-3 text-sm">{{ moment(item.expiry_date).format('DD/MM/YYYY') }}</td>
                                            <td class="px-4 py-3 text-sm text-right">{{ item.quantity }}</td>
                                            <td class="px-4 py-3 text-sm text-right">{{ item.physical_count }}</td>
                                            <td class="px-4 py-3 text-sm text-right">
                                                <span :class="getDifferenceClass(item.difference)">
                                                    {{ item.difference }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ item.remark || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
        
<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import html2pdf from 'html2pdf.js';
import moment from 'moment';
import { TailwindPagination } from "laravel-vue-pagination";


const props = defineProps({
    physicalCountReport: Object,
    filters: Object
});

const isModalOpen = ref(false);
const selectedReport = ref(null);

const getStatusClass = (status) => {
    const baseClasses = 'px-3 py-1 rounded-full text-sm font-medium';
    const statusClasses = {
        pending: `${baseClasses} bg-yellow-100 text-yellow-800`,
        reviewed: `${baseClasses} bg-blue-100 text-blue-800`,
        approved: `${baseClasses} bg-green-100 text-green-800`,
        rejected: `${baseClasses} bg-red-100 text-red-800`
    };
    return statusClasses[status] || `${baseClasses} bg-gray-100 text-gray-800`;
};

const month = ref(props.filters.month);
const per_page = ref(props.filters.per_page);

watch([
    () => month.value,
    () => per_page.value,
    () => props.filters.page,
], () => {
    reloadPage();
})

function getResult(page = 1){
    props.filters.page = page;
}

function reloadPage(){
    const query = {}
    if(month.value) query.month = month.value;
    if(per_page.value) {
        props.filters.page = 1;
        query.per_page = per_page.value;
    };
    if(props.filters.page) query.page = props.filters.page;
    
    router.get(route('reports.physicalCountShow'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            'physicalCountReport'
        ]
    })
}

const getDifferenceClass = (difference) => {
    const baseClasses = 'px-2 py-1 rounded text-sm font-medium';
    if (difference < 0) {
        return `${baseClasses} bg-red-100 text-red-800`;
    } else if (difference > 0) {
        return `${baseClasses} bg-green-100 text-green-800`;
    }
    return `${baseClasses} text-gray-600`;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedReport.value = null;
};

const downloadPDF = async () => {
    // Create a clean version of the report for PDF
    const pdfContent = document.createElement('div');
    pdfContent.innerHTML = `
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="font-size: 24px; color: #1f2937; margin-bottom: 10px;">Physical Count Report</h1>
                <p style="font-size: 16px; color: #4b5563;">${formatDate(selectedReport.value.month_year)}</p>
            </div>

            <!-- Report Info -->
            <div style="margin-bottom: 20px; padding: 15px; background-color: #f3f4f6; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; width: 33%;"><strong>Status:</strong> ${selectedReport.value.status.toUpperCase()}</td>
                        <td style="padding: 8px; width: 33%;"><strong>Adjustment Date:</strong> ${formatDateTime(selectedReport.value.adjustment_date)}</td>
                        <td style="padding: 8px; width: 33%;"><strong>Total Items:</strong> ${selectedReport.value.items.length}</td>
                    </tr>
                </table>
            </div>

            <!-- Approval Info -->
            <div style="margin-bottom: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                ${selectedReport.value.reviewer ? `
                    <div style="padding: 15px; background-color: #dbeafe; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0; color: #1e40af;">Reviewed By</h3>
                        <p style="margin: 5px 0;"><strong>Name:</strong> ${selectedReport.value.reviewer.name}</p>
                        <p style="margin: 5px 0;"><strong>Date:</strong> ${formatDateTime(selectedReport.value.reviewed_at)}</p>
                    </div>
                ` : ''}
                ${selectedReport.value.approver ? `
                    <div style="padding: 15px; background-color: #dcfce7; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0; color: #166534;">Approved By</h3>
                        <p style="margin: 5px 0;"><strong>Name:</strong> ${selectedReport.value.approver.name}</p>
                        <p style="margin: 5px 0;"><strong>Date:</strong> ${formatDateTime(selectedReport.value.approved_at)}</p>
                    </div>
                ` : ''}
                ${selectedReport.value.rejecter ? `
                    <div style="padding: 15px; background-color: #fee2e2; border-radius: 8px;">
                        <h3 style="margin: 0 0 10px 0; color: #991b1b;">Rejected By</h3>
                        <p style="margin: 5px 0;"><strong>Name:</strong> ${selectedReport.value.rejecter.name}</p>
                        <p style="margin: 5px 0;"><strong>Date:</strong> ${formatDateTime(selectedReport.value.rejected_at)}</p>
                        ${selectedReport.value.rejection_reason ? `<p style="margin: 5px 0;"><strong>Reason:</strong> ${selectedReport.value.rejection_reason}</p>` : ''}
                    </div>
                ` : ''}
            </div>

            <!-- Items Table -->
            <div style="margin-top: 20px;">
                <h2 style="font-size: 18px; color: #1f2937; margin-bottom: 15px;">Physical Count Items</h2>
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #f3f4f6;">
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Item</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">UOM</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Category</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Dosage</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Barcode</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Batch Number</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Expiry Date</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Quantity</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Physical Count</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Difference</th>
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${selectedReport.value.items.map(item => `
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px 8px;">
                                    <div style="font-weight: 500;">${item.product.name}</div>
                                    <div style="color: #6b7280; font-size: 11px;">ID: ${item.product.productID}</div>
                                </td>
                                <td style="padding: 12px 8px;">${item.uom || 'N/A'}</td>
                                <td style="padding: 12px 8px;">${item.product.category?.name}</td>
                                <td style="padding: 12px 8px;">${item.product.dosage?.name}</td>
                                <td style="padding: 12px 8px;">${item.barcode || 'N/A'}</td>
                                <td style="padding: 12px 8px;">${item.batch_number}</td>
                                <td style="padding: 12px 8px;">${formatDate(item.expiry_date)}</td>
                                <td style="padding: 12px 8px; text-align: right;">${item.quantity}</td>
                                <td style="padding: 12px 8px; text-align: right;">${item.physical_count}</td>
                                <td style="padding: 12px 8px; text-align: right;">
                                    <span style="padding: 4px 8px; border-radius: 4px; ${item.difference < 0 ? 'background-color: #fee2e2; color: #991b1b;' : item.difference > 0 ? 'background-color: #dcfce7; color: #166534;' : 'color: #4b5563;'}">
                                        ${item.difference}
                                    </span>
                                </td>
                                <td style="padding: 12px 8px;">${item.remark || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #6b7280;">
                <p>Generated on ${new Date().toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'long' })}</p>
            </div>
        </div>
    `;

    // Configure PDF options
    const options = {
        margin: [15, 15],
        filename: `physical-count-report-${selectedReport.value.month_year}.pdf`,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            logging: false
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'landscape',
            compress: true
        },
        pagebreak: { mode: 'avoid-all' }
    };

    try {
        // Generate PDF
        await html2pdf().set(options).from(pdfContent).save();
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('There was an error generating the PDF. Please try again.');
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return moment(dateString).format('MMMM YYYY');
};

const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return 'N/A';
    return moment(dateTimeString).format('DD/MM/YYYY HH:mm:ss');
};

const openModal = (report) => {
    console.log(report);
    selectedReport.value = report;
    isModalOpen.value = true;
};

</script>