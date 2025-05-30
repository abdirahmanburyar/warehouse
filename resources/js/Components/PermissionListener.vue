<template>
    <!-- This component doesn't render anything, it just listens for permission changes -->
    <div class="debug-info" v-if="debug">
        <div class="p-4 bg-gray-100 rounded shadow">
            <h3 class="font-bold">Permission Listener Debug</h3>
            <p>User ID: {{ userId }}</p>
            <p>Channel: {{ `user.${userId}` }}</p>
            <p>Status: {{ connectionStatus }}</p>
            <p>Last Event: {{ lastEvent ? JSON.stringify(lastEvent) : 'None' }}</p>
            <button @click="testEvent" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                Test Event
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        userId: {
            type: Number,
            required: true
        },
        debug: {
            type: Boolean,
            default: false
        }
    },
    
    data() {
        return {
            connectionStatus: 'Initializing...',
            lastEvent: null,
            channel: null
        };
    },
    
    mounted() {
        console.log('PermissionListener mounted for user:', this.userId);
        console.log('Echo instance available:', !!window.Echo);
        console.log('Pusher instance available:', !!window.Pusher);
        
        // Add a small delay to ensure Echo is fully initialized
        setTimeout(() => {
            this.setupPermissionListener();
            this.setupDebugListeners();
        }, 500);
    },
    
    beforeUnmount() {
        // Clean up listeners when component is destroyed
        if (this.channel) {
            console.log('Leaving channel:', `user.${this.userId}`);
            this.channel.stopListening('.permission.changed');
            window.Echo.leave(`user.${this.userId}`);
        }
    },
    
    methods: {
        processPermissionEvent(event) {
            // Show a notification to the user
            this.showNotification(event);
            
            // Check if we need to reload permissions or log out
            this.handlePermissionChange(event);
        },
        
        testEvent() {
            console.log('Simulating permission event');
            const testEvent = {
                user_id: this.userId,
                permission: 'test.permission',
                action: 'added',
                changed_by: 'Debug Tool',
                timestamp: new Date().toISOString()
            };
            
            this.lastEvent = testEvent;
            this.processPermissionEvent(testEvent);
            
            // Also try to whisper to the channel for testing
            if (this.channel) {
                try {
                    this.channel.whisper('debug', { message: 'Testing channel communication' });
                } catch (error) {
                    console.error('Error sending whisper:', error);
                }
            }
        },
        
        setupPermissionListener() {
            try {
                console.log('Setting up permission listener for channel:', `user.${this.userId}`);
                
                // Listen for permission changes on the user's private channel
                this.channel = window.Echo.private(`user.${this.userId}`);
                
                // Try multiple formats of the event name to ensure we catch it
                this.channel.listen('.permission.changed', (event) => {
                    console.log('🔔 Permission changed event received (.permission.changed):', event);
                    this.lastEvent = event;
                    this.processPermissionEvent(event);
                });
                
                this.channel.listen('permission.changed', (event) => {
                    console.log('🔔 Permission changed event received (permission.changed):', event);
                    this.lastEvent = event;
                    this.processPermissionEvent(event);
                });
                
                // Also listen for any event on this channel for debugging
                this.channel.listen('App\\Events\\UserPermissionChanged', (event) => {
                    console.log('🔔 Permission changed event received (full class name):', event);
                    this.lastEvent = event;
                    this.processPermissionEvent(event);
                });
                
                // Add a global listener for any event
                window.Echo.connector.pusher.bind('pusher:event', (event) => {
                    console.log('Pusher event received:', event);
                });
                
                // Add a test button to the debug panel
                if (this.debug) {
                    window.testPermissionEvent = () => this.testEvent();
                    console.log('Test function added. Call window.testPermissionEvent() to simulate an event.');
                }
                
                console.log('Successfully set up listener for .permission.changed event');
                this.connectionStatus = 'Connected and listening';
            } catch (error) {
                console.error('Error setting up permission listener:', error);
                this.connectionStatus = `Error: ${error.message}`;
            }
        },
        
        setupDebugListeners() {
            if (!window.Echo) {
                console.error('Echo is not available');
                return;
            }
            
            // Debug Pusher connection states
            const pusher = window.Echo.connector.pusher;
            
            pusher.connection.bind('connecting', () => {
                console.log('Pusher connecting...');
                this.connectionStatus = 'Connecting...';
            });
            
            pusher.connection.bind('connected', () => {
                console.log('Pusher connected!');
                this.connectionStatus = 'Connected';
            });
            
            pusher.connection.bind('disconnected', () => {
                console.log('Pusher disconnected');
                this.connectionStatus = 'Disconnected';
            });
            
            pusher.connection.bind('error', (err) => {
                console.error('Pusher connection error:', err);
                this.connectionStatus = `Error: ${err.message}`;
            });
            
            // Debug channel events
            if (this.channel) {
                this.channel.listenForWhisper('debug', (data) => {
                    console.log('Debug whisper received:', data);
                });
            }
        },
        
        showNotification(event) {
            // Create a notification message based on the event
            const action = event.action === 'added' ? 'granted' : 'revoked';
            const message = `Your permission "${event.permission}" has been ${action} by ${event.changed_by}.`;
            
            console.log('Showing notification:', message);
            
            // Use your app's notification system (this example assumes you have a global notification function)
            if (window.$showToast) {
                window.$showToast({
                    message: message,
                    type: 'warning',
                    duration: 8000
                });
            } else {
                // Fallback to alert if no toast system is available
                alert(message + ' The page will reload to apply these changes.');
            }
        },
        
        handlePermissionChange(event) {
            console.log('Handling permission change, will reload in 5 seconds');
            
            // Wait a moment to show the notification before reloading
            setTimeout(() => {
                console.log('Reloading page to refresh permissions');
                window.location.reload();
            }, 5000);
        }
    }
}
</script>

<style scoped>
.debug-info {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
    font-size: 12px;
}
</style>
