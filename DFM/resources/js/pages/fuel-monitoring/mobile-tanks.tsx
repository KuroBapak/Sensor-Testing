import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import MobileTankChart from '@/components/fuel-monitoring/MobileTankChart';
import MobileTankTable from '@/components/fuel-monitoring/MobileTankTable';
import ThemeToggle from '@/components/fuel-monitoring/ThemeToggle';
import { Button } from '@/components/ui/button';
import { Download } from 'lucide-react';
import { subscribe, isRealtimeEnabled } from '@/lib/echo';

interface TankData {
    tank_id: number;
    name: string;
    capacity: number;
    current_level: number;
    level_chart_data: Array<{ waktu: string; liter: number }>;
    transactions: Array<{
        tank_type: string;
        rfid: string;
        waktu: string;
        liter: number;
    }>;
}

interface Props {
    browserTanks?: TankData[];
    fuelTankers?: TankData[];
}

export default function MobileTanksMonitoring({ browserTanks = [], fuelTankers = [] }: Props) {
    const [connected] = useState(isRealtimeEnabled());

    useEffect(() => {
        return subscribe('trissan.live.mobile-tanks', 'tank-reading', () => {
            router.reload({ only: ['browserTanks', 'fuelTankers'] });
        });
    }, []);

    const renderTankList = (tanks: TankData[], color: string, headerColor: string, title: string, emptyMsg: string) => (
        <div className="space-y-4">
            <h2 className={`text-xl font-bold ${headerColor} border-b pb-2`}>
                {title} ({tanks.length})
            </h2>
            {tanks.length === 0 ? (
                <div className="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg text-slate-500 text-sm">{emptyMsg}</div>
            ) : (
                tanks.map((tank) => (
                    <div key={tank.tank_id} className="bg-slate-50 dark:bg-slate-800 rounded-lg p-4 border border-slate-200 dark:border-slate-700 space-y-3">
                        <div className="flex justify-between items-center">
                            <div>
                                <h3 className="font-bold text-slate-800 dark:text-slate-100">{tank.name}</h3>
                                <p className="text-xs text-slate-500">Cap: {tank.capacity?.toLocaleString()} L</p>
                            </div>
                            <div className="text-right">
                                <span className={`text-xl font-mono font-bold ${headerColor}`}>
                                    {tank.current_level?.toLocaleString()} L
                                </span>
                            </div>
                        </div>
                        <MobileTankChart data={tank.level_chart_data} color={color} />
                        <MobileTankTable data={tank.transactions} headerColor={headerColor} />
                    </div>
                ))
            )}
        </div>
    );

    return (
        <>
            <Head title="Mobile Tanks Monitoring" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-slate-900 text-slate-900 dark:text-white min-h-screen">
                <header className="border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight">Mobile Tanks Monitoring</h1>
                        <p className="text-slate-600 dark:text-slate-400 text-sm">Real-Time Level & Transactions for Mobile Units</p>
                    </div>
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <span className="relative flex h-3 w-3">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span className="text-sm font-mono text-emerald-600 dark:text-emerald-400">{connected ? 'LIVE' : 'STANDBY'}</span>
                        </div>
                        <Link href="/reports?report=browser-tank-refuels">
                            <Button variant="outline" size="sm" className="gap-2">
                                <Download className="h-4 w-4" />
                                Export CSV
                            </Button>
                        </Link>
                        <ThemeToggle />
                    </div>
                </header>

                <section className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {renderTankList(fuelTankers, '#3B82F6', 'text-blue-600 dark:text-blue-400', 'Fuel Tankers', 'No Fuel Tankers registered')}
                    {renderTankList(browserTanks, '#A855F7', 'text-purple-600 dark:text-purple-400', 'Browser Tanks', 'No Browser Tanks registered')}
                </section>
            </div>
        </>
    );
}

