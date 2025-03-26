<template>

    <Head title="Supplies" />

    <AuthenticatedLayout>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Supplies
        </h2>

        <div class="">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="switchTab('supplies')"
                                :class="[currentTab === 'supplies' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                                Supplies
                            </button>
                            <button @click="switchTab('suppliers')"
                                :class="[currentTab === 'suppliers' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                                Suppliers
                            </button>
                        </nav>
                    </div>

                    <!-- Supplies Tab -->
                    <div v-if="currentTab === 'supplies'">
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
                                                Products: {{ supply.items.length }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ supply.warehouse.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ supply.invoice_number || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatDate(supply.supply_date) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <button @click="openViewItemsModal(supply)"
                                                    class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                                                    <i class="fas fa-eye w-5 h-5"></i>
                                                    <span class="ml-1">View</span>
                                                </button>
                                                <button @click="openEditSupplyModal(supply)"
                                                    class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                                    <i class="fas fa-edit w-5 h-5"></i>
                                                    <span class="ml-1">Edit</span>
                                                </button>
                                                <button type="button" class="text-red-600 hover:text-red-900 inline-flex items-center"
                                                    @click="confirmDeleteSupply(supply)">
                                                    <i class="fas fa-trash w-5 h-5"></i>
                                                    <span class="ml-1">Delete</span>
                                                </button>
                                            </div>
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
                        <Pagination :links="supplies.meta.links" class="flex justify-end mt-4" />
                    </div>

                    <!-- Suppliers Tab -->
                    <div v-if="currentTab === 'suppliers'">
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

        <!-- Supply Modal -->
        <Modal :show="showSupplyModal" @close="closeSupplyModal" maxWidth="7xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ supplyForm.id ? 'Edit' : 'Add New' }} Supply
                </h2>

                <form @submit.prevent="submitSupply">
                    <!-- Common Fields -->
                    <div class="mb-4">
                        <InputLabel for="supplier_id" value="Supplier" />
                        <select id="supplier_id" v-model="supplyForm.supplier_id" class="mt-1 block w-full"
                            placeholder="Select supplier" required>
                            <option v-for="supplier in suppliers.data" :key="supplier.id" :value="supplier.id">
                                {{ supplier.name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="invoice_number" value="Invoice Number" />
                            <TextInput id="invoice_number" type="text" v-model="supplyForm.invoice_number"
                                class="mt-1 block w-full" placeholder="Enter invoice number" :disabled="isSubmitting" />
                        </div>
                        <div>
                            <InputLabel for="supply_date" value="Supply Date" />
                            <TextInput id="supply_date" type="date" v-model="supplyForm.supply_date"
                                class="mt-1 block w-full" required :disabled="isSubmitting" />
                        </div>
                    </div>

                    <!-- Product Items -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-md font-medium text-gray-700">Products</h3>
                            <SecondaryButton type="button" @click="addProduct" :disabled="isSubmitting">
                                <i class="fas fa-plus w-4 h-4 mr-1"></i>
                                Add Product
                            </SecondaryButton>
                        </div>

                        <div v-if="supplyForm.items.length === 0" class="text-center py-4 bg-gray-50 rounded-md">
                            <p class="text-gray-500">No products added. Click "Add Product" to add products to this
                                supply.</p>
                        </div>
                        <div v-for="(item, index) in supplyForm.items" :key="index"
                            class="border rounded-md p-4 mb-3 bg-gray-50">
                            <div class="flex justify-between mb-2">
                                <h4 class="font-medium">Product {{ index + 1 }}</h4>
                                <button type="button" @click="removeProduct(index)"
                                    class="text-red-600 hover:text-red-800 inline-flex items-center"
                                    :disabled="isSubmitting || (item.id && item.status !== 'pending')">
                                    <i class="fas fa-trash w-5 h-5"></i>
                                    <span class="ml-1">Remove</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                <div>
                                    <InputLabel :for="`product_${index}`" value="Product" />
                                    <Multiselect
                                        v-model="item.product_id"
                                        :options="searchResults"
                                        :searchable="true"
                                        :loading="isLoading"
                                        :internal-search="false"
                                        :clear-on-select="true"
                                        :close-on-select="true"
                                        :options-limit="300"
                                        :limit="3"
                                        :max-height="600"
                                        :show-no-results="true"
                                        :hide-selected="true"
                                        @search-change="searchProduct"
                                        placeholder="Search by product name or barcode"
                                        label="product_name"
                                        track-by="product_id"
                                        :preselect-first="false"
                                        @select="selectProduct(index, $event)"
                                    >
                                        <template v-slot:noResult>
                                            No products found.
                                        </template>
                                    </Multiselect>
                                </div>
                                <div>
                                    <InputLabel :for="`batch_number_${index}`" value="Batch Number" />
                                    <TextInput :id="`batch_number_${index}`" v-model="item.batch_number"
                                        class="mt-1 block w-full" required />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                                <div>
                                    <InputLabel :for="`quantity_${index}`" value="Quantity" />
                                    <TextInput :id="`quantity_${index}`" type="number" v-model="item.quantity"
                                        class="mt-1 block w-full" placeholder="Enter quantity" min="1" required
                                        :disabled="isSubmitting" />
                                </div>
                                <div>
                                    <InputLabel :for="`manufacturing_date_${index}`" value="Manufacturing Date" />
                                    <TextInput :id="`manufacturing_date_${index}`" type="date"
                                        v-model="item.manufacturing_date" class="mt-1 block w-full"
                                        :disabled="isSubmitting" />
                                </div>
                                <div>
                                    <InputLabel :for="`expiry_date_${index}`" value="Expiry Date" />
                                    <TextInput :id="`expiry_date_${index}`" type="date" v-model="item.expiry_date"
                                        class="mt-1 block w-full" :disabled="isSubmitting" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <InputLabel for="notes" value="Notes" />
                        <TextareaInput id="notes" v-model="supplyForm.notes" class="mt-1 block w-full" :rows="3"
                            placeholder="Enter any additional notes about this supply" :disabled="isSubmitting" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end">
                        <SecondaryButton @click="closeSupplyModal" class="mr-3" :disabled="isSubmitting">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
                            :disabled="isSubmitting || supplyForm.items.length === 0">
                            <span v-if="isSubmitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Processing...
                            </span>
                            <span v-else>{{ supplyForm.id ? 'Update' : 'Add' }} Supply</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Create Supplier Modal -->
        <Modal :show="showCreateSupplierModal" @close="closeCreateSupplierModal" maxWidth="7xl">
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
        <Modal :show="showViewItemsModal" @close="closeViewItemsModal" maxWidth="7xl">
            <div class="p-6" v-if="selectedSupply">
                <h2 class="text-lg font-medium text-gray-900">Supply Items</h2>
                <div class="mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Batch Number</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Manufacturing Date</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expiry Date</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" v-if="selectedSupply.items && selectedSupply.items.length">
                                <tr v-for="item in selectedSupply.items" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.product.name
                                        }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.batch_number
                                        }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                        formatDate(item.manufacturing_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.expiry_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full mr-2': true,
                                                'bg-green-100 text-green-800': item.status === 'approved',
                                                'bg-yellow-100 text-yellow-800': item.status === 'pending',
                                                'bg-red-100 text-red-800': item.status === 'rejected'
                                            }">
                                            {{ item.status }}
                                            </span>
                                            <template v-if="item.status === 'pending'">
                                                <div class="flex items-center space-x-3">
                                                    <button @click="approveItem(item.id, 'approved')"
                                                        class="text-green-600 hover:text-green-900 inline-flex items-center"
                                                        :disabled="isSubmitting">
                                                        <i class="fas fa-check w-5 h-5"></i>
                                                        <span class="ml-1">Approve</span>
                                                    </button>
                                                    <button @click="approveItem(item.id, 'rejected')"
                                                        class="text-red-600 hover:text-red-900 inline-flex items-center"
                                                        :disabled="isSubmitting">
                                                        <i class="fas fa-times w-5 h-5"></i>
                                                        <span class="ml-1">Reject</span>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        {{ selectedSupply.items ? 'No items found' : 'Loading items...' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeViewItemsModal">Close</SecondaryButton>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>

</template>

<script setup>
    import { ref, computed, watch, nextTick } from 'vue';
    import { router, Head } from '@inertiajs/vue3';
    import { useToast } from 'vue-toastification';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import Modal from '@/Components/Modal.vue';
    import TextInput from '@/Components/TextInput.vue';
    import SelectInput from '@/Components/SelectInput.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import DangerButton from '@/Components/DangerButton.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import InputError from '@/Components/InputError.vue';
    import TextareaInput from '@/Components/TextareaInput.vue';
    import Pagination from '@/Components/Pagination.vue';
    import axios from 'axios';
    import Swal from 'sweetalert2';
    import Multiselect from 'vue-multiselect';
    import 'vue-multiselect/dist/vue-multiselect.css';

    // Initialize toast
    const toast = useToast();

    const props = defineProps({
        supplies: Object,
        suppliers: Object,
        warehouses: Array,
        activeTab: {
            type: String,
            default: 'supplies'
        },
        errors: Object,
        auth: Object,
        flash: Object
    });

    const product = ref({});

    // Active tab state - initialized from prop but managed locally
    const currentTab = ref(props.activeTab);

    // Watch for prop changes
    watch(() => props.activeTab, (newTab) => {
        currentTab.value = newTab;
    });

    // Methods to switch tabs
    const switchTab = (tab) => {
        currentTab.value = tab;
        router.get(route('supplies.index', { tab }), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    };

    // Modal states and refs
    const showSupplyModal = ref(false);
    const showCreateSupplierModal = ref(false);
    const showViewItemsModal = ref(false);
    const selectedSupply = ref(null);
    const isSubmitting = ref(false);
    const processing = ref(false);

    const products = ref([]);

    const searchResults = ref([]);
    const isLoading = ref(false);

    async function searchProduct(query) {
        if (!query) {
            searchResults.value = [];
            return;
        }

        isLoading.value = true;
        await axios.post(route('products.search'), { search: query })
            .then((response) => {
                console.log(response.data);
                isLoading.value = false;
                const data = response.data;
                searchResults.value = data;
            })
            .catch((error) => {
                isLoading.value = false;
                console.error('Error searching products:', error);
                searchResults.value = [];
            });
    }

    // Form states
    const supplyForm = ref({
        id: null,
        supplier_id: '',
        warehouse_id: '',
        invoice_number: '',
        supply_date: '',
        notes: '',
        items: [{
            id: null,
            product_id: null,
            product_name: '',
            quantity: 1,
            batch_number: '',
            manufacturing_date: '',
            expiry_date: '',
            notes: ''
        }]
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
    watch(currentTab, (newTab) => {
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
        () => supplyFilters
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

    const warehouseOptions = computed(() => {
        if (!props.warehouses || !Array.isArray(props.warehouses)) {
            return [];
        }
        return props.warehouses.map(warehouse => ({
            value: warehouse.id,
            label: warehouse.name
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

    // Format date to YYYY-MM-DD
    const formatDateForInput = (date) => {
        if (!date) return '';
        return date.split('T')[0];
    };

    // Open create supply modal
    const openCreateSupplyModal = () => {
        resetForm();
        showSupplyModal.value = true;
    };

    // Close create supply modal
    const closeSupplyModal = () => {
        showSupplyModal.value = false;
        resetForm();
    };

    // Reset supply form
    const resetForm = () => {
        supplyForm.value = {
            id: null,
            supplier_id: '',
            warehouse_id: '',
            invoice_number: '',
            supply_date: '',
            notes: '',
            items: [{
                id: null,
                product_id: null,
                product_name: '',
                quantity: 1,
                batch_number: '',
                manufacturing_date: '',
                expiry_date: '',
                notes: ''
            }]
        };
    };

    // Open edit supply modal
    const openEditSupplyModal = (supply) => {
        resetForm();
        supplyForm.value = {
            id: supply.id,
            supplier_id: supply.supplier_id,
            warehouse_id: supply.warehouse_id,
            invoice_number: supply.invoice_number,
            supply_date: formatDateForInput(supply.supply_date),
            notes: supply.notes || '',
            items: supply.items?.map(item => ({
                id: item.id,
                product_id: {
                    product_id: item.product_id,
                    product_name: item.product_name
                },
                product_name: item.product_name,
                quantity: item.quantity,
                status: item.status,
                batch_number: item.batch_number || '',
                manufacturing_date: formatDateForInput(item.manufacturing_date),
                expiry_date: formatDateForInput(item.expiry_date),
                notes: item.notes || ''
            })) || []
        };

        showSupplyModal.value = true;
    };

    // Add product
    const addProduct = () => {
        supplyForm.value.items.push({
            id: null,
            product_id: null,
            product_name: '',
            quantity: 1,
            batch_number: '',
            manufacturing_date: '',
            expiry_date: '',
            notes: ''
        });
    };

    // Remove product
    const removeProduct = (index) => {
        if (supplyForm.value.items[index].id && supplyForm.value.items[index].status !== 'pending') {
            return;
        }
        supplyForm.value.items.splice(index, 1);
    };

    // Submit supply
    async function submitSupply() {
        isSubmitting.value = true;

        // Format the data before sending
        const formData = {
            ...supplyForm.value,
            items: supplyForm.value.items.map(item => ({
                ...item,
                product_id: item.product_id?.product_id || item.product_id,
                product_name: item.product_id?.product_name || item.product_name
            }))
        };

        await axios.post(route('supplies.store'), formData)
            .then((response) => {
                toast.success(response.data);
                closeSupplyModal();
                getSupplies();
                isSubmitting.value = false;
            })
            .catch((error) => {
                products.value = [];
                console.log(error.response.data);
                isSubmitting.value = false;
                toast.error(error.response?.data);
            });
    };

    // Open create supplier modal
    const openCreateSupplierModal = () => {
        resetSupplierForm();
        showCreateSupplierModal.value = true;
    };

    const closeCreateSupplierModal = () => {
        showCreateSupplierModal.value = false;
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
        showCreateSupplierModal.value = true;
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

    // Get supply items
    const getSupplyItems = async (supplyId) => {
        try {
            const response = await axios.get(route('supplies.items', supplyId));
            return response.data;
        } catch (error) {
            console.error('Error fetching supply items:', error);
            toast.error('Failed to fetch supply items');
            return [];
        }
    };

    // Open view items modal
    const openViewItemsModal = async (supply) => {
        // Set initial supply data
        selectedSupply.value = {
            ...supply,
            items: []
        };
        showViewItemsModal.value = true;

        // Fetch items
        try {
            const items = await getSupplyItems(supply.id);
            selectedSupply.value = {
                ...selectedSupply.value,
                items
            };
        } catch (error) {
            console.error('Error opening modal:', error);
            toast.error('Failed to load supply items');
            closeViewItemsModal();
        }
    };

    // Close view items modal
    const closeViewItemsModal = () => {
        showViewItemsModal.value = false;
        selectedSupply.value = null;
    };

    // Approve/Reject item
    const approveItem = async (itemId, status) => {
        if (isSubmitting.value) return;

        try {
            isSubmitting.value = true;
            await axios.post(route('supply-items.approve', itemId), { status });
            toast.success(`Item ${status} successfully`);
            
            // Refresh the supply items
            if (selectedSupply.value) {
                const items = await getSupplyItems(selectedSupply.value.id);
                selectedSupply.value = {
                    ...selectedSupply.value,
                    items: items
                };
            }

            // Also refresh the main supplies list
            await getSupplies();
        } catch (error) {
            console.error('Error approving item:', error);
            toast.error(error.response?.data?.message || 'Failed to update item status');
        } finally {
            isSubmitting.value = false;
        }
    };

    function selectProduct(index, product) {
        supplyForm.value.items[index].product_id = product;
        supplyForm.value.items[index].product_name = product.product_name;
    }

</script>