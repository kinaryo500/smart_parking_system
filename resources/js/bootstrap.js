import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const pusherConfig = {
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'ap1',
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
};

if (import.meta.env.VITE_PUSHER_HOST) {
    pusherConfig.wsHost = import.meta.env.VITE_PUSHER_HOST;
    pusherConfig.wsPort = import.meta.env.VITE_PUSHER_PORT ?? 443;
    pusherConfig.wssPort = import.meta.env.VITE_PUSHER_PORT ?? 443;
}

window.Echo = new Echo(pusherConfig);