<template>
    <div v-if="visible" class="splash-screen">
        <!-- Main content container -->
        <div class="content-wrapper">
            <!-- Animated Snake Icon -->
            <div class="snake-container">
                <svg class="snake-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Staff/Rod -->
                    <path d="M12 2V22" stroke="white" stroke-width="2" stroke-linecap="round" class="staff"/>
                    
                    <!-- Left Snake -->
                    <path d="M12 4C10 4 8 6 8 8C8 10 10 12 12 12C14 12 16 10 16 8C16 6 14 4 12 4" 
                          stroke="white" stroke-width="2" fill="none" class="snake-left"/>
                    <path d="M12 12C10 12 8 14 8 16C8 18 10 20 12 20C14 20 16 18 16 16C16 14 14 12 12 12" 
                          stroke="white" stroke-width="2" fill="none" class="snake-left"/>
                    
                    <!-- Right Snake -->
                    <path d="M12 4C14 4 16 6 16 8C16 10 14 12 12 12C10 12 8 10 8 8C8 6 10 4 12 4" 
                          stroke="white" stroke-width="2" fill="none" class="snake-right"/>
                    <path d="M12 12C14 12 16 14 16 16C16 18 14 20 12 20C10 20 8 18 8 16C8 14 10 12 12 12" 
                          stroke="white" stroke-width="2" fill="none" class="snake-right"/>
                    
                    <!-- Snake Heads -->
                    <circle cx="8" cy="8" r="1.5" fill="white" class="snake-head-left"/>
                    <circle cx="16" cy="8" r="1.5" fill="white" class="snake-head-right"/>
                    <circle cx="8" cy="16" r="1.5" fill="white" class="snake-head-left"/>
                    <circle cx="16" cy="16" r="1.5" fill="white" class="snake-head-right"/>
                    
                    <!-- Wings (Caduceus) -->
                    <path d="M12 2C10 2 8 3 8 4C8 5 10 6 12 6C14 6 16 5 16 4C16 3 14 2 12 2" 
                          stroke="white" stroke-width="1.5" fill="none" class="wings"/>
                    <path d="M12 2C10 2 8 1 8 0C8 -1 10 -2 12 -2C14 -2 16 -1 16 0C16 1 14 2 12 2" 
                          stroke="white" stroke-width="1.5" fill="none" class="wings"/>
                </svg>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    duration: {
        type: Number,
        default: 3500 // 3.5 seconds
    }
});

const emit = defineEmits(['complete']);

const visible = ref(true);

onMounted(() => {
    // Complete after duration
    setTimeout(() => {
        visible.value = false;
        emit('complete');
    }, props.duration);
});
</script>

<style scoped>
.splash-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #FFD700 0%, #32CD32 50%, #228B22 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0.5;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.content-wrapper {
    text-align: center;
    animation: fadeIn 0.8s ease-out;
}

.snake-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.snake-icon {
    width: 120px;
    height: 120px;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

.staff {
    animation: staffGlow 2s ease-in-out infinite;
}

.snake-left {
    animation: snakeLeftMove 3s ease-in-out infinite;
    stroke-dasharray: 20;
    stroke-dashoffset: 0;
}

.snake-right {
    animation: snakeRightMove 3s ease-in-out infinite;
    stroke-dasharray: 20;
    stroke-dashoffset: 0;
}

.snake-head-left {
    animation: snakeHeadPulse 2s ease-in-out infinite;
}

.snake-head-right {
    animation: snakeHeadPulse 2s ease-in-out infinite 0.5s;
}

.wings {
    animation: wingsFlutter 1.5s ease-in-out infinite;
}

/* Snake Icon Animations */
@keyframes staffGlow {
    0%, 100% {
        filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.5));
    }
    50% {
        filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.8));
    }
}

@keyframes snakeLeftMove {
    0%, 100% {
        stroke-dashoffset: 0;
        transform: scale(1);
    }
    25% {
        stroke-dashoffset: -5;
        transform: scale(1.02);
    }
    50% {
        stroke-dashoffset: -10;
        transform: scale(1);
    }
    75% {
        stroke-dashoffset: -5;
        transform: scale(1.02);
    }
}

@keyframes snakeRightMove {
    0%, 100% {
        stroke-dashoffset: 0;
        transform: scale(1);
    }
    25% {
        stroke-dashoffset: 5;
        transform: scale(1.02);
    }
    50% {
        stroke-dashoffset: 10;
        transform: scale(1);
    }
    75% {
        stroke-dashoffset: 5;
        transform: scale(1.02);
    }
}

@keyframes snakeHeadPulse {
    0%, 100% {
        opacity: 0.8;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.2);
    }
}

@keyframes wingsFlutter {
    0%, 100% {
        opacity: 0.7;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
}

@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: scale(0.8);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .snake-icon {
        width: 100px;
        height: 100px;
    }
}

@media (max-width: 480px) {
    .snake-icon {
        width: 80px;
        height: 80px;
    }
}
</style>
