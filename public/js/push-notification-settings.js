(() => {
    const panel = document.querySelector('[data-push-settings]');
    if (!panel) return;

    const button = panel.querySelector('[data-push-toggle]');
    const status = panel.querySelector('[data-push-status]');
    const deviceStatus = document.querySelector('[data-push-device-status]');
    const deviceList = document.querySelector('[data-push-device-list]');
    const csrfToken = panel.querySelector('input[name="_token"]')?.value;
    const publicKey = panel.dataset.publicKey;
    let registration;
    let subscription;

    const setState = (message, buttonText, disabled = false) => {
        status.textContent = message;
        button.textContent = buttonText;
        button.disabled = disabled;
    };

    function decodeApplicationServerKey(value) {
        const padding = '='.repeat((4 - value.length % 4) % 4);
        const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
        return Uint8Array.from(atob(base64), (character) => character.charCodeAt(0));
    }

    async function fingerprintEndpoint(endpoint) {
        const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(endpoint));
        return Array.from(new Uint8Array(digest), (byte) => byte.toString(16).padStart(2, '0')).join('');
    }

    function formatDate(value) {
        if (!value) return 'Not available';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? 'Not available' : date.toLocaleString();
    }

    async function loadDevices() {
        const response = await fetch(panel.dataset.devicesUrl, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) throw new Error('Could not load your devices. Try refreshing the page.');

        const { devices } = await response.json();
        const currentHash = subscription ? await fingerprintEndpoint(subscription.endpoint) : null;
        deviceList.replaceChildren();

        if (!devices.length) {
            deviceStatus.textContent = 'No notification devices are registered.';
            return;
        }

        deviceStatus.textContent = `${devices.length} ${devices.length === 1 ? 'device' : 'devices'} registered for notifications.`;

        devices.forEach((device) => {
            const isCurrent = device.endpoint_hash === currentHash;
            const row = document.createElement('li');
            row.className = 'push-device-row';

            const details = document.createElement('div');
            const name = document.createElement('p');
            name.className = 'push-device-name';
            name.textContent = device.device_name || 'Unknown browser/device';

            if (isCurrent) {
                const current = document.createElement('span');
                current.className = 'push-device-current';
                current.textContent = 'This device';
                name.append(current);
            }

            const meta = document.createElement('p');
            meta.className = 'push-device-meta';
            meta.textContent = `Registered ${formatDate(device.created_at)} · Last checked ${formatDate(device.last_seen_at)}`;
            details.append(name, meta);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'push-device-remove';
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', () => removeDevice(device, isCurrent, removeButton));
            row.append(details, removeButton);
            deviceList.append(row);
        });
    }

    async function removeDevice(device, isCurrent, removeButton) {
        if (typeof window.showConfirmation !== 'function') {
            deviceStatus.textContent = 'The confirmation dialog is unavailable. Refresh and try again.';
            return;
        }

        const confirmed = await window.showConfirmation(`Remove ${device.device_name} from your notification devices?`, {
            title: 'Remove device?',
            confirmText: 'Remove',
        });
        if (!confirmed) return;

        removeButton.disabled = true;
        try {
            const response = await fetch(`${panel.dataset.revokeUrl}/${encodeURIComponent(device.id)}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            if (!response.ok) throw new Error('Could not remove this device. Try again.');

            if (isCurrent && subscription) {
                await subscription.unsubscribe();
                subscription = null;
                setState('Notifications are off for this device.', 'Enable notifications');
            }

            await loadDevices();
        } catch (error) {
            removeButton.disabled = false;
            deviceStatus.textContent = error.message || 'Could not remove this device.';
        }
    }

    async function saveSubscription(currentSubscription) {
        const response = await fetch(panel.dataset.subscribeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(currentSubscription.toJSON()),
        });

        if (!response.ok) throw new Error('Could not save this device subscription. Try again.');
    }

    async function removeSubscription(currentSubscription) {
        const response = await fetch(panel.dataset.unsubscribeUrl, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ endpoint: currentSubscription.endpoint }),
        });

        if (!response.ok) throw new Error('Could not remove this device subscription. Try again.');
        await currentSubscription.unsubscribe();
    }

    async function initialize() {
        const pushSupported = 'serviceWorker' in navigator
            && 'PushManager' in window
            && 'Notification' in window;
        let initializationError = null;

        if (pushSupported) {
            try {
                registration = await navigator.serviceWorker.register('/service-worker.js');
                subscription = await registration.pushManager.getSubscription();
                if (subscription) await saveSubscription(subscription);
            } catch (error) {
                initializationError = error;
            }
        }

        try {
            await loadDevices();
        } catch (error) {
            deviceStatus.textContent = error.message || 'Could not load your devices.';
        }

        if (!pushSupported) {
            setState('Push notifications are not supported by this browser.', 'Unavailable', true);
        } else if (!publicKey) {
            setState('Push notifications are not configured on this server yet.', 'Unavailable', true);
        } else if (initializationError) {
            setState(initializationError.message || 'Could not initialize browser notifications.', 'Try again');
        } else if (subscription) {
            setState('This device is enabled for notifications.', 'Disable on this device');
        } else if (Notification.permission === 'denied') {
            setState('Notifications are blocked in this browser’s settings.', 'Blocked', true);
        } else {
            setState('Notifications are off for this device.', 'Enable notifications');
        }
    }

    button.addEventListener('click', async () => {
        button.disabled = true;

        try {
            if (subscription) {
                await removeSubscription(subscription);
                subscription = null;
                setState('Notifications are off for this device.', 'Enable notifications');
                await loadDevices();
                return;
            }

            const permission = Notification.permission === 'default'
                ? await Notification.requestPermission()
                : Notification.permission;

            if (permission !== 'granted') {
                setState('Allow notifications in your browser settings to enable them.', 'Enable notifications');
                return;
            }

            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: decodeApplicationServerKey(publicKey),
            });
            await saveSubscription(subscription);
            setState('This device is enabled for notifications.', 'Disable on this device');
            await loadDevices();
        } catch (error) {
            if (subscription) await subscription.unsubscribe().catch(() => {});
            subscription = null;
            setState(error.message || 'Could not update notification settings.', 'Try again');
        }
    });

    initialize();
})();