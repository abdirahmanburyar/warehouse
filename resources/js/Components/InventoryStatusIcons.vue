<template>
    <div class="bg-white rounded-lg p-6">
        
        <!-- In Stock -->
        <div class="flex items-center mb-6">
            <div class="w-16 h-16 flex items-center justify-center mr-4">
                <img src="/assets/images/in_stock.png" alt="In Stock">
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800">
                    {{ statusCounts.find(s => s.status === 'in_stock')?.count || 0 }}
                </div>
                <div class="text-base text-gray-600">In Stock</div>
            </div>
        </div>
        
        <!-- Low Stock -->
        <div class="flex items-center mb-6">
            <div class="w-16 h-16 flex items-center justify-center mr-4">
                <img src="/assets/images/low_stock.png" alt="Low Stock">
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800">
                    {{ statusCounts.find(s => s.status === 'low_stock')?.count || 0 }}
                </div>
                <div class="text-base text-gray-600">Low Stock</div>
            </div>
        </div>
        
        <!-- Out of Stock -->
        <div class="flex items-center mb-6">
            <div class="w-16 h-16 flex items-center justify-center mr-4">
                <img src="/assets/images/out_stock.png" alt="Out of Stock">
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-800">
                    {{ statusCounts.find(s => s.status === 'out_of_stock')?.count || 0 }}
                </div>
                <div class="text-base text-gray-600">Out of Stock</div>
            </div>
        </div>
        
        <!-- Expired Stock -->
        <div class="flex items-center">
            <div class="w-16 h-16 flex items-center justify-center mr-4">
                <img src="/assets/images/expired_stock.png" alt="Expired Stock">
            </div>
            <div>
                <div class="text-2xl font-bold text-red-800">
                    {{ statusCounts.find(s => s.status === 'expired')?.count || 0 }}
                </div>
                <div class="text-base text-gray-600">Expired Stock</div>
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
