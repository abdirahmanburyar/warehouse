<template>
    <AppLayout title="Test Permission Event">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Test Permission Event
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Current User Information</h3>
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>User ID:</strong> {{ $page.props.auth.user.id }}</p>
                                <p><strong>Name:</strong> {{ $page.props.auth.user.name }}</p>
                                <p><strong>Email:</strong> {{ $page.props.auth.user.email }}</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Permission Event Test</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                Click the button below to trigger a permission change event for the current user.
                                This will simulate a permission change and test if the page reloads correctly.
                            </p>
                            
                            <button 
                                @click="triggerPermissionEvent" 
                                class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                :disabled="loading"
                            >
                                {{ loading ? 'Triggering Event...' : 'Trigger Permission Event' }}
                            </button>
                        </div>

                        <div v-if="result" class="mt-6 p-4 border rounded-md" :class="result.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'">
                            <p class="font-medium" :class="result.success ? 'text-green-700' : 'text-red-700'">
                                {{ result.message }}
                            </p>
                            <p v-if="result.user_id" class="mt-2 text-sm text-gray-600">
                                Event triggered for user ID: {{ result.user_id }}
                            </p>
                        </div>

                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900">Console Output</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                Check your browser console (F12) to see the event processing logs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    components: {
        AppLayout,
    },
    data() {
        return {
            loading: false,
            result: null
        };
    },
    methods: {
        async triggerPermissionEvent() {
            this.loading = true;
            this.result = null;
            
            try {
                const response = await fetch('/test-permission-event', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                this.result = data;
                
                console.log('🚀 Permission event triggered:', data);
                
                // Display a message that the event was triggered successfully
                // The page should reload automatically due to the event listener in app.js
                if (window.$showToast) {
                    window.$showToast({
                        message: 'Permission event triggered successfully. Page will reload shortly...',
                        type: 'success',
                        duration: 2000
                    });
                }
                
                // The page should reload automatically due to the event listener in AuthenticatedLayout.vue
                // If it doesn't, we'll show a message
                setTimeout(() => {
                    if (this.loading) {
                        this.loading = false;
                        console.warn('⚠️ Page did not reload automatically. Check event handling in AuthenticatedLayout.vue');
                        
                        // Force a reload as a fallback
                        console.log('🔄 Forcing page reload as fallback...');
                        window.location.reload();
                    }
                }, 5000);
                
            } catch (error) {
                console.error('❌ Error triggering permission event:', error);
                this.result = {
                    success: false,
                    message: `Error: ${error.message || 'Unknown error occurred'}`
                };
                this.loading = false;
            }
        }
    }
};
</script>
