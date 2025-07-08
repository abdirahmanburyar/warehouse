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

// Add error handling and initialization checks
window.EchoReady = false;

if (window.Echo && window.Echo.connector) {
    // Wait for connector to be ready
    const checkConnector = () => {
        if (window.Echo.connector.connection) {
            console.log('Echo connector ready');
            window.EchoReady = true;
            
            // Add error handling for connection
            if (window.Echo.connector.connection.on) {
                window.Echo.connector.connection.on('error', (error) => {
                    console.error('Echo connection error:', error);
                });
                
                window.Echo.connector.connection.on('connected', () => {
                    console.log('Echo connected successfully');
                });
                
                window.Echo.connector.connection.on('disconnected', () => {
                    console.log('Echo disconnected');
                });
            }
        } else {
            // Retry after a short delay
            setTimeout(checkConnector, 100);
        }
    };
    
    checkConnector();
}

// Add a global function to safely access Echo
window.getEcho = () => {
    if (window.EchoReady && window.Echo && window.Echo.connector && window.Echo.connector.connection) {
        return window.Echo;
    }
    return null;
};

// Add safety check for bind method to prevent errors
const originalBind = Function.prototype.bind;
Function.prototype.bind = function(...args) {
    if (this === undefined || this === null) {
        console.warn('Attempted to bind undefined/null function');
        return function() {};
    }
    return originalBind.apply(this, args);
};

// Log when Echo is ready
console.log('Echo initialized with Reverb config:', {
    key: import.meta.env.VITE_REVERB_APP_KEY || 'warehouse-key',
    host: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    port: import.meta.env.VITE_REVERB_PORT || 443,
    appId: import.meta.env.VITE_REVERB_APP_ID || 'warehouse-app'
});