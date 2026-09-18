import { useRef, useEffect } from 'react';

interface MainTankTableProps {
    data: any[];
}

export default function MainTankTable({ data }: MainTankTableProps) {
    const tableRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (tableRef.current) {
            tableRef.current.scrollTop = tableRef.current.scrollHeight;
        }
    }, [data]);

    return (
        <div 
            ref={tableRef}
            className="overflow-auto max-h-64 bg-white dark:bg-slate-950 rounded border border-slate-200 dark:border-slate-700"
        >
            <table className="w-full text-sm font-mono">
                <thead className="bg-slate-100 dark:bg-slate-800 sticky top-0">
                    <tr className="border-b border-slate-200 dark:border-slate-700">
                        <th className="p-3 text-left text-emerald-700 dark:text-emerald-400">Waktu</th>
                        <th className="p-3 text-right text-emerald-700 dark:text-emerald-400">Total Liter</th>
                        <th className="p-3 text-right text-emerald-700 dark:text-emerald-400">Liter Masuk</th>
                        <th className="p-3 text-right text-emerald-700 dark:text-emerald-400">Liter Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((row, idx) => (
                        <tr key={idx} className="border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td className="p-3">{row.waktu}</td>
                            <td className="p-3 text-right">{row.total_liter.toLocaleString()}</td>
                            <td className="p-3 text-right text-green-600 dark:text-green-400">{row.liter_masuk.toLocaleString()}</td>
                            <td className="p-3 text-right text-orange-600 dark:text-orange-400">{row.liter_keluar.toLocaleString()}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
