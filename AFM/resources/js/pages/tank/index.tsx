import { Head } from '@inertiajs/react';
import { PageHeading } from '@/components/page-heading';
import { TankChart } from '@/components/tank/tank-chart';
import { TankGauge } from '@/components/tank/tank-gauge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { formatLiters, formatPercent } from '@/lib/format';
import { index } from '@/routes/tank';
import type { TankPanel } from '@/types/fuel';

function TankCard({ tank }: { tank: TankPanel }) {
    const site = useSite();

    return (
        <Card data-test={`tank-${tank.id}`}>
            <CardHeader>
                <CardTitle>{tank.name}</CardTitle>
                <CardDescription>
                    {fill(id.tank.capacity, {
                        liters: formatLiters(tank.capacity),
                    })}
                    {tank.latest &&
                        ` · ${fill(id.tank.latest, { time: site.dateTime(tank.latest.read_at) })}`}
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
                {tank.latest ? (
                    <div className="grid gap-6">
                        <TankGauge name={tank.name} reading={tank.latest} />
                        <div>
                            <h3 className="mb-2 text-sm font-medium">
                                {id.tank.chartTitle}
                            </h3>
                            <TankChart
                                readings={tank.chart}
                                capacity={tank.capacity}
                                timezone={site.timezone}
                            />
                        </div>
                    </div>
                ) : (
                    <p className="py-6 text-center text-sm text-muted-foreground">
                        {id.tank.noReading}
                    </p>
                )}

                <div>
                    <h3 className="mb-2 text-sm font-medium">
                        {id.tank.readingsTitle}
                    </h3>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{id.tank.time}</TableHead>
                                <TableHead className="text-right">
                                    {id.tank.totalPct}
                                </TableHead>
                                <TableHead className="text-right">
                                    {id.tank.fuelPct}
                                </TableHead>
                                <TableHead className="text-right">
                                    {id.tank.waterPct}
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {tank.recent.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={4}
                                        className="py-6 text-center text-muted-foreground"
                                    >
                                        {id.tank.readingsEmpty}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                tank.recent.map((reading) => (
                                    <TableRow key={reading.read_at}>
                                        <TableCell>
                                            {site.dateTime(reading.read_at)}
                                        </TableCell>
                                        <TableCell className="text-right font-semibold tabular-nums">
                                            {formatPercent(reading.total_pct)}
                                        </TableCell>
                                        <TableCell className="text-right text-amber-600 tabular-nums dark:text-amber-400">
                                            {formatPercent(reading.fuel_pct)}
                                        </TableCell>
                                        <TableCell className="text-right text-blue-600 tabular-nums dark:text-blue-400">
                                            {formatPercent(reading.water_pct)}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    );
}

export default function TankIndex({ tanks }: { tanks: TankPanel[] }) {
    return (
        <>
            <Head title={id.tank.title} />

            <div className="space-y-6 p-4 md:p-6">
                <PageHeading
                    title={id.tank.title}
                    subtitle={id.tank.subtitle}
                />

                <div className="grid items-start gap-6 lg:grid-cols-2">
                    {tanks.map((tank) => (
                        <TankCard key={tank.id} tank={tank} />
                    ))}
                </div>
            </div>
        </>
    );
}

TankIndex.layout = {
    breadcrumbs: [{ title: id.nav.tank, href: index() }],
};
