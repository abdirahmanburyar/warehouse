<template>
    <!-- Template content remains unchanged -->

    <Head title="Product List" />
    <AuthenticatedLayout>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight p-5">Product List</h2>

        <!-- Tab Navigation -->
        <div class="bg-white">
            <div class="mx-auto">
                <div class="flex border-b border-gray-200">
                    <button @click="activeTab = 'products'" :class="[
                        'py-4 px-6 border-b-2 font-medium text-sm focus:outline-none',
                        activeTab === 'products'
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]">
                        Products
                    </button>
                    <button @click="activeTab = 'categories'" :class="[
                        'py-4 px-6 border-b-2 font-medium text-sm focus:outline-none',
                        activeTab === 'categories'
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]">
                        Categories
                    </button>
                    <button @click="activeTab = 'dosages'" :class="[
                        'py-4 px-6 border-b-2 font-medium text-sm focus:outline-none',
                        activeTab === 'dosages'
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]">
                        Dosages
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Tab Content -->
        <div v-if="activeTab === 'products'">
            <div class="">
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
                                            fill="none" viewBox="0 0 24 24">
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
                                    <option value="10">10 / page</option>
                                    <option value="25">25 / page</option>
                                    <option value="50">50 / page</option>
                                    <option value="100">100 / page</option>
                                </select>
                                
                                <!-- Reset Filters Button -->
                                <button @click="resetFilters" 
                                    class="w-full md:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                                    <span class="flex items-center justify-center">
                                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Reset
                                    </span>
                                </button>
                            </div>
                            
                            <!-- Add New Product Button on the right -->
                            <button @click="openModal()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50">
                                <span class="flex items-center">
                                    <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add New Product
                                </span>
                            </button>
                        </div>

                        <!-- Products Table -->
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th @click="sort('id')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            ID
                                            <SortIcon :field="'id'" :current-sort="sort_field"
                                                :direction="sort_direction" />
                                        </th>
                                        <th @click="sort('name')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            Name
                                            <SortIcon :field="'name'" :current-sort="sort_field"
                                                :direction="sort_direction" />
                                        </th>
                                        <th @click="sort('sku')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            SKU
                                            <SortIcon :field="'sku'" :current-sort="sort_field"
                                                :direction="sort_direction" />
                                        </th>
                                        <th @click="sort('barcode')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            Barcode
                                            <SortIcon :field="'barcode'" :current-sort="sort_field"
                                                :direction="sort_direction" />
                                        </th>
                                        <th @click="sort('category.name')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            Category
                                            <SortIcon :field="'category.name'" :current-sort="sort_field"
                                                :direction="sort_direction" />
                                        </th>
                                        <th @click="sort('is_active')" scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            Status
                                            <SortIcon :field="'is_active'" :current-sort="sort_field"
                                                :direction="sort_direction" />
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in props.products.data" :key="product.id"
                                        class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                            product.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.sku }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ product.barcode
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{
                                            product.dosage?.category?.name }} - {{
                                                product.dosage?.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                            ]">
                                                {{ product.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openModal(product)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3 focus:outline-none focus:underline">
                                                Edit
                                            </button>
                                            <button @click="confirmDelete(product)"
                                                class="text-red-600 hover:text-red-900 focus:outline-none focus:underline">
                                                Delete
                                            </button>
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
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
                            <Pagination :links="props.products.meta.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Tab Content -->
        <div v-if="activeTab === 'categories'">
            <div class="bg-white overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search and Add Button -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-4 md:space-y-0">
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
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <button @click="openCategoryModal()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Category
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
                                            <SortIcon :field="'id'" :sort-field="categoryFilters.sort_field"
                                                :sort-direction="categoryFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortCategory('name')">
                                        <div class="flex items-center">
                                            Name
                                            <SortIcon :field="'name'" :sort-field="categoryFilters.sort_field"
                                                :sort-direction="categoryFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                        Description
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortCategory('is_active')">
                                        <div class="flex items-center">
                                            Status
                                            <SortIcon :field="'is_active'" :sort-field="categoryFilters.sort_field"
                                                :sort-direction="categoryFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortCategory('created_at')">
                                        <div class="flex items-center">
                                            Created At
                                            <SortIcon :field="'created_at'" :sort-field="categoryFilters.sort_field"
                                                :sort-direction="categoryFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(category, i) in categories.data" :key="category.id"
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
                                                class="text-blue-600 hover:text-blue-900 transition" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                                </svg>
                                            </button>
                                            <button @click="confirmDeleteCategory(category)"
                                                class="text-red-600 hover:text-red-900 transition" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="categories.data.length === 0">
                                    <td colspan="6" class="py-6 text-center text-gray-500">No categories found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center">
                        <Pagination :links="props.categories.meta.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dosages Tab Content -->
        <div v-if="activeTab === 'dosages'">
            <div class="bg-white overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search and Add Button -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-4 md:space-y-0">
                        <div class="w-full md:w-1/3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input v-model="dosageSearch" type="text" placeholder="Search by name or description..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150" />
                                <div v-if="dosageProcessing"
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
                                <select v-model="dosage_per_page"
                                    class="w-full md:w-48 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <button @click="openDosageModal()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Dosage
                            </button>
                        </div>
                    </div>

                    <!-- Dosages Table -->
                    <div class="overflow-x-auto bg-white rounded-lg overflow-y-auto relative">
                        <table
                            class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortDosage('id')">
                                        <div class="flex items-center">
                                            SN
                                            <SortIcon :field="'id'" :sort-field="dosageFilters.sort_field"
                                                :sort-direction="dosageFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortDosage('name')">
                                        <div class="flex items-center">
                                            Name
                                            <SortIcon :field="'name'" :sort-field="dosageFilters.sort_field"
                                                :sort-direction="dosageFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortDosage('category_id')">
                                        <div class="flex items-center">
                                            Category
                                            <SortIcon :field="'category_id'" :sort-field="dosageFilters.sort_field"
                                                :sort-direction="dosageFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortDosage('description')">
                                        <div class="flex items-center">
                                            Description
                                            <SortIcon :field="'description'" :sort-field="dosageFilters.sort_field"
                                                :sort-direction="dosageFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer"
                                        @click="sortDosage('is_active')">
                                        <div class="flex items-center">
                                            Status
                                            <SortIcon :field="'is_active'" :sort-field="dosageFilters.sort_field"
                                                :sort-direction="dosageFilters.sort_direction" />
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(dosage, i) in dosages.data" :key="dosage.id"
                                    class="hover:bg-gray-100 transition-colors duration-150">
                                    <td class="py-3 px-4 border-b border-gray-200">{{ i + 1 }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200">{{ dosage.name }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200">{{ dosage.category?.name }}</td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <div class="max-w-xs truncate">{{ dosage.description || 'No description' }}
                                        </div>
                                    </td>

                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <span
                                            :class="dosage.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-2 py-1 text-xs rounded-full">
                                            {{ dosage.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 border-b border-gray-200">
                                        <div class="flex items-center space-x-2">
                                            <button @click="openDosageModal(dosage)"
                                                class="text-blue-600 hover:text-blue-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button @click="confirmDeleteDosage(dosage)"
                                                class="text-red-600 hover:text-red-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="dosages.data && dosages.data.length === 0">
                                    <td colspan="5" class="py-4 text-center text-gray-500">No dosages found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center">
                        <Pagination :links="dosages.meta.links" />
                    </div>
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
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
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
                                    <InputLabel for="name" value="Product Name" required />
                                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name"
                                        required autocomplete="name" placeholder="Enter product name" />
                                </div>

                                <!-- SKU -->
                                <div>
                                    <InputLabel for="sku" value="SKU" required />
                                    <TextInput id="sku" type="text" class="mt-1 block w-full" v-model="form.sku"
                                        required placeholder="Enter SKU code" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Barcode -->
                                <div>
                                    <InputLabel for="barcode" value="Barcode" />
                                    <TextInput id="barcode" type="text" class="mt-1 block w-full" v-model="form.barcode"
                                        placeholder="Enter barcode (optional)" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <InputLabel for="category_id" value="Category" required />
                                    <select v-model="form.category_id" id="category_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md"
                                        @change="onCategoryChange">
                                        <option value="">Select a category</option>
                                        <option v-for="category in props.categories.data" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Dosage -->
                                <div>
                                    <InputLabel for="dosage_id" value="Dosage" />
                                    <select v-model="form.dosage_id" id="dosage_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md"
                                        :disabled="!form.category_id">
                                        <option value="">Select a dosage</option>
                                        <option v-for="dosage in filteredDosages" :key="dosage.id" :value="dosage.id">
                                            {{ dosage.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <InputLabel for="description" value="Description" />
                                    <textarea id="description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md"
                                        v-model="form.description" rows="3"
                                        placeholder="Enter product description"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="button"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition mr-2 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 disabled:opacity-50"
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
                        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Dosage Modal -->
        <Modal :show="showDosageModal" @close="closeDosageModal" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ dosageForm.id ? 'Edit Dosage' : 'Add New Dosage' }}
                </h2>

                <form @submit.prevent="submitDosageForm">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Name -->
                        <div>
                            <label for="dosage-name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input id="dosage-name" v-model="dosageForm.name" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': dosageErrors && dosageErrors.name }" />
                            <div v-if="dosageErrors && dosageErrors.name" class="text-red-500 text-xs mt-1">
                                {{ dosageErrors.name[0] }}
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="dosage-description"
                                class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="dosage-description" v-model="dosageForm.description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': dosageErrors && dosageErrors.description }"></textarea>
                            <div v-if="dosageErrors && dosageErrors.description" class="text-red-500 text-xs mt-1">
                                {{ dosageErrors.description[0] }}
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="dosage-category"
                                class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="dosage-category" v-model="dosageForm.category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': dosageErrors && dosageErrors.category_id }">
                                <option value="">Select a category</option>
                                <option v-for="category in props.categories.data" :key="category.id"
                                    :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="dosageErrors && dosageErrors.category_id" class="text-red-500 text-xs mt-1">
                                {{ dosageErrors.category_id[0] }}
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <div class="mt-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" v-model="dosageForm.is_active" :value="true"
                                        class="form-radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                                    <span class="ml-2">Active</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" v-model="dosageForm.is_active" :value="false"
                                        class="form-radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                                    <span class="ml-2">Inactive</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeDosageModal"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                            :disabled="dosageIsSubmitted">
                            <svg v-if="dosageProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ dosageForm.id ? 'Update Dosage' : 'Save Dosage' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Dosage Delete Modal -->
        <Modal :show="showDosageDeleteModal" @close="closeDosageDeleteModal" max-width="md">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Confirm Delete
                </h2>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete the dosage "{{ dosageToDelete?.name }}"? This action cannot be
                    undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeDosageDeleteModal"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                        @click="deleteDosage" :disabled="dosageProcessing">
                        <svg v-if="dosageProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Delete Dosage
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Category Modal -->
        <Modal :show="showCategoryModal" @close="closeCategoryModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ categoryForm.id ? 'Edit Category' : 'Create New Category' }}
                </h2>

                <div v-if="categoryErrors" class="mt-3">
                    <div v-for="(messages, field) in categoryErrors" :key="field" class="text-sm text-red-600">
                        <div v-for="(message, i) in messages" :key="i">{{ message }}</div>
                    </div>
                </div>

                <form @submit.prevent="submitCategoryForm" class="space-y-6 mt-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="categoryForm.name" required :disabled="categoryIsSubmitted || categoryProcessing"
                            placeholder="Enter category name" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <textarea id="description"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="categoryForm.description" :disabled="categoryIsSubmitted || categoryProcessing"
                            rows="3" placeholder="Enter category description"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input id="is_active" type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            v-model="categoryForm.is_active" :disabled="categoryIsSubmitted || categoryProcessing" />
                        <InputLabel for="is_active" value="Active" class="ml-2" />
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
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ categoryForm.id ? (categoryIsSubmitted ? 'Updating...' : 'Update') : (categoryIsSubmitted
                                ? 'Creating...' : 'Create') }}
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
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Delete
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
import Pagination from '@/Components/Pagination.vue';
import SortIcon from '@/Components/SortIcon.vue';
import axios from 'axios';
// Props
const props = defineProps({
    products: Object,
    filters: Object,
    warehouses: Array,
    categories: Object,
    categoryFilters: Object,
    dosages: Object,
    dosageFilters: Object,
});

// Reactive state
const activeTab = ref('products'); // Tab state: 'products' or 'categories'
const search = ref(props.filters?.search || '');
const processing = ref(false);
const showModal = ref(false);
const showDeleteModal = ref(false);
const isSubmitted = ref(false);
const selectedProduct = ref(null);
const category_id = ref(props.filters?.category_id || '');
const dosage_id = ref(props.filters?.dosage_id || '');

const categorySearch = ref(props.categoryFilters?.search || '');
const categoryProcessing = ref(false);
const showCategoryModal = ref(false);
const showCategoryDeleteModal = ref(false);
const categoryIsSubmitted = ref(false);
const categoryToDelete = ref(null);
const categoryErrors = ref(null);
const category_per_page = ref(props.categoryFilters?.category_per_page || 10);

const dosageSearch = ref(props.dosageFilters?.search || '');
const dosageProcessing = ref(false);
const showDosageModal = ref(false);
const showDosageDeleteModal = ref(false);
const dosageIsSubmitted = ref(false);
const dosageToDelete = ref(null);
const dosageErrors = ref(null);
const dosage_per_page = ref(props.dosageFilters?.dosage_per_page || 10);
const filteredDosages = ref([]);

const params = ref({});

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

// Form for creating/editing products
const form = ref({
    id: null,
    name: '',
    sku: '',
    barcode: '',
    description: '',
    category_id: '',
    dosage_id: '',
    is_active: true,
});

// Computed properties
const is_active = ref(props.filters.is_active || '');
const sort_field = ref(props.filters.sort_field || 'created_at');
const sort_direction = ref(props.filters.sort_direction || 'desc');
const per_page = ref(props.filters.per_page || 10);


// Watch for changes in search input
watch([
    () => search.value,
    () => is_active.value,
    () => sort_field.value,
    () => sort_direction.value,
    () => per_page.value,
    () => dosage_id.value,
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

// Watch for changes in dosage search input
watch([
    () => dosageSearch.value,
    () => dosage_per_page.value
], () => {
    reloadDosages();
});

function reloadProducts() {
    if (sort_field.value) {
        const newDirection = sort_field.value === sort_field.value && sort_direction.value === 'asc' ? 'desc' : 'asc';
        params.value.sort_field = sort_field.value;
        params.value.sort_direction = newDirection;
    } else {
        delete params.value.sort_field;
        delete params.value.sort_direction;
    }
    if (search.value) {
        params.value.search = search.value;
    } else {
        delete params.value.search;
    }
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
    if (category_id.value) {
        params.value.category_id = category_id.value;
    } else {
        delete params.value.category_id;
    }
    if (dosage_id.value) {
        params.value.dosage_id = dosage_id.value;
    } else {
        delete params.value.dosage_id;
    }

    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
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
    router.get(route('products.index'), params.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['categories']
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
    router.get(route('products.index'), params.value, {
        preserveState: true,
        replace: true,
        only: ['dosages']
    });
}

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
        replace: true,
        only: ['categories']
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
        replace: true,
        only: ['dosages']
    });
};

// Filter dosages based on selected category
const onCategoryChange = async () => {
    form.value.dosage_id = ''; // Reset dosage selection when category changes
    if (form.value.category_id) {
        try {
            // Use the dedicated endpoint for fetching dosages by category
            const response = await axios.get(route('dosages.by-category', form.value.category_id));
            filteredDosages.value = response.data;
        } catch (error) {
            console.error('Error loading dosages:', error);
        }
    } else {
        filteredDosages.value = [];
        form.value.dosage_id = '';
    }
};

// Handle category filter change in product listing
const onCategoryFilterChange = async () => {
    if (!category_id.value) {
        filteredDosages.value = [];
        return;
    }
    try {
        // Use the dedicated endpoint for fetching dosages by category
        const response = await axios.get(route('dosages.by-category', category_id.value));
        filteredDosages.value = response.data;
        reloadProducts();
    } catch (error) {
        console.error('Error loading dosages for filter:', error);
        reloadProducts();
    }
};

// Watch for tab changes to load appropriate data
watch(activeTab, (newTab) => {
    if (newTab === 'categories') {
        reloadCategories();
    }

    if (newTab === 'products') {
        reloadProducts();
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
        dosage_id: '',
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
        ToastService.error('Expiry date must be after manufacturing date');
        return;
    }

    isSubmitted.value = true;
    processing.value = true;


    await axios.post(route('products.store'), form.value)
        .then(response => {
            ToastService.success(response.data.message);
            closeModal();
            reloadProducts();
            showModal.value = false;
        })
        .catch(err => {
            ToastService.error(err.response.data.message);
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
    selectedProduct.value = product;
    showDeleteModal.value = true;
};

// Close delete modal
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedProduct.value = null;
};

// Delete product
const deleteProduct = async () => {
    if (!selectedProduct.value || isSubmitted.value || processing.value) return;

    isSubmitted.value = true;
    processing.value = true;

    await axios.delete(route('products.destroy', selectedProduct.value.id))
        .then(response => {
            if (response.data.success) {
                ToastService.success('Product deleted successfully');
                closeDeleteModal();
                reloadProducts();
            } else {
                ToastService.error('An error occurred while deleting the product');
            }
        })
        .catch(error => {
            ToastService.error('An error occurred while deleting the product');
            console.error(error);
        })
        .finally(() => {
            processing.value = false;
            isSubmitted.value = false;
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
    dosage_id.value = '';
    is_active.value = '';
    per_page.value = 10;
    sort_field.value = 'id';
    sort_direction.value = 'desc';
    filteredDosages.value = [];
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
                ToastService.success(response.data.message || 'Category saved successfully');
                closeCategoryModal();
                reloadCategories();
            } else {
                ToastService.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                categoryErrors.value = error.response.data.errors;
                ToastService.error('Please correct the errors in the form');
            } else {
                ToastService.error('An error occurred while saving the category');
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
                ToastService.success(response.data.message || 'Category deleted successfully');
                closeCategoryDeleteModal();
                reloadCategories();

                // If we're deleting a category, refresh the products list to update the category filter
                if (activeTab.value === 'products') {
                    reloadProducts();
                }
            } else {
                ToastService.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            ToastService.error('An error occurred while deleting the category');
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
                ToastService.success(response.data.message || 'Dosage saved successfully');
                closeDosageModal();
                reloadDosages();
            } else {
                ToastService.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error(error);
            if (error.response && error.response.data && error.response.data.errors) {
                dosageErrors.value = error.response.data.errors;
                ToastService.error('Please correct the errors in the form');
            } else {
                ToastService.error('An error occurred while saving the dosage');
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
                ToastService.success(response.data.message || 'Dosage deleted successfully');
                closeDosageDeleteModal();
                reloadDosages();
            } else {
                ToastService.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            ToastService.error('An error occurred while deleting the dosage');
            console.error(error);
        })
        .finally(() => {
            dosageProcessing.value = false;
        });
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