import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

interface MobileTankChartProps {
    data: any[];
    color: string;
}

export default function MobileTankChart({ data, color }: MobileTankChartProps) {
    return (
        <div className="mb-4 bg-white dark:bg-slate-950 p-4 rounded border border-slate-200 dark:border-slate-700">
            <ResponsiveContainer width="100%" height={200}>
                <AreaChart data={data}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#94a3b8" className="dark:stroke-slate-700" />
                    <XAxis dataKey="waktu" stroke="#64748b" tick={{ fontSize: 10 }} />
                    <YAxis stroke="#64748b" />
                    <Tooltip 
                        contentStyle={{ 
                            backgroundColor: 'rgb(248 250 252)', 
                            border: '1px solid #e2e8f0', 
                            borderRadius: '0.5rem',
                            color: '#0f172a'
                        }} 
                        wrapperClassName="dark:[&_.recharts-tooltip-wrapper]:!bg-slate-800 dark:[&_.recharts-tooltip-wrapper]:!border-slate-700 dark:[&_.recharts-tooltip-wrapper]:!text-white"
                    />
                    <Area type="monotone" dataKey="liter" stroke={color} fill={color} fillOpacity={0.6} />
                </AreaChart>
            </ResponsiveContainer>
        </div>
    );
}
