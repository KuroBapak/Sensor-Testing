import { Clock } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useSite } from '@/hooks/use-site';
import { id } from '@/lang/id';
import { formatClock, zoneLabel } from '@/lib/format';

/** The time at the site, ticking every second, with the zone name (WIB, WITA or WIT). */
export function LiveClock() {
    const { timezone } = useSite();
    const [now, setNow] = useState(() => new Date());

    useEffect(() => {
        const timer = setInterval(() => setNow(new Date()), 1000);

        return () => clearInterval(timer);
    }, []);

    return (
        <div
            className="hidden items-center gap-2 rounded-md border px-2.5 py-1.5 text-xs md:flex"
            title={id.header.clock}
            data-test="live-clock"
        >
            <Clock className="size-3.5 text-sky-600 dark:text-sky-400" />
            <span className="font-mono tabular-nums">
                {formatClock(now, timezone)}
            </span>
            <span className="font-mono font-bold">{zoneLabel(timezone)}</span>
        </div>
    );
}
