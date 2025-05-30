import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
// Configure Pusher for real-time updates
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || '6f14b3912007b57c9ffa',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    disableStats: true,
    enableLogging: true // Enable Pusher debugging
});

// Log when Echo is ready
console.log('Echo initialized with config:', {
    key: import.meta.env.VITE_PUSHER_APP_KEY || '6f14b3912007b57c9ffa',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1'
});