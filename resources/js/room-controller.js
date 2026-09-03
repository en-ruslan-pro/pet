const controller = document.querySelector('[data-room-controller]');

if (controller !== null) {
    const status = controller.querySelector('[data-tv-status]');
    const statusDot = status?.querySelector('span');
    const commandStatus = controller.querySelector('[data-command-status]');
    const meowButton = controller.querySelector('[data-meow-button]');
    const actionButtons = controller.querySelectorAll('[data-pet-action]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const request = async (url, options = {}) => fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken ?? '',
            ...options.headers,
        },
        ...options,
    });

    const updateTvStatus = async () => {
        const response = await request(controller.dataset.statusUrl);

        if (!response.ok || status === null || statusDot === null) {
            return;
        }

        const { connected, needs } = await response.json();
        statusDot.className = `h-2.5 w-2.5 rounded-full ${connected ? 'bg-emerald-400' : 'bg-stone-500'}`;
        status.lastChild.textContent = connected ? 'Телевизор подключён' : 'Телевизор не подключён';
        updateNeeds(needs);
    };

    const updateNeeds = (needs) => {
        if (needs === undefined) {
            return;
        }

        Object.entries(needs).forEach(([need, value]) => {
            const valueElement = controller.querySelector(`[data-need-value="${need}"]`);
            const bar = controller.querySelector(`[data-need-bar="${need}"]`);

            if (valueElement !== null) {
                valueElement.textContent = String(value);
            }

            if (bar !== null) {
                bar.style.width = `${value}%`;
            }
        });
    };

    meowButton?.addEventListener('click', async () => {
        meowButton.disabled = true;

        try {
            const response = await request(controller.dataset.meowUrl, { method: 'POST' });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error('Не удалось отправить команду.');
            }

            if (commandStatus !== null) {
                commandStatus.textContent = payload.message;
            }
        } catch (error) {
            if (commandStatus !== null) {
                commandStatus.textContent = error.message;
            }
        } finally {
            meowButton.disabled = false;
        }
    });

    actionButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const action = button.dataset.petAction;

            if (action === undefined) {
                return;
            }

            button.disabled = true;

            try {
                const response = await request(controller.dataset.actionsUrl.replace('__action__', action), { method: 'POST' });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error('Не удалось отправить команду.');
                }

                updateNeeds(payload.needs);
                commandStatus.textContent = payload.message;
            } catch (error) {
                commandStatus.textContent = error.message;
            } finally {
                button.disabled = false;
            }
        });
    });

    updateTvStatus();
    window.setInterval(updateTvStatus, 5_000);
}
