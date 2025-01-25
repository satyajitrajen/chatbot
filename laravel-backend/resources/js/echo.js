import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Pusher.logToConsole = true;

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '5952bd2c8029334f47cd',
    cluster: 'ap2',
    forceTLS: true,
});
