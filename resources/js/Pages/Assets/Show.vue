<template>
    <AuthenticatedLayout
        :title="`Asset: ${props.asset.asset_tag}`"
        description="Detailed asset information"
        img="/assets/images/asset-header.png"
    >
        <div class="">
            <!-- Header Section -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-10">
                <div
                    class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 sm:p-8"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="w-10 h-10 text-white"
                                >
                                    <path
                                        d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM6.24 5h11.52l.83 1H5.41l.83-1zM5 19V8h14v11H5zm3-5h8v-2H8v2zm0-3h8V9H8v2z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">
                                    {{ asset.asset_tag || "Asset Details" }}
                                </h1>
                                <p class="text-blue-100 text-sm mt-1">
                                    <p>
                                        {{ asset.serial_number }}
                                    </p>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-xs font-semibold',
                                            asset.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-yellow-100 text-yellow-800',
                                        ]"
                                    >
                                    {{
                                        formatStatus(asset.status) || "N/A"
                                    }}
                                    </span>
                                    
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 sm:mt-0 flex space-x-3">
                            <Link
                                :href="route('assets.edit', asset.id)"
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-indigo-700 bg-white hover:bg-indigo-50 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="w-4 h-4 mr-2"
                                >
                                    <path
                                        d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"
                                    />
                                </svg>
                                Edit Asset
                            </Link>
                            <Link
                                :href="route('assets.index')"
                                class="inline-flex items-center justify-center px-4 py-2 border border-white/50 text-sm font-medium rounded-md text-white hover:bg-white/10 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="w-4 h-4 mr-2"
                                >
                                    <path
                                        d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"
                                    />
                                </svg>
                                Back to List
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Main Details Sections -->
                <div class="p-6 sm:p-8">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6"
                    >
                        <!-- Asset Information -->
                        <section>
                            <h2
                                class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="w-5 h-5 text-blue-500 mr-2"
                                >
                                    <path
                                        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"
                                    /></svg
                                >Asset Information
                            </h2>
                            <dl class="space-y-3">
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Serial Number
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900 font-mono"
                                    >
                                        {{ asset.serial_number || "—" }}
                                    </dd>
                                </div>
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Category
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900"
                                    >
                                        {{ asset.category?.name || "—" }}
                                    </dd>
                                </div>
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Description
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900"
                                    >
                                        {{ asset.item_description || "—" }}
                                    </dd>
                                </div>
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Assigned To
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900"
                                    >
                                        {{ asset.person_assigned || "—" }}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <!-- Location & Financial Details -->
                        <section>
                            <h2
                                class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="w-5 h-5 text-green-500 mr-2"
                                >
                                    <path
                                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                                    /></svg
                                >Location & Financials
                            </h2>
                            <dl class="space-y-3">
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Location
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900"
                                    >
                                        {{ asset.location?.name || "—" }}
                                    </dd>
                                </div>
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Sub-Location
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900"
                                    >
                                        {{ asset.sub_location?.name || "—" }}
                                    </dd>
                                </div>
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Acquisition Date
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900"
                                    >
                                        {{
                                            formatDate(
                                                asset.acquisition_date
                                            ) || "—"
                                        }}
                                    </dd>
                                </div>
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Original Value
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900 font-semibold"
                                    >
                                        {{
                                            formatMoney(asset.original_value) ||
                                            "—"
                                        }}
                                    </dd>
                                </div>
                                <div
                                    class="grid grid-cols-3 gap-2 items-center"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Fund Source
                                    </dt>
                                    <dd
                                        class="col-span-2 text-sm text-gray-900"
                                    >
                                        {{ asset.fund_source?.name || "—" }}
                                    </dd>
                                </div>
                            </dl>
                        </section>
                    </div>
                </div>
            </div>

            <!-- Asset History Section -->
            <div
                v-if="asset.history && asset.history.length"
                class="bg-white shadow-xl rounded-2xl p-6 sm:p-8 mb-10"
            >
                <h2
                    class="text-xl font-semibold text-gray-800 mb-6 flex items-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="w-6 h-6 text-purple-500 mr-3"
                    >
                        <path
                            d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.25 2.52.77-1.28-3.52-2.09V8H12z"
                        /></svg
                    >Asset History
                </h2>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li
                            v-for="(event, eventIdx) in asset.history"
                            :key="event.id"
                        >
                            <div class="relative pb-8">
                                <span
                                    v-if="eventIdx !== asset.history.length - 1"
                                    class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200"
                                    aria-hidden="true"
                                ></span>
                                <div
                                    class="relative flex items-start space-x-3"
                                >
                                    <div class="relative">
                                        <span
                                            class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center ring-8 ring-white"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                class="w-6 h-6 text-purple-500"
                                            >
                                                <path
                                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 12c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div>
                                            <div class="text-sm">
                                                <strong
                                                    class="font-medium text-gray-900"
                                                    >{{
                                                        event.custodian ||
                                                        "System Event"
                                                    }}</strong
                                                >
                                            </div>
                                            <p
                                                class="mt-0.5 text-xs text-gray-500"
                                            >
                                                Assigned on
                                                {{
                                                    formatDate(
                                                        event.assigned_at
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700">
                                            <p
                                                v-if="event.status_notes"
                                                class="bg-gray-50 p-3 rounded-md"
                                            >
                                                {{ event.status_notes }}
                                            </p>
                                            <p
                                                v-if="event.returned_at"
                                                class="mt-1 text-sm text-green-700 font-medium"
                                            >
                                                Returned on:
                                                {{
                                                    formatDate(
                                                        event.returned_at
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Warranty Section -->
            <div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8 mb-8 flex flex-col md:flex-row md:items-center gap-6" v-if="asset.has_warranty">
                <div class="flex items-center gap-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-yellow-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6 1A9 9 0 11 3 12a9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <div class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            Warranty
                            <span v-if="asset.asset_warranty_start && asset.asset_warranty_end"
                                  :class="isWarrantyActive(asset.asset_warranty_start, asset.asset_warranty_end) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                  class="ml-2 px-2 py-0.5 rounded text-xs font-semibold">
                                {{ isWarrantyActive(asset.asset_warranty_start, asset.asset_warranty_end) ? 'Active' : 'Expired' }}
                            </span>
                        </div>
                        <div class="flex flex-col md:flex-row md:items-center gap-2 mt-1">
                            <div class="flex items-center gap-1 text-sm text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Start:
                                <span class="ml-1 font-medium">{{ formatDate(asset.asset_warranty_start) || '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-sm text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                                </svg>
                                End:
                                <span class="ml-1 font-medium">{{ formatDate(asset.asset_warranty_end) || '—' }}</span>
                            </div>
                        </div>
                        <!-- warranty notification before one month -->
                        <div v-if="isWarrantyExpiringInOneMonth(asset.asset_warranty_end)" class="mt-2 text-sm text-blue-700 bg-blue-50 border border-blue-200 px-3 py-2 rounded flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Warranty will expire in {{ daysLeft(asset.asset_warranty_end) }} days
                        </div>
                        <!-- warrenty notification before one week -->
                        <div v-if="isWarrantyExpiringSoon(asset.asset_warranty_end)" class="mt-2 text-sm text-yellow-600">
                            Warranty expires in {{ daysLeft(asset.asset_warranty_end) }} days
                        </div>
                    </div>
                </div>
            </div>
            <!-- Attachments Section -->
            <div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8">
                <h2
                    class="text-xl font-semibold text-gray-800 mb-6 flex items-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="w-6 h-6 text-teal-500 mr-3"
                    >
                        <path
                            d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 015 0v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H14v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"
                        /></svg
                    >Attachments
                </h2>
                <div
                    v-if="asset.attachments && asset.attachments.length"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"
                >
                    <div
                        v-for="attachment in asset.attachments"
                        :key="attachment.id"
                        class="bg-gray-50 hover:bg-gray-100 p-4 rounded-lg border border-gray-200 hover:border-teal-300 flex flex-col gap-2"
                    >
                        <div class="flex items-center justify-between">
    <a
        :href="`/${attachment.file}`"
        target="_blank"
        class="text-sm text-teal-700 hover:underline break-all"
    >
        {{ attachment.type }}
    </a>
    <button
        class="ml-2 text-red-500 hover:text-red-700 focus:outline-none"
        @click="confirmDeleteAttachment(attachment.id)"
        title="Delete attachment"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
<span class="text-xs text-gray-400">
    Uploaded: {{ formatDate(attachment.created_at) }}
</span>
                    </div>
                </div>
                <div v-else class="py-6 text-center">
                    <p class="mt-4 text-sm font-medium text-gray-500">
                        No attachments found for this asset.
                    </p>
                </div>
            </div>
            <!-- Add Document Section -->
            <div class="bg-white shadow rounded-lg p-6 mt-8">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-5 h-5 mr-2 text-teal-500"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 4.5v15m7.5-7.5h-15"
                        />
                    </svg>
                    Add Document(s)
                </h3>
                <form @submit.prevent="submitDocuments">
                    <div
                        v-for="(doc, idx) in newDocuments"
                        :key="idx"
                        class="flex flex-col md:flex-row md:items-center gap-3 mb-4"
                    >
                        <input
                            type="text"
                            v-model="doc.type"
                            placeholder="Document Type"
                            class="border rounded px-3 py-2 w-full md:w-1/3"
                            required
                        />
                        <input
                            type="file"
                            accept="application/pdf"
                            @change="onFileChange($event, idx)"
                            class="w-full md:w-1/3"
                            required
                        />
                        <button
                            type="button"
                            @click="removeDocumentRow(idx)"
                            v-if="newDocuments.length > 1"
                            class="text-red-600 hover:text-red-800 ml-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-5 h-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <div class="flex gap-2 mb-4">
                        <button
                            type="button"
                            @click="addDocumentRow"
                            class="inline-flex items-center px-3 py-1 bg-teal-100 text-teal-700 rounded hover:bg-teal-200 transition"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-4 h-4 mr-1"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4.5v15m7.5-7.5h-15"
                                />
                            </svg>
                            Add Row
                        </button>
                        <button
                            type="submit"
                            :disabled="uploading"
                            class="inline-flex items-center px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition disabled:opacity-60"
                        >
                            <svg
                                v-if="uploading"
                                class="animate-spin h-4 w-4 mr-1"
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
                            Upload
                        </button>
                    </div>
                    <div
                        v-if="docErrors.length"
                        class="mb-2 text-red-600 text-sm"
                    >
                        <ul>
                            <li v-for="(err, i) in docErrors" :key="i">
                                {{ err }}
                            </li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loading State Overlay -->
        <div
            v-if="loading"
            class="fixed inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-50"
        >
            <div class="flex flex-col items-center">
                <svg
                    class="animate-spin h-10 w-10 text-blue-600"
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
                <p class="mt-3 text-sm font-medium text-gray-700">
                    Loading asset details...
                </p>
            </div>
        </div>

        <!-- Error Toast -->
        <div
            v-if="error"
            aria-live="assertive"
            class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50"
        >
            <div
                class="w-full flex flex-col items-center space-y-4 sm:items-end"
            >
                <div
                    class="max-w-sm w-full bg-red-50 shadow-lg rounded-lg pointer-events-auto ring-1 ring-red-400 ring-opacity-5 overflow-hidden"
                >
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="w-6 h-6 text-red-400"
                                >
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-red-700">
                                    An Error Occurred!
                                </p>
                                <p class="mt-1 text-sm text-red-600">
                                    {{ error }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Section -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-10" v-if="asset.submitted_for_approval || canApprove">
                <div class="bg-gradient-to-r from-purple-600 to-pink-700 p-6 sm:p-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 mr-3">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approval Status
                    </h2>
                </div>
                <div class="p-6 sm:p-8">
                    <div v-if="asset.submitted_for_approval" class="space-y-6">
                        <!-- Approval Status -->
                        <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-yellow-600 mr-3">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <div>
                                    <h3 class="text-lg font-semibold text-yellow-800">Pending Approval</h3>
                                    <p class="text-yellow-600">Submitted on {{ formatDate(asset.submitted_at) }} by {{ asset.submitted_by_user?.name || 'Unknown' }}</p>
                                </div>
                            </div>
                            <div v-if="!canApprove" class="text-sm text-yellow-600">
                                Waiting for approval...
                            </div>
                        </div>

                        <!-- Approval Steps -->
                        <div v-if="approvalHistory.length > 0" class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-800">Approval Steps</h3>
                            <div class="space-y-3">
                                <div v-for="(step, index) in approvalHistory" :key="step.id" 
                                     class="flex items-center p-4 border rounded-lg"
                                     :class="{
                                         'bg-green-50 border-green-200': step.status === 'approved',
                                         'bg-red-50 border-red-200': step.status === 'rejected',
                                         'bg-gray-50 border-gray-200': step.status === 'pending'
                                     }">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-4"
                                         :class="{
                                             'bg-green-500 text-white': step.status === 'approved',
                                             'bg-red-500 text-white': step.status === 'rejected',
                                             'bg-gray-300 text-gray-600': step.status === 'pending'
                                         }">
                                        <span class="text-sm font-semibold">{{ index + 1 }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ step.role?.name || 'Unknown Role' }}</p>
                                                <p class="text-sm text-gray-500">{{ step.action }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      :class="{
                                                          'bg-green-100 text-green-800': step.status === 'approved',
                                                          'bg-red-100 text-red-800': step.status === 'rejected',
                                                          'bg-gray-100 text-gray-800': step.status === 'pending'
                                                      }">
                                                    {{ step.status }}
                                                </span>
                                            </div>
                                        </div>
                                        <div v-if="step.approved_at" class="mt-2 text-sm text-gray-500">
                                            {{ step.status === 'approved' ? 'Approved' : 'Rejected' }} on {{ formatDate(step.approved_at) }}
                                            <span v-if="step.approver">by {{ step.approver.name }}</span>
                                        </div>
                                        <div v-if="step.notes" class="mt-2 text-sm text-gray-600">
                                            <strong>Notes:</strong> {{ step.notes }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approval Actions -->
                        <div v-if="canApprove" class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Take Action</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="approval-notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes (Optional)
                                    </label>
                                    <textarea
                                        id="approval-notes"
                                        v-model="approvalNotes"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Add any notes about your decision..."
                                    ></textarea>
                                </div>
                                <div class="flex space-x-4">
                                    <button
                                        @click="approveAsset('approve')"
                                        :disabled="approvalLoading"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                                    >
                                        <svg v-if="approvalLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Approve
                                    </button>
                                    <button
                                        @click="approveAsset('reject')"
                                        :disabled="approvalLoading"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                                    >
                                        <svg v-if="approvalLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                            <path d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit for Approval Button -->
                    <div v-else-if="!asset.submitted_for_approval && asset.status !== 'pending_approval'" class="text-center py-8">
                        <div class="max-w-md mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16 text-gray-400 mx-auto mb-4">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Ready for Approval</h3>
                            <p class="text-gray-500 mb-6">Submit this asset for approval to activate it in the system.</p>
                            <button
                                @click="submitForApproval"
                                :disabled="submitLoading"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                <svg v-if="submitLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                Submit for Approval
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>

import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import moment from "moment";
import axios from "axios";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";

const props = defineProps({
    asset: Object,
});

const loading = ref(false);
const error = ref(null);
const uploading = ref(false);
const newDocuments = ref([{ type: "", file: null, asset_id: props.asset.id }]);
const docErrors = ref([]);

// Approval related reactive variables
const approvalHistory = ref([]);
const canApprove = ref(false);
const approvalNotes = ref('');
const approvalLoading = ref(false);
const submitLoading = ref(false);

function addDocumentRow() {
    newDocuments.value.push({ type: "", file: null, asset_id: props.asset.id });
}
function removeDocumentRow(idx) {
    newDocuments.value.splice(idx, 1);
    if (newDocuments.value.length === 0) addDocumentRow();
}



function confirmDeleteAttachment(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the attachment.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(async(result) => {
        if (result.isConfirmed) {
            await axios.get(route('assets.document.delete', id))
                .then((response) => {
                    Swal.fire('Deleted!', 'Attachment has been deleted.', 'success');
                    router.reload();
                })
                .catch((error) => {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: error.response?.data || 'Failed to delete attachment',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                })
        }
    });
}


function onFileChange(e, idx) {
    const file = e.target.files[0];
    if (file && file.type !== "application/pdf") {
        docErrors.value.push(`File at row ${idx + 1} must be a PDF.`);
        newDocuments.value[idx].file = null;
        e.target.value = "";
        return;
    }
    newDocuments.value[idx].file = file;
}
async function submitDocuments() {
    docErrors.value = [];
    let hasError = false;
    newDocuments.value.forEach((doc, idx) => {
        if (!doc.type || !doc.file) {
            docErrors.value.push(
                `Row ${idx + 1}: Both type and PDF file are required.`
            );
            hasError = true;
        } else if (doc.file.type !== "application/pdf") {
            docErrors.value.push(`Row ${idx + 1}: File must be a PDF.`);
            hasError = true;
        }
    });
    if (hasError) return;

    const formData = new FormData();
    newDocuments.value.forEach((doc, idx) => {
        formData.append(`documents[${idx}][type]`, doc.type);
        formData.append(`documents[${idx}][file]`, doc.file);
        formData.append(`documents[${idx}][asset_id]`, doc.asset_id);
    });

    uploading.value = true;
    await axios
        .post(route("assets.documents.store"), formData)
        .then((response) => {
            uploading.value = false;
            // Success: reload or update attachments
            Swal.fire({
                icon: "success",
                title: "Success",
                text: response.data,
                showConfirmButton: false,
                timer: 1500,
            }).then(() => {
                router.reload();
            });
        })
        .catch((error) => {
            uploading.value = false;
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response.data,
                showConfirmButton: false,
                timer: 1500,
            });
        })
        .finally(() => {
            uploading.value = false;
            newDocuments.value = [
                { type: "", file: null, asset_id: props.asset.id },
            ];
        });
}

function formatDate(date) {
    if (!date) return "-";
    return moment(date).format("DD/MM/YYYY");
}
function formatMoney(amount) {
    if (amount == null) return "-";
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}
function formatStatus(status) {
    if (!status) return "-";
    const map = {
        in_use: "In Use",
        maintenance: "Maintenance",
        disposed: "Disposed",
        lost: "Lost",
    };
    return map[status] || status;
}
function isWarrantyActive(start, end) {
  if (!start || !end) return false;
  const now = new Date();
  const startDate = new Date(start);
  const endDate = new Date(end);
  return now >= startDate && now <= endDate;
}

function daysLeft(end) {
  if (!end) return 0;
  const now = new Date();
  const endDate = new Date(end);
  const diff = endDate - now;
  return diff > 0 ? Math.ceil(diff / (1000 * 60 * 60 * 24)) : 0;
}
function isWarrantyExpired(end) {
  if (!end) return false;
  const now = new Date();
  const endDate = new Date(end);
  return now > endDate;
}
function isWarrantyExpiringSoon(end) {
  const dl = daysLeft(end);
  return dl > 0 && dl <= 7;
}
function isWarrantyExpiringInOneMonth(end) {
  const dl = daysLeft(end);
  return dl > 7 && dl <= 30;
}

// Approval methods
async function loadApprovalHistory() {
    try {
        const response = await axios.get(route('assets.approval-history', props.asset.id));
        approvalHistory.value = response.data.approval_history;
        
        // Check if current user can approve
        const pendingStep = approvalHistory.value.find(step => step.status === 'pending');
        if (pendingStep) {
            // This is a simplified check - in a real app you'd check user roles
            canApprove.value = true;
        }
    } catch (error) {
        console.error('Error loading approval history:', error);
    }
}

async function submitForApproval() {
    submitLoading.value = true;
    try {
        const response = await axios.post(route('assets.submit-approval', props.asset.id));
        
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.data.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            router.reload();
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data || 'Failed to submit for approval',
            showConfirmButton: false,
            timer: 1500
        });
    } finally {
        submitLoading.value = false;
    }
}

async function approveAsset(action) {
    approvalLoading.value = true;
    try {
        const response = await axios.post(route('assets.approve', props.asset.id), {
            action: action,
            notes: approvalNotes.value
        });
        
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.data.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            router.reload();
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data || 'Failed to process approval',
            showConfirmButton: false,
            timer: 1500
        });
    } finally {
        approvalLoading.value = false;
    }
}

// Load approval history when component mounts
loadApprovalHistory();

</script>
