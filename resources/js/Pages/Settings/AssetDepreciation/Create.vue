<template>
    <AuthenticatedLayout :title="'Create Asset Depreciation Setting'" description="Add new depreciation configuration">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Create Asset Depreciation Setting</h1>
                        <p class="text-gray-600 mt-1">Add new depreciation configuration to the system</p>
                    </div>
                    <Link 
                        :href="route('settings.asset-depreciation.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Back to Settings
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white shadow sm:rounded-lg">
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    <!-- Setting Key -->
                    <div>
                        <label for="key" class="block text-sm font-medium text-gray-700">Setting Key</label>
                        <input
                            id="key"
                            v-model="form.key"
                            type="text"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="e.g., default_useful_life_years"
                        />
                        <p class="mt-1 text-sm text-gray-500">Unique identifier for this setting</p>
                    </div>

                    <!-- Value -->
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700">Value</label>
                        <input
                            v-if="form.type === 'string' || form.type === 'integer' || form.type === 'float'"
                            id="value"
                            v-model="form.value"
                            :type="form.type === 'integer' ? 'number' : form.type === 'float' ? 'number' : 'text'"
                            :step="form.type === 'float' ? '0.01' : '1'"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :placeholder="getValuePlaceholder()"
                        />
                        <select
                            v-else-if="form.type === 'boolean'"
                            v-model="form.value"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                            <option value="">Select...</option>
                            <option :value="true">Yes</option>
                            <option :value="false">No</option>
                        </select>
                        <textarea
                            v-else-if="form.type === 'json'"
                            v-model="jsonValue"
                            rows="4"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder='{"key": "value"}'
                        ></textarea>
                        <p class="mt-1 text-sm text-gray-500">The value for this setting</p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Data Type</label>
                        <select
                            id="type"
                            v-model="form.type"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                            <option value="">Select type...</option>
                            <option v-for="(label, value) in types" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select
                            id="category"
                            v-model="form.category"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                            <option value="">Select category...</option>
                            <option v-for="(label, value) in categories" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Brief description of what this setting controls..."
                        ></textarea>
                    </div>

                    <!-- Asset Category (for category overrides) -->
                    <div v-if="form.category === 'category_override'">
                        <label for="asset_category" class="block text-sm font-medium text-gray-700">Asset Category</label>
                        <select
                            id="asset_category"
                            v-model="form.metadata.asset_category"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                            <option value="">Select asset category...</option>
                            <option v-for="category in assetCategories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        >
                            <span v-if="isSubmitting">Creating...</span>
                            <span v-else>Create Setting</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    categories: Object,
    types: Object,
    depreciationMethods: Object,
    assetCategories: Array,
});

const form = ref({
    key: '',
    value: '',
    type: '',
    description: '',
    category: '',
    metadata: {},
});

const jsonValue = ref('{}');
const isSubmitting = ref(false);

const getValuePlaceholder = () => {
    switch (form.value.type) {
        case 'integer': return 'e.g., 5';
        case 'float': return 'e.g., 0.2';
        case 'string': return 'e.g., straight_line';
        default: return '';
    }
};

const submitForm = () => {
    isSubmitting.value = true;
    
    // Convert JSON string to object if needed
    if (form.value.type === 'json') {
        try {
            form.value.value = JSON.parse(jsonValue.value);
        } catch (e) {
            alert('Invalid JSON format');
            isSubmitting.value = false;
            return;
        }
    }
    
    // Convert value to proper type
    if (form.value.type === 'integer') {
        form.value.value = parseInt(form.value.value);
    } else if (form.value.type === 'float') {
        form.value.value = parseFloat(form.value.value);
    } else if (form.value.type === 'boolean') {
        form.value.value = Boolean(form.value.value);
    }
    
    router.post(route('settings.asset-depreciation.store'), form.value, {
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

// Watch for type changes to reset value
watch(() => form.value.type, (newType) => {
    if (newType === 'json') {
        form.value.value = {};
        jsonValue.value = '{}';
    } else {
        form.value.value = '';
        jsonValue.value = '{}';
    }
});
</script>
