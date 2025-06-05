<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout title="Supply Insights" description="All Key Metrics, One Place">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="p-4">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div v-for="card in $page.props.dashboardData.summary" :key="card.label" :class="`rounded-xl p-4 flex flex-col items-start shadow-sm bg-white border-t-4 border-${card.color}-400`">
                    <div class="text-xs text-gray-500 mb-2">{{ card.label }}</div>
                    <div class="flex items-end space-x-2">
                        <div class="text-2xl font-bold">{{ card.value.toLocaleString() }}</div>
                        <span class="text-sm text-gray-400">{{ card.unit }}</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ card.date }}</div>
                </div>
            </div>
            <!-- Charts Row (Bar & Area) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Bar Chart Placeholder -->
                <div class="bg-white rounded-xl p-4 shadow-sm flex flex-col justify-between">
                    <div class="h-48 flex items-end justify-around">
                        <div v-for="(bar, idx) in [20,40,60,20,70,70,40]" :key="idx" class="flex flex-col items-center w-10">
                            <div class="relative flex flex-col items-center">
                                <div class="w-6 h-24 bg-gray-100 rounded-full flex items-end">
                                    <div :style="`height: ${bar}%`" class="w-6 rounded-b-full bg-orange-400"></div>
                                </div>
                                <span class="absolute -top-6 text-xs font-bold text-orange-500">{{ bar }}%</span>
                            </div>
                            <span class="mt-2 text-[10px] text-gray-500 text-center">Label</span>
                        </div>
                    </div>
                </div>
                <!-- Area Chart Placeholder -->
                <div class="bg-white rounded-xl p-4 shadow-sm flex flex-col justify-between">
                    <svg viewBox="0 0 240 90" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-32">
                        <path d="M0 80 Q40 40 80 60 Q120 90 160 50 Q200 10 240 60 V90 H0 Z" fill="#6ee7b7" fill-opacity="0.6"/>
                        <circle cx="200" cy="15" r="8" fill="#34d399" stroke="#fff" stroke-width="3" />
                        <text x="190" y="10" font-size="10" fill="#059669">Green General Hospital</text>
                    </svg>
                </div>
            </div>
            <!-- Tasks & Status Progress List -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-r from-orange-400 to-pink-400 rounded-xl p-4 shadow-sm text-white">
                    <div class="font-bold text-lg mb-2">Tasks</div>
                    <ul class="list-disc pl-6">
                        <li v-for="task in $page.props.dashboardData.tasks" :key="task">{{ task }}</li>
                    </ul>
                    <div class="flex space-x-4 mt-4">
                        <button class="bg-white/20 px-4 py-2 rounded-lg font-semibold hover:bg-white/30">Go</button>
                        <button class="bg-white/20 px-4 py-2 rounded-lg font-semibold hover:bg-white/30">Go</button>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <div class="font-bold text-gray-700 mb-4">Order Status Progress</div>
                    <div class="flex flex-col gap-5">
                        <div v-for="status in [
                            { label: 'Approved', percent: 85, count: 53, color: 'blue', icon: '✅' },
                            { label: 'Rejected', percent: 3, count: 8, color: 'red', icon: '❌' },
                            { label: 'Pending Approval', percent: 12, count: 6, color: 'amber', icon: '⏳' },
                            { label: 'In Process', percent: 44, count: 43, color: 'gray', icon: '⚙️' },
                            { label: 'Dispatched', percent: 98, count: 735, color: 'purple', icon: '🚚' },
                            { label: 'Delivered', percent: 85, count: 725, color: 'green', icon: '📦' },
                        ]" :key="status.label" class="flex items-center gap-3">
                            <svg :width="48" :height="48" viewBox="0 0 48 48">
                                <circle cx="24" cy="24" r="20" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                                <circle
                                    :stroke="status.color === 'blue' ? '#3b82f6' : status.color === 'red' ? '#ef4444' : status.color === 'amber' ? '#f59e42' : status.color === 'gray' ? '#6b7280' : status.color === 'purple' ? '#8b5cf6' : '#22c55e'"
                                    cx="24" cy="24" r="20" fill="none" stroke-width="6" stroke-linecap="round"
                                    :stroke-dasharray="`${(status.percent/100)*125.6} 125.6`" transform="rotate(-90 24 24)"/>
                                <text x="24" y="28" text-anchor="middle" font-size="16" fill="#222">{{ status.percent }}%</text>
                            </svg>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">{{ status.icon }}</span>
                                <span class="font-bold text-lg">{{ status.count }}</span>
                                <span class="text-sm text-gray-600">{{ status.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
