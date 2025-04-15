<template>
    <!-- Template content remains unchanged -->

    <Head title="Product List" />
    <AuthenticatedLayout>
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'products'"
                    :class="[activeTab === 'products' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                    Products
                </button>
                <button @click="activeTab = 'categories'"
                    :class="[activeTab === 'categories' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                    Categories
                </button>
                <button @click="activeTab = 'subcategories'"
                    :class="[activeTab === 'subcategories' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                    Subcategories
                </button>
                <button @click="activeTab = 'dosages'"
                    :class="[activeTab === 'dosages' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                    Dosages
                </button>
                <button @click="activeTab = 'eligible'"
                    :class="[activeTab === 'eligible' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                    Eligible Drugs
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div v-show="activeTab === 'products'" class="transition-opacity duration-150" :class="{'opacity-100': activeTab === 'products', 'opacity-0': activeTab !== 'products'}">
            <div class="bg-white overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <!-- Left side filters in a row -->
                        <div class="flex flex-col md:flex-row gap-3 md:items-center flex-grow">
                            <!-- Search Bar -->
                            <div class="w-full md:w-auto flex-grow relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input v-model="search" type="text" placeholder="Search by name, SKU, barcode..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150" />
                                <div v-if="processing"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Category Filter -->
                            <select v-model="category_id"
                                class="w-full md:w-40 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                @change="onCategoryFilterChange">
                                <option value="">All Categories</option>
                                <option v-for="category in props.categories.data" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>

                            <!-- Subcategory Filter -->
                            <select v-model="sub_category_id"
                                class="w-full md:w-40 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                                <option value="">All Subcategories</option>
                                <option v-for="subcategory in props.subcategories.data" :key="subcategory.id" :value="subcategory.id">
                                    {{ subcategory.name }}
                                </option>
                            </select>

                            <!-- Dosage Filter -->
                            <select v-model="dosage_id"
                                class="w-full md:w-40 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                :disabled="!category_id">
                                <option value="">All Dosages</option>
                                <option v-for="dosage in filteredDosages" :key="dosage.id" :value="dosage.id">
                                    {{ dosage.name }}
                                </option>
                            </select>
                            
                            <!-- Status Filter -->
                            <select v-model="is_active"
                                class="w-full md:w-32 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                                <option value="">All Status</option>
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                            
                            <!-- Per Page Selector -->
                            <select v-model="per_page"
                                class="w-full md:w-32 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                                <option value="6">6 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                                <option value="100">100 / page</option>
                            </select>
                            
                            <!-- Reset Filters Button -->
                            <button @click="resetFilters" 
                                class="w-full md:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                                <span class="flex items-center justify-center">
                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 014 12H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset
                                </span>
                            </button>
                        </div>
                        
                        <!-- Add New Product Button on the right -->
                        <button @click="openModal()"
                            class="rounded-full p-3 bg-gray-900 hover:bg-gray-800 text-white shadow-sm transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider cursor-pointer">
                                        <div class="flex items-center justify-center">
                                            <input
                                                type="checkbox"
                                                :checked="selectedItems.length === props.products.data.length && props.products.data.length > 0"
                                                @change="toggleSelectAll"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            />
                                        </div>
                                    </th>
                                    <th @click="sort('name')" scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Name
                                    </th>
                                    <th @click="sort('sku')" scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        SKU
                                    </th>
                                    <th @click="sort('barcode')" scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Barcode
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subcategory
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Dosage
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in props.products.data" :key="product.id"
                                    class="hover:bg-gray-50 transition-colors">
                                    <td class="px-3 py-2 whitespace-nowrap border-r">
                                        <div class="flex items-center justify-center">
                                            <input
                                                type="checkbox"
                                                v-model="selectedItems"
                                                :value="product.id"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.sku }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.barcode }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ product.dosage?.category?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ product.sub_category?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ product.dosage?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <div class="flex items-center justify-center space-x-3">
                                            <button @click="openModal(product)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                                title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                                </svg>
                                                <span class="sr-only">Edit</span>
                                            </button>
                                            <button @click="confirmDelete(product)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150"
                                                title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="sr-only">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="props.products.data.length === 0">
                                    <td colspan="9" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <p>No products found</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bulk Delete Modal -->
                    <Modal :show="showBulkDeleteModal" @close="showBulkDeleteModal = false">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900">
                                Delete Selected Products
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                Are you sure you want to delete the selected products? This action cannot be undone.
                            </p>

                            <div class="mt-6 flex justify-end space-x-3">
                                <SecondaryButton @click="showBulkDeleteModal = false">Cancel</SecondaryButton>
                                <DangerButton :disabled="isBulkDeleting" @click="bulkDelete">Delete</DangerButton>
                            </div>
                        </div>
                    </Modal>

                    <!-- Bulk Actions -->
                    <div v-if="selectedItems.length > 0" 
                        class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50 flex items-center bg-white rounded-lg shadow-lg border border-gray-200 px-4 py-2 space-x-2">
                        <span class="text-sm text-gray-600">{{ selectedItems.length }} products selected</span>
                        <button 
                            @click="confirmBulkDelete"
                            class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-2 flex justify-between items-center">
                        <span class="text-sm text-gray-600 mr-3">
                            Showing {{ props.products.meta.from }} to {{ props.products.meta.to }} of {{ props.products.meta.total }}
                        </span>
                        <Pagination :links="props.products.meta.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Tab Content -->
        <div v-show="activeTab === 'categories'" class="transition-opacity duration-150" :class="{'opacity-100': activeTab === 'categories', 'opacity-0': activeTab !== 'categories'}">
            <div class="p-4 bg-white overflow-hidden sm:rounded-lg">
                <div class="bg-white border-b border-gray-200">
                    <!-- Search and Add Button -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between mb-1 space-y-4 md:space-y-0">
                        <div class="w-full md:w-1/3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input v-model="categorySearch" type="text"
                                    placeholder="Search by name or description..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150" />
                                <div v-if="categoryProcessing"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- per page -->
                            <div class="w-full md:w-auto">
                                <select v-model="category_per_page"
                                    class="w-full md:w-48 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                                    <option value="6">6 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>

                            <button @click="openCategoryModal()"
                            class="rounded-full p-3 bg-gray-900 hover:bg-gray-800 text-white shadow-sm transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="overflow-x-auto bg-white rounded-lg overflow-y-auto relative">
                        <table
                            class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortCategory('id')">
                                        <div class="flex items-center">
                                            SN
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortCategory('name')">
                                        <div class="flex items-center">
                                            Name
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                        Description
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortCategory('is_active')">
                                        <div class="flex items-center">
                                            Status
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortCategory('created_at')">
                                        <div class="flex items-center">
                                            Created At
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(category, i) in props.categories.data" :key="category.id"
                                    class="hover:bg-gray-100 transition-colors duration-150">
                                    <td class="py-3 px-4 border-b border-gray-200">{{ i + 1 }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200">{{ category.name }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <div class="max-w-xs truncate">{{ category.description || 'No description' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <span :class="[
                                            'px-2 py-1 rounded-full text-xs font-medium',
                                            category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ category.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200">{{ formatDate(category.created_at) }}
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <div class="flex items-center space-x-2">
                                            <button @click="openCategoryModal(category)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                                title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                                </svg>
                                                <span class="sr-only">Edit</span>
                                            </button>
                                            <button @click="confirmDeleteCategory(category)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150"
                                                title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="sr-only">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="props.categories.data.length === 0">
                                    <td colspan="6" class="py-6 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-lg font-medium">No categories found</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <Pagination :links="props.categories.meta.links" class="flex justify-end"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- SubCategories Tab Content -->
        <div v-show="activeTab === 'subcategories'" class="transition-opacity duration-150" :class="{'opacity-100': activeTab === 'subcategories', 'opacity-0': activeTab !== 'subcategories'}">
            <div class="p-4 bg-white overflow-hidden sm:rounded-lg">
                <div class="bg-white border-b border-gray-200">
                    <!-- Search and Add Button -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between mb-1 space-y-4 md:space-y-0">
                        <div class="w-full md:w-1/3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input v-model="subcategorySearch" type="text"
                                    placeholder="Search by name or description..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150" />
                                <div v-if="subcategoryProcessing"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- per page -->
                            <div class="w-full md:w-auto">
                                <select v-model="subcategory_per_page"
                                    class="w-full md:w-48 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                                    <option value="6">6 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>

                            <button @click="openSubcategoryModal()"
                            class="rounded-full p-3 bg-gray-900 hover:bg-gray-800 text-white shadow-sm transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                        </div>
                    </div>

                    <!-- SubCategories Table -->
                    <div class="overflow-x-auto bg-white rounded-lg overflow-y-auto relative">
                        <table
                            class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortSubcategory('id')">
                                        <div class="flex items-center">
                                            SN
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortSubcategory('name')">
                                        <div class="flex items-center">
                                            Name
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                        Description
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortSubcategory('is_active')">
                                        <div class="flex items-center">
                                            Status
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortSubcategory('created_at')">
                                        <div class="flex items-center">
                                            Created At
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(subcategory, i) in props.subcategories.data" :key="subcategory.id"
                                    class="hover:bg-gray-100 transition-colors duration-150">
                                    <td class="py-3 px-4 border-b border-gray-200">{{ i + 1 }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200">{{ subcategory.name }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <div class="max-w-xs truncate">{{ subcategory.description || 'No description' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <span :class="[
                                            'px-2 py-1 rounded-full text-xs font-medium',
                                            subcategory.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ subcategory.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200">{{ formatDate(subcategory.created_at) }}
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <div class="flex items-center space-x-2">
                                            <button @click="openSubcategoryModal(subcategory)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                                title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                                </svg>
                                                <span class="sr-only">Edit</span>
                                            </button>
                                            <button @click="confirmDeleteSubcategory(subcategory)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150"
                                                title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="sr-only">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="props.subcategories.data.length === 0">
                                    <td colspan="6" class="py-6 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-lg font-medium">No subcategories found</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <Pagination :links="props.subcategories.meta.links" class="flex justify-end"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dosages Tab Content -->
        <div v-show="activeTab === 'dosages'" class="transition-opacity duration-150 bg-white" :class="{'opacity-100': activeTab === 'dosages', 'opacity-0': activeTab !== 'dosages'}">
            <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <!-- Search and Add Button -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <!-- Left side with search -->
                    <div class="w-full md:w-1/3 relative">
                        <div class="relative">
                            <input type="text" v-model="dosageSearch"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                placeholder="Search dosages...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center mt-4 md:mt-0 space-x-4">
                        <!-- Per page dropdown -->
                        <div class="w-full md:w-auto">
                            <select v-model="dosage_per_page"
                                class="w-full md:w-48 pl-3 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                                <option value="6">6 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                            </select>
                        </div>

                        <button @click="openDosageModal()"
                            class="rounded-full p-3 bg-gray-900 hover:bg-gray-800 text-white shadow-sm transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Dosages Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        #
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
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
                                <tr v-for="(dosage, i) in props.dosages.data" :key="dosage.id"
                                    class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ i + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ dosage.name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ dosage.description || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ dosage.category?.name || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                dosage.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]">
                                            {{ dosage.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openDosageModal(dosage)"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                            </svg>
                                            <span class="sr-only">Edit</span>
                                        </button>
                                        <button @click="confirmDeleteDosage(dosage)"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150"
                                            title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="sr-only">Delete</span>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Empty state -->
                                <tr v-if="props.dosages.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-lg font-medium">No dosages found</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-5">
                    <Pagination :links="props.dosages.meta.links" class="flex justify-end"/>
                </div>
            </div>
        </div>

        <!-- Eligible Drugs tab -->
        <div v-if="activeTab === 'eligible'" class="transition-opacity duration-150" :class="{'opacity-100': activeTab === 'eligible', 'opacity-0': activeTab !== 'eligible'}">
            <div class="bg-white overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label for="facility" class="block text-sm font-medium text-gray-700">Facility</label>
                            <select id="facility" v-model="filters.facility_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">All</option>
                                <option v-for="facility in props.facilities" :key="facility.id" :value="facility.id">{{ facility.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="search" name="search" id="search" v-model="filters.search" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search facilities">
                        </div>
                        <div>
                            <button @click="openEligibleCreateModal()"
                                class="inline-flex items-center justify-center w-full px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150 ease-in-out">
                                Add Eligible Item
                            </button>
                        </div>

                    </div>
                </div>
                <div class="mt-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="eligibleItem in props.eligibleItems.data" :key="eligibleItem.id">
                                <td class="px-6 py-4 whitespace-no-wrap">
                                    {{ eligibleItem.product?.name }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Product Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="7xl">
            <div class="p-6">
                <div class="mb-4">
                    <div class="text-red-500" v-if="error">
                        {{ error }}
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ form.id ? 'Edit Product' : 'Create New Product' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:underline"
                        :disabled="isSubmitted || processing">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
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
                                    <label for="name">Product Name</label>
                                    <input id="name" type="text" class="mt-1 block w-full" v-model="form.name"
                                        required autocomplete="name" placeholder="Enter product name" />
                                </div>

                                <!-- SKU -->
                                <div>
                                    <label for="sku">SKU</label>
                                    <input id="sku" type="text" class="mt-1 block w-full" v-model="form.sku"
                                        required placeholder="Enter SKU code" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Barcode -->
                                <div>
                                    <label for="barcode">Barcode</label>
                                    <input id="barcode" type="text" class="mt-1 block w-full" v-model="form.barcode"
                                        placeholder="Enter barcode (optional)" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <label for="category_id">Category</label>
                                    <select v-model="form.category_id" id="category_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md"
                                        @change="onCategoryChange">
                                        <option value="">Select a category</option>
                                        <option v-for="category in props.categories.data" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Subcategory -->
                                <div>
                                    <label for="sub_category_id">Subcategory</label>
                                    <select v-model="form.sub_category_id" id="sub_category_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md">
                                        <option value="">Select a subcategory</option>
                                        <option v-for="subcategory in props.subcategories.data" :key="subcategory.id" :value="subcategory.id">
                                            {{ subcategory.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Dosage -->
                                <div>
                                    <label for="dosage_id">Dosage</label>
                                    <select v-model="form.dosage_id" id="dosage_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md"
                                        :disabled="!form.category_id">
                                        <option value="">Select a dosage</option>
                                        <option v-for="dosage in filteredFormDosages" :key="dosage.id" :value="dosage.id">
                                            {{ dosage.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Reorder Level -->
                                <div>
                                    <label for="reorder_level">Reorder Level</label>
                                    <input id="reorder_level" type="number" class="mt-1 block w-full" v-model="form.reorder_level"
                                        placeholder="Enter reorder level" />
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label for="description">Description</label>
                                    <textarea id="description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition"
                                        v-model="form.description" rows="3"
                                        placeholder="Enter product description"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="button"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition mr-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 disabled:opacity-50"
                                @click="closeModal" :disabled="isSubmitted || processing">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50"
                                :disabled="isSubmitted || processing">
                                <span v-if="processing || isSubmitted">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
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
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Product
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    Are you sure you want to delete this product? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <button type="button"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                        @click="closeDeleteModal" :disabled="processing">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                        @click="deleteProduct" :disabled="processing">
                        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Dosage Modal -->
        <Modal :show="showDosageModal" @close="closeDosageModal" max-width="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ dosageForm.id ? 'Edit Dosage' : 'Add New Dosage' }}
                    </h2>
                    <button @click="closeDosageModal" 
                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Error Display -->
                <div v-if="dosageErrors" class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside">
                                    <li v-for="(error, field) in dosageErrors" :key="field">
                                        {{ error[0] }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitDosageForm" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="dosage-name" class="block text-sm font-medium text-gray-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="dosage-name" v-model="dosageForm.name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': dosageErrors?.name }"
                            placeholder="Enter dosage name">
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="dosage-category" class="block text-sm font-medium text-gray-700">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select id="dosage-category" v-model="dosageForm.category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': dosageErrors?.category_id }">
                            <option value="">Select a category</option>
                            <option v-for="category in props.categories.data" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="dosage-description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea id="dosage-description" v-model="dosageForm.description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': dosageErrors?.description }"
                            placeholder="Enter description"></textarea>
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" id="dosage-status" v-model="dosageForm.is_active"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="dosage-status" class="ml-2 block text-sm text-gray-700">Active</label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-5">
                        <button type="button"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition"
                            @click="closeDosageModal"
                            :disabled="dosageProcessing">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            :disabled="dosageProcessing">
                            <svg v-if="dosageProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ dosageForm.id ? 'Update Dosage' : 'Create Dosage' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Dosage Delete Modal -->
        <Modal :show="showDosageDeleteModal" @close="closeDosageDeleteModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">Delete Dosage</h2>
                    <button @click="closeDosageDeleteModal" 
                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mt-3 text-sm text-gray-600">
                    Are you sure you want to delete this dosage? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition"
                        @click="closeDosageDeleteModal"
                        :disabled="dosageProcessing">
                        Cancel
                    </button>
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                        @click="deleteDosage"
                        :disabled="dosageProcessing">
                        <svg v-if="dosageProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Delete Dosage
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Category Modal -->
        <Modal :show="showCategoryModal" @close="closeCategoryModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ categoryForm.id ? 'Edit Category' : 'Add New Category' }}
                    </h2>
                    <button @click="closeCategoryModal" 
                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>

                <div v-if="categoryErrors" class="mt-3">
                    <div v-for="(messages, field) in categoryErrors" :key="field" class="text-sm text-red-600">
                        <div v-for="(message, i) in messages" :key="i">{{ message }}</div>
                    </div>
                </div>

                <form @submit.prevent="submitCategoryForm" class="space-y-6 mt-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="name" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="categoryForm.name" required :disabled="categoryIsSubmitted || categoryProcessing"
                            placeholder="Enter category name" />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description"
                            class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="categoryForm.description" :disabled="categoryIsSubmitted || categoryProcessing"
                            rows="3" placeholder="Enter category description"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input id="is_active" type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            v-model="categoryForm.is_active" :disabled="categoryIsSubmitted || categoryProcessing" />
                        <label for="is_active" class="ml-2">Active</label>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                        <button type="button"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                            @click="closeCategoryModal" :disabled="categoryIsSubmitted || categoryProcessing">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                            :disabled="categoryIsSubmitted || categoryProcessing">
                            <svg v-if="categoryProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            {{ categoryForm.id ? 'Update Category' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Category Delete Confirmation Modal -->
        <Modal :show="showCategoryDeleteModal" @close="closeCategoryDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Category
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    Are you sure you want to delete this category? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <button type="button"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                        @click="closeCategoryDeleteModal" :disabled="categoryProcessing">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                        @click="deleteCategory" :disabled="categoryProcessing">
                        <svg v-if="categoryProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Subcategory Modal -->
        <Modal :show="showSubcategoryModal" @close="closeSubcategoryModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ subcategoryForm.id ? 'Edit Subcategory' : 'Add New Subcategory' }}
                    </h2>
                    <button @click="closeSubcategoryModal" 
                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="subcategoryErrors" class="mt-3">
                    <div v-for="(messages, field) in subcategoryErrors" :key="field" class="text-sm text-red-600">
                        <div v-for="(message, i) in messages" :key="i">{{ message }}</div>
                    </div>
                </div>

                <form @submit.prevent="submitSubcategoryForm" class="space-y-6 mt-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="name" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="subcategoryForm.name" required :disabled="subcategoryIsSubmitted || subcategoryProcessing"
                            placeholder="Enter subcategory name" />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description"
                            class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="subcategoryForm.description" :disabled="subcategoryIsSubmitted || subcategoryProcessing"
                            rows="3" placeholder="Enter subcategory description"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input id="is_active" type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            v-model="subcategoryForm.is_active" :disabled="subcategoryIsSubmitted || subcategoryProcessing" />
                        <label for="is_active" class="ml-2">Active</label>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                        <button type="button"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                            @click="closeSubcategoryModal" :disabled="subcategoryIsSubmitted || subcategoryProcessing">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                            :disabled="subcategoryIsSubmitted || subcategoryProcessing">
                            <svg v-if="subcategoryProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            {{ subcategoryForm.id ? 'Update Subcategory' : 'Create Subcategory' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Subcategory Delete Confirmation Modal -->
        <Modal :show="showSubcategoryDeleteModal" @close="closeSubcategoryDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Subcategory
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    Are you sure you want to delete this subcategory? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <button type="button"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                        @click="closeSubcategoryDeleteModal" :disabled="subcategoryProcessing">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                        @click="deleteSubcategory" :disabled="subcategoryProcessing">
                        <svg v-if="subcategoryProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </Modal>


        <!-- Subcategory Delete Confirmation Modal -->
        <Modal :show="eligibleShow" @close="closeSubcategoryDeleteModal">
            <div class="p-6">
                <form>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="product" class="block text-sm font-medium text-gray-700">Product</label>
                            <select id="product" v-model="eligibleItemForm.product_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option v-for="product in props.products.data" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="facility" class="block text-sm font-medium text-gray-700">Facility</label>
                            <select id="facility" v-model="eligibleItemForm.facility_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option v-for="facility in facilities" :key="facility.id" :value="facility.id">
                                    {{ facility.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    products: Object,
    filters: Object,
    warehouses: Array,
    categories: Object,
    categoryFilters: Object,
    dosages: Object,
    dosageFilters: Object,
    subcategories: {
        type: Object,
        required: true
    },
    subcategoryFilters: {
        type: Object,
        required: true
    },
    eligibleItems: {
        type: Object,
        required: true
    },
    facilities: Array
});

// Reactive state
const search = ref(props.filters?.search || '');
const category_id = ref(props.filters?.category_id || '');
const sub_category_id = ref(props.filters?.sub_category_id || '');
const dosage_id = ref(props.filters?.dosage_id || '');
const is_active = ref(props.filters?.is_active || '');
const per_page = ref(props.filters?.per_page || 6);
const processing = ref(false);
const params = ref({});
const activeTab = ref('products');

// Product form state
const showModal = ref(false);
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const isSubmitted = ref(false);
const errors = ref(null);

// Category state
const categorySearch = ref(props.categoryFilters?.search || '');
const categoryProcessing = ref(false);
const showCategoryModal = ref(false);
const showCategoryDeleteModal = ref(false);
const categoryIsSubmitted = ref(false);
const categoryToDelete = ref(null);
const categoryErrors = ref(null);
const category_per_page = ref(props.categoryFilters?.category_per_page || 6);

// SubCategory state
const subcategorySearch = ref(props.subcategoryFilters?.search || '');
const subcategoryProcessing = ref(false);
const showSubcategoryModal = ref(false);
const showSubcategoryDeleteModal = ref(false);
const subcategoryIsSubmitted = ref(false);
const subcategoryToDelete = ref(null);
const subcategoryErrors = ref(null);
const subcategory_per_page = ref(props.subcategoryFilters?.subcategory_per_page || 6);

// Dosage state
const dosageSearch = ref(props.dosageFilters?.search || '');
const dosageProcessing = ref(false);
const showDosageModal = ref(false);
const showDosageDeleteModal = ref(false);
const dosageIsSubmitted = ref(false);
const dosageToDelete = ref(null);
const dosageErrors = ref(null);
const dosage_per_page = ref(props.dosageFilters?.dosage_per_page || 6);

// Category form
const categoryForm = ref({
    id: null,
    name: '',
    description: '',
    is_active: true,
});

// Dosage form
const dosageForm = ref({
    id: null,
    name: '',
    description: '',
    category_id: '',
    is_active: true,
});

// Subcategory form
const subcategoryForm = ref({
    id: null,
    name: '',
    description: '',
    category_id: '',
    is_active: true,
});

// Form for creating/editing products
const form = ref({
    id: null,
    name: '',
    sku: '',
    barcode: '',
    description: '',
    category_id: '',
    sub_category_id: '',
    dosage_id: '',
    reorder_level: 0,
    is_active: true,
});

// Computed properties
const filteredDosages = computed(() => {
    if (!category_id.value) return [];
    return props.dosages.data.filter(dosage => dosage.category_id === category_id.value);
});

const filteredFormDosages = computed(() => {
    if (!form.value.category_id) return [];
    return props.dosages.data.filter(dosage => dosage.category_id === form.value.category_id);
});

// Bulk delete functionality
const selectedItems = ref([]);
const isBulkDeleting = ref(false);
const showBulkDeleteModal = ref(false);

const toggleSelectAll = () => {
    if (selectedItems.value.length === props.products.data.length) {
        selectedItems.value = [];
    } else {
        selectedItems.value = props.products.data.map(product => product.id);
    }
};

const confirmBulkDelete = () => {
    if (selectedItems.value.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Products Selected',
            text: 'Please select at least one product to delete.',
            confirmButtonText: 'OK'
        });
        return;
    }
    Swal.fire({
        title: 'Delete Products',
        text: `Are you sure you want to delete ${selectedItems.value.length} products?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Delete'
    }).then(async (result) => {
        if (result.isConfirmed) {
            bulkDelete();
        }
    });
};

const bulkDelete = async () => {
    try {
        showBulkDeleteModal.value = false;
        isBulkDeleting.value = true;

        // Perform the actual delete operation
        const response = await axios.post(route('products.bulk'), {
            ids: selectedItems.value,
            action: 'delete'
        });
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.data.message || 'Selected products have been deleted successfully',
            timer: 1500,
            showConfirmButton: false
        });

        selectedItems.value = [];
        router.reload();
    } catch (error) {
        console.error('Delete error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            html: error.response?.data?.message || 'An error occurred while deleting products',
            confirmButtonText: 'OK'
        });
    } finally {
        isBulkDeleting.value = false;
    }
};

// Watch for changes in search input
watch([
    () => search.value,
    () => category_id.value,
    () => sub_category_id.value,
    () => dosage_id.value,
    () => is_active.value,
    () => per_page.value,
], () => {
    reloadProducts();
});

// Watch for changes in category search input
watch([
    () => categorySearch.value,
    () => category_per_page.value
], () => {
    reloadCategories();
});

// Watch for changes in subcategory search input
watch([
    () => subcategorySearch.value,
    () => subcategory_per_page.value
], () => {
    reloadSubcategories();
});

// Watch for changes in dosage search input
watch([
    () => dosageSearch.value,
    () => dosage_per_page.value
], () => {
    reloadDosages();
});

function reloadProducts() {
    if (is_active.value) {
        params.value.is_active = is_active.value;
    } else {
        delete params.value.is_active;
    }
    if (per_page.value) {
        params.value.per_page = per_page.value;
    } else {
        delete params.value.per_page;
    }
    if (search.value) {
        params.value.search = search.value;
    } else {
        delete params.value.search;
    }
    if (category_id.value) {
        params.value.category_id = category_id.value;
    } else {
        delete params.value.category_id;
    }
    if (sub_category_id.value) {
        params.value.sub_category_id = sub_category_id.value;
    } else {
        delete params.value.sub_category_id;
    }
    if (dosage_id.value) {
        params.value.dosage_id = dosage_id.value;
    } else {
        delete params.value.dosage_id;
    }

    // Always include the page parameter
    params.value.page = route().params.page || 1;

    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['products']
    });
}

onMounted(() => {
    reloadProducts();
    onCategoryFilterChange();
});

function reloadCategories() {
    if (categorySearch.value) {
        params.value.categorySearch = categorySearch.value;
    } else {
        delete params.value.categorySearch;
    }
    if (category_per_page.value) {
        params.value.category_per_page = category_per_page.value;
    } else {
        delete params.value.category_per_page;
    }
    if (params.value.category_page) {
        params.value.category_page = params.value.category_page;
    } else {
        params.value.category_page = 1;
    }
    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['categories']
    });
}

function reloadSubcategories() {
    if (subcategorySearch.value) {
        params.value.subcategorySearch = subcategorySearch.value;
    } else {
        delete params.value.subcategorySearch;
    }
    if (subcategory_per_page.value) {
        params.value.subcategory_per_page = subcategory_per_page.value;
    } else {
        delete params.value.subcategory_per_page;
    }
    if (params.value.subcategory_page) {
        params.value.subcategory_page = params.value.subcategory_page;
    } else {
        params.value.subcategory_page = 1;
    }
    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['subcategories']
    });
}

function reloadDosages() {
    if (dosageSearch.value) {
        params.value.dosageSearch = dosageSearch.value;
    } else {
        delete params.value.dosageSearch;
    }
    if (dosage_per_page.value) {
        params.value.dosage_per_page = dosage_per_page.value;
    } else {
        delete params.value.dosage_per_page;
    }
    if (params.value.dosage_page) {
        params.value.dosage_page = params.value.dosage_page;
    } else {
        params.value.dosage_page = 1;
    }
    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['dosages']
    });
};

// Sort categories
const sortCategory = (field) => {
    if (props.categoryFilters.sort_field === field) {
        params.value.categoryDirection = props.categoryFilters.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        params.value.categoryField = field;
        params.value.categoryDirection = 'asc';
    }

    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['categories']
    });
};

// Sort subcategories
const sortSubcategory = (field) => {
    if (props.subcategoryFilters.sort_field === field) {
        params.value.subcategoryDirection = props.subcategoryFilters.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        params.value.subcategoryField = field;
        params.value.subcategoryDirection = 'asc';
    }

    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['subcategories']
    });
};

// Sort dosages
const sortDosage = (field) => {
    if (props.dosageFilters.sort_field === field) {
        params.value.dosageDirection = props.dosageFilters.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        params.value.dosageField = field;
        params.value.dosageDirection = 'asc';
    }

    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['dosages']
    });
};

// Filter dosages based on selected category
const onCategoryChange = () => {
    form.value.dosage_id = ''; // Reset dosage selection when category changes
};

// Handle category filter change in product listing
const onCategoryFilterChange = () => {
    dosage_id.value = ''; // Reset dosage selection when category filter changes
    reloadProducts();
};

// Watch for tab changes to load appropriate data
watch(activeTab, (newTab) => {
    if (newTab === 'categories') {
        reloadCategories();
    }

    if (newTab === 'products') {
        reloadProducts();
    }

    if (newTab === 'subcategories') {
        reloadSubcategories();
    }

    if (newTab === 'dosages') {
        reloadDosages();
    }
});

// Open modal for create/edit
const openModal = (product = null) => {
    if (product) {
        // Edit mode
        form.value.id = product.id;
        form.value.name = product.name;
        form.value.sku = product.sku;
        form.value.barcode = product.barcode || '';
        form.value.description = product.description || '';
        form.value.category_id = product.category_id || '';
        onCategoryChange();
        form.value.dosage_id = product.dosage_id || '';
        form.value.is_active = product.is_active !== undefined ? product.is_active : true;
    } else {
        // Create mode
        resetForm();
        showModal.value = true;
    }
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    if (processing.value || isSubmitted.value) return;

    showModal.value = false;
    setTimeout(() => {
        resetForm()
    }, 300);
};

function resetForm() {
    form.value = {
        id: null,
        name: '',
        sku: '',
        barcode: '',
        description: '',
        category_id: '',
        sub_category_id: '',
        dosage_id: '',
        reorder_level: 0,
        is_active: true
    };

}

const error = ref('');
// Submit form
const submitForm = async () => {
    error.value = '';
    if (isSubmitted.value || processing.value) return;

    // Client-side validation for dates
    if (form.manufacturing_date && form.expiry_date && form.manufacturing_date > form.expiry_date) {
        toast.error('Expiry date must be after manufacturing date');
        return;
    }

    isSubmitted.value = true;
    processing.value = true;


    await axios.post(route('products.store'), form.value)
        .then(response => {
            toast.success(response.data.message);
            closeModal();
            reloadProducts();
            showModal.value = false;
        })
        .catch(err => {
            toast.error(err.response.data.message);
            error.value = err.response.data?.message;
            
            console.error(err);
        })
        .finally(() => {
            processing.value = false;
            isSubmitted.value = false;
        });
};

// Confirm delete
const confirmDelete = (product) => {
    Swal.fire({
        title: 'Delete Product',
        text: `Are you sure you want to delete ${product.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Delete'
    }).then(async (result) => {
        if (result.isConfirmed) {
            let timerInterval;
            try {
                // Show the countdown Swal
                await Swal.fire({
                    title: "Deleting Product",
                    html: "Processing will complete in <b></b> milliseconds.",
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        const timer = Swal.getPopup().querySelector("b");
                        timerInterval = setInterval(() => {
                            timer.textContent = `${Swal.getTimerLeft()}`;
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                });

                await router.delete(route('products.destroy', product.id));
                
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Product has been deleted successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.response.data || 'An error occurred while deleting the product',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '';

    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

// Reset all filters
const resetFilters = () => {
    search.value = '';
    category_id.value = '';
    sub_category_id.value = '';
    dosage_id.value = '';
    is_active.value = '';
    per_page.value = 5;
    reloadProducts();
};

// Open category modal for create/edit
const openCategoryModal = (category = null) => {
    categoryErrors.value = null;
    categoryIsSubmitted.value = false;

    if (category) {
        categoryForm.value.id = category.id;
        categoryForm.value.name = category.name;
        categoryForm.value.description = category.description || '';
        categoryForm.value.is_active = category.is_active;
    } else {
        categoryFormReset();
    }

    showCategoryModal.value = true;
};

function categoryFormReset() {
    categoryForm.value.id = null;
    categoryForm.value.name = '';
    categoryForm.value.description = '';
    categoryForm.value.is_active = true;
}

// Close category modal
const closeCategoryModal = () => {
    showCategoryModal.value = false;
};

// Submit category form
const submitCategoryForm = () => {
    categoryIsSubmitted.value = true;
    categoryProcessing.value = true;
    categoryErrors.value = null;

    axios.post(route('categories.store'), categoryForm.value)
        .then(response => {
            if (response.data.success) {
                toast.success(response.data.message || 'Category saved successfully');
                closeCategoryModal();
                reloadCategories();
            } else {
                toast.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                categoryErrors.value = error.response.data.errors;
                toast.error('Please correct the errors in the form');
            } else {
                toast.error('An error occurred while saving the category');
            }
        })
        .finally(() => {
            categoryProcessing.value = false;
            categoryIsSubmitted.value = false;
        });
};

// Confirm delete category
const confirmDeleteCategory = (category) => {
    categoryToDelete.value = category;
    showCategoryDeleteModal.value = true;
};

// Close category delete modal
const closeCategoryDeleteModal = () => {
    showCategoryDeleteModal.value = false;
    categoryToDelete.value = null;
};

// Delete category
const deleteCategory = () => {
    if (!categoryToDelete.value) return;

    categoryProcessing.value = true;

    axios.delete(route('categories.destroy', categoryToDelete.value.id))
        .then(response => {
            if (response.data.success) {
                toast.success(response.data || 'Category deleted successfully');
                closeCategoryDeleteModal();
                reloadCategories();

                // If we're deleting a category, refresh the products list to update the category filter
                if (activeTab.value === 'products') {
                    reloadProducts();
                }
            } else {
                toast.error(response.data || 'An error occurred');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response.data || 'An error occurred',
            });
            console.error(error);
        })
        .finally(() => {
            categoryProcessing.value = false;
        });
};

// Open dosage modal for create/edit
const openDosageModal = (dosage = null) => {
    dosageErrors.value = null;
    dosageIsSubmitted.value = false;

    if (dosage) {
        dosageForm.value.id = dosage.id;
        dosageForm.value.name = dosage.name;
        dosageForm.value.description = dosage.description || '';
        dosageForm.value.category_id = dosage.category_id || '';
        dosageForm.value.is_active = dosage.is_active;
    } else {
        dosageFormReset();
    }

    showDosageModal.value = true;
};

function dosageFormReset() {
    dosageForm.value.id = null;
    dosageForm.value.name = '';
    dosageForm.value.description = '';
    dosageForm.value.category_id = '';
    dosageForm.value.is_active = true;
}

// Close dosage modal
const closeDosageModal = () => {
    showDosageModal.value = false;
};

// Submit dosage form
const submitDosageForm = async () => {
    dosageIsSubmitted.value = true;
    dosageProcessing.value = true;
    dosageErrors.value = null;

    await axios.post(route('dosages.store'), dosageForm.value)
        .then(response => {
            if (response.data.success) {
                toast.success(response.data.message || 'Dosage saved successfully');
                closeDosageModal();
                reloadDosages();
            } else {
                toast.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error(error);
            if (error.response && error.response.data && error.response.data.errors) {
                dosageErrors.value = error.response.data.errors;
                toast.error('Please correct the errors in the form');
            } else {
                toast.error('An error occurred while saving the dosage');
            }
        })
        .finally(() => {
            dosageProcessing.value = false;
            dosageIsSubmitted.value = false;
        });
};

// Confirm delete dosage
const confirmDeleteDosage = (dosage) => {
    dosageToDelete.value = dosage;
    showDosageDeleteModal.value = true;
};

// Close dosage delete modal
const closeDosageDeleteModal = () => {
    showDosageDeleteModal.value = false;
    dosageToDelete.value = null;
};

// Delete dosage
const deleteDosage = () => {
    if (!dosageToDelete.value) return;

    dosageProcessing.value = true;

    axios.delete(route('dosages.destroy', dosageToDelete.value.id))
        .then(response => {
            if (response.data.success) {
                Swal.fire('Deleted!', response.data, 'success');
                closeDosageDeleteModal();
                reloadDosages();
            } else {
                Swal.fire('Error!', response.data || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error!', error.response.data, 'error');
            console.error(error);
        })
        .finally(() => {
            dosageProcessing.value = false;
        });
};

// Open subcategory modal for create/edit
const openSubcategoryModal = (subcategory = null) => {
    subcategoryErrors.value = null;
    subcategoryIsSubmitted.value = false;

    if (subcategory) {
        subcategoryForm.value.id = subcategory.id;
        subcategoryForm.value.name = subcategory.name;
        subcategoryForm.value.description = subcategory.description || '';
        subcategoryForm.value.category_id = subcategory.category_id || '';
        subcategoryForm.value.is_active = subcategory.is_active;
    } else {
        subcategoryFormReset();
    }

    showSubcategoryModal.value = true;
};

function subcategoryFormReset() {
    subcategoryForm.value.id = null;
    subcategoryForm.value.name = '';
    subcategoryForm.value.description = '';
    subcategoryForm.value.category_id = '';
    subcategoryForm.value.is_active = true;
}

// Close subcategory modal
const closeSubcategoryModal = () => {
    showSubcategoryModal.value = false;
};

// Submit subcategory form
const submitSubcategoryForm = async () => {
    subcategoryIsSubmitted.value = true;
    subcategoryProcessing.value = true;
    subcategoryErrors.value = null;

    await axios.post(route('subcategories.store'), subcategoryForm.value)
        .then(response => {
            if (response.data.success) {
                toast.success(response.data.message || 'Subcategory saved successfully');
                closeSubcategoryModal();
                reloadSubcategories();
            } else {
                toast.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error(error);
            if (error.response && error.response.data && error.response.data.errors) {
                subcategoryErrors.value = error.response.data.errors;
                toast.error('Please correct the errors in the form');
            } else {
                toast.error('An error occurred while saving the subcategory');
            }
        })
        .finally(() => {
            subcategoryProcessing.value = false;
            subcategoryIsSubmitted.value = false;
        });
};

// Confirm delete subcategory
const confirmDeleteSubcategory = (subcategory) => {
    subcategoryToDelete.value = subcategory;
    showSubcategoryDeleteModal.value = true;
};

// Close subcategory delete modal
const closeSubcategoryDeleteModal = () => {
    showSubcategoryDeleteModal.value = false;
    subcategoryToDelete.value = null;
};

// Delete subcategory
const deleteSubcategory = () => {
    if (!subcategoryToDelete.value) return;

    subcategoryProcessing.value = true;

    axios.delete(route('subcategories.destroy', subcategoryToDelete.value.id))
        .then(response => {
            if (response.data.success) {
                toast.success(response.data || 'Subcategory deleted successfully');
                closeSubcategoryDeleteModal();
                reloadSubcategories();
            } else {
                toast.error(response.data || 'An error occurred');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response.data || 'An error occurred',
            });
            console.error(error);
        })
        .finally(() => {
            subcategoryProcessing.value = false;
        });
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
};

watch(category_id, (newVal) => {
    if (!newVal) {
        sub_category_id.value = '';
    }
});

watch(() => form.value.category_id, (newVal) => {
    if (!newVal) {
        form.value.sub_category_id = '';
    }
});

const eligibleShow = ref(false);

// Eligible Item form
const eligibleItemForm = ref({
    id: null,
    product_id: '',
    facility_id: '',
});

function openEligibleCreateModal(){
    eligibleShow.value = true;
}
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