import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface MainTankChartProps {
    data: any[];
}

export default function MainTankChart({ data }: MainTankChartProps) {
    return (
        <div className="mb-6 bg-white dark:bg-slate-950 p-4 rounded border border-slate-200 dark:border-slate-700">
            <ResponsiveContainer width="100%" height={300}>
                <BarChart data={data}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#94a3b8" className="dark:stroke-slate-700" />
                    <XAxis dataKey="waktu" stroke="#64748b" />
                    <YAxis stroke="#64748b" label={{ value: 'Volume (Liters)', angle: -90, position: 'insideLeft', fill: '#64748b' }} />
                    <Tooltip 
                        contentStyle={{ 
                            backgroundColor: 'rgb(248 250 252)', 
                            border: '1px solid #e2e8f0', 
                            borderRadius: '0.5rem',
                            color: '#0f172a'
                        }} 
                        wrapperClassName="dark:[&_.recharts-tooltip-wrapper]:!bg-slate-800 dark:[&_.recharts-tooltip-wrapper]:!border-slate-700 dark:[&_.recharts-tooltip-wrapper]:!text-white"
                    />
                    <Legend />
                    <Bar dataKey="liter_masuk" fill="#10B981" name="Liter Masuk" />
                    <Bar dataKey="liter_keluar" fill="#F59E0B" name="Liter Keluar" />
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}
