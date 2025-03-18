<template>
    <!-- Template content remains unchanged -->
    <Head title="Product Management" />
    <AuthenticatedLayout>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight p-5">Product</h2>
        <div class="">
            <div class="bg-white overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                        <div class="w-full md:w-1/3 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by name, SKU, barcode..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                @input="debouncedSearch"
                            />
                            <div v-if="processing" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Warehouse Filter -->
                            <div class="w-full md:w-auto">
                                <select 
                                    v-model="filters.warehouse_id" 
                                    @change="reloadProducts"
                                    class="w-full md:w-48 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                >
                                    <option value="">All Warehouses</option>
                                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                        {{ warehouse.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Category Filter -->
                            <div class="w-full md:w-auto">
                                <select 
                                    v-model="filters.category_id" 
                                    @change="reloadProducts"
                                    class="w-full md:w-48 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="w-full md:w-auto">
                                <select 
                                    v-model="filters.is_active" 
                                    @change="reloadProducts"
                                    class="w-full md:w-48 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                >
                                    <option value="">All Status</option>
                                    <option value="true">Active</option>
                                    <option value="false">Inactive</option>
                                </select>
                            </div>

                            <button
                                @click="openModal()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50"
                            >
                                Add New Product
                            </button>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th @click="sort('id')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        ID
                                        <SortIcon :field="'id'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('name')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Name
                                        <SortIcon :field="'name'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('sku')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        SKU
                                        <SortIcon :field="'sku'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('active_ingredient')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Active Ingredient
                                        <SortIcon :field="'active_ingredient'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('stock_quantity')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Stock
                                        <SortIcon :field="'stock_quantity'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('price')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Price
                                        <SortIcon :field="'price'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('expiry_date')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Expiry Date
                                        <SortIcon :field="'expiry_date'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('is_active')" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Status
                                        <SortIcon :field="'is_active'" :current-sort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.sku }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.active_ingredient }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.stock_quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${{ product.price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(product.expiry_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="[
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openModal(product)" class="text-indigo-600 hover:text-indigo-900 mr-3 focus:outline-none focus:underline">
                                            Edit
                                        </button>
                                        <button @click="confirmDelete(product)" class="text-red-600 hover:text-red-900 focus:outline-none focus:underline">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="9" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <p>No products found</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex justify-center">
                        <Pagination :links="products.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="7xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ form.id ? 'Edit Product' : 'Create New Product' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500 focus:outline-none" :disabled="isSubmitted || processing">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">                    
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h3 class="text-md font-medium text-gray-700 mb-3">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Name -->
                                <div>
                                    <InputLabel for="name" value="Product Name" required />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        autocomplete="name"
                                        placeholder="Enter product name"
                                    />
                                </div>
                                
                                <!-- SKU -->
                                <div>
                                    <InputLabel for="sku" value="SKU" required />
                                    <TextInput
                                        id="sku"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.sku"
                                        required
                                        placeholder="Enter SKU code"
                                    />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Barcode -->
                                <div>
                                    <InputLabel for="barcode" value="Barcode" />
                                    <TextInput
                                        id="barcode"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.barcode"
                                        placeholder="Enter barcode (optional)"
                                    />
                                </div>
                                
                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <InputLabel for="description" value="Description" />
                                    <textarea
                                        id="description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.description"
                                        rows="3"
                                        placeholder="Enter product description"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inventory Information Section -->
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h3 class="text-md font-medium text-gray-700 mb-3">Inventory Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Price -->
                                <div>
                                    <InputLabel for="price" value="Price" required />
                                    <TextInput
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full"
                                        v-model="form.price"
                                        required
                                        placeholder="0.00"
                                    />
                                </div>
                                
                                <!-- Stock Quantity -->
                                <div>
                                    <InputLabel for="stock_quantity" value="Stock Quantity" required />
                                    <TextInput
                                        id="stock_quantity"
                                        type="number"
                                        min="0"
                                        class="mt-1 block w-full"
                                        v-model="form.stock_quantity"
                                        required
                                        placeholder="0"
                                    />
                                </div>
                                
                                <!-- Status -->
                                <div>
                                    <InputLabel for="is_active" value="Status" />
                                    <select
                                        id="is_active"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.is_active"
                                    >
                                        <option :value="true">Active</option>
                                        <option :value="false">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <!-- Warehouse -->
                                <div>
                                    <InputLabel for="warehouse_id" value="Warehouse" />
                                    <select
                                        id="warehouse_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.warehouse_id"
                                    >
                                        <option value="">Select Warehouse</option>
                                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                            {{ warehouse.name }}
                                        </option>
                                    </select>
                                </div>
                                
                                <!-- Category -->
                                <div>
                                    <InputLabel for="category_id" value="Category" />
                                    <select
                                        id="category_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.category_id"
                                    >
                                        <option value="">Select Category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pharmaceutical Details Section -->
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h3 class="text-md font-medium text-gray-700 mb-3">Pharmaceutical Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Active Ingredient -->
                                <div>
                                    <InputLabel for="active_ingredient" value="Active Ingredient" />
                                    <TextInput
                                        id="active_ingredient"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.active_ingredient"
                                        placeholder="e.g. Paracetamol, Amoxicillin"
                                    />
                                </div>
                                
                                <!-- Dosage Form -->
                                <div>
                                    <InputLabel for="dosage_form" value="Dosage Form" />
                                    <TextInput
                                        id="dosage_form"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.dosage_form"
                                        placeholder="e.g. Tablet, Capsule, Syrup"
                                    />
                                </div>
                                
                                <!-- Strength -->
                                <div>
                                    <InputLabel for="strength" value="Strength" />
                                    <TextInput
                                        id="strength"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.strength"
                                        placeholder="e.g. 500mg, 10mg/ml"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Manufacturing Details Section -->
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h3 class="text-md font-medium text-gray-700 mb-3">Manufacturing Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Manufacturer -->
                                <div>
                                    <InputLabel for="manufacturer" value="Manufacturer" />
                                    <TextInput
                                        id="manufacturer"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.manufacturer"
                                        placeholder="Enter manufacturer name"
                                    />
                                </div>
                                
                                <!-- Batch Number -->
                                <div>
                                    <InputLabel for="batch_number" value="Batch Number" />
                                    <TextInput
                                        id="batch_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.batch_number"
                                        placeholder="Enter batch number"
                                    />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Manufacturing Date -->
                                <div>
                                    <InputLabel for="manufacturing_date" value="Manufacturing Date" />
                                    <TextInput
                                        id="manufacturing_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.manufacturing_date"
                                        placeholder="YYYY-MM-DD"
                                    />
                                </div>
                                
                                <!-- Expiry Date -->
                                <div>
                                    <InputLabel for="expiry_date" value="Expiry Date" />
                                    <TextInput
                                        id="expiry_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.expiry_date"
                                        :min="form.manufacturing_date"
                                        placeholder="YYYY-MM-DD"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Regulatory Information Section -->
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <h3 class="text-md font-medium text-gray-700 mb-3">Regulatory Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Storage Conditions -->
                                <div>
                                    <InputLabel for="storage_conditions" value="Storage Conditions" />
                                    <TextInput
                                        id="storage_conditions"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.storage_conditions"
                                        placeholder="e.g. Store below 25°C"
                                    />
                                </div>
                                
                                <!-- Prescription Required -->
                                <div>
                                    <InputLabel for="prescription_required" value="Prescription Required" />
                                    <select
                                        id="prescription_required"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.prescription_required"
                                    >
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition mr-2 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 disabled:opacity-50"
                                @click="closeModal"
                                :disabled="isSubmitted || processing"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50"
                                :disabled="isSubmitted || processing"
                            >
                                <span v-if="processing || isSubmitted">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                                <span v-else>{{ form.id ? 'Update Product' : 'Create Product' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Delete Product</h2>
                    <button @click="closeDeleteModal" class="text-gray-400 hover:text-gray-500 focus:outline-none" :disabled="isSubmitted || processing">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-gray-700">Are you sure you want to delete this product? This action cannot be undone.</p>
                    <div class="mt-4 bg-yellow-50 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Attention</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Deleting this product will remove it from your inventory and all associated records.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 disabled:opacity-50"
                        @click="closeDeleteModal"
                        :disabled="isSubmitted || processing"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 disabled:opacity-50"
                        @click="deleteProduct"
                        :disabled="isSubmitted || processing"
                    >
                        <span v-if="processing || isSubmitted">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else>Delete Product</span>
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import ToastService from '@/Services/ToastService';

// Props
const props = defineProps({
    products: Object,
    warehouses: Array,
    categories: Array,
    filters: Object,
    errors: Object,
});

// Reactive state
const search = ref(props.filters?.search || '');
const processing = ref(false);
const showModal = ref(false);
const showDeleteModal = ref(false);
const isSubmitted = ref(false);
const selectedProduct = ref(null);

// Form for creating/editing products
const form = useForm({
    id: null,
    name: '',
    sku: '',
    barcode: '',
    description: '',
    price: 0,
    stock_quantity: 0,
    warehouse_id: '',
    category_id: '',
    active_ingredient: '',
    dosage_form: '',
    strength: '',
    manufacturer: '',
    batch_number: '',
    manufacturing_date: '',
    expiry_date: '',
    storage_conditions: '',
    prescription_required: 'no',
    is_active: true,
});

// Computed properties
const filters = computed(() => ({
    search: search.value,
    warehouse_id: props.filters?.warehouse_id || '',
    category_id: props.filters?.category_id || '',
    is_active: props.filters?.is_active || '',
    sort_field: props.filters?.sort_field || 'created_at',
    sort_direction: props.filters?.sort_direction || 'desc',
}));

// Watch for changes in search input
watch(search, (value) => {
    if (value === null) {
        search.value = '';
    }
    if (value !== null && value !== undefined) {
        debouncedSearch();
    }
});

// Reload products with current filters
const reloadProducts = () => {
    router.get(route('products.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['products'],
        onBefore: () => processing.value = true,
        onFinish: () => processing.value = false,
    });
};

// Search with debounce
let searchTimeout;
const debouncedSearch = () => {
    processing.value = true;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('products.index'), {
            ...filters.value,
            search: search.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['products'],
            onFinish: () => processing.value = false,
        });
    }, 300);
};

// Sort table columns
const sort = (field) => {
    const newDirection = field === filters.value.sort_field && filters.value.sort_direction === 'asc' ? 'desc' : 'asc';
    
    router.get(route('products.index'), {
        ...filters.value,
        sort_field: field,
        sort_direction: newDirection,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['products'],
        onBefore: () => processing.value = true,
        onFinish: () => processing.value = false,
    });
};

// Open modal for create/edit
const openModal = (product = null) => {
    form.clearErrors();
    
    if (product) {
        // Edit mode
        form.id = product.id;
        form.name = product.name;
        form.sku = product.sku;
        form.barcode = product.barcode || '';
        form.description = product.description || '';
        form.price = product.price;
        form.stock_quantity = product.stock_quantity;
        form.warehouse_id = product.warehouse_id || '';
        form.category_id = product.category_id || '';
        form.active_ingredient = product.active_ingredient || '';
        form.dosage_form = product.dosage_form || '';
        form.strength = product.strength || '';
        form.manufacturer = product.manufacturer || '';
        form.batch_number = product.batch_number || '';
        form.manufacturing_date = product.manufacturing_date ? dayjs(product.manufacturing_date).format('YYYY-MM-DD') : '';
        form.expiry_date = product.expiry_date ? dayjs(product.expiry_date).format('YYYY-MM-DD') : '';
        form.storage_conditions = product.storage_conditions || '';
        form.prescription_required = product.prescription_required || 'no';
        form.is_active = product.is_active !== undefined ? product.is_active : true;
    } else {
        // Create mode
        form.reset();
        form.is_active = true;
        form.prescription_required = 'no';
    }
    
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    if (processing.value || isSubmitted.value) return;
    
    showModal.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
    }, 300);
};

// Submit form
const submitForm = () => {
    if (isSubmitted.value || processing.value) return;
    
    // Client-side validation for dates
    if (form.manufacturing_date && form.expiry_date && form.manufacturing_date > form.expiry_date) {
        ToastService.error('Expiry date must be after manufacturing date');
        return;
    }
    
    isSubmitted.value = true;
    processing.value = true;
    
    if (form.id) {
        // Update existing product
        form.put(route('products.update', form.id), {
            onSuccess: () => {
                showModal.value = false;
                isSubmitted.value = false;
                form.reset();
                form.clearErrors();
                ToastService.success('Product updated successfully');
            },
            onError: (errors) => {
                isSubmitted.value = false;
                // Show toast for each error
                Object.keys(errors).forEach(key => {
                    ToastService.error(errors[key]);
                });
            },
            onFinish: () => {
                processing.value = false;
            },
            preserveScroll: true
        });
    } else {
        // Create new product
        form.post(route('products.store'), {
            onSuccess: () => {
                showModal.value = false;
                isSubmitted.value = false;
                form.reset();
                form.clearErrors();
                ToastService.success('Product created successfully');
            },
            onError: (errors) => {
                isSubmitted.value = false;
                // Show toast for each error
                Object.keys(errors).forEach(key => {
                    ToastService.error(errors[key]);
                });
            },
            onFinish: () => {
                processing.value = false;
            },
            preserveScroll: true
        });
    }
};

// Confirm delete
const confirmDelete = (product) => {
    selectedProduct.value = product;
    showDeleteModal.value = true;
};

// Close delete modal
const closeDeleteModal = () => {
    if (processing.value || isSubmitted.value) return;
    
    showDeleteModal.value = false;
    selectedProduct.value = null;
};

// Delete product
const deleteProduct = () => {
    if (!selectedProduct.value || isSubmitted.value || processing.value) return;
    
    isSubmitted.value = true;
    processing.value = true;
    
    router.delete(route('products.destroy', selectedProduct.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedProduct.value = null;
            isSubmitted.value = false;
        },
        onError: (errors) => {
            // Handle errors
            console.error(errors);
            isSubmitted.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
        preserveScroll: true
    });
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return dayjs(dateString).format('DD/MM/YYYY');
};
</script>

<style scoped>
/* Add custom styles for fullscreen modal */
:deep(.modal-content) {
    max-height: 90vh;
    overflow-y: auto;
}

:deep(.modal) {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>