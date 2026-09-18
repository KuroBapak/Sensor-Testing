import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AlarmTable from '@/components/fuel-monitoring/AlarmTable';
import ThemeToggle from '@/components/fuel-monitoring/ThemeToggle';

interface AlarmLog {
    id: number;
    rfid: string;
    waktu_kejadian: string;
    jumlah_liter: number;
}

interface Props {
    initialAlarmData?: AlarmLog[];
}

export default function AlarmsMonitoring({ initialAlarmData = [] }: Props) {
    const [alarmData] = useState<AlarmLog[]>(initialAlarmData);

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
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                            <span className="text-sm font-mono text-red-600 dark:text-red-400">MONITORING</span>
                        </div>
                        <ThemeToggle />
                    </div>
                </header>

                {/* Provide false since we aren't dynamically injecting new alarms continuously for now */}
                <AlarmTable data={alarmData} isPulsing={false} />
            </div>
        </>
    );
}
