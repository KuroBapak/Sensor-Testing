import { Head } from '@inertiajs/react';
import { useState } from 'react';
import MobileTankChart from '@/components/fuel-monitoring/MobileTankChart';
import MobileTankTable from '@/components/fuel-monitoring/MobileTankTable';
import ThemeToggle from '@/components/fuel-monitoring/ThemeToggle';

interface MobileTankLog {
    id: number;
    tank_type: string;
    rfid: string;
    waktu: string;
    liter: number;
}

interface Props {
    initialFuelTankerData?: MobileTankLog[];
    initialBrowserTankData?: MobileTankLog[];
}

export default function MobileTanksMonitoring({ initialFuelTankerData = [], initialBrowserTankData = [] }: Props) {
    const [fuelTankerData] = useState<MobileTankLog[]>(initialFuelTankerData);
    const [browserTankData] = useState<MobileTankLog[]>(initialBrowserTankData);

    return (
        <>
            <Head title="Mobile Tanks Monitoring" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-slate-900 text-slate-900 dark:text-white min-h-screen">
                <header className="border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight">Mobile Tanks Level Monitoring</h1>
                        <p className="text-slate-600 dark:text-slate-400 text-sm">Browser Tank & Fuel Tanker Real-Time Data</p>
                    </div>
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <span className="relative flex h-3 w-3">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span className="text-sm font-mono text-emerald-600 dark:text-emerald-400">LIVE</span>
                        </div>
                        <ThemeToggle />
                    </div>
                </header>

                <section className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="bg-slate-50 dark:bg-slate-800 rounded-lg p-6 border border-slate-200 dark:border-slate-700">
                        <h3 className="text-xl font-bold mb-4 text-blue-600 dark:text-blue-400">Fuel Tanker</h3>
                        <MobileTankChart data={fuelTankerData} color="#3B82F6" />
                        <MobileTankTable data={fuelTankerData} headerColor="text-blue-600 dark:text-blue-400" />
                    </div>
                    <div className="bg-slate-50 dark:bg-slate-800 rounded-lg p-6 border border-slate-200 dark:border-slate-700">
                        <h3 className="text-xl font-bold mb-4 text-purple-600 dark:text-purple-400">Browser Tank</h3>
                        <MobileTankChart data={browserTankData} color="#A855F7" />
                        <MobileTankTable data={browserTankData} headerColor="text-purple-600 dark:text-purple-400" />
                    </div>
                </section>
            </div>
        </>
    );
}
