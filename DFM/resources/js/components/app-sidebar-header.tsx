import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Bell, Clock, MapPin } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import ThemeToggle from '@/components/fuel-monitoring/ThemeToggle';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { site, unreadAlarmsCount } = usePage<{
        site?: { name: string; timezone: string };
        unreadAlarmsCount?: number;
    }>().props;

    const [currentTime, setCurrentTime] = useState<string>('');
    const [tzAbbr, setTzAbbr] = useState<string>('WIB');

    const timezone = site?.timezone || 'Asia/Jakarta';

    useEffect(() => {
        const updateClock = () => {
            const now = new Date();
            try {
                const formatted = new Intl.DateTimeFormat('id-ID', {
                    timeZone: timezone,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                }).format(now);

                let abbr = 'WIB';
                if (timezone.includes('Makassar') || timezone.includes('Ujung_Pandang')) abbr = 'WITA';
                else if (timezone.includes('Jayapura')) abbr = 'WIT';

                setCurrentTime(formatted);
                setTzAbbr(abbr);
            } catch {
                setCurrentTime(now.toLocaleTimeString());
            }
        };

        updateClock();
        const interval = setInterval(updateClock, 1000);
        return () => clearInterval(interval);
    }, [timezone]);

    return (
        <header className="border-sidebar-border/50 flex h-16 shrink-0 items-center justify-between border-b px-4 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-6">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <div className="flex items-center gap-3">
                {/* Site Name & Clock */}
                <div className="hidden items-center gap-3 rounded-lg border border-sidebar-border/40 bg-sidebar/50 px-3 py-1.5 text-xs text-muted-foreground sm:flex">
                    <div className="flex items-center gap-1.5 font-medium text-foreground">
                        <MapPin className="h-3.5 w-3.5 text-emerald-500" />
                        <span>{site?.name || 'Trissan Site'}</span>
                    </div>
                    <div className="h-3.5 w-px bg-border" />
                    <div className="flex items-center gap-1.5 font-mono">
                        <Clock className="h-3.5 w-3.5 text-blue-500" />
                        <span>{currentTime || '--:--:--'}</span>
                        <span className="font-semibold text-primary">{tzAbbr}</span>
                    </div>
                </div>

                {/* Alarm Notification Bell */}
                <Button variant="ghost" size="icon" asChild className="relative">
                    <Link href="/fuel-monitoring/alarms" title="View Alarm Logs">
                        <Bell className="h-4 w-4" />
                        {Number(unreadAlarmsCount) > 0 && (
                            <span className="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white animate-pulse">
                                {unreadAlarmsCount}
                            </span>
                        )}
                        <span className="sr-only">Notifications</span>
                    </Link>
                </Button>

                {/* Dark/Light Mode Toggle */}
                <ThemeToggle />
            </div>
        </header>
    );
}

