import { useCallback, useEffect, useState } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import type { TooltipContentProps } from 'recharts';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useSite } from '@/hooks/use-site';
import { id } from '@/lang/id';
import { formatLiters } from '@/lib/format';
import { show } from '@/routes/usage';
import type { ResetPeriod, SeriesPoint, UsageDetail } from '@/types/fuel';

type Props = {
    cardId: number | null;
    plate: string;
    onClose: () => void;
};

type State =
    | { status: 'loading' }
    | { status: 'error' }
    | { status: 'ready'; detail: UsageDetail };

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

/** Side panel with one vehicle's fuel history: a graph and the recent fuelings. */
export function VehicleDetail({ cardId, plate, onClose }: Props) {
    const site = useSite();
    const [state, setState] = useState<State>({ status: 'loading' });
    const [granularity, setGranularity] = useState<ResetPeriod>('day');
    const [attempt, setAttempt] = useState(0);

    const retry = useCallback(() => setAttempt((value) => value + 1), []);

    useEffect(() => {
        if (cardId === null) {
            return;
        }

        const controller = new AbortController();
        setState({ status: 'loading' });

        fetch(show.url(cardId), {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(String(response.status));
                }

                return response.json() as Promise<UsageDetail>;
            })
            .then((detail) => setState({ status: 'ready', detail }))
            .catch((error: unknown) => {
                if ((error as Error).name !== 'AbortError') {
                    setState({ status: 'error' });
                }
            });

        return () => controller.abort();
    }, [cardId, attempt]);

    const series =
        state.status === 'ready' ? state.detail.series[granularity] : [];
    const hasUsage = series.some((point) => point.liters > 0);

    return (
        <Sheet
            open={cardId !== null}
            onOpenChange={(open) => !open && onClose()}
        >
            <SheetContent
                className="w-full overflow-y-auto sm:max-w-lg"
                data-test="vehicle-detail"
            >
                <SheetHeader>
                    <SheetTitle>
                        {id.usage.detail.title} · {plate}
                    </SheetTitle>
                    <SheetDescription>
                        {state.status === 'ready' &&
                            `${state.detail.card.vehicle_type} · ${id.categories[state.detail.card.category]}`}
                    </SheetDescription>
                </SheetHeader>

                <div className="space-y-6 px-4 pb-6">
                    {state.status === 'loading' && (
                        <div className="space-y-3" aria-busy="true">
                            <Skeleton className="h-8 w-48" />
                            <Skeleton className="h-52 w-full" />
                            <Skeleton className="h-24 w-full" />
                        </div>
                    )}

                    {state.status === 'error' && (
                        <div className="space-y-3 py-6 text-center">
                            <p className="text-sm text-muted-foreground">
                                {id.usage.detail.failed}
                            </p>
                            <Button variant="outline" size="sm" onClick={retry}>
                                {id.common.retry}
                            </Button>
                        </div>
                    )}

                    {state.status === 'ready' && (
                        <>
                            <div className="space-y-3">
                                <div className="flex items-center justify-between gap-2">
                                    <h3 className="text-sm font-medium">
                                        {id.usage.detail.graph}
                                    </h3>
                                    <ToggleGroup
                                        type="single"
                                        variant="outline"
                                        size="sm"
                                        value={granularity}
                                        onValueChange={(value) =>
                                            value &&
                                            setGranularity(value as ResetPeriod)
                                        }
                                    >
                                        {(
                                            ['day', 'week', 'month'] as const
                                        ).map((period) => (
                                            <ToggleGroupItem
                                                key={period}
                                                value={period}
                                            >
                                                {id.periods[period]}
                                            </ToggleGroupItem>
                                        ))}
                                    </ToggleGroup>
                                </div>

                                {hasUsage ? (
                                    <ResponsiveContainer
                                        width="100%"
                                        height={220}
                                    >
                                        <BarChart
                                            data={series}
                                            margin={{
                                                left: 0,
                                                right: 8,
                                                top: 8,
                                            }}
                                        >
                                            <CartesianGrid
                                                strokeDasharray="3 3"
                                                vertical={false}
                                            />
                                            <XAxis
                                                dataKey="label"
                                                fontSize={11}
                                                tickMargin={6}
                                            />
                                            <YAxis width={44} fontSize={11} />
                                            <Tooltip
                                                content={BarTooltip}
                                                cursor={{
                                                    fill: 'rgba(0,0,0,0.05)',
                                                }}
                                            />
                                            <Bar
                                                dataKey="liters"
                                                fill="#d97706"
                                                radius={[3, 3, 0, 0]}
                                                isAnimationActive={false}
                                            />
                                        </BarChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <p className="py-10 text-center text-sm text-muted-foreground">
                                        {id.usage.detail.noGraph}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <h3 className="text-sm font-medium">
                                    {id.usage.detail.recent}
                                </h3>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>
                                                {id.usage.detail.time}
                                            </TableHead>
                                            <TableHead className="text-right">
                                                {id.usage.detail.liters}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {state.detail.fuelings.length === 0 ? (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={2}
                                                    className="py-6 text-center text-muted-foreground"
                                                >
                                                    {id.usage.detail.noFuelings}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            state.detail.fuelings.map(
                                                (fueling) => (
                                                    <TableRow key={fueling.id}>
                                                        <TableCell>
                                                            {site.dateTime(
                                                                fueling.dispensed_at,
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-right tabular-nums">
                                                            {formatLiters(
                                                                fueling.liters,
                                                            )}
                                                        </TableCell>
                                                    </TableRow>
                                                ),
                                            )
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </>
                    )}
                </div>
            </SheetContent>
        </Sheet>
    );
}
