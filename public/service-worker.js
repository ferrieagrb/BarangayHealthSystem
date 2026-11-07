self.addEventListener('push', (event) => {
    let payload = {};

    if (event.data) {
        try {
            payload = event.data.json();
        } catch {
            payload = { body: event.data.text() };
        }
    }

    event.waitUntil(self.registration.showNotification(payload.title || 'Barangay Health Update', {
        body: payload.body || 'A new announcement is available. Sign in to view it.',
        icon: '/images/amuyong.png',
        badge: '/images/amuyong.png',
        tag: payload.tag || 'barangay-health-update',
        data: { url: payload.url || '/' },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const target = new URL(event.notification.data?.url || '/', self.location.origin);
    if (target.origin !== self.location.origin) return;

    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
        const existingClient = clients.find((client) => new URL(client.url).origin === target.origin);

        if (existingClient) {
            return existingClient.focus().then(() => existingClient.navigate(target.href));
        }

        return self.clients.openWindow(target.href);
    }));
});