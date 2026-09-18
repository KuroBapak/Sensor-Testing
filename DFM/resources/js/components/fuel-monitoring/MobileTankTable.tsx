interface MobileTankTableProps {
    data: any[];
    headerColor: string;
}

export default function MobileTankTable({ data, headerColor }: MobileTankTableProps) {
    return (
        <div className="overflow-auto max-h-48 bg-white dark:bg-slate-950 rounded border border-slate-200 dark:border-slate-700">
            <table className="w-full text-sm font-mono">
                <thead className="bg-slate-100 dark:bg-slate-800 sticky top-0">
                    <tr className="border-b border-slate-200 dark:border-slate-700">
                        <th className={`p-2 text-left ${headerColor}`}>RFID (Sektor)</th>
                        <th className={`p-2 text-left ${headerColor}`}>Waktu</th>
                        <th className={`p-2 text-right ${headerColor}`}>Liter</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((row, idx) => (
                        <tr key={idx} className="border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td className="p-2">{row.rfid}</td>
                            <td className="p-2">{row.waktu}</td>
                            <td className="p-2 text-right">{row.liter.toLocaleString()}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
