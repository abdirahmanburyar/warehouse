// Form states
const form = ref({
    product_id: '',
    quantity: '0',
    reorder_level: '10',
    manufacturing_date: '',
    expiry_date: '',
    batch_number: '',
    location: '',
    notes: '',
    is_active: true,
});

// Reset filters
const resetFilters = () => {
    search.value = '';
    productId.value = '';
    location.value = '';
    batchNumber.value = '';
    isActive.value = '';
    expiryDateFrom.value = '';
    expiryDateTo.value = '';
    applyFilters();
};

// Add new inventory item
const addInventory = () => {
    formErrors.value = {};
    form.value = {
        product_id: '',
        quantity: '0',
        reorder_level: '10',
        manufacturing_date: '',
        expiry_date: '',
        batch_number: '',
        location: '',
        notes: '',
        is_active: true,
    };
    showAddModal.value = true;
};
