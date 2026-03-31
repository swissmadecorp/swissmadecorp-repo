import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

/**
 * Testing Channels & Events & Connections
 */
// window.Echo.channel("products")
//  .listen("ProductUpdateEvent", (event) => {
//     alert(event);
// });

/**
 * Testing Private Channels & Events & Connections
 */
// window.Echo.private("message")
//  .listen(".new-message", (event) => {
//     alert(event);
// });

    // Example: In a Vue component

        window.Echo.channel('chat') // Or the specific private channel
            .listen('MessageRead', (e) => {
                // Update the UI to show the message as read
                // e.g., mark the message with e.messageId as read for e.userId
                console.log(`Message ${e.messageId} read by user ${e.userId}`);
                // Update your message list to reflect the read status
            });
