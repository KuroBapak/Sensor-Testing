import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo<any> | null;
    }
}

/**
 * Laravel Echo client configured for Reverb (default) or Pusher-compatible
 * broadcasters. When no broadcaster is configured (e.g. `log` driver in dev),
 * this returns null and callers should fall back to a periodic refresh rather
 * than crashing the page.
 */
let echoInstance: Echo<any> | null | undefined;

const driver = (import.meta.env.VITE_BROADCAST_CONNECTION ?? 'log') as string;
const appKey = import.meta.env.VITE_REVERB_APP_KEY ?? import.meta.env.VITE_PUSHER_APP_KEY;

export function getEcho(): Echo<any> | null {
    if (echoInstance !== undefined) {
        return echoInstance;
    }

    if (driver === 'reverb' && appKey) {
        window.Pusher = Pusher;

        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: appKey,
            wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
            wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
            wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } else if (driver === 'pusher' && appKey) {
        window.Pusher = Pusher;

        echoInstance = new Echo({
            broadcaster: 'pusher',
            key: appKey,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
            wsHost: import.meta.env.VITE_PUSHER_HOST,
            wsPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 443),
            wssPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 443),
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } else {
        echoInstance = null;
    }

    return echoInstance;
}

/** Whether real-time WebSocket broadcasting is available in this build. */
export function isRealtimeEnabled(): boolean {
    return getEcho() !== null;
}

/**
 * Subscribe to a private channel, returning an unsubscribe function.
 * No-ops (returns a noop) when realtime is disabled so callers can safely
 * fall back to periodic refresh.
 */
export function subscribe(
    channel: string,
    event: string,
    handler: (payload: any) => void,
): () => void {
    const echo = getEcho();
    if (!echo) {
        return () => {};
    }

    const presence = echo.private(channel);
    // Laravel's broadcastAs() prefixes the socket event name with a leading dot.
    presence.listen(`.${event}`, (e: any) => handler(e));

    return () => {
        echo.leave(channel);
    };
}
