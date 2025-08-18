<template>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Settings Test Page</h1>
            
            <!-- Simple Test Content -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Basic Test</h2>
                <p class="text-gray-600">If you can see this, the page is loading correctly.</p>
                <p class="text-gray-600">Current time: {{ currentTime }}</p>
            </div>
            
            <!-- Debug Info -->
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                <strong>Debug Info:</strong>
                <p>Page loaded: {{ pageLoaded }}</p>
                <p>User: {{ userInfo.name || 'No user' }}</p>
                <p>Email: {{ userInfo.email || 'No email' }}</p>
                <p>Permissions: {{ permissionsCount }}</p>
            </div>
            
            <!-- Test Navigation -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Test Navigation</h2>
                <div class="space-y-2">
                    <a href="/dashboard" class="block text-blue-600 hover:text-blue-800">Go to Dashboard</a>
                    <a href="/facilities" class="block text-blue-600 hover:text-blue-800">Go to Facilities</a>
                    <button @click="testFunction" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Test Button
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Simple reactive variables
const pageLoaded = ref(false);
const currentTime = ref('');
const userInfo = ref({});
const permissionsCount = ref(0);

// Test function
const testFunction = () => {
    alert('Button clicked! JavaScript is working.');
};

onMounted(() => {
    console.log('Settings test page mounted');
    
    // Set current time
    currentTime.value = new Date().toLocaleString();
    
    // Try to get user info from window if available
    if (window.Inertia && window.Inertia.page && window.Inertia.page.props) {
        const props = window.Inertia.page.props;
        userInfo.value = {
            name: props.auth?.user?.name || 'Unknown',
            email: props.auth?.user?.email || 'Unknown'
        };
        permissionsCount.value = props.auth?.user?.permissions?.length || 0;
        console.log('Inertia props found:', props);
    } else {
        console.log('Inertia props not found');
    }
    
    // Mark page as loaded
    pageLoaded.value = true;
    
    console.log('Page setup complete');
});
</script>