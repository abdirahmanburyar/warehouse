<template>
    <AuthenticatedLayout title="Products" description="products" img="/assets/images/products.png">
        <div class="flex justify-between gap-3 items-center">
            <h1>Products List</h1>
            <div class="flex gap-2">
                <button
                    @click="openUploadModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :disabled="isUploading"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-2"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span v-if="isUploading">
                        <svg
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Uploading...
                    </span>
                    <span v-else>Upload Excel</span>
                </button>

                <Link
                    :href="route('products.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-2"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    Create Product
                </Link>
            </div>
        </div>
        <div class="flex justify-end mt-4 items-center space-x-4">
            <Link
                :href="route('products.categories.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-2"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5z"
                    />
                    <path
                        d="M11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"
                    />
                </svg>
                Categories List
            </Link>
            <Link
                :href="route('products.dosages.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-2"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M17.725 4.275a1.75 1.75 0 00-2.475 0L11 8.525V5a1 1 0 00-2 0v7a1 1 0 001 1h7a1 1 0 000-2h-3.525l4.25-4.25a1.75 1.75 0 000-2.475z"
                        clip-rule="evenodd"
                    />
                    <path
                        d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 10-2 0v7H5V5h7a1 1 0 100-2H5z"
                    />
                </svg>
                Dosage Forms List
            </Link>
            <Link
                :href="route('products.eligible.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-2"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 2a1 1 0 00-1 1v1.323l-3.954 1.582A1 1 0 004 6.82v10.36a1 1 0 001.046.976l4-1.5a1 1 0 01.908 0l4 1.5A1 1 0 0015 17.18V6.82a1 1 0 00-1.046-.976L10 4.323V3a1 1 0 00-1-1zm0 2.618l4 1.6v9.464l-4-1.5V4.618zm-2 0L4 6.218v9.464l4-1.5V4.618z"
                        clip-rule="evenodd"
                    />
                </svg>
                Eligible List
            </Link>
        </div>
        <div class="flex flex-wrap gap-4 items-center mt-3">
            <div
                class="flex items-center bg-gray-50 rounded flex-grow md:flex-grow-0 min-w-[600px]"
            >
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or barcode..."
                    class="focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full"
                />
            </div>
            <div class="w-[300px]">
                <Multiselect
                    v-model="category"
                    :options="props.categories"
                    :searchable="true"
                    :close-on-select="true"
                    :show-labels="false"
                    :allow-empty="true"
                    placeholder="Select Category"
                />
            </div>

            <div class="w-[300px]">
                <Multiselect
                    v-model="dosage"
                    :options="props.dosages"
                    :searchable="true"
                    :close-on-select="true"
                    :show-labels="false"
                    :allow-empty="true"
                    placeholder="Select Dosage Form"
                />
            </div>
            <div class="w-[300px]">
                <Multiselect
                    v-model="eligible"
                    :options="eligibleItems"
                    :searchable="true"
                    :close-on-select="true"
                    :show-labels="false"
                    :allow-empty="true"
                    placeholder="Select Eligible"
                />
            </div>
            <div class="w-[300px]">
            <select
                v-model="status"
                class="w-[200px] rounded-3xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        </div>

        <div class="flex justify-end">
            <select
                v-model="perPage"
                class="w-[200px] rounded-3xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                @change="props.filters.page = 1"
            >
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>

        <div class="py-6 mb-5">
            <div class="overflow-x-auto">
                <!-- Empty State -->
                <div
                    v-if="!products.data.length"
                    class="flex flex-col items-center justify-center py-12 bg-white rounded-lg border border-gray-200"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-16 w-16 text-gray-400 mb-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                        />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        No Products Found
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        There are no products matching your search criteria.
                    </p>
                    <Link
                        :href="route('products.create')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-2"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Add New Product
                    </Link>
                </div>
                <table
                    v-else
                    class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-3xl overflow-hidden"
                >
                    <thead style="background-color: #eef1f8">
                        <tr>
                            <th
                                style="color: #495fa7"
                                class="p-5 text-left text-sm font-bold text-gray-700 uppercase tracking-wider"
                            >
                                Item Name
                            </th>
                            <th
                                style="color: #495fa7"
                                class="p-5 text-left text-sm font-bold text-gray-700 uppercase tracking-wider"
                            >
                                Category
                            </th>
                            <th
                                style="color: #495fa7"
                                class="p-5 text-left text-sm font-bold text-gray-700 uppercase tracking-wider"
                            >
                                Dosage Form
                            </th>
                            <th
                                style="color: #495fa7"
                                class="p-5 text-left text-sm font-bold text-gray-700 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                style="color: #495fa7"
                                class="p-5 text-left text-sm font-bold text-gray-700 uppercase tracking-wider"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ product.name }}
                                </div>
                            </td>
                            <td
                                class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ product.category?.name || "N/A" }}
                            </td>
                            <td
                                class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ product.dosage?.name || "N/A" }}
                            </td>
                            <!-- <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                {{ product.reorder_level || '0' }}
                            </td> -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800':
                                                product.is_active,
                                            'bg-red-100 text-red-800':
                                                !product.is_active,
                                        }"
                                    >
                                        {{
                                            product.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <div
                                    class="flex items-center justify-center space-x-3"
                                >
                                    <Link
                                        :href="
                                            route('products.edit', product.id)
                                        "
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="confirmToggleStatus(product)"
                                        :class="{
                                            'opacity-50 cursor-wait':
                                                loadingProducts.has(product.id),
                                            'bg-gray-200': !product.is_active,
                                            'bg-green-500': product.is_active,
                                        }"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        :disabled="
                                            loadingProducts.has(product.id)
                                        "
                                    >
                                        <span
                                            :class="{
                                                'translate-x-5':
                                                    product.is_active,
                                                'translate-x-0':
                                                    !product.is_active,
                                                'bg-gray-400 animate-pulse':
                                                    loadingProducts.has(
                                                        product.id
                                                    ),
                                            }"
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        ></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex justify-end items-center">
                <TailwindPagination
                    :data="props.products"
                    :limit="2"
                    @pagination-change-page="getResults"
                />
            </div>

            <!-- Excel Upload Modal -->
            <div
                v-if="showUploadModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">
                            Upload Products Excel File
                        </h3>
                        <button
                            @click="closeUploadModal"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">
                            Upload an Excel file (.xlsx, .xls) with the
                            following columns:
                        </p>
                        <ul
                            class="list-disc list-inside text-sm text-gray-600 ml-2 mb-4"
                        >
                            <li>item description (required)</li>
                            <li>category (optional)</li>
                            <li>dosage form (optional)</li>
                        </ul>

                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:bg-gray-50 transition-colors cursor-pointer"
                            @click="triggerFileInput"
                        >
                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                @change="handleFileUpload"
                                accept=".xlsx,.xls"
                            />
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-10 w-10 mx-auto text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">
                                Click to select or drag and drop file here
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                .xlsx and .csv files are supported
                            </p>
                        </div>

                        <div
                            v-if="selectedFile"
                            class="mt-3 flex items-center justify-between bg-blue-50 p-2 rounded"
                        >
                            <div class="flex items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-blue-500 mr-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                <span class="text-sm truncate max-w-[200px]">{{
                                    selectedFile.name
                                }}</span>
                            </div>
                            <button
                                @click.stop="removeSelectedFile"
                                class="text-red-500 hover:text-red-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeUploadModal"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="uploadFile"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            :disabled="!selectedFile || isUploading"
                        >
                            <span v-if="isUploading">
                                <svg
                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Uploading...
                            </span>
                            <span v-else>Upload</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { defineProps, ref, defineComponent, h, watch, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";
import axios from "axios";
import { TailwindPagination } from "laravel-vue-pagination";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";

const toast = useToast();

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    dosages: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const eligibleItems = [
    "All",
    "Health Centre",
    "Primary Health Unit",
    "District Hospital",
    "Regional Hospital",
];

const eligible = ref(props.filters.eligible || "");

const search = ref(props.filters.search || "");
const category = ref(props.filters.category || "");
const dosage = ref(props.filters.dosage || "");
const perPage = ref(props.filters.per_page || 25);
const status = ref(props.filters.status || 1);
const fileInput = ref(null);
const showUploadModal = ref(false);
const loadingProducts = ref(new Set());
const selectedFile = ref(null);
const isUploading = ref(false);

function updateRoute() {
    const query = {};
    if (search.value) query.search = search.value;
    if (category.value) query.category = category.value;
    if (dosage.value) query.dosage = dosage.value;
    if (perPage.value) query.per_page = perPage.value;
    if (status.value) query.status = status.value;
    if (eligible.value) query.eligible = eligible.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("products.index"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["products"],
    });
}

watch(
    [
        () => search.value,
        () => category.value,
        () => dosage.value,
        () => perPage.value,
        () => status.value,
        () => eligible.value,
        () => props.filters.page,
    ],
    () => {
        updateRoute();
    }
);

function getResults(page = 1) {
    props.filters.page = page;
}

const openUploadModal = () => {
    showUploadModal.value = true;
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Check file type - allow .xlsx and .csv
    const fileExtension = "." + file.name.split(".").pop().toLowerCase();
    const validExtensions = [".xlsx", ".csv"];

    if (!validExtensions.includes(fileExtension)) {
        toast.error(
            "Invalid file type. Please upload an Excel file (.xlsx) or CSV file (.csv)"
        );
        event.target.value = null; // Clear the file input
        selectedFile.value = null;
        return;
    }

    // Check file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        toast.error("File is too large. Maximum file size is 5MB.");
        event.target.value = null;
        selectedFile.value = null;
        return;
    }

    selectedFile.value = file;
};

const removeSelectedFile = () => {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const uploadFile = async () => {
    if (!selectedFile.value) {
        toast.error("Please select a file to upload");
        return;
    }

    // Show loading toast
    const loadingToast = toast.info("Preparing to upload file...", {
        timeout: false,
        closeOnClick: false,
        draggable: false,
    });

    isUploading.value = true;
    const formData = new FormData();
    formData.append("file", selectedFile.value);

    try {
        const response = await axios.post(
            route("products.import-excel"),
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            }
        );

        if (response.data.success) {
            toast.success(response.data.message);
            closeUploadModal();

            // Show detailed results if there are errors or skipped items
            if (
                (response.data.errors && response.data.errors.length > 0) ||
                response.data.skipped > 0
            ) {
                let errorList = response.data.errors
                    ? response.data.errors.join("\n")
                    : "";

                // Create a more detailed and user-friendly results dialog
                Swal.fire({
                    title: "Import Results",
                    html: `<div class="text-left">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p><strong>Imported:</strong> ${
                                response.data.imported
                            } products</p>
                        </div>
                        
                        ${
                            response.data.skipped > 0
                                ? `
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p><strong>Skipped:</strong> ${response.data.skipped} products (already exist)</p>
                        </div>`
                                : ""
                        }
                        
                        ${
                            errorList
                                ? `
                        <div class="flex items-start mb-2">
                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-2 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p><strong>Errors:</strong></p>
                                <pre class="text-xs mt-2 bg-gray-100 p-2 rounded overflow-auto max-h-40 border border-gray-200">${errorList}</pre>
                            </div>
                        </div>`
                                : ""
                        }
                        
                        <div class="mt-4 text-sm text-gray-600">
                            <p>If you have errors, please check that your Excel file has the correct format and column names.</p>
                        </div>
                    </div>`,
                    icon:
                        response.data.errors && response.data.errors.length > 0
                            ? "warning"
                            : "success",
                    confirmButtonText: "OK",
                    width: "600px",
                });
            }

            // Refresh the products list
            updateRoute();
        } else {
            toast.error(response.data.message || "Failed to import products");
        }
    } catch (error) {
        console.error("Upload error:", error);

        // Provide more detailed error messages
        if (error.response?.status === 422) {
            // Validation error
            const errors = error.response.data.errors;
            if (errors) {
                const errorMessages = Object.values(errors).flat();
                toast.error(errorMessages.join("\n"));
            } else {
                toast.error(
                    error.response.data.message ||
                        "Validation failed. Please check your file format."
                );
            }
        } else if (error.response?.status === 413) {
            toast.error("File is too large. Please upload a smaller file.");
        } else {
            toast.error(
                error.response?.data?.message ||
                    "Error uploading file. Please try again."
            );
        }
    } finally {
        // Dismiss the loading toast
        toast.dismiss(loadingToast);

        isUploading.value = false;
        selectedFile.value = null;
        if (fileInput.value) {
            fileInput.value.value = null;
        }
    }
};

const confirmToggleStatus = (product) => {
    const action = product.is_active ? "deactivate" : "activate";

    Swal.fire({
        title: "Are you sure?",
        html: `<p>Do you want to ${action} ${product.name}?</p>`,
        showConfirmButton: true,
        icon: undefined,
        showCancelButton: true,
        confirmButtonColor: product.is_active ? "#d33" : "#3085d6",
        cancelButtonColor: "#6b7280",
        confirmButtonText: product.is_active
            ? "Yes, deactivate!"
            : "Yes, activate!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            loadingProducts.value.add(product.id);
            try {
                await axios.get(route("products.toggle-status", product.id));
                updateRoute();
                Swal.fire(
                    action === "activate" ? "Activated!" : "Deactivated!",
                    `Product has been ${action}d.`,
                    "success"
                );
            } catch (error) {
                toast.error(error.response?.data || "An error occurred");
            } finally {
                loadingProducts.value.delete(product.id);
            }
        }
    });
};

const confirmDelete = (product) => {
    Swal.fire({
        title: "Are you sure?",
        text: `Do you want to delete ${product.name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("products.destroy", product.id), {
                onSuccess: () => {
                    Swal.fire(
                        "Deleted!",
                        "Product has been deleted.",
                        "success"
                    );
                    updateRoute();
                },
                onError: (error) => {
                    toast.error(error);
                },
            });
        }
    });
};
</script>
