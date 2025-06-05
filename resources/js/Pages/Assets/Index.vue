<template>
    <AuthenticatedLayout
        title="Assets"
        description="Manage your assets"
        img="/assets/images/asset-header.png"
    >
        <div class="mb-[80px]">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Assets</h1>
                <div class="flex gap-2 items-center">
                    <!-- Export Button -->
                    <button
                        v-if="$page.props.auth.can.asset_export"
                        @click="exportToExcel"
                        class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700"
                    >
                        Export to Excel
                    </button>

                    <!-- Import Form -->
                    <button
                        v-if="$page.props.auth.can.asset_import"
                        @click="showImportModal = true"
                        class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700"
                    >
                        Import Excel
                    </button>

                    <Link
                        v-if="$page.props.auth.can.asset_import"
                        :href="route('assets.create')"
                        class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700"
                    >
                        Add Asset
                    </Link>
                </div>
            </div>
            <!-- Filters Row -->
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-1">
                <!-- Search Filter -->
                <input
                    type="text"
                    v-model="search"
                    placeholder="Search assets by tag, serial number, or description"
                    class="w-full"
                />
                <!-- Region Filter -->
                <div class="">
                    <Multiselect
                        v-model="regionFilter"
                        :options="regionOptions"
                        label="name"
                        track-by="id"
                        placeholder="Region"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :allow-empty="true"
                        class=""
                        @input="onRegionChange"
                    />
                </div>
                <!-- Fund Source Filter -->
                <div>
                    <Multiselect
                        v-model="fundSourceFilter"
                        :options="props.fundSources"
                        label="name"
                        track-by="id"
                        placeholder="Fund Source"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :allow-empty="true"
                        class=""
                    />
                </div>
                <!-- Location Filter -->
                <div class="">
                    <Multiselect
                        v-model="locationFilter"
                        :options="locationOptions"
                        label="name"
                        track-by="id"
                        placeholder="Location"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :allow-empty="true"
                        class=""
                        @input="onLocationChange"
                    />
                </div>
                <!-- SubLocation Filter -->
                <div class="">
                    <Multiselect
                        v-model="selectedSubLocations"
                        :options="filteredSubLocations"
                        label="name"
                        track-by="id"
                        placeholder="Sub Location(s)"
                        :multiple="true"
                        :show-labels="false"
                        :allow-empty="true"
                        class="text-xs"
                    />
                </div>
            </div>
            <!-- Create Asset Button -->
            <!-- Per Page Row -->
            <div class="flex justify-end mt-2">
                <div class="flex items-center gap-2">
                    <select
                        v-model="per_page"
                        @change="props.filters.page = 1"
                        class="border border-gray-300 rounded-lg py-2 pl-3 pr-8 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="1">1 per page</option>
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
            <!-- Main content area -->
            <div class="flex-grow p-4">
                <!-- Asset cards will go here -->
                <!-- Dashboard Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div
                        class="bg-white rounded-xl shadow p-4 flex flex-col items-center"
                    >
                        <span class="text-3xl font-bold text-indigo-600">{{
                            props.assets.data.length
                        }}</span>
                        <span class="text-xs text-gray-500 mt-1"
                            >Total Assets</span
                        >
                    </div>
                    <div
                        class="bg-white rounded-xl shadow p-4 flex flex-col items-center"
                    >
                        <span class="text-3xl font-bold text-green-600">{{
                            props.assets.data.filter(
                                (a) => a.status === "in_use"
                            ).length
                        }}</span>
                        <span class="text-xs text-gray-500 mt-1">In Use</span>
                    </div>
                    <div
                        class="bg-white rounded-xl shadow p-4 flex flex-col items-center"
                    >
                        <span class="text-3xl font-bold text-orange-500">{{
                            props.assets.data.filter(
                                (a) => a.status === "maintenance"
                            ).length
                        }}</span>
                        <span class="text-xs text-gray-500 mt-1"
                            >Maintenance</span
                        >
                    </div>
                </div>
                <!-- Asset Table -->
                <div class="overflow-auto rounded-xl">
                    <table class="min-w-full text-left">
                        <thead
                            class="p-6 text-black"
                            style="background-color: rgb(167, 204, 240)"
                        >
                            <tr>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Asset Tag
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Category
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Serial Number
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Description
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Assigned To
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Location
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Sub-Location
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Acquisition Date
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Original Value
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Funded Source
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    Attachments
                                </th>
                                <th
                                    class="px-4 py-2 text-xs font-bold text-gray-600"
                                >
                                    History
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="asset in props.assets.data"
                                :key="asset.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-4 py-2 whitespace-nowrap font-semibold"
                                >
                                    <Link :href="route('assets.show', asset.id)">{{ asset.asset_tag }}</Link>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ asset.category?.name }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ asset.serial_number }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap max-w-xs truncate"
                                    :title="asset.item_description"
                                >
                                    {{ asset.item_description }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap flex items-center gap-2"
                                >
                                    <span>{{ asset.person_assigned }}</span>
                                    <button
                                        @click="openTransferModal(asset)"
                                        class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200"
                                    >
                                        Transfer
                                    </button>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ asset.location?.name }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ asset.sub_location?.name }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-700':
                                                asset.status === 'in_use',
                                            'bg-orange-100 text-orange-700':
                                                asset.status === 'maintenance',
                                            'bg-gray-100 text-gray-600':
                                                asset.status !== 'in_use' &&
                                                asset.status !== 'maintenance',
                                        }"
                                        class="px-2 py-1 rounded-full text-xs font-bold"
                                    >
                                        {{
                                            asset.status
                                                .replace("_", " ")
                                                .toUpperCase()
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-xs">
                                    {{ formatDate(asset.acquisition_date) }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-right font-semibold text-indigo-600"
                                >
                                    ${{
                                        parseFloat(
                                            asset.original_value
                                        ).toLocaleString()
                                    }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-right font-semibold text-indigo-600"
                                >
                                    {{ asset.fund_source?.name || "N/A" }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-center"
                                >
                                    <button
                                        v-if="
                                            asset.attachments &&
                                            asset.attachments.length
                                        "
                                        @click="
                                            openAttachmentsModal(
                                                asset.attachments
                                            )
                                        "
                                        class="text-green-600 hover:underline text-xs"
                                    >
                                        {{ asset.attachments.length }} file(s)
                                    </button>
                                    <span v-else class="text-gray-400 text-xs"
                                        >None</span
                                    >
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-center"
                                >
                                    <button
                                        v-if="
                                            asset.history &&
                                            asset.history.length
                                        "
                                        @click="openHistoryModal(asset)"
                                        class="text-indigo-600 hover:underline text-xs"
                                    >
                                        {{ asset.history.length }} entr{{
                                            asset.history.length === 1
                                                ? "y"
                                                : "ies"
                                        }}
                                    </button>
                                    <span v-else class="text-gray-400 text-xs"
                                        >None</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination footer -->
            <div class="flex justify-end mb-5 mt-3">
                <TailwindPagination
                    :data="props.assets"
                    @pagination-change-page="getResults"
                    :limit="2"
                />
            </div>
        </div>
        <!-- Attachments Modal -->
        <TransitionRoot as="template" :show="isAttachmentsModalOpen">
            <Dialog
                as="div"
                class="fixed z-[99] inset-0 overflow-y-auto"
                @close="closeAttachmentsModal"
            >
                <div
                    class="flex items-center justify-center min-h-screen p-4 text-center"
                >
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <div
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                            aria-hidden="true"
                        />
                    </TransitionChild>
                    <!-- Modal panel -->
                    <span
                        class="hidden sm:inline-block sm:align-middle sm:h-screen"
                        aria-hidden="true"
                        >&#8203;</span
                    >
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel
                            class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                        >
                            <div class="flex justify-between items-center mb-4">
                                <DialogTitle
                                    as="h3"
                                    class="text-lg leading-6 font-medium text-gray-900"
                                    >Asset Attachments</DialogTitle
                                >
                                <button
                                    @click="closeAttachmentsModal"
                                    class="text-gray-400 hover:text-gray-600 focus:outline-none"
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
                            <div v-if="selectedAttachments.length">
                                <ul class="divide-y divide-gray-200">
                                    <li
                                        v-for="file in selectedAttachments"
                                        :key="file.id"
                                        class="py-2 flex items-center justify-between"
                                    >
                                        <span
                                            class="truncate max-w-xs"
                                            :title="file.file"
                                            >{{
                                                file.type || "Attachment"
                                            }}</span
                                        >
                                        <a
                                            :href="'/' + file.file"
                                            target="_blank"
                                            class="ml-4 px-3 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold"
                                            >View</a
                                        >
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="text-gray-400 text-sm">
                                No attachments found.
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>
        <!-- History Modal -->
        <TransitionRoot as="template" :show="isHistoryModalOpen">
            <Dialog
                as="div"
                class="fixed z-50 inset-0 overflow-y-auto"
                @close="closeHistoryModal"
            >
                <div class="flex items-center justify-center min-h-screen px-0">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0"
                        enter-to="opacity-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100"
                        leave-to="opacity-0"
                    >
                        <DialogOverlay
                            class="fixed inset-0 bg-black bg-opacity-40 transition-opacity"
                        />
                    </TransitionChild>

                    <span
                        class="inline-block align-middle h-screen"
                        aria-hidden="true"
                        >&#8203;</span
                    >
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <div
                            class="fixed inset-0 w-screen h-screen p-0 m-0 bg-white shadow-xl z-50 flex flex-col rounded-none"
                            style="max-width: 100vw; max-height: 100vh"
                        >
                            <div class="flex justify-between items-center mb-4">
                                <DialogTitle
                                    as="h3"
                                    class="text-lg leading-6 font-medium text-gray-900"
                                    >Asset History</DialogTitle
                                >
                                <button
                                    @click="closeHistoryModal"
                                    class="text-gray-400 hover:text-gray-600 focus:outline-none"
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
                            <div v-if="selectedAssetHistory.length">
                                <div class="overflow-x-auto">
                                    <table
                                        class="min-w-full text-left text-xs border border-black"
                                    >
                                        <thead>
                                            <tr>
                                                <th
                                                    class="px-2 py-1 border border-black font-bold text-left"
                                                >
                                                    Date
                                                </th>
                                                <th
                                                    class="px-2 py-1 border border-black font-bold text-left"
                                                >
                                                    Custodian
                                                </th>
                                                <th
                                                    class="px-2 py-1 border border-black font-bold text-left"
                                                >
                                                    Status
                                                </th>
                                                <th
                                                    class="px-2 py-1 border border-black font-bold text-left"
                                                >
                                                    Assigned At
                                                </th>
                                                <th
                                                    class="px-2 py-1 border border-black font-bold text-left"
                                                >
                                                    Returned At
                                                </th>
                                                <th
                                                    class="px-2 py-1 border border-black font-bold text-left"
                                                >
                                                    Status Notes
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="entry in selectedAssetHistory"
                                                :key="entry.id"
                                            >
                                                <td
                                                    class="px-2 py-1 border border-black text-left"
                                                >
                                                    {{
                                                        formatDate(
                                                            entry.created_at
                                                        )
                                                    }}
                                                </td>
                                                <td
                                                    class="px-2 py-1 border border-black text-left"
                                                >
                                                    {{ entry.custodian }}
                                                </td>
                                                <td
                                                    class="px-2 py-1 border border-black text-left"
                                                >
                                                    <span
                                                        :class="{
                                                            'bg-green-100 text-green-700':
                                                                entry.status ===
                                                                'in_use',
                                                            'bg-orange-100 text-orange-700':
                                                                entry.status ===
                                                                'maintenance',
                                                            'bg-gray-100 text-gray-600':
                                                                entry.status !==
                                                                    'in_use' &&
                                                                entry.status !==
                                                                    'maintenance',
                                                        }"
                                                        class="px-2 py-1 rounded-full text-xs font-bold"
                                                    >
                                                        {{
                                                            entry.status
                                                                .replace(
                                                                    "_",
                                                                    " "
                                                                )
                                                                .toUpperCase()
                                                        }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-2 py-1 border border-black text-left"
                                                >
                                                    {{
                                                        entry.assigned_at
                                                            ? moment(
                                                                  entry.assigned_at
                                                              ).format(
                                                                  "DD/MM/YYYY HH:mm"
                                                              )
                                                            : "-"
                                                    }}
                                                </td>
                                                <td
                                                    class="px-2 py-1 border border-black text-left"
                                                >
                                                    {{
                                                        entry.returned_at || "-"
                                                    }}
                                                </td>
                                                <td
                                                    class="px-2 py-1 border border-black text-left"
                                                >
                                                    {{ entry.status_notes }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-gray-400 text-sm">
                                No history found.
                            </div>
                        </div>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>
    </AuthenticatedLayout>
    <TransitionRoot as="template" :show="showImportModal">
        <Dialog
            as="div"
            class="fixed z-50 inset-0 overflow-y-auto"
            @close="showImportModal = false"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <DialogOverlay class="fixed inset-0 bg-black opacity-30" />
                <div
                    class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto z-50 p-6 relative"
                >
                    <DialogTitle class="text-lg font-bold mb-2"
                        >Import Assets from Excel</DialogTitle
                    >
                    <form @submit.prevent="uploadExcel">
                        <input
                            type="file"
                            ref="importFile"
                            accept=".xlsx,.xls,.csv"
                            required
                            class="block w-full mb-4"
                        />
                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                @click="showImportModal = false"
                                class="px-3 py-1 rounded bg-gray-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="isUploading"
                                class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
                            >
                                {{ isUploading ? "Uploading..." : "Import" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
    <!-- Transfer Modal -->
    <TransitionRoot as="template" :show="isTransferModalOpen">
        <Dialog
            as="div"
            class="fixed z-[100] inset-0 overflow-y-auto"
            @close="closeTransferModal"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <DialogOverlay class="fixed inset-0 bg-black opacity-30" />
                <div
                    class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto z-50 p-6 relative"
                >
                    <DialogTitle class="text-lg font-bold mb-2"
                        >Transfer Asset</DialogTitle
                    >
                    <form @submit.prevent="submitTransfer">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1"
                                >New Custodian</label
                            >
                            <input
                                v-model="transferForm.custodian"
                                type="text"
                                class="w-full border rounded px-2 py-1"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1"
                                >Transfer Date</label
                            >
                            <input
                                v-model="transferForm.transfer_date"
                                type="date"
                                class="w-full border rounded px-2 py-1"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1"
                                >Notes</label
                            >
                            <textarea
                                v-model="transferForm.assignment_notes"
                                class="w-full border rounded px-2 py-1"
                                rows="2"
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                @click="closeTransferModal"
                                class="px-3 py-1 rounded bg-gray-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="isTransferring"
                                class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
                            >
                                {{
                                    isTransferring
                                        ? "Transferring..."
                                        : "Transfer"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { TailwindPagination } from "laravel-vue-pagination";
import { Link, router } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import { debounce } from "lodash";
import axios from "axios";
import * as XLSX from "xlsx";
import moment from "moment";
import { useToast } from "vue-toastification";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { reactive } from "vue";
import {
    Dialog,
    DialogOverlay,
    DialogTitle,
    TransitionRoot,
    DialogPanel,
    TransitionChild,
} from "@headlessui/vue";

const toast = useToast();

const props = defineProps({
    locations: {
        type: Array,
        required: true,
    },
    assets: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
    },
    regions: {
        type: Array,
        required: true,
    },
    fundSources: {
        type: Array,
        required: true,
    },
});

const isTransferModalOpen = ref(false);
const isTransferring = ref(false);
const selectedAsset = ref(null);
const showImportModal = ref(false);

const transferForm = reactive({
    asset_id: null,
    custodian: "",
    transfer_date: "",
    assignment_notes: "",
});

function openTransferModal(asset) {
    selectedAsset.value = asset;
    transferForm.asset_id = asset.id;
    transferForm.custodian = asset.person_assigned || "";
    transferForm.transfer_date = "";
    transferForm.assignment_notes = "";
    isTransferModalOpen.value = true;
}

function closeTransferModal() {
    isTransferModalOpen.value = false;
}

function formatDate(date) {
    return moment(date).format("DD/MM/YYYY");
}

const assets = ref([]);
const loading = ref(false);
const selectedLocations = ref([]);
const selectedSubLocations = ref([]);
const collapsedLocations = ref([]);
const isHistoryModalOpen = ref(false);
const selectedAssetHistory = ref([]);

function openHistoryModal(asset) {
    selectedAssetHistory.value = [...asset.history].sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );
    isHistoryModalOpen.value = true;
}
function closeHistoryModal() {
    isHistoryModalOpen.value = false;
    selectedAssetHistory.value = [];
}
const isAttachmentsModalOpen = ref(false);
const selectedAttachments = ref([]);

function openAttachmentsModal(attachments) {
    selectedAttachments.value = attachments;
    isAttachmentsModalOpen.value = true;
}
function closeAttachmentsModal() {
    isAttachmentsModalOpen.value = false;
    selectedAttachments.value = [];
}

const search = ref(props.filters.search);
const per_page = ref(props.filters.per_page);

// Region, Location, SubLocation filter state
const regionFilter = ref(null);
const locationFilter = ref(null);
const subLocationFilter = ref(null);
const subLocationOptions = ref([]);
const fundSourceFilter = ref(null);

const regionOptions = computed(() => props.regions || []);
const locationOptions = computed(() => props.locations || []);
// subLocationOptions is now a ref, loaded dynamically

const filteredSubLocations = computed(() => subLocationOptions.value);

function onRegionChange(selected) {
    locationFilter.value = null;
    subLocationFilter.value = null;
    subLocationOptions.value = [];
}

async function onLocationChange(selected) {
    subLocationFilter.value = null;
    subLocationOptions.value = [];
    if (!selected) return;
    const locationId = selected.id || selected;
    try {
        const response = await axios.get(
            route("assets.locations.sub-locations", { location: locationId })
        );
        subLocationOptions.value = response.data;
    } catch (error) {
        subLocationOptions.value = [];
    }
}

// Asset transfer
async function submitTransfer() {
    if (!transferForm.custodian || !transferForm.transfer_date) {
        toast.error("Custodian and transfer date are required.");
        return;
    }
    isTransferring.value = true;
    try {
        const response = await axios.post(
            route("assets.transfer", { asset: transferForm.asset_id }),
            {
                asset_id: transferForm.asset_id,
                custodian: transferForm.custodian,
                transfer_date: transferForm.transfer_date,
                assignment_notes: transferForm.assignment_notes,
            }
        );
        if (selectedAsset.value) {
            selectedAsset.value.person_assigned = transferForm.custodian;
            selectedAsset.value.transfer_date = transferForm.transfer_date;
        }
        toast.success("Asset transferred successfully!");
        closeTransferModal();
    } catch (error) {
        toast.error(error.response?.data || "Transfer failed.");
    } finally {
        isTransferring.value = false;
    }
}

// Computed property for filtered assets
const filteredAssets = computed(() => {
    return assets.value.filter((asset) => {
        const regionMatch =
            !regionFilter.value || asset.region_id === regionFilter.value.id;
        const locationMatch =
            !locationFilter.value ||
            asset.asset_location_id === locationFilter.value.id;
        const subLocationMatch =
            !subLocationFilter.value ||
            asset.sub_location_id === subLocationFilter.value.id;
        const searchMatch =
            !search.value ||
            [asset.asset_tag, asset.serial_number, asset.item_description].some(
                (field) =>
                    field &&
                    field.toLowerCase().includes(search.value.toLowerCase())
            );
        return regionMatch && locationMatch && subLocationMatch && searchMatch;
    });
});

function getResults(page = 1) {
    props.filters.page = page;
}

// Watch for location change to load sub-locations
watch(
    () => locationFilter.value,
    async (newLocation) => {
        if (newLocation && newLocation.id) {
            // Fetch sub-locations for the selected location
            try {
                const response = await axios.get(
                    route("assets.locations.sub-locations", newLocation.id)
                );
                subLocationOptions.value = response.data;
            } catch (e) {
                subLocationOptions.value = [];
            }
        } else {
            subLocationOptions.value = [];
            subLocationFilter.value = null;
        }
    },
    { immediate: true }
);

watch(
    [
        () => props.filters.page,
        () => search.value,
        () => per_page.value,
        () => (regionFilter.value ? regionFilter.value.id : null),
        () => (locationFilter.value ? locationFilter.value.id : null),
        () => (fundSourceFilter.value ? fundSourceFilter.value.id : null),
    ],
    () => {
        reloadAssets();
    }
);

// Watch for changes in selectedSubLocations and reload assets
watch(
    selectedSubLocations,
    () => {
        reloadAssets();
    },
    { deep: true }
);

function reloadAssets() {
    const query = {};
    if (props.filters.page) query.page = props.filters.page;
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (regionFilter.value && regionFilter.value.id)
        query.region_id = regionFilter.value.id;
    if (locationFilter.value && locationFilter.value.id)
        query.location_id = locationFilter.value.id;
    if (selectedSubLocations.value && selectedSubLocations.value.length) {
        query["sub_location_ids"] = selectedSubLocations.value.map((x) => x.id);
    }
    if (fundSourceFilter.value && fundSourceFilter.value.id)
        query.fund_source_id = fundSourceFilter.value.id;
    router.get(route("assets.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["assets", "locations"],
    });
}

// Toggle location selection
const toggleLocation = (locationId) => {
    const index = selectedLocations.value.indexOf(locationId);
    if (index === -1) {
        selectedLocations.value.push(locationId);
        // Auto-select all sub-locations
        const location = props.locations.find((l) => l.id === locationId);
        if (location?.sub_locations) {
            location.sub_locations.forEach((sub) => {
                if (!selectedSubLocations.value.includes(sub.id)) {
                    selectedSubLocations.value.push(sub.id);
                }
            });
        }
    } else {
        selectedLocations.value.splice(index, 1);
        // Deselect all sub-locations of this location
        const location = props.locations.find((l) => l.id === locationId);
        if (location?.sub_locations) {
            location.sub_locations.forEach((sub) => {
                const subIndex = selectedSubLocations.value.indexOf(sub.id);
                if (subIndex !== -1) {
                    selectedSubLocations.value.splice(subIndex, 1);
                }
            });
        }
    }
};

// Format money
const formatMoney = (amount) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

// Toggle sub-location selection
const toggleSubLocation = (subLocationId, parentLocationId) => {
    const index = selectedSubLocations.value.indexOf(subLocationId);
    if (index === -1) {
        selectedSubLocations.value.push(subLocationId);
        // Ensure parent location is selected
        if (!selectedLocations.value.includes(parentLocationId)) {
            selectedLocations.value.push(parentLocationId);
        }
    } else {
        selectedSubLocations.value.splice(index, 1);
        // Check if all sub-locations are deselected
        const location = props.locations.find((l) => l.id === parentLocationId);
        const anySubSelected = location?.sub_locations.some((sub) =>
            selectedSubLocations.value.includes(sub.id)
        );
        if (!anySubSelected) {
            const parentIndex =
                selectedLocations.value.indexOf(parentLocationId);
            if (parentIndex !== -1) {
                selectedLocations.value.splice(parentIndex, 1);
            }
        }
    }
};

// Toggle location collapse state
const toggleCollapse = (locationId) => {
    const index = collapsedLocations.value.indexOf(locationId);
    if (index === -1) {
        collapsedLocations.value.push(locationId);
    } else {
        collapsedLocations.value.splice(index, 1);
    }
};

// Computed properties for the cards
const totalAssets = computed(() => assets.value.length);
const activeAssets = computed(
    () => assets.value.filter((asset) => asset.status === "in_use").length
);
const maintenanceAssets = computed(
    () => assets.value.filter((asset) => asset.status === "maintenance").length
);


const importFile = ref(null);
const isUploading = ref(false);

const uploadExcel = async () => {
    const file = importFile.value?.files[0];
    if (!file) {
        toast.error("Please select a file.");
        return;
    }
    const formData = new FormData();
    formData.append("file", file);
    isUploading.value = true;
    try {
        await axios.post(route("assets.import"), formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        toast.success("Import started. You will be notified when complete.");
        importFile.value.value = ""; // reset input
        showImportModal.value = false; // close modal after upload
    } catch (error) {
        toast.error(error.response?.data || "Import failed.");
    } finally {
        isUploading.value = false;
    }
};

// Excel export function
const exportToExcel = () => {
    // Prepare the data
    const exportData = props.assets.data.map((asset) => ({
        "Asset Tag": asset.asset_tag,
        Category: asset.category.name,
        "Serial Number": asset.serial_number,
        Description: asset.item_description,
        "Assigned To": asset.person_assigned,
        Region: asset.region?.name,
        Location: asset.location.name,
        "Sub Location": asset.sub_location?.name || "",
        "Acquisition Date": formatDate(asset.acquisition_date),
        Status: asset.status,
        "Original Value": asset.original_value,
        "Source Agency": asset.fund_source?.name || "",
    }));

    // Create worksheet
    const worksheet = XLSX.utils.json_to_sheet(exportData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Assets");

    // Generate file name with current date
    const date = new Date().toISOString().split("T")[0];
    const fileName = `assets_${date}.xlsx`;

    // Save the file
    XLSX.writeFile(workbook, fileName);
};
</script>
