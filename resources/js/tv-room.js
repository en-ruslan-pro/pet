import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const tvRoom = document.querySelector('[data-tv-room]');

if (tvRoom !== null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const status = tvRoom.querySelector('[data-tv-room-status]');
    const frame = tvRoom.querySelector('iframe');
    const reverb = JSON.parse(tvRoom.dataset.reverb ?? '{}');
    const character = JSON.parse(tvRoom.dataset.character ?? 'null');
    const petNeeds = JSON.parse(tvRoom.dataset.petNeeds ?? '{}');
    const sendToScene = (message) => frame?.contentWindow?.postMessage(message, window.location.origin);
    const request = (url, options = {}) => fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ?? '',
            ...options.headers,
        },
        ...options,
    });
    const clientSessionId = crypto.randomUUID();
    let viewSessionId;
    const executionIdsByToken = new Map();
    const pendingFinishTokens = new Set();

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

    const heartbeat = async () => {
        if (viewSessionId === undefined) {
            return request(tvRoom.dataset.heartbeatUrl);
        }

        const response = await request(tvRoom.dataset.sessionHeartbeatUrl.replace('__session__', String(viewSessionId)));

        if (response.ok) {
            const payload = await response.json();
            sendToScene({ action: 'sync-needs', needs: payload.needs });
        }

        return response;
    };

    echo.private(`room.${tvRoom.dataset.roomCode}`)
        .listen('.room.command.requested', (event) => {
            sendToScene({ action: event.action, executionId: event.executionId, petName: event.petName, needs: event.needs });

            if (status !== null) {
                status.textContent = event.action === 'meow' ? `${event.petName} мяукает` : 'Получена команда';
            }
        });

    frame?.addEventListener('load', () => {
        sendToScene({ action: 'sync-character', character });
        sendToScene({ action: 'sync-needs', needs: petNeeds });
    });

    window.addEventListener('message', async (event) => {
        if (event.origin !== window.location.origin || event.source !== frame?.contentWindow) {
            return;
        }

        if (event.data?.type === 'pet-action-start') {
            const executionId = event.data.executionId;
            const response = executionId === undefined
                ? await request(tvRoom.dataset.autonomousActionUrl, { body: JSON.stringify({ action: event.data.action }) })
                : await request(tvRoom.dataset.actionStartUrl.replace('__execution__', String(executionId)));

            if (! response.ok) {
                return;
            }

            const payload = await response.json();
            executionIdsByToken.set(event.data.token, payload.id);
            sendToScene({ type: 'pet-action-execution', token: event.data.token, executionId: payload.id });

            if (pendingFinishTokens.delete(event.data.token)) {
                const finishResponse = await request(tvRoom.dataset.actionFinishUrl.replace('__execution__', String(payload.id)));

                if (finishResponse.ok) {
                    const finishPayload = await finishResponse.json();
                    sendToScene({ action: 'sync-needs', needs: finishPayload.needs });
                }
            }
        }

        if (event.data?.type === 'pet-action-finish') {
            const executionId = event.data.executionId ?? executionIdsByToken.get(event.data.token);

            if (! Number.isInteger(executionId)) {
                pendingFinishTokens.add(event.data.token);

                return;
            }

            const response = await request(tvRoom.dataset.actionFinishUrl.replace('__execution__', String(executionId)));

            if (response.ok) {
                const payload = await response.json();
                sendToScene({ action: 'sync-needs', needs: payload.needs });
            }

            executionIdsByToken.delete(event.data.token);
        }
    });

    request(tvRoom.dataset.sessionStartUrl, { body: JSON.stringify({ client_session_id: clientSessionId }) }).then(async (response) => {
        if (response.ok) {
            viewSessionId = (await response.json()).id;
        }

        return heartbeat();
    }).then(() => {
        if (status !== null) {
            status.textContent = 'Телевизор подключён';
        }
    });
    window.setInterval(heartbeat, 10_000);

    window.addEventListener('pagehide', () => {
        if (viewSessionId === undefined) {
            return;
        }

        fetch(tvRoom.dataset.sessionEndUrl.replace('__session__', String(viewSessionId)), {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
        });
    });
}
