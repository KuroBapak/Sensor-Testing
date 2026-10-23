import { useId } from 'react';
import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import type { TooltipContentProps } from 'recharts';
import { LevelPatterns } from '@/components/tank/tank-gauge';
import { id } from '@/lang/id';
import { formatLiters, formatPercent, formatTime } from '@/lib/format';
import type { Reading } from '@/types/fuel';

type Props = {
    readings: Reading[];
    capacity: number;
    timezone: string;
};

type Point = Reading & { time: string };

function LevelTooltip({ active, payload }: TooltipContentProps) {
    if (!active || !payload?.length) {
        return null;
    }

    const point = payload[0].payload as Point;
    const rows = [
        [id.tank.fuel, point.fuel_pct, point.fuel_liters],
        [id.tank.water, point.water_pct, point.water_liters],
        [id.tank.empty, point.empty_pct, null],
    ] as const;

    return (
        <div className="rounded-md border bg-popover px-3 py-2 text-xs shadow-md">
            <div className="mb-1 font-medium">{point.time}</div>
            {rows.map(([label, pct, liters]) => (
                <div
                    key={label}
                    className="flex justify-between gap-4 tabular-nums"
                >
                    <span>{label}</span>
                    <span>
                        {formatPercent(pct)}%
                        {liters !== null &&
                            ` · ${formatLiters(liters)} ${id.common.litersUnit}`}
                    </span>
                </div>
            ))}
        </div>
    );
}

/** Stacked water / fuel / empty level over the last 24 hours. */
export function TankChart({ readings, timezone }: Props) {
    const prefix = useId().replace(/:/g, '');

    if (readings.length === 0) {
        return (
            <p className="py-10 text-center text-sm text-muted-foreground">
                {id.tank.chartEmpty}
            </p>
        );
    }

    const data: Point[] = readings.map((reading) => ({
        ...reading,
        time: formatTime(reading.read_at, timezone),
    }));

    return (
        <div aria-label={id.tank.chartTitle} role="img">
            <ResponsiveContainer width="100%" height={220}>
                <AreaChart
                    data={data}
                    margin={{ left: 0, right: 8, top: 8, bottom: 0 }}
                >
                    <LevelPatterns prefix={prefix} />
                    <CartesianGrid strokeDasharray="3 3" vertical={false} />
                    <XAxis
                        dataKey="time"
                        tickMargin={6}
                        minTickGap={24}
                        fontSize={11}
                    />
                    <YAxis
                        domain={[0, 100]}
                        ticks={[0, 25, 50, 75, 100]}
                        tickFormatter={(value: number) => `${value}%`}
                        width={42}
                        fontSize={11}
                    />
                    <Tooltip content={LevelTooltip} />
                    <Area
                        type="monotone"
                        dataKey="water_pct"
                        stackId="level"
                        stroke="#1d4ed8"
                        fill={`url(#${prefix}-water)`}
                        isAnimationActive={false}
                    />
                    <Area
                        type="monotone"
                        dataKey="fuel_pct"
                        stackId="level"
                        stroke="#d97706"
                        fill={`url(#${prefix}-fuel)`}
                        isAnimationActive={false}
                    />
                    <Area
                        type="monotone"
                        dataKey="empty_pct"
                        stackId="level"
                        stroke="#9ca3af"
                        fill={`url(#${prefix}-empty)`}
                        isAnimationActive={false}
                    />
                </AreaChart>
            </ResponsiveContainer>
        </div>
    );
}
