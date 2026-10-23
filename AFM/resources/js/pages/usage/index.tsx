import { Head, router } from '@inertiajs/react';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { Fragment, useState } from 'react';
import { PageHeading } from '@/components/page-heading';
import { ConsumptionChart } from '@/components/usage/consumption-chart';
import { VehicleDetail } from '@/components/usage/vehicle-detail';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { formatLiters } from '@/lib/format';
import { cn } from '@/lib/utils';
import { index } from '@/routes/usage';
import type {
    Category,
    ResetPeriod,
    SeriesPoint,
    UsageGroup,
    UsageRow,
} from '@/types/fuel';

type Props = {
    side: Category;
    period: ResetPeriod;
    range: { from: string; to: string };
    totals: Record<Category, number>;
    chart: SeriesPoint[];
    groups: UsageGroup[];
};

export default function UsageIndex({
    side,
    period,
    range,
    totals,
    chart,
    groups,
}: Props) {
    const site = useSite();
    const [collapsed, setCollapsed] = useState<Set<string>>(new Set());
    const [selected, setSelected] = useState<UsageRow | null>(null);

    // The dropdown and the switch only change what is shown; they never touch the reset period.
    const visit = (next: Partial<{ side: Category; period: ResetPeriod }>) =>
        router.get(
            index.url({
                query: {
                    side: next.side ?? side,
                    period: next.period ?? period,
                },
            }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );

    const toggle = (name: string) =>
        setCollapsed((current) => {
            const next = new Set(current);
            if (!next.delete(name)) {
                next.add(name);
            }

            return next;
        });

    return (
        <>
            <Head title={id.usage.title} />

            <div className="space-y-6 p-4 md:p-6">
                <PageHeading
                    title={id.usage.title}
                    subtitle={id.usage.subtitle}
                />

                <div className="flex flex-wrap items-center justify-between gap-3">
                    <ToggleGroup
                        type="single"
                        variant="outline"
                        value={side}
                        aria-label={id.usage.sideLabel}
                        onValueChange={(value) =>
                            value && visit({ side: value as Category })
                        }
                    >
                        {(['internal', 'external'] as const).map((category) => (
                            <ToggleGroupItem
                                key={category}
                                value={category}
                                className="px-4"
                                data-test={`side-${category}`}
                            >
                                {id.categories[category]}
                                <span className="ml-2 text-xs text-muted-foreground tabular-nums">
                                    {formatLiters(totals[category])}{' '}
                                    {id.common.litersUnit}
                                </span>
                            </ToggleGroupItem>
                        ))}
                    </ToggleGroup>
                </div>

                <section className="space-y-4">
                    <div className="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <div className="text-sm text-muted-foreground">
                                {fill(id.usage.totalFor, {
                                    side: id.categories[side],
                                })}
                            </div>
                            <div
                                className="text-3xl font-semibold tabular-nums"
                                data-test="usage-total"
                            >
                                {formatLiters(totals[side])}{' '}
                                <span className="text-base font-normal text-muted-foreground">
                                    {id.common.litersUnit}
                                </span>
                            </div>
                            <div className="mt-1 text-xs text-muted-foreground">
                                {fill(id.usage.periodRange, {
                                    from: site.dateTime(range.from),
                                    to: site.dateTime(range.to),
                                })}
                            </div>
                        </div>
                        <div className="grid gap-1">
                            <span className="text-xs text-muted-foreground">
                                {id.usage.period}
                            </span>
                            <Select
                                value={period}
                                onValueChange={(value) =>
                                    visit({ period: value as ResetPeriod })
                                }
                            >
                                <SelectTrigger
                                    className="w-36"
                                    aria-label={id.usage.period}
                                    data-test="period-select"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {(['day', 'week', 'month'] as const).map(
                                        (value) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {id.periods[value]}
                                            </SelectItem>
                                        ),
                                    )}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <ConsumptionChart
                        side={side}
                        period={period}
                        points={chart}
                    />
                    <p className="text-xs text-muted-foreground">
                        {id.usage.displayOnly}
                    </p>
                </section>

                <Card>
                    <CardContent className="px-2 md:px-4">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{id.usage.plate}</TableHead>
                                    <TableHead>
                                        {id.usage.vehicleType}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {id.usage.used}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {fill(id.usage.quota, {
                                            unit: site.resetUnit,
                                        })}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {id.usage.remaining}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {groups.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            {id.usage.empty}
                                        </TableCell>
                                    </TableRow>
                                )}

                                {groups.map((group) => {
                                    const isCollapsed = collapsed.has(
                                        group.vehicle_type,
                                    );
                                    const Chevron = isCollapsed
                                        ? ChevronRight
                                        : ChevronDown;

                                    return (
                                        <Fragment key={group.vehicle_type}>
                                            <TableRow className="bg-muted/40 font-medium hover:bg-muted/60">
                                                <TableCell colSpan={2}>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="-ml-2 h-7 gap-1 px-2 font-medium"
                                                        aria-expanded={
                                                            !isCollapsed
                                                        }
                                                        aria-label={`${isCollapsed ? id.usage.expand : id.usage.collapse}: ${group.vehicle_type}`}
                                                        onClick={() =>
                                                            toggle(
                                                                group.vehicle_type,
                                                            )
                                                        }
                                                    >
                                                        <Chevron />
                                                        {group.vehicle_type}
                                                    </Button>
                                                    <span className="ml-1 text-xs font-normal text-muted-foreground">
                                                        {fill(
                                                            id.usage.cardsCount,
                                                            {
                                                                count: group
                                                                    .rows
                                                                    .length,
                                                            },
                                                        )}
                                                    </span>
                                                </TableCell>
                                                <TableCell className="text-right tabular-nums">
                                                    <span className="sr-only">
                                                        {id.usage.subtotal}{' '}
                                                    </span>
                                                    {formatLiters(
                                                        group.subtotal,
                                                    )}
                                                </TableCell>
                                                <TableCell colSpan={2} />
                                            </TableRow>

                                            {!isCollapsed &&
                                                group.rows.map((row) => (
                                                    <TableRow
                                                        key={row.id}
                                                        className="cursor-pointer"
                                                        tabIndex={0}
                                                        aria-label={fill(
                                                            id.usage.openDetail,
                                                            {
                                                                plate: row.plate_number,
                                                            },
                                                        )}
                                                        onClick={() =>
                                                            setSelected(row)
                                                        }
                                                        onKeyDown={(event) => {
                                                            if (
                                                                event.key ===
                                                                    'Enter' ||
                                                                event.key ===
                                                                    ' '
                                                            ) {
                                                                event.preventDefault();
                                                                setSelected(
                                                                    row,
                                                                );
                                                            }
                                                        }}
                                                    >
                                                        <TableCell className="pl-8 font-medium">
                                                            {row.plate_number}
                                                            {row.deleted && (
                                                                <Badge
                                                                    variant="outline"
                                                                    className="ml-2"
                                                                >
                                                                    {
                                                                        id.usage
                                                                            .deletedCard
                                                                    }
                                                                </Badge>
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            {row.vehicle_type}
                                                        </TableCell>
                                                        <TableCell className="text-right text-amber-600 tabular-nums dark:text-amber-400">
                                                            {formatLiters(
                                                                row.used,
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-right tabular-nums">
                                                            {formatLiters(
                                                                row.quota,
                                                            )}
                                                        </TableCell>
                                                        <TableCell
                                                            className={cn(
                                                                'text-right font-semibold tabular-nums',
                                                                row.remaining ===
                                                                    null
                                                                    ? ''
                                                                    : row.remaining <=
                                                                        0
                                                                      ? 'text-red-600 dark:text-red-400'
                                                                      : 'text-emerald-600 dark:text-emerald-400',
                                                            )}
                                                        >
                                                            {formatLiters(
                                                                row.remaining,
                                                            )}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                        </Fragment>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <VehicleDetail
                cardId={selected?.id ?? null}
                plate={selected?.plate_number ?? ''}
                onClose={() => setSelected(null)}
            />
        </>
    );
}

UsageIndex.layout = {
    breadcrumbs: [{ title: id.nav.usage, href: index() }],
};
