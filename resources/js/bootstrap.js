import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Configure Reverb for real-time updates
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'warehouse-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 443,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: true,
    enabledTransports: ['wss'],
    authEndpoint: '/broadcasting/auth',
    disableStats: true,
    enableLogging: true,
    // Reverb specific options
    reverb: {
        appId: import.meta.env.VITE_REVERB_APP_ID || 'warehouse-app',
        appKey: import.meta.env.VITE_REVERB_APP_KEY || 'warehouse-key',
        appSecret: import.meta.env.VITE_REVERB_APP_SECRET || 'warehouse-secret',
    }
});

// Log when Echo is ready
console.log('Echo initialized with Reverb config:', {
    key: import.meta.env.VITE_REVERB_APP_KEY || 'warehouse-key',
    host: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    port: import.meta.env.VITE_REVERB_PORT || 443,
    appId: import.meta.env.VITE_REVERB_APP_ID || 'warehouse-app'
});

// Add error handling with proper initialization checks
function setupEchoEventHandlers() {
    if (window.Echo && window.Echo.connector && window.Echo.connector.connection) {
        // Add error handling
        window.Echo.connector.connection.on('error', (error) => {
            console.error('Echo connection error:', error);
        });

        window.Echo.connector.connection.on('connected', () => {
            console.log('Echo connected successfully');
        });

        window.Echo.connector.connection.on('disconnected', () => {
            console.log('Echo disconnected');
        });

        window.Echo.connector.connection.on('connecting', () => {
            console.log('Echo connecting...');
        });

        console.log('Echo event handlers set up successfully');
    } else {
        console.warn('Echo connector not ready, retrying in 1 second...');
        setTimeout(setupEchoEventHandlers, 1000);
    }
}

// Wait for Echo to be fully initialized
setTimeout(setupEchoEventHandlers, 100);

// Also try to set up handlers when the page loads
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(setupEchoEventHandlers, 500);
});

// Fallback for immediate setup
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupEchoEventHandlers);
} else {
    setupEchoEventHandlers();
}