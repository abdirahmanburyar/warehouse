<template>
    <AuthenticatedLayout title="Warehouse Monthly Report" description="Monthly Inventory Movement Summary" img="/assets/images/report.png">
        <Head title="Warehouse Monthly Report" />
        
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            {{ props.reportData }}
        </div>
        <!-- ... -->
    </AuthenticatedLayout>
</template>

<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import { ref, computed, watch, onMounted } from 'vue';
    import axios from 'axios';
    import Swal from 'sweetalert2';
    import moment from 'moment';

    const props = defineProps({
        reportData: {
            type: Array,
            default: () => []
        },
        filters: {
            type: Object,
            default: () => ({})
        },
        inventoryReport: {
            type: Object,
            default: () => ({})
        }
    });
    const currentAdjustments = ref({});
    const originalAdjustments = ref({});
    const month_year = ref(props.filters.month_year);
    const cellLoading = ref({});

    // Initialize cell loading states
    onMounted(() => {
        props.reportData.forEach(item => {
            cellLoading.value[item.product.id] = {
                positive_adjustment: false,
                negative_adjustment: false,
                months_of_stock: false
            };
        });
    });
    const processing = ref(false);
    const saving = ref(false);



    watch([
        () => month_year.value,
    ], () => {
        reloadData();
    });

    // Initialize adjustments when component is mounted
    onMounted(() => {
        initializeAdjustments();
    });

    function reloadData(){
        const query = {}
        if(month_year.value) query.month_year = month_year.value
        router.get(route('reports.warehouseMonthly', query, {
            preserveState: true,
            preserveScroll: true,
            only: [
                'reportData'
            ]
        }))
    }
    // Safe getter for adjustment values
    const getCurrentAdjustment = (productId, field) => {
        if (!currentAdjustments.value[productId]) {
            return 0; // Return default value if not initialized
        }
        return currentAdjustments.value[productId][field] || 0;
    };

    // Initialize adjustments tracking
    const initializeAdjustments = () => {
        if (props.reportData && Array.isArray(props.reportData)) {
            const newOriginal = {};
            const newCurrent = {};
            
            props.reportData.forEach(item => {
                if (!item.product?.id) return; // Skip if no product id
                
                // Initialize with default values if any field is undefined
                newOriginal[item.product.id] = {
                    positive_adjustment: item.positive_adjustment ?? 0,
                    negative_adjustment: item.negative_adjustment ?? 0,
                    months_of_stock: item.months_of_stock?.toString() ?? '0'  // Convert to string
                };
                
                newCurrent[item.product.id] = {
                    positive_adjustment: item.positive_adjustment ?? 0,
                    negative_adjustment: item.negative_adjustment ?? 0,
                    months_of_stock: item.months_of_stock?.toString() ?? '0'  // Convert to string
                };
            });
            
            originalAdjustments.value = newOriginal;
            currentAdjustments.value = newCurrent;
        }
    };

    // format dates
    const formatDate = (date) => {
        return moment(date).format('DD/MM/YYYY');
    };

    // Computed properties
    const canEdit = computed(() => {
        return props.inventoryReport?.status === 'generated' || props.inventoryReport?.status === 'rejected';
    });

    const submittedBy = computed(() => {
        return props.inventoryReport?.submitted_by ? props.inventoryReport.submitted_by.name : null;
    });

    const reviewedBy = computed(() => {
        return props.inventoryReport?.reviewed_by ? props.inventoryReport.reviewed_by.name : null;
    });

    const approvedBy = computed(() => {
        return props.inventoryReport?.approved_by ? props.inventoryReport.approved_by.name : null;
    });

    const rejectedBy = computed(() => {
        return props.inventoryReport?.rejected_by ? props.inventoryReport.rejected_by.name : null;
    });

    const canSubmit = computed(() => {
        return props.inventoryReport?.status === 'generated' || props.inventoryReport?.status === 'rejected';
    });

    const canReview = computed(() => {
        return props.inventoryReport?.status === 'submitted';
    });

    const canApprove = computed(() => {
        return props.inventoryReport?.status === 'under_review';
    });

    const canReject = computed(() => {
        return props.inventoryReport?.status === 'under_review';
    });

    const hasUnsavedChanges = computed(() => {
        if (!currentAdjustments.value || !originalAdjustments.value) {
            return false;
        }

        return Object.keys(currentAdjustments.value).some(id => {
            const current = currentAdjustments.value[id];
            const original = originalAdjustments.value[id];
            
            // Return false if either current or original is undefined
            if (!current || !original) {
                return false;
            }

            return current.positive_adjustment !== original.positive_adjustment ||
                   current.negative_adjustment !== original.negative_adjustment ||
                   current.months_of_stock !== original.months_of_stock;
        });
    });

    // Format month year for display
    const formatMonthYear = (dateString) => {
        if (!dateString) return 'Select Month';
        return moment(dateString).format('MMMM YYYY');
    };

    // Format numbers with commas
    const formatNumber = (num) => {
        if (num === null || num === undefined) return '0';
        return Number(num).toLocaleString();
    };

    // Get balance color based on value
    const getBalanceColor = (balance) => {
        if (balance > 0) return 'text-green-600';
        if (balance < 0) return 'text-red-600';
        return 'text-gray-600';
    };

    // Get status badge class
    const getStatusBadgeClass = (status) => {
        switch (status) {
            case 'generated':
                return 'bg-yellow-100 text-yellow-800';
            case 'submitted':
                return 'bg-blue-100 text-blue-800';
            case 'under_review':
                return 'bg-purple-100 text-purple-800';
            case 'approved':
                return 'bg-green-100 text-green-800';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    // Get status text
    const getStatusText = (status) => {
        switch (status) {
            case 'generated':
                return 'Draft';
            case 'submitted':
                return 'Submitted for Review';
            case 'under_review':
                return 'Under Review';
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            default:
                return 'Unknown';
        }
    };

    // Update adjustment value
    const updateAdjustment = async (productId, field, value, event) => {

        console.log('Updating adjustment:', productId, field, value, event);
        // Show loading indicator for this cell
        cellLoading.value[productId] = {
            ...cellLoading.value[productId],
            [field]: true
        };



        // Update current adjustments
        if (!currentAdjustments.value[productId]) {
            currentAdjustments.value[productId] = {};
        }
        currentAdjustments.value[productId][field] = value;

        // If value is empty or invalid number, reset to original
        if (!value || isNaN(Number(value))) {
            currentAdjustments.value[productId][field] = null;
            cellLoading.value[productId] = {
                ...cellLoading.value[productId],
                [field]: false
            };
            return;
        }

        // Convert to number
        currentAdjustments.value[productId][field] = Number(value);

        // Update original adjustments if not set
        if (!originalAdjustments.value[productId]) {
            originalAdjustments.value[productId] = {};
        }
        if (!originalAdjustments.value[productId][field]) {
            originalAdjustments.value[productId][field] = props.reportData.find(item => item.product.id === productId)[field];
        }

        // Hide loading indicator after update
        cellLoading.value[productId] = {
            ...cellLoading.value[productId],
            [field]: false
        };

        // If Enter key is pressed, save the adjustment
        if (event.key === 'Enter') {
            await saveAdjustments(productId);
        }
    };

    // Save adjustments on Enter key press
    const saveAdjustments = async (productId) => {
        if (saving.value) return;

        const item = props.reportData.find(item => item.product.id === productId);
        if (!item) return;

        const currentAdj = currentAdjustments.value[productId];
        if (!currentAdj) return;

        const changedItem = {
            product_id: productId,
            positive_adjustment: currentAdj.positive_adjustment || item.positive_adjustment,
            negative_adjustment: currentAdj.negative_adjustment || item.negative_adjustment,
            months_of_stock: currentAdj.months_of_stock || item.months_of_stock
        };

        try {
            saving.value = true;
            const loading = Swal.fire({
                title: 'Saving adjustment...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await axios.put(route('reports.warehouseMonthly.updateInventoryReportAdjustments'), {
                month_year: month_year.value,
                adjustments: [changedItem]
            });

            if (response.data.message) {
                loading.close();
                await Swal.fire(
                    'Success',
                    response.data.message,
                    'success'
                );

                // Reload data to get fresh state from server
                await reloadData();
            } else {
                loading.close();
                await Swal.fire(
                    'Error',
                    'Failed to save adjustment. Please try again.',
                    'error'
                );
            }
        } catch (error) {
            saving.value = false;
            Swal.close();
            console.error('Error saving adjustment:', error);
            await Swal.fire(
                'Error',
                error.response?.data?.message || 'Failed to save adjustment',
                'error'
            );
        } finally {
            saving.value = false;
        }
    };


    // Export to Excel
    const exportToExcel = () => {
        window.location.href = route('reports.warehouseMonthly.exportToExcel', { monthYear: month_year.value });
    };

    // Approve report
    const approveReport = async () => {
        try {
            const { isConfirmed } = await Swal.fire({
                title: 'Approve Report?',
                text: "Are you sure you want to approve this report?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            });

            if (isConfirmed) {
                const response = await axios.put(route('reports.warehouseMonthly.approve'), {
                    month_year: month_year.value
                });

                await Swal.fire(
                    'Approved!',
                    response.data.message,
                    'success'
                );

                // Refresh the page to get updated report status
                reloadData();
            }
        } catch (error) {
            console.error('Error approving report:', error);
            Swal.fire(
                'Error!',
                error.response?.data?.message || 'Failed to approve report',
                'error'
            );
        }
    };

    // Reject report
    const rejectReport = async () => {
        try {
            const { isConfirmed, value: reason } = await Swal.fire({
                title: 'Reject Report?',
                text: "Please provide a reason for rejection",
                input: 'textarea',
                inputPlaceholder: 'Enter rejection reason...',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to provide a rejection reason';
                    }
                    return true;
                },
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6366f1',
                confirmButtonText: 'Reject'
            });

            if (isConfirmed && reason) {
                const response = await axios.put(route('reports.warehouseMonthly.reject'), {
                    month_year: month_year.value,
                    reason: reason
                });

                await Swal.fire(
                    'Rejected!',
                    response.data.message,
                    'success'
                );

                // Refresh the page to get updated report status
                reloadData();
            }
        } catch (error) {
            console.error('Error rejecting report:', error);
            Swal.fire(
                'Error!',
                error.response?.data?.message || 'Failed to reject report',
                'error'
            );
        }
    };
</script>

   