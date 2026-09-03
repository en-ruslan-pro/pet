import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const tvRoom = document.querySelector('[data-tv-room]');

if (tvRoom !== null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const status = tvRoom.querySelector('[data-tv-room-status]');
    const frame = tvRoom.querySelector('iframe');
    const reverb = JSON.parse(tvRoom.dataset.reverb ?? '{}');

    window.Pusher = Pusher;

    const echo = new Echo({
        broadcaster: 'reverb',
        key: reverb.appKey,
        wsHost: reverb.host,
        wsPort: reverb.port,
        wssPort: reverb.port,
        forceTLS: reverb.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
        },
    });

    const heartbeat = () => fetch(tvRoom.dataset.heartbeatUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken ?? '',
        },
    });

    echo.private(`room.${tvRoom.dataset.roomCode}`)
        .listen('.room.command.requested', (event) => {
            frame?.contentWindow?.postMessage({ action: event.action, petName: event.petName }, window.location.origin);

            if (status !== null) {
                status.textContent = event.action === 'meow' ? `${event.petName} мяукает` : 'Получена команда';
            }
        });

    heartbeat().then(() => {
        if (status !== null) {
            status.textContent = 'Телевизор подключён';
        }
    });
    window.setInterval(heartbeat, 10_000);
}
