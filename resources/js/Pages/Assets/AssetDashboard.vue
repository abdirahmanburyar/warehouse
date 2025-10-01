<template>
    <Head title="Asset Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Asset Dashboard</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center h-64">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                </div>

                <!-- Dashboard Content -->
                <div v-else class="space-y-6">
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Total Assets</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ assetStatsData.total_assets || 0 }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Active Assets</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ assetStatsData.active_assets || 0 }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Approval</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ assetStatsData.pending_approval || 0 }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Disposed Assets</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ assetStatsData.disposed_assets || 0 }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Asset Status Chart -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Asset Status Distribution</h3>
                                <div class="h-64">
                                    <DoughnutChart :data="assetStatusChartData" :options="chartOptions" />
                                </div>
                            </div>
                        </div>

                        <!-- Asset Categories Chart -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Asset Categories</h3>
                                <div class="h-64">
                                    <BarChart :data="assetCategoryChartData" :options="chartOptions" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Assets and Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Assets -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Recent Assets</h3>
                                    <Link :href="route('assets.index')" class="text-sm text-blue-600 hover:text-blue-500">
                                        View All
                                    </Link>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="asset in recentAssets" :key="asset.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ asset.asset_name }}</p>
                                                <p class="text-xs text-gray-500">{{ asset.asset_tag }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded-full" :class="getStatusClass(asset.status)">
                                            {{ asset.status }}
                                        </span>
                                    </div>
                                    <div v-if="!recentAssets || recentAssets.length === 0" class="text-center text-gray-500 py-4">
                                        No recent assets found
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                            <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                                        </div>
                                    </div>
                                    <div v-if="!recentActivity || recentActivity.length === 0" class="text-center text-gray-500 py-4">
                                        No recent activity found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <Link 
                                    v-if="userPermissions.includes('asset-create')"
                                    :href="route('assets.get-create')" 
                                    class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
                                >
                                    <div class="text-center">
                                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-700">Create Asset</p>
                                    </div>
                                </Link>

                                <Link 
                                    v-if="userPermissions.includes('asset-view')"
                                    :href="route('assets.index')" 
                                    class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors"
                                >
                                    <div class="text-center">
                                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-700">View Assets</p>
                                    </div>
                                </Link>

                                <Link 
                                    v-if="userPermissions.includes('asset-approve')"
                                    :href="route('assets.approvals')" 
                                    class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors"
                                >
                                    <div class="text-center">
                                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-700">Pending Approvals</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(ChartDataLabels);

const props = defineProps({
    assetStats: {
        type: Object,
        required: true,
    },
    userPermissions: {
        type: Array,
        required: true,
    },
    recentAssets: {
        type: Array,
        default: () => [],
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const assetStatsData = ref(props.assetStats);

// Chart data for asset status distribution
const assetStatusChartData = computed(() => {
    const stats = assetStatsData.value;
    return {
        labels: ['Active', 'Inactive', 'Pending', 'Disposed'],
        datasets: [{
            data: [
                stats.active_assets || 0,
                stats.inactive_assets || 0,
                stats.pending_approval || 0,
                stats.disposed_assets || 0
            ],
            backgroundColor: [
                '#10B981', // Green for active
                '#6B7280', // Gray for inactive
                '#F59E0B', // Yellow for pending
                '#EF4444'  // Red for disposed
            ],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    };
});

// Chart data for asset categories
const assetCategoryChartData = computed(() => {
    const categories = assetStatsData.value.asset_categories || [];
    return {
        labels: categories.map(cat => cat.name),
        datasets: [{
            label: 'Assets',
            data: categories.map(cat => cat.count),
            backgroundColor: generateCategoryColors(categories.length),
            borderWidth: 1,
            borderColor: '#e5e7eb'
        }]
    };
});

// Chart options
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
        datalabels: {
            display: true,
            color: '#ffffff',
            font: {
                weight: 'bold'
            }
        }
    }
};

// Generate colors for categories
function generateCategoryColors(count) {
    const colors = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
    ];
    return Array.from({ length: count }, (_, i) => colors[i % colors.length]);
}

// Get status class for styling
function getStatusClass(status) {
    const statusClasses = {
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-gray-100 text-gray-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'disposed': 'bg-red-100 text-red-800'
    };
    return statusClasses[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
}

// Format date for display
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

onMounted(() => {
    // Any initialization logic can go here
});
</script>