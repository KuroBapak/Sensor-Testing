import { usePage } from '@inertiajs/react';
import { formatDate, formatDateTime, formatTime } from '@/lib/format';
import type { Site } from '@/types/fuel';

const FALLBACK: Site = {
    name: null,
    timezone: 'Asia/Jakarta',
    resetPeriod: 'day',
    resetUnit: 'hari',
};

/** The shared site settings plus date formatters bound to the site time zone. */
export function useSite() {
    const site = usePage().props.site ?? FALLBACK;

    return {
        ...site,
        date: (iso: string) => formatDate(iso, site.timezone),
        time: (iso: string) => formatTime(iso, site.timezone),
        dateTime: (iso: string) => formatDateTime(iso, site.timezone),
    };
}
