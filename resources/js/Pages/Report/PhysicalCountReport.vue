<template>
    <AuthenticatedLayout title="Physical Count Report" description="Inventory Verification Tool" img="/assets/images/report.png">
       <div class="mb-[100px]">
        <div class="flex justify-between mb-4">
            <div class="flex items-center">
                <h2 class="text-xl font-semibold">{{ monthYearFormatted }}</h2>
            </div>
            <button 
                type="button" 
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full"
                @click="handleButtonClick"
                v-if="$page.props.auth.can.report_physical_count_generate"
            >
                Generate
            </button>
        </div>
        
        <div v-if="hasAdjustment" class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-black">Physical Counting Report</h3>
                <p class="mt-1 max-w-2xl text-sm text-black">
                    Created on {{ formatDate(props.physicalCountReport.adjustment_date) }}
                </p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span :class="getItemStatusClass(props.physicalCountReport.status)">{{ props.physicalCountReport.status.toUpperCase() }}</span>
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Total Items</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ props.physicalCountReport.items ? props.physicalCountReport.items.length : 0 }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Adjustment ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ props.physicalCountReport.id }}
                        </dd>
                    </div>
                    <div v-if="props.physicalCountReport.reviewed_by" class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Reviewed By</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ props.physicalCountReport.reviewer ? props.physicalCountReport.reviewer.name : 'N/A' }}
                            <span v-if="props.physicalCountReport.reviewed_at" class="text-xs text-gray-500 ml-2">
                                ({{ formatDate(props.physicalCountReport.reviewed_at) }})
                            </span>
                        </dd>
                    </div>
                    <div v-if="props.physicalCountReport.approved_by" class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ props.physicalCountReport.approver ? props.physicalCountReport.approver.name : 'N/A' }}
                            <span v-if="props.physicalCountReport.approved_at" class="text-xs text-gray-500 ml-2">
                                ({{ formatDate(props.physicalCountReport.approved_at) }})
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div v-if="hasAdjustment && props.physicalCountReport.items && props.physicalCountReport.items.length > 0" class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 flex justify-between">
                <h3 class="text-lg leading-6 font-medium text-black">Inventory Items</h3>
                <div class="relative">
                    <input 
                        type="text" 
                        v-model="search" 
                        placeholder="Search items..." 
                        class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-black">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Item</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">UOM</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Barcode</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Expiry Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Batch Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Warehouse</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">System Qty</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider w-40">Physical Count</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Difference</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in filteredItems" :key="item.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-black">{{ item.product ? item.product.name : 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ item.uom || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ item.barcode || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ formatDate(item.expiry_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ item.batch_number || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ item.warehouse ? item.warehouse.name : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ item.location ? item.location.location : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                {{ item.quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                <input 
                                    type="number" 
                                    v-model="item.physical_count" 
                                    class="w-32 px-2 py-1 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                    min="0"
                                    @input="item.difference = calculateDifference(item)"
                                />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getDifferenceClass(item)">
                                <input 
                                    type="text" 
                                    v-model="item.difference" 
                                    class="w-full px-2 py-1 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                    placeholder="Enter difference"
                                    readonly
                                />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                <input 
                                    type="text" 
                                    v-model="item.remarks" 
                                    class="w-full px-2 py-1 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                    placeholder="Enter remarks"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex justify-end mt-3 pr-6 pb-4">
                    <button v-if="props.physicalCountReport.status === 'pending'" :disabled="isSubmitting" @click="submitPhysicalCount" type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        {{ isSubmitting ? "Submitting..." : "Submit" }}
                    </button>
                    <button v-else-if="props.physicalCountReport.status === 'submitted' && $page.props.auth.can.report_physical_count_review" type="button" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded-lg"
                    @click="reviewPhysicalCount">
                        Review
                    </button>
                    <div v-else-if="props.physicalCountReport.status == 'reviewed' && $page.props.auth.can.report_physical_count_approve" class="flex gap-3">
                        <button type="button" class="bg-green-300 hover:bg-green-400 text-black font-bold py-2 px-4 rounded-lg"
                        @click="approvePhysicalCount"
                        :disabled="isApproving"
                        >
                            {{ isApproving ? "Approving..." : "Approve" }}
                        </button>
                        <button v-if="$page.props.auth.can.report_physical_count_approve" type="button" class="bg-red-300 hover:bg-red-400 text-black font-bold py-2 px-4 rounded-lg"
                        @click="rejectPhysicalCount"
                        :disabled="isRejecting"
                        >
                            {{ isRejecting ? "Rejecting..." : "Reject" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!hasAdjustment" class="bg-white shadow overflow-hidden sm:rounded-lg p-6 text-center mb-[100px]">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-black">No physical count adjustment</h3>
            <p class="mt-1 text-sm text-black">
                Click the Generate button to prepare inventory for physical count adjustment.
            </p>
        </div>
       </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import moment from 'moment';
import Swal from "sweetalert2";
import axios from "axios";

const props = defineProps({
    physicalCountReport: Object,
    currentMonthYear: String
});

const isSubmitting = ref(false);
const search = ref('');

// Computed properties
const hasAdjustment = computed(() => {
    return props.physicalCountReport && Object.keys(props.physicalCountReport).length > 0;
});

const monthYearFormatted = computed(() => {
    return moment(props.currentMonthYear).format('MMMM YYYY');
});

function submitPhysicalCount(){
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to submit the physical count report',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, submit!'
    }).then(async (result) => {
        if (result.isConfirmed) {
             // Show loading alert with counter
             let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Submitting Physical Count Data',
                html: 'Submitting physical count Data... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            await axios.post(route('reports.physical-count.update'), {
                id: props.physicalCountReport.id,
                items: props.physicalCountReport.items
            })
            .then(response => {
                console.log(response);
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
                Swal.fire({
                    title: 'Success!',
                    text: response.data,
                    icon: 'success'
                })
                .then(() => {
                    router.get(route('reports.physicalCount'));
                })
            })
            .catch(error => {
                console.log(error);

                Swal.fire({
                    title: 'Error!',
                    text: error.response.data,
                    icon: 'error'
                });
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
            });
        }
    });
}

function reviewPhysicalCount(){
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to review the physical count report',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, reviewed!'
    }).then(async (result) => {
        if (result.isConfirmed) {
             // Show loading alert with counter
             let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Marking Physical Count Data as Reviewed',
                html: 'Marking physical count data as reviewed... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            await axios.post(route('reports.physical-count.status'), {
                id: props.physicalCountReport.id,
                status: 'reviewed'
            })
            .then(response => {
                console.log(response);
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
                Swal.fire({
                    title: 'Success!',
                    text: response.data,
                    icon: 'success'
                })
                .then(() => {
                    router.get(route('reports.physicalCount'));
                })
            })
            .catch(error => {
                console.log(error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response.data.message,
                    icon: 'error'
                });
                isSubmitting.value = false;
                clearInterval(timerInterval);
                Swal.close();
            });
        }
    });
}

const isApproving = ref(false);

function approvePhysicalCount(){
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to approve the physical count report',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approved!'
    }).then(async (result) => {
        if (result.isConfirmed) {
             // Show loading alert with counter
             isApproving.value = true;
             let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Marking Physical Count Data as Approved',
                html: 'Marking physical count data as approved... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            await axios.post(route('reports.physical-count.approve'), {
                id: props.physicalCountReport.id,
                status: 'approved'
            })
            .then(response => {
                console.log(response);
                isApproving.value = false;
                clearInterval(timerInterval);
                Swal.close();
                Swal.fire({
                    title: 'Success!',
                    text: response.data,
                    icon: 'success'
                })
                .then(() => {
                    router.get(route('reports.physicalCount'));
                })
            })
            .catch(error => {
                console.log(error);

                Swal.fire({
                    title: 'Error!',
                    text: error.response.data,
                    icon: 'error'
                });
                isApproving.value = false;
                clearInterval(timerInterval);
                Swal.close();
            });
        }
    });
}

const isRejecting = ref(false);

function rejectPhysicalCount(){
    Swal.fire({
        title: 'Reject Physical Count Report',
        text: 'Please provide a reason for rejection',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter your reason for rejection...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
            'maxlength': '500',
            'rows': '4'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Reject Report',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a rejection reason!'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            isRejecting.value = true;
            let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Marking Physical Count Data as Rejected',
                html: 'Marking physical count data as rejected... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });

            try {
                await axios.post(route('reports.physical-count.reject'), {
                    id: props.physicalCountReport.id,
                    status: 'rejected',
                    rejection_reason: result.value
                });
                
                clearInterval(timerInterval);
                Swal.close();
                
                await Swal.fire({
                    title: 'Rejected!',
                    text: 'Physical count report has been rejected successfully.',
                    icon: 'success'
                });
                
                router.get(route('reports.physicalCount'));
            } catch (error) {
                console.error(error);
                clearInterval(timerInterval);
                Swal.close();
                
                await Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'An error occurred while rejecting the report.',
                    icon: 'error'
                });
            } finally {
                isRejecting.value = false;
            }
        }
    });
}


const filteredItems = computed(() => {
    if (!hasAdjustment.value || !props.physicalCountReport.items) return [];
    
    if (!search.value) return props.physicalCountReport.items;
    
    const searchTerm = search.value.toLowerCase();
    return props.physicalCountReport.items.filter(item => {
        const productName = item.product ? item.product.name.toLowerCase() : '';
        const batchNumber = item.batch_number ? item.batch_number.toLowerCase() : '';
        
        return productName.includes(searchTerm) || batchNumber.includes(searchTerm);
    });
});

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return moment(dateString).format('DD/MM/YYYY');
};

const calculateDifference = (item) => {
    if (item.physical_count === null || item.quantity === null) return 'N/A';
    return item.physical_count - item.quantity;
};

const getDifferenceClass = (item) => {
    const diff = calculateDifference(item);
    if (diff === 'N/A') return 'text-gray-500';
    if (diff < 0) return 'text-red-600 font-medium';
    if (diff > 0) return 'text-green-600 font-medium';
    return 'text-gray-500';
};

const getItemStatusClass = (status) => {
    const statusClasses = {
        'pending': 'px-2 py-1 text-lg font-medium rounded-full bg-yellow-100 text-yellow-800',
        'submitted': 'px-2 py-1 text-lg font-medium rounded-full bg-green-100 text-green-800',
        'reviewed': 'px-2 py-1 text-lg font-medium rounded-full bg-blue-100 text-blue-800',
        'approved': 'px-2 py-1 text-lg font-medium rounded-full bg-green-500 text-green-800',
        'rejected': 'px-2 py-1 text-lg font-medium rounded-full bg-red-100 text-red-800'
    };
    
    return statusClasses[status] || 'px-2 py-1 text-lg font-medium rounded-full bg-gray-100 text-gray-800';
};


const handleButtonClick = () => {
    Swal.fire({
        title: 'Prepare Inventory for Adjustments',
        text: 'Are you sure you want to prepare the warehouse inventory for physical count adjustments?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!'
    }).then( async (result) => {
        if (result.isConfirmed) {
            isSubmitting.value = true;
            
            // Show loading alert with counter
            let timerInterval;
            let counter = 0;
            
            Swal.fire({
                title: 'Preparing Inventory Data',
                html: 'Processing inventory data... <b>0</b> seconds',
                timer: 60000, // 60 seconds max wait time
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        counter++;
                        if (b) {
                            b.textContent = counter;
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            
            await axios.post(route('reports.physicalCountReport'))
                .then(response => {
                    isSubmitting.value = false;
                    clearInterval(timerInterval);
                    Swal.close();
                    
                    console.log(response.data);
                    
                    Swal.fire({
                        title: 'Preparation Complete',
                        text: 'Warehouse inventory has been successfully prepared for adjustments.',
                        icon: 'success'
                    }).then(() => {
                        // Reload the page to show the new data
                        window.location.reload();
                    });
                })
                .catch(error => {
                    isSubmitting.value = false;
                    clearInterval(timerInterval);
                    Swal.close();
                    
                    // Get the error message from the response if available
                    const errorMessage = error.response && error.response.data && error.response.data.message
                        ? error.response.data.message
                        : 'There was an error preparing inventory for adjustments: ' + error.message;
                    
                    Swal.fire({
                        title: 'Cannot Proceed',
                        text: errorMessage,
                        icon: 'warning'
                    });
                    console.error(error);
                });
        }
    });
};
</script>
