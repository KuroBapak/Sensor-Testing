import { Head, useForm } from '@inertiajs/react';
import { FileSpreadsheet, Download } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useEffect } from 'react';

export default function Reports() {
    const reportOptions = [
        { value: 'main-tank-transactions', label: 'Main Tank - Transactions' },
        { value: 'main-tank-daily-reconciliation', label: 'Main Tank - Daily Reconciliation' },
        { value: 'browser-tank-refuels', label: 'Browser Tank - Refuels' },
        { value: 'browser-tank-daily-consumption', label: 'Browser Tank - Daily Consumption' },
        { value: 'fuel-tanker-unloads', label: 'Fuel Tanker - Unloads & Vendor Fills' },
        { value: 'alarm-log', label: 'Alarm Log' },
        { value: 'tank-level-readings', label: 'Tank Level Readings (Raw Data)' },
    ];

    const today = new Date().toISOString().split('T')[0];
    const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

    const { data, setData } = useForm({
        reportType: 'main-tank-transactions',
        from: thirtyDaysAgo,
        to: today,
        format: 'standard',
        tank: '',
    });

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const reportParam = params.get('report');
        if (reportParam && reportOptions.some(opt => opt.value === reportParam)) {
            setData('reportType', reportParam);
        }
    }, []);

    const handleExport = () => {
        const params = new URLSearchParams();
        params.append('from', data.from);
        params.append('to', data.to);
        params.append('format', data.format);
        if (data.reportType === 'tank-level-readings') {
            params.append('tank', data.tank || 'Main Tank 1');
        }
        const url = '/api/v1/reports/' + data.reportType + '/export.csv?' + params.toString();
        window.location.href = url;
    };

    const isExportDisabled = data.reportType === 'tank-level-readings' && !data.tank;

    return (
        <>
            <Head title="Export Reports" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-white dark:bg-slate-900 text-slate-900 dark:text-white min-h-screen">
                <header className="border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div className="flex items-center gap-3">
                        <FileSpreadsheet className="h-8 w-8 text-emerald-600 dark:text-emerald-400" />
                        <div>
                            <h1 className="text-3xl font-extrabold tracking-tight">Export Reports</h1>
                            <p className="text-slate-600 dark:text-slate-400 text-sm">Download CSV exports of fuel monitoring data</p>
                        </div>
                    </div>
                </header>

                <div className="max-w-2xl bg-slate-50 dark:bg-slate-800 rounded-lg p-6 border border-slate-200 dark:border-slate-700">
                    <div className="flex flex-col gap-5">
                        <div>
                            <label className="block text-sm font-medium mb-2">Report Type</label>
                            <select 
                                className="w-full rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                                value={data.reportType}
                                onChange={e => setData('reportType', e.target.value)}
                            >
                                {reportOptions.map(opt => (
                                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                                ))}
                            </select>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium mb-2">From Date</label>
                                <input 
                                    type="date"
                                    className="w-full rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                                    value={data.from}
                                    onChange={e => setData('from', e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-2">To Date</label>
                                <input 
                                    type="date"
                                    className="w-full rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                                    value={data.to}
                                    onChange={e => setData('to', e.target.value)}
                                />
                            </div>
                        </div>


                        {data.reportType === 'tank-level-readings' && (
                            <div className="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md p-4">
                                <label className="block text-sm font-medium mb-2">Tank Selection (Required)</label>
                                <select 
                                    className="w-full rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                                    value={data.tank}
                                    onChange={e => setData('tank', e.target.value)}
                                >
                                    <option value="">-- Select a Tank --</option>
                                    <option value="Main Tank 1">Main Tank 1</option>
                                </select>
                                <p className="text-xs text-slate-600 dark:text-slate-400 mt-2">Raw readings are limited to 31 days.</p>
                            </div>
                        )}

                        <div>
                            <label className="block text-sm font-medium mb-2">Export Format</label>
                            <div className="flex flex-col gap-2">
                                <label className="flex items-center gap-2 text-sm cursor-pointer">
                                    <input 
                                        type="radio" 
                                        name="format" 
                                        value="standard" 
                                        checked={data.format === 'standard'} 
                                        onChange={e => setData('format', e.target.value)}
                                    />
                                    <span>Standard CSV (comma decimal)</span>
                                </label>
                                <label className="flex items-center gap-2 text-sm cursor-pointer">
                                    <input 
                                        type="radio" 
                                        name="format" 
                                        value="excel_id" 
                                        checked={data.format === 'excel_id'} 
                                        onChange={e => setData('format', e.target.value)}
                                    />
                                    <span>Excel Indonesia (semicolon, comma decimal)</span>
                                </label>
                            </div>
                        </div>

                        <div className="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                            <Button 
                                onClick={handleExport}
                                disabled={isExportDisabled}
                                className="bg-emerald-600 hover:bg-emerald-700 text-white gap-2 disabled:opacity-50"
                            >
                                <Download className="h-4 w-4" />
                                Export CSV
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

Reports.layout = {
    breadcrumbs: [
        {
            title: 'Reports & Export',
            href: '/reports',
        },
    ],
};

