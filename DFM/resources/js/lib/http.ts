function readCookie(name: string): string | null {
    const match = document.cookie.match(
        new RegExp('(^|;\\s*)' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'),
    );
    return match ? decodeURIComponent(match[2]) : null;
}

/**
 * Perform a JSON request against a same-origin endpoint, attaching the CSRF
 * token that Laravel's VerifyCsrfToken middleware exposes via the XSRF-TOKEN
 * cookie. Works for both the session-authenticated web routes and Sanctum SPA.
 */
export async function httpJson<T = any>(
    method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE',
    url: string,
    body?: unknown,
): Promise<T> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    const xsrf = readCookie('XSRF-TOKEN');
    if (xsrf) {
        headers['X-XSRF-TOKEN'] = xsrf;
    }

    const response = await fetch(url, {
        method,
        headers,
        credentials: 'same-origin',
        body: body === undefined ? undefined : JSON.stringify(body),
    });

    if (response.status === 204) {
        return undefined as T;
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        const message = data?.message ?? `Request failed (${response.status})`;
        throw new Error(message);
    }

    return data as T;
}
