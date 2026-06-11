import axios from 'axios';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}

export async function registerPushNotifications() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    // Push subscriptions require HTTPS (except localhost)
    const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    if (!isSecure) {
        console.warn('Push notifications require HTTPS. Use ngrok or a real domain for testing on mobile.');
        return;
    }

    try {
        const registration = await navigator.serviceWorker.register('/sw.js');
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return;

        const { data } = await axios.get('/push/public-key');
        const applicationServerKey = urlBase64ToUint8Array(data.public_key);

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey,
        });

        const sub = subscription.toJSON();
        await axios.post('/push/subscribe', {
            endpoint:   sub.endpoint,
            p256dh_key: sub.keys.p256dh,
            auth_token: sub.keys.auth,
        });
    } catch (e) {
        console.warn('Push registration failed:', e);
    }
}
