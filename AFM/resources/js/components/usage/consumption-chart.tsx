import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import type { TooltipContentProps } from 'recharts';
import { fill, id } from '@/lang/id';
import { formatLiters } from '@/lib/format';
import type { Category, ResetPeriod, SeriesPoint } from '@/types/fuel';

type Props = {
    side: Category;
    period: ResetPeriod;
    points: SeriesPoint[];
};

const CURRENT = '#d97706';
const EARLIER = '#fcd34d';

function BarTooltip({ active, payload }: TooltipContentProps) {
    if (!active || !payload?.length) {
        return null;
    }

    const point = payload[0].payload as SeriesPoint;

    return (
        <div className="rounded-md border bg-popover px-3 py-2 text-xs shadow-md">
            <div className="font-medium">{point.label}</div>
            <div className="tabular-nums">
                {formatLiters(point.liters)} {id.common.litersUnit}
            </div>
        </div>
    );
}

/**
 * Consumption of one side over the last days, weeks or months. The last bar is the
 * current period and matches the total shown above it.
 */
export function ConsumptionChart({ side, period, points }: Props) {
    const hasUsage = points.some((point) => point.liters > 0);

    if (!hasUsage) {
        return (
            <p className="py-10 text-center text-sm text-muted-foreground">
                {id.usage.chartEmpty}
            </p>
        );
    }

    return (
        <div data-test="consumption-chart">
            <div
                role="img"
                aria-label={fill(id.usage.chartLabel, {
                    side: id.categories[side],
                    unit: id.periods[period].toLowerCase(),
                })}
            >
                <ResponsiveContainer width="100%" height={240}>
                    <BarChart
                        data={points}
                        margin={{ left: 0, right: 8, top: 8, bottom: 0 }}
                    >
                        <CartesianGrid strokeDasharray="3 3" vertical={false} />
                        <XAxis dataKey="label" fontSize={11} tickMargin={6} />
                        <YAxis width={52} fontSize={11} />
                        <Tooltip
                            content={BarTooltip}
                            cursor={{ fill: 'rgba(0,0,0,0.05)' }}
                        />
                        <Bar
                            dataKey="liters"
                            radius={[3, 3, 0, 0]}
                            isAnimationActive={false}
                        >
                            {points.map((point, index) => (
                                <Cell
                                    key={point.start}
                                    fill={
                                        index === points.length - 1
                                            ? CURRENT
                                            : EARLIER
                                    }
                                />
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            </div>

            <ul className="mt-2 flex flex-wrap gap-4 text-xs text-muted-foreground">
                <li className="flex items-center gap-1.5">
                    <span
                        className="size-3 rounded-sm"
                        style={{ background: CURRENT }}
                    />
                    {id.usage.currentPeriod}
                </li>
                <li className="flex items-center gap-1.5">
                    <span
                        className="size-3 rounded-sm"
                        style={{ background: EARLIER }}
                    />
                    {id.usage.earlierPeriods}
                </li>
            </ul>
        </div>
    );
}
