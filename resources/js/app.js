import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import SplashScreen from './Components/SplashScreen.vue';

// Set up global toast function
window.$showToast = null;

// Debug flag for permission events
window.debugPermissionEvents = false;

// Set up permission change listener
if (window.Echo) {
    console.log('🔄 Setting up Echo listeners for permissions channel');
    
    // Debug Pusher connection
    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log(`🔌 Pusher connection state changed from ${states.previous} to ${states.current}`);
    });
    
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ Pusher connected successfully');
    });
    
    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('❌ Pusher connection error:', err);
    });
    
    // Debug all events (global listener)
    window.Echo.connector.pusher.bind_global((event, data) => {
        console.log(`🌐 Global event received: ${event}`, data);
        
        // Special case: directly handle the permissions-changed event from global binding
        // This ensures we catch the event even if the channel listeners miss it
        if (event === 'permissions-changed' || event === 'client-permissions-changed') {
            console.log('🎯 Caught permissions-changed from global event binding');
            handlePermissionEvent(data);
        }
    });
    
    // Listen for our specific event
    const channel = window.Echo.channel('app-events');
    console.log('📡 Subscribed to channel:', 'app-events');
    
    // Try multiple event name formats to ensure we catch it
    channel.listen('permissions-changed', (event) => {
        console.log('🔔 Permission changed event received (permissions-changed):', event);
        handlePermissionEvent(event);
    });
    
    channel.listen('.permissions-changed', (event) => {
        console.log('🔔 Permission changed event received (.permissions-changed):', event);
        handlePermissionEvent(event);
    });
    
    channel.listen('GlobalPermissionChanged', (event) => {
        console.log('🔔 Permission changed event received (GlobalPermissionChanged):', event);
        handlePermissionEvent(event);
    });
    
    // Also listen on the App namespace format
    channel.listen('App\\Events\\GlobalPermissionChanged', (event) => {
        console.log('🔔 Permission changed event received (full class name):', event);
        handlePermissionEvent(event);
    });
    
    // Function to handle the permission event
    function handlePermissionEvent(event) {
        console.log('🔍 Processing permission event:', event);
        
        // Enable debug mode if we're on the test endpoint
        if (window.location.pathname === '/test-permission-event' || 
            window.location.href.includes('/test-permission-event')) {
            window.debugPermissionEvents = true;
            console.log('💡 Debug mode enabled for permission events');
        }
        
        // Ensure we have a valid event object
        if (!event || typeof event !== 'object') {
            console.error('❌ Invalid event object received:', event);
            return;
        }
        
        // Get the current user ID from Inertia props
        let currentUserId;
        
        // Try different ways to get the user ID
        if (window.Inertia && window.Inertia.page && window.Inertia.page.props && window.Inertia.page.props.auth) {
            currentUserId = window.Inertia.page.props.auth.user?.id;
        } else if (window.Laravel) {
            currentUserId = window.Laravel.user?.id;
        } else if (window.$page && window.$page.props && window.$page.props.auth) {
            currentUserId = window.$page.props.auth.user?.id;
        } else {
            // Try to get from document cookie as a last resort
            const userIdMatch = document.cookie.match(/user_id=(\d+)/);
            if (userIdMatch) {
                currentUserId = parseInt(userIdMatch[1]);
            }
        }
        
        // For testing purposes, if we're on the test endpoint, set a default user ID
        const isTestEndpoint = window.location.pathname === '/test-permission-event' || 
                              window.location.href.includes('/test-permission-event');
        
        if (isTestEndpoint) {
            console.log('🧪 Test endpoint detected, forcing reload regardless of user ID');
            // For test endpoint, we'll reload regardless of user ID match
            currentUserId = event.user_id; // Force a match
        }
        
        console.log('👤 Current user ID detection attempts:', {
            'window.Inertia?.page?.props?.auth?.user?.id': window.Inertia?.page?.props?.auth?.user?.id,
            'window.Laravel?.user?.id': window.Laravel?.user?.id,
            'window.$page?.props?.auth?.user?.id': window.$page?.props?.auth?.user?.id,
            'cookie user_id': document.cookie.match(/user_id=(\d+)/) ? document.cookie.match(/user_id=(\d+)/)[1] : undefined,
            'Final currentUserId': currentUserId,
            'Event user_id': event.user_id
        });
        
        // Reload if it's the current user OR if we're on the test endpoint OR if debug mode is enabled
        if ((currentUserId && event.user_id === currentUserId) || isTestEndpoint || window.debugPermissionEvents) {
            console.log('🔄 Permission change detected, reloading page...');
            console.log('💬 Reload reason:', {
                'currentUserMatch': (currentUserId && event.user_id === currentUserId),
                'isTestEndpoint': isTestEndpoint,
                'debugModeEnabled': window.debugPermissionEvents
            });
            
            // Show notification
            if (window.$showToast) {
                window.$showToast({
                    message: 'Your permissions have been updated. The page will reload to apply changes.',
                    type: 'info',
                    duration: 3000
                });
            } else {
                console.log('⚠️ $showToast not available, showing alert instead');
                alert('Your permissions have been updated. The page will reload to apply changes.');
            }
            
            // Reload the page after a short delay
            setTimeout(() => {
                console.log('🔄 Reloading page now...');
                window.location.reload();
            }, 3000);
        } else {
            console.log('ℹ️ Permission event ignored - not for current user', {
                'event.user_id': event.user_id,
                'currentUserId': currentUserId,
                'isTestEndpoint': isTestEndpoint,
                'debugModeEnabled': window.debugPermissionEvents
            });
        }
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Create a splash screen element
const splashElement = document.createElement('div');
splashElement.id = 'splash-container';
document.body.appendChild(splashElement);

// Mount splash screen
const splashApp = createApp(SplashScreen, {
    duration: 2500
});
const splashInstance = splashApp.mount('#splash-container');



// Create and mount the main application after splash completes
setTimeout(() => {
    createInertiaApp({
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue'),
            ),
        setup({ el, App, props, plugin }) {
            // Remove splash screen
            splashApp.unmount();
            document.body.removeChild(splashElement);
            
            const app = createApp({ render: () => h(App, props) });
            
            // Use the Toast plugin
            app.use(Toast, {
                position: "top-right",
                timeout: 5000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: true,
                closeButton: "button",
                icon: true,
                rtl: false,
                zIndex: 9999
            });
            
            // Assign the toast function to our global variable
            window.$showToast = (options) => {
                const { useToast } = Toast;
                const toast = useToast();
                toast(options.message, {
                    type: options.type || 'default',
                    timeout: options.duration || 5000
                });
            };
            
            return app
                .use(plugin)
                .use(ZiggyVue)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });
}, 2500); // Match the duration in the SplashScreen component
