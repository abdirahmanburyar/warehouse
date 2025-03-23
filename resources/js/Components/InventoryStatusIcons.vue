<template>
    <div class="bg-white rounded-lg p-4">
        <h3 class="font-medium text-gray-700 mb-3">Inventory Status</h3>
        
        <!-- In Stock -->
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center mr-3">
                <img src="/assets/images/in_stock.png" alt="In Stock" style="width: 100%; height: 100%;">
            </div>
            <div>
                <div class="text-lg font-bold text-gray-800">
                    {{ statusCounts.find(s => s.status === 'in_stock')?.count || 0 }}
                </div>
                <div class="text-sm text-gray-600">In Stock</div>
            </div>
        </div>
        
        <!-- Low Stock -->
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mr-3">
                <img src="/assets/images/low_stock.png" alt="Low Stock" style="width: 100%; height: 100%;">
            </div>
            <div>
                <div class="text-lg font-bold text-gray-800">
                    {{ statusCounts.find(s => s.status === 'low_stock')?.count || 0 }}
                </div>
                <div class="text-sm text-gray-600">Low Stock</div>
            </div>
        </div>
        
        <!-- Out of Stock -->
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mr-3">
                <img src="/assets/images/out_stock.png" alt="Out of Stock" style="width: 100%; height: 100%;">
            </div>
            <div>
                <div class="text-lg font-bold text-gray-800">
                    {{ statusCounts.find(s => s.status === 'out_of_stock')?.count || 0 }}
                </div>
                <div class="text-sm text-gray-600">Out of Stock</div>
            </div>
        </div>
        
        <!-- Expired Stock -->
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-danger-500 flex items-center justify-center mr-3">
                <img src="/assets/images/expired_stock.png" alt="Expired Stock" style="width: 100%; height: 100%;">
            </div>
            <div>
                <div class="text-lg font-bold text-danger-800">
                    {{ statusCounts.find(s => s.status === 'expired')?.count || 0 }}
                </div>
                <div class="text-sm text-gray-600">Expired Stock</div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    statusCounts: {
        type: Array,
        required: true
    }
});
</script>
