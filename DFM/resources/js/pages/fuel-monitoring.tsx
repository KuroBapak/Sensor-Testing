import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import MainTankChart from '@/components/fuel-monitoring/MainTankChart';
import MainTankTable from '@/components/fuel-monitoring/MainTankTable';
import MobileTankChart from '@/components/fuel-monitoring/MobileTankChart';
import MobileTankTable from '@/components/fuel-monitoring/MobileTankTable';
import AlarmTable from '@/components/fuel-monitoring/AlarmTable';
import {
    generateMainTankData,
    generateMobileTankData,
    generateAlarmData,
} from '@/lib/mock-data-generators';

export default function FuelMonitoring() {
    const [mainTankChartData, setMainTankChartData] = useState<any[]>([]);
    const [mainTankTableData, setMainTankTableData] = useState<any[]>([]);
    const [fuelTankerData, setFuelTankerData] = useState<any[]>([]);
    const [browserTankData, setBrowserTankData] = useState<any[]>([]);
    const [alarmData, setAlarmData] = useState<any[]>([]);
    const [newAlarmPulse, setNewAlarmPulse] = useState(false);

    useEffect(() => {
        const interval = setInterval(() => {
            const newData = generateMainTankData();
            setMainTankChartData((prev) => [...prev, newData].slice(-10));
            setMainTankTableData((prev) => [...prev, newData]);
        }, 3000);
        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        const interval = setInterval(() => {
            const fuel = generateMobileTankData('fuel_tanker');
            const browser = generateMobileTankData('browser');
            setFuelTankerData((prev) => [...prev, fuel].slice(-20));
            setBrowserTankData((prev) => [...prev, browser].slice(-20));
        }, 4000);
        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        const interval = setInterval(() => {
            if (Math.random() > 0.6) {
                const alarm = generateAlarmData();
                setAlarmData((prev) => [alarm, ...prev]);
                setNewAlarmPulse(true);
                setTimeout(() => setNewAlarmPulse(false), 3000);
            }
        }, 6000);
        return () => clearInterval(interval);
    }, []);

    return (
        <>
            <Head title="Fuel Monitoring Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-900 text-white min-h-screen">
                <header className="border-b border-slate-800 pb-4 flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-white">Fuel Monitoring System</h1>
                        <p className="text-slate-400 text-sm">Industrial IoT Real-Time Control & Telemetry</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="relative flex h-3 w-3">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span className="text-sm font-mono text-emerald-400">STREAMING</span>
                    </div>
                </header>

                <section className="bg-slate-800 rounded-lg p-6 border border-slate-700">
                    <h2 className="text-2xl font-bold mb-4 text-emerald-400">Main Tank Level Monitoring</h2>
                    <MainTankChart data={mainTankChartData} />
                    <MainTankTable data={mainTankTableData} />
                </section>

                <section className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="bg-slate-800 rounded-lg p-6 border border-slate-700">
                        <h3 className="text-xl font-bold mb-4 text-blue-400">Fuel Tanker</h3>
                        <MobileTankChart data={fuelTankerData} color="#3B82F6" />
                        <MobileTankTable data={fuelTankerData} headerColor="text-blue-400" />
                    </div>
                    <div className="bg-slate-800 rounded-lg p-6 border border-slate-700">
                        <h3 className="text-xl font-bold mb-4 text-purple-400">Browser Tank</h3>
                        <MobileTankChart data={browserTankData} color="#A855F7" />
                        <MobileTankTable data={browserTankData} headerColor="text-purple-400" />
                    </div>
                </section>

                <AlarmTable data={alarmData} isPulsing={newAlarmPulse} />
            </div>
        </>
    );
}
