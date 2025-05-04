<template>
    <!-- Template content remains unchanged -->

    <Head title="Product List" />
    <AuthenticatedLayout title="Product Registration" description="Product Registration" img="/assets/images/tracking.png">
        <!-- Tabs -->
        <div class="mb-4 border-b border-gray-200">
            <nav class="flex -mb-px space-x-8">
                <button @click="activeTab = 'products'" :class="[
                    activeTab === 'products'
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                ]">
                    Products
                </button>
                <button @click="activeTab = 'categories'" :class="[
                    activeTab === 'categories'
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                ]">
                    Categories
                </button>
                <button @click="activeTab = 'dosages'" :class="[
                    activeTab === 'dosages'
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                ]">
                    Dosages Form
                </button>
                <button @click="activeTab = 'eligible'" :class="[
                    activeTab === 'eligible'
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                ]">
                    Eligible Drugs
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div v-show="activeTab === 'products'" class="transition-opacity duration-150" :class="{
            'opacity-100': activeTab === 'products',
            'opacity-0': activeTab !== 'products',
        }">
            <div class="overflow-hidden sm:rounded-lg">
                <div class=" border-b border-gray-200">
                    <!-- Search and Filters -->
                    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
                        <!-- Left side filters in a row -->
                        <div class="flex flex-col flex-grow gap-3 md:flex-row md:items-center">
                            <!-- Search Bar -->
                            <div class="relative flex-grow md:w-auto">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input v-model="search" type="text" placeholder="Search by name, SKU, barcode..."
                                    class="w-full py-2 pl-10 pr-4 transition duration-150 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                                <div v-if="processing"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg"
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
                                class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-40 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                                <option v-for="category in props.categories.data" :key="category.id"
                                    :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>

                            <!-- Dosage Filter -->
                            <select v-model="dosage_id"
                                class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-40 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Dosage Form</option>
                                <option v-for="dosage in props.dosages.data" :key="dosage.id" :value="dosage.id">
                                    {{ dosage.name }}
                                </option>
                            </select>

                            <!-- Status Filter -->
                            <select v-model="is_active"
                                class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-32 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Status</option>
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>

                            <!-- Per Page Selector -->
                            <select v-model="per_page"
                                class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-32 focus:ring-blue-500 focus:border-blue-500">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                                <option value="100">100 / page</option>
                                <option value="200">200 / page</option>
                            </select>

                        </div>

                        <!-- Add New Product Button on the right -->
                        <button @click="openModal()"
                            class="p-3 text-white transition-colors duration-200 bg-gray-900 rounded-full hover:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto ">
                        <table class="min-w-full border border-black">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 py-2 text-xs font-medium tracking-wider text-left text-gray-900 uppercase cursor-pointer border border-black">
                                        <div class="flex items-center justify-center">
                                            <input type="checkbox" :checked="selectedItems.length ===
                                                props.products.data
                                                    .length &&
                                                props.products.data.length >
                                                0
                                                " @change="toggleSelectAll"
                                                class="text-indigo-600 border-gray-300 rounded focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                        </div>
                                    </th>
                                    <th @click="sort('name')" scope="col"
                                        class="px-6 py-3 text-sm font-medium tracking-wider text-left text-black-500 uppercase cursor-pointer hover:bg-gray-100 border border-black">
                                        Name
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-sm font-medium tracking-wider text-left text-black-500 uppercase border border-black">
                                        Category
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-sm font-medium tracking-wider text-left text-black-500 uppercase border border-black">
                                        Dosage Form
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-sm font-medium tracking-wider text-left text-black-500 uppercase border border-black">
                                        Reorder Level
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-sm font-medium tracking-wider text-left text-black-500 uppercase border border-black">
                                        Max
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-sm font-medium tracking-wider text-left text-black-500 uppercase border border-black">
                                        Min
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-sm font-medium tracking-wider text-center text-black-500 uppercase border border-black">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in props.products.data" :key="product.id"
                                    class="transition-colors hover:bg-gray-50 border-b border-gray-200">
                                    <td class="px-3 py-2 border border-black">
                                        <div class="flex items-center justify-center">
                                            <input type="checkbox" v-model="selectedItems" :value="product.id"
                                                class="text-indigo-600 border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border border-black">
                                        <div class="flex flex-col">
                                            <span> {{ product.name }}</span>
                                            <span> Dose: {{ product.dose }}</span>
                                            <span> Barcode: {{ product.barcode }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border border-black">
                                        {{ product.category?.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border border-black">
                                        {{ product.dosage?.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border border-black">
                                        {{ product.reorder_level }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border border-black">
                                        {{ product.max }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border border-black">
                                        {{ product.min }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-center border border-black">
                                        <div class="flex items-center justify-center space-x-3">
                                            <button @click="openModal(product)"
                                                class="inline-flex items-center justify-center w-8 h-8 text-blue-600 transition-colors duration-150 rounded-full bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                                </svg>
                                                <span class="sr-only">Edit</span>
                                            </button>
                                            <button @click="confirmDelete(product)"
                                                class="inline-flex items-center justify-center w-8 h-8 text-red-600 transition-colors duration-150 rounded-full bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="sr-only">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="props.products.data.length === 0">
                                    <td colspan="9"
                                        class="px-6 py-10 text-center text-gray-500 bg-gray-50 border-t border-gray-200">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-10 h-10 mb-2 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                    clip-rule="evenodd" />
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
                                Are you sure you want to delete the selected
                                products? This action cannot be undone.
                            </p>

                            <div class="flex justify-end mt-6 space-x-3">
                                <SecondaryButton @click="showBulkDeleteModal = false">Cancel</SecondaryButton>
                                <DangerButton :disabled="isBulkDeleting" @click="bulkDelete">Delete</DangerButton>
                            </div>
                        </div>
                    </Modal>

                    <!-- Bulk Actions -->
                    <div v-if="selectedItems.length > 0"
                        class="fixed z-50 flex items-center px-4 py-2 space-x-2 transform -translate-x-1/2 bg-white border border-gray-200 rounded-lg shadow-lg bottom-20 left-1/2">
                        <span class="text-sm text-gray-600">{{ selectedItems.length }} products selected</span>
                        <button @click="confirmBulkDelete"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white transition-colors duration-200 bg-red-600 rounded-md shadow-sm hover:bg-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>

                </div>
                <!-- Pagination -->
                <div class="flex items-center justify-between mt-5 mb-[40px]">
                    <span class="mr-3 text-sm text-gray-600">
                        Showing {{ props.products.meta.from }} to
                        {{ props.products.meta.to }} of
                        {{ props.products.meta.total }}
                    </span>
                    <Pagination :links="props.products.meta.links" type="page" />
                </div>
            </div>
        </div>

        <!-- Categories Tab Content -->
        <div v-show="activeTab === 'categories'" class="transition-opacity duration-150" :class="{
            'opacity-100': activeTab === 'categories',
            'opacity-0': activeTab !== 'categories',
        }">
            <div class="overflow-hidden sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <!-- Search and Add Button -->
                    <div
                        class="flex flex-col mb-1 space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                        <div class="w-full md:w-1/3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input v-model="categorySearch" type="text"
                                    placeholder="Search by name or description..."
                                    class="w-full py-2 pl-10 pr-4 transition duration-150 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                                <div v-if="categoryProcessing"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg"
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
                                    class="w-full py-2 pl-3 pr-10 text-sm transition duration-150 border border-gray-300 rounded-lg md:w-48 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>

                            <button @click="openCategoryModal()"
                                class="p-3 text-white transition-colors duration-200 bg-gray-900 rounded-full shadow-sm hover:bg-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="relative overflow-x-auto overflow-y-auto rounded-lg">
                        <table
                            class="relative w-full border-collapse table-auto table-striped border border-black">
                            <thead>
                                <tr class="text-left">
                                    <th class="px-4 py-3 text-sm font-semibold uppercase  border border-black cursor-pointer"
                                        @click="sortCategory('id')">
                                        <div class="flex items-center">SN</div>
                                    </th>
                                    <th class="px-4 py-3 text-sm font-semibold uppercase  border border-black cursor-pointer"
                                        @click="sortCategory('name')">
                                        <div class="flex items-center">
                                            Name
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-sm font-semibold uppercase  border border-black">
                                        Description
                                    </th>
                                    <th class="px-4 py-3 text-sm font-semibold uppercase  border border-black cursor-pointer"
                                        @click="sortCategory('is_active')">
                                        <div class="flex items-center">
                                            Status
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-sm font-semibold uppercase  border border-black cursor-pointer"
                                        @click="sortCategory('created_at')">
                                        <div class="flex items-center">
                                            Created At
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-sm font-semibold uppercase  border border-black">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(category, i) in props.categories
                                    .data" :key="category.id"
                                    class="transition-colors duration-150 hover:bg-gray-100">
                                    <td class="px-4 py-3 border-b  border border-black">
                                        {{ i + 1 }}
                                    </td>
                                    <td class="px-4 py-3 border-b  border border-black">
                                        {{ category.name }}
                                    </td>
                                    <td class="px-4 py-3 border-b  border border-black">
                                        <div class="max-w-xs truncate">
                                            {{
                                                category.description ||
                                                "No description"
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b  border border-black">
                                        <span :class="[
                                            'px-2 py-1 rounded-full text-xs font-medium',
                                            category.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]">
                                            {{
                                                category.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border-b  border border-black">
                                        {{ formatDate(category.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 border-b  border border-black">
                                        <div class="flex items-center space-x-2">
                                            <button @click="
                                                openCategoryModal(category)
                                                "
                                                class="inline-flex items-center justify-center w-8 h-8 text-blue-600 transition-colors duration-150 rounded-full bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                                </svg>
                                                <span class="sr-only">Edit</span>
                                            </button>
                                            <button @click="
                                                deleteCategory(
                                                    category
                                                )
                                                "
                                                class="inline-flex items-center justify-center w-8 h-8 text-red-600 transition-colors duration-150 rounded-full bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
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
                                            <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                        <div class="mb-6 p-6">
                            <div class="flex justify-between items-center">
                                <div class="text-xs text-muted">
                                    Showing {{ props.categories.meta.from }} to {{ props.categories.meta.to }} of {{
                                        props.categories.meta.total }}
                                </div>
                                <Pagination :links="props.categories.meta.links" type="categories" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Dosages Tab Content -->
        <div v-show="activeTab === 'dosages'" class="transition-opacity duration-150" :class="{
            'opacity-100': activeTab === 'dosages',
            'opacity-0': activeTab !== 'dosages',
        }">
            <div class="mx-auto">
                <!-- Search and Add Button -->
                <div class="flex flex-col mb-6 md:flex-row md:items-center md:justify-between">
                    <!-- Left side with search -->
                    <div class="relative w-full md:w-1/3">
                        <div class="relative">
                            <input type="text" v-model="dosageSearch"
                                class="w-full py-2 pl-10 pr-4 transition duration-150 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Search dosages..." />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center mt-4 space-x-4 md:mt-0">
                        <!-- Per page dropdown -->
                        <div class="w-full md:w-auto">
                            <select v-model="dosage_per_page"
                                class="w-full py-2 pl-3 pr-10 text-sm transition duration-150 border border-gray-300 rounded-lg md:w-48 focus:ring-blue-500 focus:border-blue-500">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                            </select>
                        </div>

                        <button @click="openDosageModal()"
                            class="p-3 text-white transition-colors duration-200 bg-gray-900 rounded-full shadow-sm hover:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Dosages Table -->
                <div class="overflow-hidden rounded-lg shadow">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-black">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border border-black">
                                        #
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border border-black">
                                        Name
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border border-black">
                                        Description
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border border-black">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase border border-black">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(dosage, i) in props.dosages.data" :key="dosage.id"
                                    class="transition-colors hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-500  border border-black">
                                        {{ i + 1 }}
                                    </td>
                                    <td class="px-6 py-4  border border-black">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ dosage.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 border border-black">
                                        <div class="text-sm text-gray-900">
                                            {{ dosage.description || "-" }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4  border border-black">
                                        <span :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            dosage.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]">
                                            {{
                                                dosage.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-right  border border-black">
                                        <button @click="openDosageModal(dosage)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 transition-colors duration-150 rounded-full bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                            </svg>
                                            <span class="sr-only">Edit</span>
                                        </button>
                                        <button @click="deleteDosage(dosage)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 transition-colors duration-150 rounded-full bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
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
                                            <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                <div class="mt-5 mb-5">
                    <div class="mb-6 p-6">
                        <div class="flex justify-between items-center">
                            <div class="text-xs text-muted">
                                Showing {{ props.dosages.meta.from }} to {{ props.dosages.meta.to }} of {{
                                    props.dosages.meta.total }}
                            </div>
                            <Pagination :links="props.dosages.meta.links" type="dosages" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Eligible Drugs tab -->
        <div v-if="activeTab === 'eligible'" class="transition-opacity duration-150" :class="{
            'opacity-100': activeTab === 'eligible',
            'opacity-0': activeTab !== 'eligible',
        }">
            <div class="overflow-hidden sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label for="facility" class="block text-sm font-medium text-gray-700">Facility</label>
                            <select id="facility_type" v-model="facility_type"
                                class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Type</option>
                                <option value="District Hospital">District Hospital</option>
                                <option value="Regional Hospital">Regional Hospital</option>
                                <option value="Health Centre">Health Centre</option>
                                <option value="Primary Health Unit">Primary Health Unit</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="search" name="search" id="search" v-model="eligibleSearch"
                                class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Search facilities" />
                        </div>
                        <div class="space-y-2">
                            <label for="eligible_per_page" class="block text-sm font-medium text-gray-700">Items per
                                page</label>
                            <select id="eligible_per_page" v-model="eligible_per_page"
                                class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button @click="openEligibleCreateModal()"
                            class="inline-flex items-center justify-end px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition-colors duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Add Eligible Item
                        </button>
                    </div>
                </div>
                <div class="mt-6">
                    <table class="min-w-full border border-black">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-sm font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border border-black">
                                    Name
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-sm font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border border-black">
                                    Pack Size
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-sm font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border border-black">
                                    Facility
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-sm font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border border-black">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white border border-black">
                            <tr v-for="eligibleItem in props.eligibleItems.data" :key="eligibleItem.id">
                                <td class="px-6 py-4 border border-black">
                                    {{ eligibleItem.product?.name }}
                                </td>
                                <td class="px-6 py-4 border border-black">
                                    {{ eligibleItem.product?.pack_size }}
                                </td>
                                <td class="px-6 py-4 border border-black">
                                    {{ eligibleItem.facility_type }}
                                </td>
                                <td class="px-6 py-4 border border-black">
                                    <button @click="editEligibleItem(eligibleItem)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 transition-colors duration-150 rounded-full bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1-8l2-2 2.727 2.727L9 12l2.727 2.727 2-2M2 16.5a2.5 2.5 0 005 0 2.5 2.5 0 00-5 0z" />
                                            </svg>
                                            <span class="sr-only">Edit</span>
                                        </button>
                                    <button @click="deleteEligibleItem(eligibleItem.id)"
                                        class="inline-flex items-center justify-center w-8 h-8 text-red-600 transition-colors duration-150 rounded-full bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mb-6 p-6">
                    <div class="flex justify-between items-center">
                        <!-- add show -->
                        <div class="text-xs text-muted">
                            Showing {{ props.eligibleItems.meta.from }} to {{ props.eligibleItems.meta.to }} of {{
                                props.eligibleItems.meta.total }}
                        </div>
                        <Pagination :links="props.eligibleItems.meta.links" type="eligible" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="7x1">
            <div class="p-6">
                <div class="mb-4">
                    <div class="text-red-500" v-if="error">
                        {{ error }}
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ form.id ? "Edit Product" : "Create New Product" }}
                    </h2>
                    <button @click="closeModal"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none focus:underline"
                        :disabled="isSubmitted || processing">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Basic Information Section -->
                        <div class="p-4 mb-4 rounded-md bg-gray-50">
                            <h3 class="mb-3 font-medium text-gray-700 text-md">
                                Basic Information
                            </h3>

                            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                                <!-- Name -->
                                <div>
                                    <label for="name">Product Name</label>
                                    <input id="name" type="text" class="block w-full mt-1" v-model="form.name" required
                                        autocomplete="name" placeholder="Enter product name" />
                                </div>
                                <div>
                                    <label for="barcode">Barcode</label>
                                    <input id="barcode" type="text" class="block w-full mt-1" v-model="form.barcode"
                                        placeholder="Enter barcode (optional)" />
                                </div>

                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <!-- Dose -->

                                <div>
                                    <label for="dose">Dose</label>
                                    <input id="dose" type="text" class="block w-full mt-1"
                                        v-model="form.dose" placeholder="Enter dose" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <label for="category_id">Category</label>
                                    <select v-model="form.category_id" id="category_id"
                                        class="block w-full mt-1 border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
                                        @change="onCategoryChange">
                                        <option value="">
                                            Select a category
                                        </option>
                                        <option v-for="category in props.categories
                                            .data" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Dosage -->
                                <div>
                                    <label for="dosage_id">Dosage</label>
                                    <select v-model="form.dosage_id" id="dosage_id"
                                        class="block w-full mt-1 border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">
                                            Select a dosage
                                        </option>
                                        <option v-for="dosage in props.dosages.data" :key="dosage.id" :value="dosage.id">
                                            {{ dosage.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Reorder Level -->
                                <div>
                                    <label for="reorder_level">Reorder Level</label>
                                    <input id="reorder_level" type="number" class="block w-full mt-1"
                                        v-model="form.reorder_level" placeholder="Enter reorder level" />
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-3">
                                    <label for="description">Description</label>
                                    <textarea id="description"
                                        class="block w-full mt-1 transition border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        v-model="form.description" rows="3"
                                        placeholder="Enter product description"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="button"
                                class="px-4 py-2 mr-2 text-gray-800 transition bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 disabled:opacity-50"
                                @click="closeModal" :disabled="isSubmitted || processing">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50"
                                :disabled="isSubmitted || processing">
                                <span v-if="processing || isSubmitted">
                                    <svg class="inline-block w-4 h-4 mr-2 -ml-1 text-white animate-spin"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Processing...
                                </span>
                                <span v-else>{{
                                    form.id
                                        ? "Update Product"
                                        : "Create Product"
                                }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Dosage Modal -->
        <Modal :show="showDosageModal" @close="closeDosageModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ dosageForm.id ? "Edit Dosage Form" : "Add New Dosage Form" }}
                    </h2>
                    <button @click="closeDosageModal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Error Display -->
                <div v-if="dosageErrors" class="p-4 mb-4 border-l-4 border-red-400 bg-red-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Please correct the following errors:
                            </h3>
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
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': dosageErrors?.name }" placeholder="Enter dosage name" />
                    </div>
                    <!-- Description -->
                    <div>
                        <label for="dosage-description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea id="dosage-description" v-model="dosageForm.description" rows="3"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{
                                'border-red-300': dosageErrors?.description,
                            }" placeholder="Enter description"></textarea>
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" id="dosage-status" v-model="dosageForm.is_active"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                            <label for="dosage-status" class="block ml-2 text-sm text-gray-700">Active</label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end pt-5 space-x-3">
                        <button type="button"
                            class="px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            @click="closeDosageModal" :disabled="dosageProcessing">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            :disabled="dosageProcessing">
                            <svg v-if="dosageProcessing" class="w-4 h-4 mr-2 -ml-1 text-white animate-spin"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{
                                dosageForm.id
                                    ? "Update Dosage"
                                    : "Create Dosage"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Category Modal -->
        <Modal :show="showCategoryModal" @close="closeCategoryModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{
                            categoryForm.id
                                ? "Edit Category"
                                : "Add New Category"
                        }}
                    </h2>
                    <button @click="closeCategoryModal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>

                <div v-if="categoryErrors" class="mt-3">
                    <div v-for="(messages, field) in categoryErrors" :key="field" class="text-sm text-red-600">
                        <div v-for="(message, i) in messages" :key="i">
                            {{ message }}
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitCategoryForm" class="mt-4 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="name" type="text" class="block w-full mt-1" v-model="categoryForm.name" required
                            autocomplete="name" placeholder="Enter category name" />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description"
                            class="block w-full mt-1 transition border-gray-300 rounded-md focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            v-model="categoryForm.description" rows="3" placeholder="Enter category description"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input id="is_active" type="checkbox"
                            class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            v-model="categoryForm.is_active" />
                        <label for="is_active" class="ml-2">Active</label>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                        <button type="button"
                            class="px-4 py-2 mr-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            @click="closeCategoryModal" :disabled="categoryIsSubmitted || categoryProcessing
                                ">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            :disabled="categoryIsSubmitted || categoryProcessing
                                ">
                            <svg v-if="categoryProcessing" class="w-4 h-4 mr-2 -ml-1 text-white animate-spin"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            {{
                                categoryForm.id
                                    ? "Update Category"
                                    : "Create Category"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Eligible Item Modal -->
        <Modal :show="eligibleShow" class="max-w-6xl mx-auto">
            <div class="px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Add Eligible Item
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    Please select the product and facility below.
                </p>

                <form class="mt-6 space-y-4" @submit.prevent="addEligibleItem">
                    <div class="space-y-2">
                        <label for="product" class="block text-sm font-medium text-gray-700">Product</label>
                        <select id="product" required v-model="eligibleItemForm.product_id"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option v-for="product in props.eligibleProducts" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="facility" class="block text-sm font-medium text-gray-700">Facility</label>
                        <select id="facility_type" required v-model="eligibleItemForm.facility_type"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Type</option>
                            <option value="District Hospital">District Hospital</option>
                            <option value="Regional Hospital">Regional Hospital</option>
                            <option value="Health Centre">Health Centre</option>
                            <option value="Primary Health Unit">Primary Health Unit</option>
                        </select>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="button"
                            class="px-4 py-2 mr-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            @click="eligibleShow = false" :disabled="eligibleSubmit">
                            Cancel
                        </button>
                        <button
                            class="flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            :disabled="eligibleSubmit">
                            {{ eligibleSubmit ? "Adding..." : "Add" }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    eligibleProducts: Array,
    products: Object,
    filters: Object,
    warehouses: Array,
    categories: Object,
    categoryFilters: Object,
    dosages: Object,
    dosageFilters: Object,
    eligibleItems: {
        type: Object,
        required: true,
    },
    facilities: Array,
});

// Reactive state
const search = ref(props.filters?.search || "");
const eligibleSearch = ref(props.filters?.eligibleSearch || "");
const eligible_per_page = ref(props.filters?.eligible_per_page || 10);
const eligible_page = ref(props.filters?.eligible_page || 1);
const facility_type = ref(props.filters?.facility_type || "");
const category_id = ref(props.filters?.category_id || "");
const dosage_id = ref(props.filters?.dosage_id || "");
const is_active = ref(props.filters?.is_active || "");
const per_page = ref(props.filters?.per_page || 10);
const products_per_page = ref(props.filters?.products_per_page || 10);
const categories_per_page = ref(props.filters?.categories_per_page || 10);
const dosages_per_page = ref(props.filters?.dosages_per_page || 10);
const processing = ref(false);
const activeTab = ref("products");

// Product form state
const showModal = ref(false);
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const isSubmitted = ref(false);
const errors = ref(null);

// Category state
const categorySearch = ref(props.categoryFilters?.search || "");
const categoryProcessing = ref(false);
const showCategoryModal = ref(false);
const categoryIsSubmitted = ref(false);
const categoryErrors = ref(null);

// Dosage state
const dosageSearch = ref(props.dosageFilters?.search || "");
const dosageProcessing = ref(false);
const showDosageModal = ref(false);
const dosageIsSubmitted = ref(false);
const dosageErrors = ref(null);

// Category form
const categoryForm = ref({
    id: null,
    name: "",
    description: "",
    is_active: true,
});

// Dosage form
const dosageForm = ref({
    id: null,
    name: "",
    description: "",
    is_active: true,
});

// Form for creating/editing products
const form = ref({
    id: null,
    name: "",
    dose: "",
    barcode: "",
    description: "",
    category_id: "",
    dosage_id: "",
    reorder_level: 0,
    is_active: true,
});

// Bulk delete functionality
const selectedItems = ref([]);
const isBulkDeleting = ref(false);
const showBulkDeleteModal = ref(false);

const toggleSelectAll = () => {
    if (selectedItems.value.length === props.products.data.length) {
        selectedItems.value = [];
    } else {
        selectedItems.value = props.products.data.map((product) => product.id);
    }
};

// Eligible Item state
function editEligibleItem(eligibleItem) {
    eligibleItemForm.value.id = eligibleItem.id;
    eligibleItemForm.value.product_id = eligibleItem.product_id;
    eligibleItemForm.value.facility_type = eligibleItem.facility_type;
    eligibleItemForm.value.pack_size = eligibleItem.pack_size;

    eligibleShow.value = true;
}

const eligibleSubmit = ref(false);

async function addEligibleItem() {
    eligibleSubmit.value = true;
    await axios
        .post(route("eligible-items.store"), eligibleItemForm.value)
        .then((response) => {
            eligibleSubmit.value = false;
            toast.success(response.data);
            eligibleShow.value = false;
            reloadProducts();
            eligibleItemForm.value = {
                id: null,
                product_id: "",
                facility_type: "",
                pack_size: "",
            };
        })
        .catch((error) => {
            eligibleSubmit.value = false;
            toast.error(error.response.data);
        });
}

function deleteEligibleItem(id) {
    Swal.fire({
        title: "Delete Eligible Item",
        text: "Are you sure you want to delete this eligible item?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Delete",
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios
                .get(route("eligible-items.destroy", id))
                .then((response) => {
                    toast.success(response.data);
                    reloadProducts();
                })
                .catch((error) => {
                    console.log(error);
                    toast.error(error.response.data);
                });
        }
    });
}

const confirmBulkDelete = () => {
    if (selectedItems.value.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Products Selected",
            text: "Please select at least one product to delete.",
            confirmButtonText: "OK",
        });
        return;
    }
    Swal.fire({
        title: "Delete Products",
        text: `Are you sure you want to delete ${selectedItems.value.length} products?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Delete",
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
        const response = await axios.post(route("products.bulk"), {
            ids: selectedItems.value,
            action: "delete",
        });

        // Show success message
        Swal.fire({
            icon: "success",
            title: "Success!",
            text:
                response.data.message ||
                "Selected products have been deleted successfully",
            timer: 1500,
            showConfirmButton: false,
        });

        selectedItems.value = [];
        router.reload();
    } catch (error) {
        console.error("Delete error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            html:
                error.response?.data?.message ||
                "An error occurred while deleting products",
            confirmButtonText: "OK",
        });
    } finally {
        isBulkDeleting.value = false;
    }
};

// Reload products function
const reloadProducts = () => {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Update URL with current state while preserving existing parameters
    if (search.value) currentParams.set('search', search.value);
    if (category_id.value) currentParams.set('category_id', category_id.value);
    if (dosage_id.value) currentParams.set('dosage_id', dosage_id.value);
    if (is_active.value !== '') currentParams.set('is_active', is_active.value);
    if (per_page.value) currentParams.set('per_page', per_page.value.toString());
    
    // Categories params
    if (categorySearch.value) currentParams.set('category_search', categorySearch.value);
    if (categories_per_page.value) currentParams.set('categories_per_page', categories_per_page.value.toString());
    
    // Dosages params
    if (dosageSearch.value) currentParams.set('dosage_search', dosageSearch.value);
    if (dosages_per_page.value) currentParams.set('dosages_per_page', dosages_per_page.value.toString());
    
    // Eligible params
    if (eligibleSearch.value) currentParams.set('eligible_search', eligibleSearch.value);
    if (eligible_per_page.value) currentParams.set('eligible_per_page', eligible_per_page.value.toString());
    if (facility_type.value) currentParams.set('facility_type', facility_type.value);
    
    const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
    router.get(newUrl, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products', 'categories', 'dosages', 'eligible_items', 'filters']
    });
};

// Initialize on mount
onMounted(() => {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Set initial values from URL parameters
    search.value = currentParams.get('search') || '';
    category_id.value = currentParams.get('category_id') || '';
    dosage_id.value = currentParams.get('dosage_id') || '';
    is_active.value = currentParams.get('is_active') || '';
    per_page.value = parseInt(currentParams.get('per_page')) || 10;
    products_per_page.value = parseInt(currentParams.get('products_per_page')) || 10;
    categories_per_page.value = parseInt(currentParams.get('categories_per_page')) || 10;
    dosages_per_page.value = parseInt(currentParams.get('dosages_per_page')) || 10;
    
    categorySearch.value = currentParams.get('category_search') || '';
    dosageSearch.value = currentParams.get('dosage_search') || '';
    eligibleSearch.value = currentParams.get('eligible_search') || '';
    eligible_per_page.value = parseInt(currentParams.get('eligible_per_page')) || 10;
    facility_type.value = currentParams.get('facility_type') || '';
    
    // Initial load if needed
    if (!props.products) {
        reloadProducts();
    }
});

// Sort categories
const sortCategory = (field) => {
    if (props.categoryFilters.sort_field === field) {
        params.value.categoryDirection =
            props.categoryFilters.sort_direction === "asc" ? "desc" : "asc";
    } else {
        params.value.categoryField = field;
        params.value.categoryDirection = "asc";
    }

    router.get(route("products.index"), params.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["categories"],
    });
};

// Sort dosages
const sortDosage = (field) => {
    if (props.dosageFilters.sort_field === field) {
        params.value.dosageDirection =
            props.dosageFilters.sort_direction === "asc" ? "desc" : "asc";
    } else {
        params.value.dosageField = field;
        params.value.dosageDirection = "asc";
    }

    router.get(route("products.index"), params.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["dosages"],
    });
};

// Filter dosages based on selected category
const onCategoryChange = () => {
    form.value.dosage_id = ""; // Reset dosage selection when category changes
};

// Handle category filter change in product listing
const onCategoryFilterChange = () => {
    dosage_id.value = ""; // Reset dosage selection when category filter changes
    reloadProducts();
};


// Watch for changes in search input and filters
watch(
    [
        () => search.value,
        () => category_id.value,
        () => dosage_id.value,
        () => is_active.value,
    ],
    ([newSearch, newCategoryId, newDosageId, newIsActive]) => {
        const currentParams = new URLSearchParams(window.location.search);
        
        // Update URL with current state while preserving other parameters
        if (newSearch) currentParams.set('search', newSearch);
        else currentParams.delete('search');
        
        if (newCategoryId) currentParams.set('category_id', newCategoryId);
        else currentParams.delete('category_id');
        
        if (newDosageId) currentParams.set('dosage_id', newDosageId);
        else currentParams.delete('dosage_id');
        
        if (newIsActive !== '') currentParams.set('is_active', newIsActive);
        else currentParams.delete('is_active');
        
        // Reset to first page when filters change
        currentParams.delete('page');
        
        const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
        router.get(newUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['products', 'filters']
        });
    },
    { deep: true }
);

// Watch for products per_page changes
watch(
    () => per_page.value,
    (newPerPage) => {
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.set('per_page', newPerPage.toString());
        currentParams.delete('page');
        
        const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
        router.get(newUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['products', 'filters']
        });
    }
);

// Watch for category filters
watch(
    [categorySearch, categories_per_page],
    ([newSearch, newPerPage]) => {
        const currentParams = new URLSearchParams(window.location.search);
        
        if (newSearch) currentParams.set('category_search', newSearch);
        else currentParams.delete('category_search');
        
        if (newPerPage) currentParams.set('categories_per_page', newPerPage.toString());
        else currentParams.delete('categories_per_page');
        
        // Reset to first page when filters change
        currentParams.delete('categories_page');
        
        const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
        router.get(newUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['categories', 'filters']
        });
    },
    { deep: true }
);

// Watch for dosage filters
watch(
    [dosageSearch, dosages_per_page],
    ([newSearch, newPerPage]) => {
        const currentParams = new URLSearchParams(window.location.search);
        
        if (newSearch) currentParams.set('dosage_search', newSearch);
        else currentParams.delete('dosage_search');
        
        if (newPerPage) currentParams.set('dosages_per_page', newPerPage.toString());
        else currentParams.delete('dosages_per_page');
        
        // Reset to first page when filters change
        currentParams.delete('dosages_page');
        
        const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
        router.get(newUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['dosages', 'filters']
        });
    },
    { deep: true }
);

// Watch for eligible filters
watch(
    [eligibleSearch, eligible_per_page, facility_type],
    ([newSearch, newPerPage, newFacilityType]) => {
        const currentParams = new URLSearchParams(window.location.search);
        
        if (newSearch) currentParams.set('eligible_search', newSearch);
        else currentParams.delete('eligible_search');
        
        if (newPerPage) currentParams.set('eligible_per_page', newPerPage.toString());
        else currentParams.delete('eligible_per_page');
        
        if (newFacilityType) currentParams.set('facility_type', newFacilityType);
        else currentParams.delete('facility_type');
        
        // Reset to first page when filters change
        currentParams.delete('eligible_page');
        
        const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
        router.get(newUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['eligible_items', 'filters']
        });
    },
    { deep: true }
);

// Reset filters functions
const resetProductFilters = () => {
    search.value = '';
    category_id.value = '';
    dosage_id.value = '';
    is_active.value = '';
    per_page.value = 10;
    
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.delete('search');
    currentParams.delete('category_id');
    currentParams.delete('dosage_id');
    currentParams.delete('is_active');
    currentParams.delete('per_page');
    currentParams.delete('page');
    
    const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
    router.get(newUrl, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products', 'filters']
    });
};

const resetCategoryFilters = () => {
    categorySearch.value = '';
    categories_per_page.value = 10;
    
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.delete('category_search');
    currentParams.delete('categories_per_page');
    currentParams.delete('categories_page');
    
    const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
    router.get(newUrl, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['categories', 'filters']
    });
};

const resetDosageFilters = () => {
    dosageSearch.value = '';
    dosages_per_page.value = 10;
    
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.delete('dosage_search');
    currentParams.delete('dosages_per_page');
    currentParams.delete('dosages_page');
    
    const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
    router.get(newUrl, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['dosages', 'filters']
    });
};

const resetEligibleFilters = () => {
    eligibleSearch.value = '';
    eligible_per_page.value = 10;
    facility_type.value = '';
    
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.delete('eligible_search');
    currentParams.delete('eligible_per_page');
    currentParams.delete('facility_type');
    currentParams.delete('eligible_page');
    
    const newUrl = `${window.location.pathname}?${currentParams.toString()}`;
    router.get(newUrl, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['eligible_items', 'filters']
    });
};

// Open modal for create/edit
const openModal = (product = null) => {
    if (product) {
        // Edit mode
        form.value.id = product.id;
        form.value.name = product.name;
        form.value.dose = product.dose;
        form.value.barcode = product.barcode || "";
        form.value.description = product.description || "";
        form.value.category_id = product.category_id || "";
        onCategoryChange();
        form.value.dosage_id = product.dosage_id || "";
        form.value.is_active =
            product.is_active !== undefined ? product.is_active : true;
    } else {
        // Create mode
        resetForm();
        showModal.value = true;
    }
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
};

function resetForm() {
    form.value = {
        id: null,
        name: "",
        dose: "",
        barcode: "",
        description: "",
        category_id: "",
        dosage_id: "",
        reorder_level: 0,
        is_active: true,
    };
}

const error = ref("");
// Submit form
const submitForm = async () => {
    error.value = "";
    if (isSubmitted.value || processing.value) return;

    // Client-side validation for dates
    if (
        form.manufacturing_date &&
        form.expiry_date &&
        form.manufacturing_date > form.expiry_date
    ) {
        toast.error("Expiry date must be after manufacturing date");
        return;
    }

    isSubmitted.value = true;
    processing.value = true;

    await axios
        .post(route("products.store"), form.value)
        .then((response) => {
            toast.success(response.data);
            closeModal();
            reloadProducts();
            showModal.value = false;
        })
        .catch((err) => {
            toast.error(err.response.data);
            error.value = err.response.data;

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
        title: "Delete Product",
        text: `Are you sure you want to delete ${product.name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Delete",
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
                    },
                });

                await router.delete(route("products.destroy", product.id));

                Swal.fire({
                    icon: "success",
                    title: "Deleted!",
                    text: "Product has been deleted successfully",
                    timer: 1500,
                    showConfirmButton: false,
                });
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.response.data || "An error occurred",
                });
            }
        }
    });
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return "";

    const date = new Date(dateString);
    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
};

// Open category modal for create/edit
const openCategoryModal = (category = null) => {
    categoryErrors.value = null;
    categoryIsSubmitted.value = false;

    if (category) {
        categoryForm.value.id = category.id;
        categoryForm.value.name = category.name;
        categoryForm.value.description = category.description || "";
        categoryForm.value.is_active = category.is_active;
    } else {
        categoryFormReset();
    }

    showCategoryModal.value = true;
};

function categoryFormReset() {
    categoryForm.value.id = null;
    categoryForm.value.name = "";
    categoryForm.value.description = "";
    categoryForm.value.is_active = true;
}

// Close category modal
const closeCategoryModal = () => {
    showCategoryModal.value = false;
};

// Submit category form
const submitCategoryForm = async () => {
    categoryIsSubmitted.value = true;
    categoryProcessing.value = true;
    categoryErrors.value = null;

    await axios
        .post(route("categories.store"), categoryForm.value)
        .then((response) => {
            if (response.data.success) {
                toast.success(
                    response.data.message || "Category saved successfully"
                );
                closeCategoryModal();
                reloadProducts();
            } else {
                toast.error(response.data.message || "An error occurred");
            }
        })
        .catch((error) => {
            if (
                error.response &&
                error.response.data &&
                error.response.data.errors
            ) {
                categoryErrors.value = error.response.data.errors;
                toast.error("Please correct the errors in the form");
            } else {
                toast.error("An error occurred while saving the category");
            }
        })
        .finally(() => {
            categoryProcessing.value = false;
            categoryIsSubmitted.value = false;
        });
};

// Delete category
const deleteCategory = (category) => {
    Swal.fire({
        title: `Are you sure to delete ${category.name}?`,
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios
                .get(route("categories.destroy", category.id))
                .then((response) => {
                    categoryProcessing.value = false;
                    toast.success(response.data);
                    reloadProducts();
                })
                .catch((error) => {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: error.response.data || "An error occurred",
                    });
                    console.error(error);
                    categoryProcessing.value = false;
                });
        }
    });
};

// Open dosage modal for create/edit
const openDosageModal = (dosage = null) => {
    dosageErrors.value = null;
    dosageIsSubmitted.value = false;

    if (dosage) {
        dosageForm.value.id = dosage.id;
        dosageForm.value.name = dosage.name;
        dosageForm.value.description = dosage.description || "";
        dosageForm.value.is_active = dosage.is_active;
    } else {
        dosageFormReset();
    }

    showDosageModal.value = true;
};

function dosageFormReset() {
    dosageForm.value.id = null;
    dosageForm.value.name = "";
    dosageForm.value.description = "";
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

    await axios
        .post(route("dosages.store"), dosageForm.value)
        .then((response) => {
            if (response.data.success) {
                toast.success(
                    response.data.message || "Dosage saved successfully"
                );
                closeDosageModal();
                reloadProducts();
            } else {
                toast.error(response.data.message || "An error occurred");
            }
        })
        .catch((error) => {
            console.error(error);
            if (
                error.response &&
                error.response.data &&
                error.response.data.errors
            ) {
                dosageErrors.value = error.response.data.errors;
                toast.error("Please correct the errors in the form");
            } else {
                toast.error("An error occurred while saving the dosage");
            }
        })
        .finally(() => {
            dosageProcessing.value = false;
            dosageIsSubmitted.value = false;
        });
};

// Delete dosage
const deleteDosage = (dosage) => {

    Swal.fire({
        title: `Are you sure to delete ${dosage.name}?`,
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then(async (result) => {
        if (result.isConfirmed) {
            let timerInterval;
            try {
                // Show the countdown Swal
                await Swal.fire({
                    title: "Deleting Dosage",
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
                    },
                });

                await axios
                .get(route("dosages.destroy", dosage.id))
                .then((response) => {
                    Swal.fire("Deleted!", response.data, "success");
                    reloadProducts();              
                })
                .catch((error) => {
                    Swal.fire("Error!", error.response.data, "error");
                    console.error(error);
                });
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.response.data || "An error occurred",
                });
            }
        }
    });


};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
};

const eligibleShow = ref(false);

// Eligible Item form
const eligibleItemForm = ref({
    id: null,
    product_id: "",
    facility_type: "",
    pack_size: "",
});

function openEligibleCreateModal() {
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
