import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import AlarmTable, { type AlarmRow } from '@/components/fuel-monitoring/AlarmTable';
import ThemeToggle from '@/components/fuel-monitoring/ThemeToggle';
import { Button } from '@/components/ui/button';
import { Download } from 'lucide-react';
import { usePermission } from '@/hooks/use-permission';
import { subscribe, isRealtimeEnabled } from '@/lib/echo';
import { httpJson } from '@/lib/http';

type StatusValue = 'open' | 'investigating' | 'resolved' | 'false_positive';

interface Props {
    initialAlarmData?: AlarmRow[];
}

function formatTime(iso?: string): string {
    if (!iso) return '';
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? iso : d.toLocaleString();
}

export default function AlarmsMonitoring({ initialAlarmData = [] }: Props) {
    const [alarms, setAlarms] = useState<AlarmRow[]>(initialAlarmData);
    const [connected, setConnected] = useState(isRealtimeEnabled());
    const canManage = usePermission('alarms.manage');

    useEffect(() => {
        setAlarms(initialAlarmData);
    }, [initialAlarmData]);

    // Live anomaly insertion over WebSocket (no polling).
    useEffect(() => {
        return subscribe('trissan.alarms', 'anomaly-detected', (payload: Partial<AlarmRow> & { anomaly_time?: string }) => {
            setConnected(true);
            if (payload.id == null) return;
            setAlarms((current) => {
                if (current.some((a) => a.id === payload.id)) return current;
                const row: AlarmRow = {
                    id: payload.id as number,
                    rfid: (payload as any).rfid ?? '-',
                    waktu_kejadian: payload.anomaly_time ? formatTime(payload.anomaly_time) : formatTime(payload.waktu_kejadian),
                    jumlah_liter: Number(Math.abs(Number(payload.jumlah_liter ?? 0))),
                    anomaly_type: payload.anomaly_type,
                    tank_name: payload.tank_name ?? null,
                    status: payload.status ?? 'open',
                    resolved_by: payload.resolved_by ?? null,
                    is_read: false,
                };
                return [row, ...current];
            });
            toast.error(`🚨 Anomali terdeteksi: ${(payload.anomaly_type ?? 'fuel').replace(/_/g, ' ')}`);
        });
    }, []);

    const handleMarkRead = async (id: number) => {
        await httpJson('POST', `/fuel-monitoring/alarms/${id}/read`);
        setAlarms((current) =>
            current.map((a) =>
                a.id === id
                    ? { ...a, is_read: true, status: a.status === 'open' ? 'investigating' : a.status }
                    : a,
            ),
        );
        router.reload({ only: ['initialAlarmData'] });
    };

    const handleStatusChange = async (id: number, status: StatusValue, resolutionNote?: string) => {
        await httpJson('PUT', `/fuel-monitoring/alarms/${id}/status`, { status, resolution_note: resolutionNote });
        setAlarms((current) => current.map((a) => (a.id === id ? { ...a, status, is_read: true } : a)));
        router.reload({ only: ['initialAlarmData'] });
    };

    return (
        <>
            <Head title="Fuel Theft Alarm Log" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-slate-900 text-slate-900 dark:text-white min-h-screen">
                <header className="border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-red-600 dark:text-red-400">🚨 Alarm Log Pencurian Bahan Bakar</h1>
                        <p className="text-slate-600 dark:text-slate-400 text-sm">Critical Security Monitoring & Theft Detection</p>
                    </div>
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <span className="relative flex h-3 w-3">
                                <span className={`animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75 ${connected ? '' : 'hidden'}`}></span>
                                <span className="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                            <span className="text-sm font-mono text-red-600 dark:text-red-400">
                                {connected ? 'LIVE' : 'STANDBY'}
                            </span>
                        </div>
                        <Link href="/reports?report=alarm-log">
                            <Button variant="outline" size="sm" className="gap-2">
                                <Download className="h-4 w-4" />
                                Export Alarm Log
                            </Button>
                        </Link>
                        <ThemeToggle />
                    </div>
                </header>

                <AlarmTable
                    data={alarms}
                    isPulsing={connected}
                    canManage={canManage}
                    onMarkRead={handleMarkRead}
                    onStatusChange={handleStatusChange}
                />
            </div>
        </>
    );
}
