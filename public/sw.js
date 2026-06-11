self.addEventListener('push', function (event) {
    let data = { title: 'New Message', body: 'You have a new message', url: '/' };
    try { data = event.data.json(); } catch (e) {}

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/images/other_user.jpg',
            badge: '/images/other_user.jpg',
            data: { url: data.url },
            vibrate: [200, 100, 200],
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (const client of clientList) {
                if (client.url.includes('/chat') && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(event.notification.data.url || '/chat');
        })
    );
});
