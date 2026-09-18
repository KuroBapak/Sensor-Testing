interface AlarmTableProps {
    data: any[];
    isPulsing: boolean;
}

export default function AlarmTable({ data, isPulsing }: AlarmTableProps) {
    return (
        <section className={`bg-slate-50 dark:bg-slate-800 rounded-lg p-6 border-2 ${isPulsing ? 'border-red-600 animate-pulse' : 'border-slate-200 dark:border-slate-700'}`}>
            <h2 className="text-2xl font-bold mb-4 text-red-600 dark:text-red-400">Critical Alerts</h2>
            
            <div className="overflow-auto max-h-96 bg-white dark:bg-slate-950 rounded border border-slate-200 dark:border-slate-700">
                <table className="w-full text-sm font-mono">
                    <thead className="bg-red-100 dark:bg-red-900/50 sticky top-0">
                        <tr className="border-b border-red-200 dark:border-red-700">
                            <th className="p-3 text-left text-red-700 dark:text-red-300">RFID (Sektor)</th>
                            <th className="p-3 text-left text-red-700 dark:text-red-300">Waktu Kejadian</th>
                            <th className="p-3 text-right text-red-700 dark:text-red-300">Jumlah Liter</th>
                        </tr>
                    </thead>
                    <tbody>
                        {data.length === 0 ? (
                            <tr>
                                <td colSpan={3} className="p-6 text-center text-slate-500 dark:text-slate-400">
                                    No alarms detected
                                </td>
                            </tr>
                        ) : (
                            data.map((row, idx) => (
                                <tr 
                                    key={idx} 
                                    className={`border-b border-slate-200 dark:border-slate-800 ${idx === 0 ? 'bg-red-600 text-white font-bold' : 'hover:bg-slate-100 dark:hover:bg-slate-800/50'}`}
                                >
                                    <td className="p-3">{row.rfid}</td>
                                    <td className="p-3">{row.waktu_kejadian}</td>
                                    <td className="p-3 text-right">{row.jumlah_liter.toLocaleString()}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </section>
    );
}
