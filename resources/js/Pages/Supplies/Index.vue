<template>

    <Head title="Supplies" />

    <AuthenticatedLayout>
        <template #header>
        </template>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Supplies
        </h2>

        <div class="">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="activeTab = 'supplies'" :class="[
                                activeTab === 'supplies'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]">
                                Supplies
                            </button>
                            <button @click="activeTab = 'suppliers'" :class="[
                                activeTab === 'suppliers'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]">
                                Suppliers
                            </button>
                        </nav>
                    </div>

                    <!-- Supplies Tab -->
                    <div v-if="activeTab === 'supplies'">
                        <!-- Filters -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <InputLabel for="search" value="Search" />
                                    <TextInput id="search" type="text" v-model="supplyFilters.search"
                                        class="mt-1 block w-full" placeholder="Search supplies..."
                                        @keyup.enter="getSupplies" />
                                </div>
                                <div>
                                    <InputLabel for="warehouse_filter" value="Warehouse" />
                                    <SelectInput id="warehouse_filter" v-model="supplyFilters.warehouse_id"
                                        :options="warehouseOptions" class="mt-1 block w-full"
                                        placeholder="All warehouses" />
                                </div>
                                <div>
                                    <InputLabel for="date_from" value="Date From" />
                                    <TextInput id="date_from" type="date" v-model="supplyFilters.date_from"
                                        class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <InputLabel for="date_to" value="Date To" />
                                    <TextInput id="date_to" type="date" v-model="supplyFilters.date_to"
                                        class="mt-1 block w-full" />
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <PrimaryButton @click="getSupplies">
                                        Filter
                                    </PrimaryButton>
                                    <SecondaryButton class="ml-2" @click="resetSupplyFilters">
                                        Reset
                                    </SecondaryButton>
                                </div>
                                <PrimaryButton @click="openCreateSupplyModal">
                                    Add Supply
                                </PrimaryButton>
                            </div>
                        </div>
                        <!-- Supplies Table -->
                        <div class="overflow-x-auto bg-white rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Supplier
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Warehouse
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Invoice
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="supply in supplies.data" :key="supply.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ supply.supplier.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Products: {{ supply.supply_items }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ supply.warehouse.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ supply.invoice_number || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatDate(supply.supply_date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supply.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                @click="openViewItemsModal(supply)">
                                                View Items
                                            </button>
                                            <button type="button" class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                @click="openEditSupplyModal(supply)">
                                                Edit
                                            </button>
                                            <button type="button" class="text-red-600 hover:text-red-900"
                                                @click="confirmDeleteSupply(supply)">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="supplies.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No supplies found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="supplies.links" class="mt-4" />
                    </div>

                    <!-- Suppliers Tab -->
                    <div v-if="activeTab === 'suppliers'">
                        <!-- Filters -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="supplier_search" value="Search" />
                                    <TextInput id="supplier_search" type="text" v-model="supplierFilters.search"
                                        class="mt-1 block w-full" placeholder="Search suppliers..."
                                        @keyup.enter="getSuppliers" />
                                </div>
                                <div>
                                    <InputLabel for="active_filter" value="Status" />
                                    <SelectInput id="active_filter" v-model="supplierFilters.active" :options="[
                                        { value: '', label: 'All' },
                                        { value: '1', label: 'Active' },
                                        { value: '0', label: 'Inactive' }
                                    ]" class="mt-1 block w-full" />
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <PrimaryButton @click="getSuppliers">
                                        Filter
                                    </PrimaryButton>
                                    <SecondaryButton class="ml-2" @click="resetSupplierFilters">
                                        Reset
                                    </SecondaryButton>
                                </div>
                                <PrimaryButton @click="openCreateSupplierModal">
                                    Add Supplier
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Suppliers Table -->
                        <div class="overflow-x-auto bg-white rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact Person
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Phone
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Supplies
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ supplier.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.contact_person || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.email || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.phone || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.supplies_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                supplier.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800',
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                            ]">
                                                {{ supplier.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                @click="openEditSupplierModal(supplier)">
                                                Edit
                                            </button>
                                            <button type="button" class="text-red-600 hover:text-red-900"
                                                @click="confirmDeleteSupplier(supplier)">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No suppliers found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="suppliers.links" class="mt-4" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Supply Modal -->
        <Modal :show="createSupplyModal" @close="closeCreateSupplyModal" maxWidth="7xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Add New Supply
                </h2>

                <form @submit.prevent="submitCreateSupply">
                    <!-- Common Fields -->
                    <div class="mb-4">
                        <InputLabel for="supplier_id" value="Supplier" />
                        <SelectInput id="supplier_id" v-model="supplyForm.supplier_id" :options="props.suppliers.data"
                            class="mt-1 block w-full" placeholder="Select supplier" required :disabled="isSubmitting" />
                        <InputError :message="supplyForm.errors.supplier_id" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="invoice_number" value="Invoice Number" />
                            <TextInput id="invoice_number" type="text" v-model="supplyForm.invoice_number"
                                class="mt-1 block w-full" placeholder="Enter invoice number" :disabled="isSubmitting" />
                            <InputError :message="supplyForm.errors.invoice_number" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="supply_date" value="Supply Date" />
                            <TextInput id="supply_date" type="date" v-model="supplyForm.supply_date"
                                class="mt-1 block w-full" required :disabled="isSubmitting" />
                            <InputError :message="supplyForm.errors.supply_date" class="mt-2" />
                        </div>
                    </div>

                    <!-- Product Items -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-md font-medium text-gray-700">Products</h3>
                            <SecondaryButton type="button" @click="addProductRow" :disabled="isSubmitting">
                                Add Product
                            </SecondaryButton>
                        </div>

                        <div v-if="supplyForm.products.length === 0" class="text-center py-4 bg-gray-50 rounded-md">
                            <p class="text-gray-500">No products added. Click "Add Product" to add products to this
                                supply.
                            </p>
                        </div>

                        <div v-for="(product, index) in supplyForm.products" :key="index"
                            class="border rounded-md p-4 mb-3 bg-gray-50">
                            <div class="flex justify-between mb-2">
                                <h4 class="font-medium">Product {{ index + 1 }}</h4>
                                <button type="button" @click="removeProductRow(index)"
                                    class="text-red-600 hover:text-red-800" :disabled="isSubmitting">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                <div>
                                    <InputLabel :for="`product_id_${index}`" value="Product" />
                                    <SelectInput :id="`product_id_${index}`" v-model="product.product_id"
                                        :options="props.products" class="mt-1 block w-full" placeholder="Select product"
                                        required :disabled="isSubmitting" />
                                    <InputError :message="getProductError(index, 'product_id')" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel :for="`batch_number_${index}`" value="Batch Number" />
                                    <TextInput :id="`batch_number_${index}`" type="text" v-model="product.batch_number"
                                        class="mt-1 block w-full" placeholder="Enter batch number"
                                        :disabled="isSubmitting" />
                                    <InputError :message="getProductError(index, 'batch_number')" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                                <div>
                                    <InputLabel :for="`quantity_${index}`" value="Quantity" />
                                    <TextInput :id="`quantity_${index}`" type="number" v-model="product.quantity"
                                        class="mt-1 block w-full" placeholder="Enter quantity" min="1" required
                                        :disabled="isSubmitting" />
                                    <InputError :message="getProductError(index, 'quantity')" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel :for="`manufacturing_date_${index}`" value="Manufacturing Date" />
                                    <TextInput :id="`manufacturing_date_${index}`" type="date"
                                        v-model="product.manufacturing_date" class="mt-1 block w-full"
                                        :disabled="isSubmitting" />
                                    <InputError :message="getProductError(index, 'manufacturing_date')" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel :for="`expiry_date_${index}`" value="Expiry Date" />
                                    <TextInput :id="`expiry_date_${index}`" type="date" v-model="product.expiry_date"
                                        class="mt-1 block w-full" :disabled="isSubmitting" />
                                    <InputError :message="getProductError(index, 'expiry_date')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <InputLabel for="notes" value="Notes" />
                        <TextareaInput id="notes" v-model="supplyForm.notes" class="mt-1 block w-full" :rows="3"
                            placeholder="Enter any additional notes about this supply" :disabled="isSubmitting" />
                        <InputError :message="supplyForm.errors.notes" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end">
                        <SecondaryButton @click="closeCreateSupplyModal" class="mr-3" :disabled="isSubmitting">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
                            :disabled="isSubmitting || supplyForm.products.length === 0">
                            <span v-if="isSubmitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Processing...
                            </span>
                            <span v-else>Add Supply</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Create Supplier Modal -->
        <Modal :show="createSupplierModal" @close="closeCreateSupplierModal" maxWidth="7xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ form.id ? 'Edit Supplier' : 'Add New Supplier' }}
                </h2>

                <form @submit.prevent="submitSupplierForm">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput id="name" type="text" v-model="form.name" class="mt-1 block w-full" required
                                placeholder="Enter supplier name" :disabled="isSubmitting" />
                            <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <InputLabel for="contact_person" value="Contact Person" />
                            <TextInput id="contact_person" type="text" v-model="form.contact_person"
                                placeholder="Enter contact person name" class="mt-1 block w-full"
                                :disabled="isSubmitting" />
                            <div v-if="form.errors.contact_person" class="text-sm text-red-600 mt-1">{{
                                form.errors.contact_person }}</div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full"
                                placeholder="Enter email address" :disabled="isSubmitting" />
                            <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}
                            </div>
                        </div>

                        <div>
                            <InputLabel for="phone" value="Phone" />
                            <TextInput id="phone" type="text" v-model="form.phone" class="mt-1 block w-full"
                                placeholder="Enter phone number" :disabled="isSubmitting" />
                            <div v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="address" value="Address" />
                            <TextareaInput id="address" v-model="form.address" class="mt-1 block w-full" :rows="3"
                                placeholder="Enter complete address" :disabled="isSubmitting" />
                            <div v-if="form.errors.address" class="text-sm text-red-600 mt-1">{{ form.errors.address }}
                            </div>
                        </div>

                        <div>
                            <InputLabel for="notes" value="Notes" />
                            <TextareaInput id="notes" v-model="form.notes" class="mt-1 block w-full" :rows="3"
                                placeholder="Enter additional notes" :disabled="isSubmitting" />
                            <div v-if="form.errors.notes" class="text-sm text-red-600 mt-1">{{ form.errors.notes }}
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.is_active" :disabled="isSubmitting"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end mt-6 gap-3">
                        <button type="button" @click="closeCreateSupplierModal"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            :disabled="isSubmitting">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            :disabled="isSubmitting">
                            {{ form.id ? isSubmitting ? 'Updating...' : 'Update' : isSubmitting ? 'Creating...' :
                            'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- View Supply Items Modal -->
        <Modal :show="viewItemsModal" @close="closeViewItemsModal" maxWidth="7xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900">
                        Supply Items
                    </h2>
                    <div v-if="hasPendingItems" class="flex space-x-2">
                        <button @click="approveBulk('approved')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Approve All
                        </button>
                        <button @click="approveBulk('rejected')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Reject All
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Product
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Batch Number
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Manufacturing Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Expiry Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in selectedSupply.items" :key="item.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ item.product.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ item.batch_number || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(item.manufacturing_date) || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(item.expiry_date) || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="{
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                        'bg-yellow-100 text-yellow-800': item.status === 'pending',
                                        'bg-green-100 text-green-800': item.status === 'approved',
                                        'bg-red-100 text-red-800': item.status === 'rejected'
                                    }">
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" v-if="item.status === 'pending'">
                                    <button @click="approveItem(item, 'approved')"
                                        class="text-green-600 hover:text-green-900 mr-2"
                                        :disabled="isSubmitting">
                                        Approve
                                    </button>
                                    <button @click="approveItem(item, 'rejected')"
                                        class="text-red-600 hover:text-red-900"
                                        :disabled="isSubmitting">
                                        Reject
                                    </button>
                                </td>
                                <td v-else class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ item.approval_notes || 'No notes' }}</div>
                                    <div class="text-xs text-gray-400" v-if="item.approver">
                                        by {{ item.approver.name }} at {{ formatDate(item.approved_at) }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end">
                    <SecondaryButton @click="closeViewItemsModal">
                        Close
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>

</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    supplies: Object,
    suppliers: Object,
    warehouses: Array,
    products: Array,
    supplyFilters: Object,
    supplierFilters: Object,
    activeTab: String,
});

// Initialize toast
const toast = useToast();

// Active tab state - set from URL or default to 'supplies'
const activeTab = ref(props.activeTab || 'supplies');

// Modal states
const createSupplyModal = ref(false);
const editSupplyModal = ref(false);
const createSupplierModal = ref(false);
const editSupplierModal = ref(false);
const supplyToDelete = ref(null);
const supplierToDelete = ref(null);
const processing = ref(false);
const isSubmitting = ref(false);
const viewItemsModal = ref(false);
const selectedSupply = ref({});

// Form states
const supplyForm = ref({
    supplier_id: '',
    warehouse_id: '',
    invoice_number: '',
    supply_date: '',
    notes: '',
    products: [],
    errors: {},
});

const form = ref({
    id: null,
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    is_active: true,
    notes: '',
    errors: {}
});

// Filter states
const supplyFilters = ref({
    search: props.supplyFilters?.search || '',
    warehouse_id: props.supplyFilters?.warehouse_id || '',
    date_from: props.supplyFilters?.date_from || '',
    date_to: props.supplyFilters?.date_to || '',
});

const supplierFilters = ref({
    search: props.supplierFilters?.search || '',
    active: props.supplierFilters?.active || '',
});

// Watch for tab changes
watch([
    () => activeTab.value
], (newTab) => {
    if (newTab === 'supplies') {
        getSupplies();
    } else if (newTab === 'suppliers') {
        getSuppliers();
    }
});

// Methods for supplies
const getSupplies = () => {
    router.get(route('supplies.index'), {
        ...supplyFilters.value,
        tab: 'supplies'
    }, {
        preserveState: true,
        replace: true,
    });
};

// Methods for suppliers
const getSuppliers = () => {
    router.get(route('supplies.index'), {
        ...supplierFilters.value,
        tab: 'suppliers'
    }, {
        preserveState: true,
        replace: true,
    });
};

// Reset filters
const resetSupplyFilters = () => {
    supplyFilters.value = {
        search: '',
        warehouse_id: '',
        date_from: '',
        date_to: '',
    };
    getSupplies();
};

watch([
    () => supplyFilters.value
], (newFilters) => {
    getSupplies();
});

const resetSupplierFilters = () => {
    supplierFilters.value = {
        search: '',
        active: '',
    };
    getSuppliers();
};

// Computed properties
const productOptions = computed(() => {
    if (!props.products || !Array.isArray(props.products)) {
        return [];
    }
    return props.products.map(product => ({
        value: product.id,
        label: product.name
    }));
});

const warehouseOptions = computed(() => {
    if (!props.warehouses || !Array.isArray(props.warehouses)) {
        return [];
    }
    return props.warehouses.map(warehouse => ({
        value: warehouse.id,
        label: warehouse.name
    }));
});

const supplierOptions = computed(() => {
    if (!props.suppliers || !props.suppliers.data) {
        return [];
    }
    return props.suppliers.data.map(supplier => ({
        value: supplier.id,
        label: supplier.name
    }));
});

const hasPendingItems = computed(() => {
    return selectedSupply.value?.items?.some(item => item.status === 'pending') || false;
});

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

// Open create supply modal
const openCreateSupplyModal = () => {
    resetSupplyForm();
    addProductRow(); // Add one product row by default
    createSupplyModal.value = true;
};

// Close create supply modal
const closeCreateSupplyModal = () => {
    createSupplyModal.value = false;
    resetSupplyForm();
};

// Reset supply form
const resetSupplyForm = () => {
    supplyForm.value = {
        supplier_id: '',
        warehouse_id: '',
        invoice_number: '',
        supply_date: new Date().toISOString().substr(0, 10),
        notes: '',
        products: [],
        errors: {},
    };
};

// Add product row
const addProductRow = () => {
    supplyForm.value.products.push({
        product_id: '',
        quantity: 1,
        batch_number: '',
        manufacturing_date: '',
        expiry_date: '',
    });
};

// Remove product row
const removeProductRow = (index) => {
    supplyForm.value.products.splice(index, 1);
};

// Get product error
const getProductError = (index, field) => {
    const errors = supplyForm.value.errors;
    if (errors && errors[`products.${index}.${field}`]) {
        return errors[`products.${index}.${field}`];
    }
    return null;
};

// Submit create supply
const submitCreateSupply = async () => {
    console.log(supplyForm.value);
    if (isSubmitting.value) return;

    isSubmitting.value = true;
    supplyForm.value.errors = {};
    await axios.post(route('supplies.store'), supplyForm.value)
        .then((response) => {
            isSubmitting.value = false;
            toast.success('Supply created successfully!');
            closeCreateSupplyModal();
            getSupplies();
        })
        .catch((error) => {
            isSubmitting.value = false;
            toast.error(error.response.data);
        });
};

// Open edit supply modal
const openEditSupplyModal = (supply) => {
    supplyToEdit.value = supply;
    supplyForm.value = {
        id: supply.id,
        supplier_id: supply.supplier_id,
        warehouse_id: supply.warehouse_id,
        invoice_number: supply.invoice_number || '',
        supply_date: supply.supply_date,
        notes: supply.notes || '',
        products: [{
            product_id: supply.product_id,
            quantity: supply.quantity,
            batch_number: supply.batch_number || '',
            manufacturing_date: supply.manufacturing_date || '',
            expiry_date: supply.expiry_date || '',
        }],
        errors: {},
    };
    editSupplyModal.value = true;
};

// Close edit supply modal
const closeEditSupplyModal = () => {
    editSupplyModal.value = false;
    supplyToEdit.value = null;
    resetSupplyForm();
};

// Submit edit supply
const submitEditSupply = async () => {
    if (isSubmitting.value) return;

    isSubmitting.value = true;
    supplyForm.value.errors = {};

    try {
        await axios.put(route('supplies.update', supplyToEdit.value.id), supplyForm.value);
        toast.success('Supply updated successfully!');
        closeEditSupplyModal();
        getSupplies();
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            supplyForm.value.errors = error.response.data.errors;
            toast.error('There are errors in your form. Please check and try again.');
        } else {
            toast.error('An error occurred while updating the supply.');
        }
    } finally {
        isSubmitting.value = false;
    }
};

// Confirm delete supply
const confirmDeleteSupply = (supply) => {
    supplyToDelete.value = supply;
    deleteSupplyModal.value = true;
};

// Close supply modal
const closeSupplyModal = () => {
    deleteSupplyModal.value = false;
    supplyToDelete.value = null;
};

// Delete supply
const deleteSupply = () => {
    processing.value = true;

    axios.delete(route('supplies.destroy', supplyToDelete.value.id))
        .then(response => {
            toast.success('Supply deleted successfully!');
            closeSupplyModal();
            getSupplies();
        })
        .catch(error => {
            toast.error('An error occurred while deleting the supply.');
        })
        .finally(() => {
            processing.value = false;
        });
};

// Open create supplier modal
const openCreateSupplierModal = () => {
    resetSupplierForm();
    createSupplierModal.value = true;
};

const closeCreateSupplierModal = () => {
    createSupplierModal.value = false;
    resetSupplierForm();
};

const submitSupplierForm = async () => {

    isSubmitting.value = true;
    form.value.errors = {};

    await axios.post(route('suppliers.store'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            toast.success(response.data);
            closeCreateSupplierModal();
            getSuppliers();
        })
        .catch((error) => {
            isSubmitting.value = false;
            toast.error(error.response.data);
        });
};

// Open edit supplier modal
function openEditSupplierModal(supplier) {
    form.value = {
        id: supplier.id,
        name: supplier.name,
        contact_person: supplier.contact_person || '',
        email: supplier.email || '',
        phone: supplier.phone || '',
        address: supplier.address || '',
        is_active: supplier.is_active,
        notes: supplier.notes || '',
        errors: {},
    };
    createSupplierModal.value = true;
};

// Reset supplier form
const resetSupplierForm = () => {
    form.value = {
        id: null,
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        address: '',
        is_active: true,
        notes: '',
        errors: {},
    };
};

// Confirm delete supplier
const confirmDeleteSupplier = (supplier) => {
    if (!confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
        return;
    }
    supplierToDelete.value = supplier;
    deleteSupplier();
};

// Close supplier modal
const closeSupplierModal = () => {
    supplierToDelete.value = null;
};

// Delete supplier
const deleteSupplier = async () => {
    if (!supplierToDelete.value || isSubmitting.value) return;

    isSubmitting.value = true;

    try {
        await axios.delete(route('suppliers.destroy', supplierToDelete.value.id));
        toast.success('Supplier deleted successfully!');
        closeSupplierModal();
        getSuppliers();
    } catch (error) {
        if (error.response?.status === 422) {
            toast.error('This supplier cannot be deleted because it has associated supplies.');
        } else {
            toast.error('An error occurred while deleting the supplier.');
        }
    } finally {
        isSubmitting.value = false;
    }
};

// Open view items modal
const openViewItemsModal = (supply) => {
    selectedSupply.value = supply;
    viewItemsModal.value = true;
};

// Close view items modal
const closeViewItemsModal = () => {
    viewItemsModal.value = false;
    selectedSupply.value = {};
};

// Approve item
const approveItem = async (item, status) => {
    console.log(item, status);
    isSubmitting.value = true;
    await axios.get(route('supply-items.update', item.id))
        .then((response) => {
            isSubmitting.value = false;
            toast.success(response.data);
            closeViewItemsModal();
            getSupplies();
        })
        .catch((error) => {
            console.log(error);
            isSubmitting.value = false;
            toast.error(error.response.data);
        });
}

// Bulk approve items
const approveBulk = async (status) => {
    if (isSubmitting.value) return;

    const confirmed = await Swal.fire({
        title: `${status === 'approved' ? 'Approve' : 'Reject'} all pending items?`,
        text: `This will ${status === 'approved' ? 'approve' : 'reject'} all pending items in this supply.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: status === 'approved' ? 'Yes, approve all' : 'Yes, reject all',
        cancelButtonText: 'Cancel'
    });

    if (!confirmed.isConfirmed) return;

    isSubmitting.value = true;

    try {
        await axios.get(route('supplies.approve-bulk', selectedSupply.value.id), {
            status,
            notes: ''
        });
        
        toast.success(`All pending items have been ${status} successfully!`);
        closeViewItemsModal();
        getSupplies();
    } catch (error) {
        if (error.response?.status === 422) {
            toast.error('Some items cannot be processed because they have associated issues.');
        } else {
            toast.error('An error occurred while processing the items.');
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>