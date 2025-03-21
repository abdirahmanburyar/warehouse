<template>
    <div v-if="visible" class="splash-screen">
        <div class="splash-content">
            <div class="logo-container">
                <img src="/assets/images/logo.svg" alt="Logo" class="logo" />
            </div>
            <h1 class="title">Warehouse Management System</h1>
            <div class="loading-bar">
                <div class="loading-progress" :style="{ width: `${progress}%` }"></div>
            </div>
            <p class="loading-text">{{ loadingText }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    duration: {
        type: Number,
        default: 2000 // 2 seconds
    }
});

const emit = defineEmits(['complete']);

const visible = ref(true);
const progress = ref(0);
const loadingText = ref('Loading Warehouse Management System...');

let progressInterval;
let loadingTexts = [
    'Loading resources...', 
    'Preparing application...', 
    'Setting up warehouse data...',
    'Almost ready...'
];
let currentTextIndex = 0;

onMounted(() => {
    // Start progress animation
    const increment = 100 / (props.duration / 50); // Update every 50ms
    progressInterval = setInterval(() => {
        progress.value = Math.min(progress.value + increment, 100);
        
        // Update loading text periodically
        if (progress.value > 30 && progress.value < 60 && currentTextIndex === 0) {
            loadingText.value = loadingTexts[1];
            currentTextIndex = 1;
        } else if (progress.value > 60 && progress.value < 70 && currentTextIndex === 1) {
            loadingText.value = loadingTexts[2];
            currentTextIndex = 2;
        } else if (progress.value > 80 && progress.value < 90 && currentTextIndex === 2) {
            loadingText.value = loadingTexts[3];
            currentTextIndex = 3;
        }
        
        // Complete when progress reaches 100%
        if (progress.value >= 100) {
            clearInterval(progressInterval);
            setTimeout(() => {
                visible.value = false;
                emit('complete');
            }, 200);
        }
    }, 50);
});

onBeforeUnmount(() => {
    clearInterval(progressInterval);
});
</script>

<style scoped>
.splash-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #1a202c;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    overflow: hidden;
}

.splash-content {
    text-align: center;
    padding: 2rem;
    max-width: 500px;
    animation: fadeIn 0.5s ease-in-out;
}

.logo-container {
    margin-bottom: 2rem;
}

.logo {
    width: 150px;
    height: auto;
    animation: pulse 2s infinite;
}

.title {
    font-size: 2.5rem;
    font-weight: bold;
    color: white;
    margin-bottom: 2rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.loading-bar {
    height: 8px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.loading-progress {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5 0%, #6366f1 100%);
    border-radius: 4px;
    transition: width 0.1s ease-out;
}

.loading-text {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
    font-weight: 500;
    letter-spacing: 0.5px;
}

@keyframes pulse {
    0% {
        opacity: 0.6;
        transform: scale(0.98);
    }
    50% {
        opacity: 1;
        transform: scale(1.02);
    }
    100% {
        opacity: 0.6;
        transform: scale(0.98);
    }
}

@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 640px) {
    .title {
        font-size: 1.8rem;
    }
    
    .logo {
        width: 120px;
    }
    
    .splash-content {
        padding: 1.5rem;
    }
}
</style>
