import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import MainTankChart from '@/components/fuel-monitoring/MainTankChart';
import MainTankTable from '@/components/fuel-monitoring/MainTankTable';
import ThemeToggle from '@/components/fuel-monitoring/ThemeToggle';
import { Button } from '@/components/ui/button';
import { Download } from 'lucide-react';

interface MainTankLog {
    id: number;
    waktu: string;
    total_liter: number;
    liter_masuk: number;
    liter_keluar: number;
}

interface Props {
    initialChartData?: MainTankLog[];
    initialTableData?: MainTankLog[];
}

export default function MainTankMonitoring({ initialChartData = [], initialTableData = [] }: Props) {
    const [chartData] = useState<MainTankLog[]>(initialChartData);
    const [tableData] = useState<MainTankLog[]>(initialTableData);

    return (
        <>
            <Head title="Main Tank Level Monitoring" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-slate-900 text-slate-900 dark:text-white min-h-screen">
                <header className="border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight">Main Tank Level Monitoring</h1>
                        <p className="text-slate-600 dark:text-slate-400 text-sm">Real-Time Fuel Inflow & Outflow Tracking</p>
                    </div>
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <span className="relative flex h-3 w-3">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span className="text-sm font-mono text-emerald-600 dark:text-emerald-400">LIVE</span>
                        </div>
                        <Link href="/reports?report=main-tank-transactions">
                            <Button variant="outline" size="sm" className="gap-2">
                                <Download className="h-4 w-4" />
                                Export CSV
                            </Button>
                        </Link>
                        <ThemeToggle />
                    </div>
                </header>

                <section className="bg-slate-50 dark:bg-slate-800 rounded-lg p-6 border border-slate-200 dark:border-slate-700">
                    <h2 className="text-2xl font-bold mb-4 text-emerald-600 dark:text-emerald-400">Volume Chart</h2>
                    <MainTankChart data={chartData} />
                    <MainTankTable data={tableData} />
                </section>
            </div>
        </>
    );
}
