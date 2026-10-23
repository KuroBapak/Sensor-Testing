/** Indonesian formats: numbers as 1.234,5 and dates as dd/mm/yyyy with a 24-hour clock. */

const numberFormats = new Map<number, Intl.NumberFormat>();

function numberFormat(digits: number): Intl.NumberFormat {
    let format = numberFormats.get(digits);
    if (!format) {
        format = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
        });
        numberFormats.set(digits, format);
    }

    return format;
}

/** 1234.5 becomes "1.234,5". */
export function formatNumber(value: number, digits = 1): string {
    return numberFormat(digits).format(value);
}

export function formatLiters(value: number | null | undefined): string {
    return value === null || value === undefined ? '–' : formatNumber(value, 1);
}

export function formatPercent(value: number): string {
    return formatNumber(value, 1);
}

type Part = 'day' | 'month' | 'year' | 'hour' | 'minute';

function parts(iso: string, timezone: string): Record<Part, string> {
    const formatted = new Intl.DateTimeFormat('id-ID', {
        timeZone: timezone,
        hourCycle: 'h23',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).formatToParts(new Date(iso));

    const result = {} as Record<Part, string>;
    for (const part of formatted) {
        if (part.type in { day: 1, month: 1, year: 1, hour: 1, minute: 1 }) {
            result[part.type as Part] = part.value;
        }
    }

    return result;
}

/** "22/10/2026" */
export function formatDate(iso: string, timezone: string): string {
    const p = parts(iso, timezone);

    return `${p.day}/${p.month}/${p.year}`;
}

/** "14:30" */
export function formatTime(iso: string, timezone: string): string {
    const p = parts(iso, timezone);

    return `${p.hour}:${p.minute}`;
}

/** "22/10/2026 14:30" */
export function formatDateTime(iso: string, timezone: string): string {
    return `${formatDate(iso, timezone)} ${formatTime(iso, timezone)}`;
}

/**
 * Last valid day of a temporary card, counting today as day one (the server
 * rule). Returned as dd/mm/yyyy in the given time zone.
 */
export function expiryDate(
    now: Date,
    validDays: number,
    timezone: string,
): string {
    const today = parts(now.toISOString(), timezone);
    const start = Date.UTC(
        Number(today.year),
        Number(today.month) - 1,
        Number(today.day),
    );
    const last = new Date(start + (validDays - 1) * 86_400_000);

    const day = String(last.getUTCDate()).padStart(2, '0');
    const month = String(last.getUTCMonth() + 1).padStart(2, '0');

    return `${day}/${month}/${last.getUTCFullYear()}`;
}

/** Today's date as YYYY-MM-DD in the given time zone, for date inputs. */
export function todayIso(timezone: string, now: Date = new Date()): string {
    return new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(now);
}

/** First day of the current month as YYYY-MM-DD in the given time zone. */
export function monthStartIso(
    timezone: string,
    now: Date = new Date(),
): string {
    return `${todayIso(timezone, now).slice(0, 7)}-01`;
}

const ZONE_LABELS: Record<string, string> = {
    'Asia/Jakarta': 'WIB',
    'Asia/Makassar': 'WITA',
    'Asia/Jayapura': 'WIT',
};

/** WIB, WITA or WIT for an Indonesian time zone; the zone name for anything else. */
export function zoneLabel(timezone: string): string {
    return ZONE_LABELS[timezone] ?? timezone;
}

/** The time of day as "17.52.21" (24-hour, dots as in Indonesian clocks). */
export function formatClock(now: Date, timezone: string): string {
    const clock = new Intl.DateTimeFormat('id-ID', {
        timeZone: timezone,
        hourCycle: 'h23',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    })
        .formatToParts(now)
        .filter((part) => ['hour', 'minute', 'second'].includes(part.type))
        .map((part) => part.value);

    return clock.join('.');
}
