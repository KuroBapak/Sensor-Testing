import { Head, router } from '@inertiajs/react';
import {
    Download,
    FileUp,
    Plus,
    PlusCircle,
    QrCode,
    Search,
    Trash2,
    X,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { BatchAddDialog } from '@/components/batch-add-dialog';
import { PageHeading } from '@/components/page-heading';
import { CardStatus } from '@/components/qr/card-status';
import { CreateCardDialog } from '@/components/qr/create-card-dialog';
import { QrCounters } from '@/components/qr/qr-counters';
import { ShowQrDialog } from '@/components/qr/show-qr-dialog';
import { TopUpDialog } from '@/components/qr/top-up-dialog';
import { SortableHead } from '@/components/sortable-head';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { formatLiters } from '@/lib/format';
import { cn } from '@/lib/utils';
import { destroy, exportMethod, index } from '@/routes/qr-cards';
import type {
    Counters,
    Paginated,
    QrCardRow,
    QrFilters,
    VehicleOption,
} from '@/types/fuel';

type Props = {
    filters: QrFilters;
    counters: Counters;
    cards: Paginated<QrCardRow>;
    vehicleTypes: VehicleOption[];
    lowQuotaPercent: number;
};

const ALL = 'all';

/** Query string for the current filters; empty values are left out. */
function query(
    filters: Partial<QrFilters>,
    page?: number,
): Record<string, string | number> {
    const result: Record<string, string | number> = {};

    for (const [key, value] of Object.entries(filters)) {
        if (value !== undefined && value !== '' && value !== ALL) {
            result[key] = value;
        }
    }
    if (result.sort === 'remaining' && result.dir === 'asc') {
        delete result.sort;
        delete result.dir;
    }
    if (page && page > 1) {
        result.page = page;
    }

    return result;
}

function FilterSelect({
    label,
    value,
    onChange,
    options,
}: {
    label: string;
    value: string | undefined;
    onChange: (value: string) => void;
    options: { value: string; label: string }[];
}) {
    return (
        <Select value={value ?? ALL} onValueChange={onChange}>
            <SelectTrigger className="w-40" aria-label={label} size="sm">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    value={ALL}
                >{`${label}: ${id.common.all}`}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

export default function QrIndex({
    filters,
    counters,
    cards,
    vehicleTypes,
}: Props) {
    const site = useSite();
    const [term, setTerm] = useState(filters.search ?? '');
    const [createOpen, setCreateOpen] = useState(false);
    const [importOpen, setImportOpen] = useState(false);
    const [qrCard, setQrCard] = useState<QrCardRow | null>(null);
    const [topUpCard, setTopUpCard] = useState<QrCardRow | null>(null);
    const [deleting, setDeleting] = useState<QrCardRow | null>(null);
    const [processing, setProcessing] = useState(false);
    const firstRender = useRef(true);

    const visit = (next: Partial<QrFilters>, page?: number) =>
        router.get(
            index.url({ query: query({ ...filters, ...next }, page) }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );

    // Search by plate as the user types.
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timer = setTimeout(() => visit({ search: term }), 300);

        return () => clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [term]);

    const sortBy = (sort: string) =>
        visit({
            sort,
            dir:
                filters.sort === sort && filters.dir === 'asc' ? 'desc' : 'asc',
        });

    const hasFilters = [
        'validity',
        'state',
        'reset',
        'category',
        'vehicle_type',
        'search',
    ].some((key) => Boolean(filters[key as keyof QrFilters]));

    const clearFilters = () => {
        setTerm('');
        router.get(
            index.url(),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(destroy.url(deleting.id), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    };

    const exportUrl = exportMethod.url({ query: query(filters) });
    const headProps = { sort: filters.sort, dir: filters.dir, onSort: sortBy };

    return (
        <>
            <Head title={id.qr.title} />

            <div className="space-y-6 p-4 md:p-6">
                <PageHeading
                    title={id.qr.title}
                    subtitle={id.qr.subtitle}
                    actions={
                        <>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => setImportOpen(true)}
                            >
                                <FileUp />
                                {id.common.batchAdd}
                            </Button>
                            <Button variant="outline" size="sm" asChild>
                                <a href={exportUrl}>
                                    <Download />
                                    {id.common.export}
                                </a>
                            </Button>
                            <Button
                                size="sm"
                                onClick={() => setCreateOpen(true)}
                                data-test="create-card"
                            >
                                <Plus />
                                {id.qr.create}
                            </Button>
                        </>
                    }
                />

                <QrCounters
                    values={counters}
                    filters={filters}
                    onSelect={(next) => visit(next)}
                />

                <Card>
                    <CardContent className="space-y-4 px-2 md:px-4">
                        <div className="flex flex-wrap items-center gap-2">
                            <div className="relative">
                                <Search className="pointer-events-none absolute top-2 left-2.5 size-4 text-muted-foreground" />
                                <Input
                                    className="h-8 w-56 pl-8"
                                    value={term}
                                    placeholder={id.qr.searchPlate}
                                    aria-label={id.common.search}
                                    onChange={(event) =>
                                        setTerm(event.target.value)
                                    }
                                />
                            </div>
                            <FilterSelect
                                label={id.qr.filters.validity}
                                value={filters.validity}
                                onChange={(value) => visit({ validity: value })}
                                options={[
                                    {
                                        value: 'lifetime',
                                        label: id.validity.lifetime,
                                    },
                                    {
                                        value: 'temporary',
                                        label: id.validity.temporary,
                                    },
                                ]}
                            />
                            <FilterSelect
                                label={id.qr.filters.state}
                                value={filters.state}
                                onChange={(value) => visit({ state: value })}
                                options={[
                                    {
                                        value: 'active',
                                        label: id.qr.filters.stateActive,
                                    },
                                    {
                                        value: 'expired',
                                        label: id.qr.filters.stateExpired,
                                    },
                                    {
                                        value: 'depleted',
                                        label: id.qr.filters.stateDepleted,
                                    },
                                    {
                                        value: 'low',
                                        label: id.qr.filters.stateLow,
                                    },
                                ]}
                            />
                            <FilterSelect
                                label={id.qr.filters.reset}
                                value={filters.reset}
                                onChange={(value) => visit({ reset: value })}
                                options={[
                                    {
                                        value: 'on',
                                        label: id.qr.filters.resetOn,
                                    },
                                    {
                                        value: 'off',
                                        label: id.qr.filters.resetOff,
                                    },
                                ]}
                            />
                            <FilterSelect
                                label={id.qr.filters.category}
                                value={filters.category}
                                onChange={(value) => visit({ category: value })}
                                options={[
                                    {
                                        value: 'internal',
                                        label: id.categories.internal,
                                    },
                                    {
                                        value: 'external',
                                        label: id.categories.external,
                                    },
                                ]}
                            />
                            <FilterSelect
                                label={id.qr.filters.vehicleType}
                                value={filters.vehicle_type}
                                onChange={(value) =>
                                    visit({ vehicle_type: value })
                                }
                                options={vehicleTypes.map((type) => ({
                                    value: String(type.id),
                                    label: type.name,
                                }))}
                            />
                            {hasFilters && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={clearFilters}
                                >
                                    <X />
                                    {id.common.clearFilters}
                                </Button>
                            )}
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <SortableHead
                                        label={id.qr.columns.plate}
                                        sortKey="plate"
                                        {...headProps}
                                    />
                                    <SortableHead
                                        label={id.qr.columns.category}
                                        sortKey="category"
                                        {...headProps}
                                    />
                                    <SortableHead
                                        label={id.qr.columns.vehicleType}
                                        sortKey="vehicle_type"
                                        {...headProps}
                                    />
                                    <SortableHead
                                        label={id.qr.columns.validity}
                                        sortKey="expiry"
                                        {...headProps}
                                    />
                                    <SortableHead
                                        label={id.qr.columns.reset}
                                        sortKey="reset"
                                        {...headProps}
                                    />
                                    <SortableHead
                                        label={fill(id.qr.columns.quota, {
                                            unit: site.resetUnit,
                                        })}
                                        sortKey="quota"
                                        align="right"
                                        {...headProps}
                                    />
                                    <SortableHead
                                        label={id.qr.columns.remaining}
                                        sortKey="remaining"
                                        align="right"
                                        {...headProps}
                                    />
                                    <SortableHead
                                        label={id.qr.columns.status}
                                        sortKey="status"
                                        {...headProps}
                                    />
                                    <TableHead className="text-right">
                                        {id.common.actions}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {cards.data.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={9}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            {hasFilters
                                                ? id.qr.noResults
                                                : id.qr.empty}
                                        </TableCell>
                                    </TableRow>
                                )}
                                {cards.data.map((card) => (
                                    <TableRow
                                        key={card.id}
                                        data-test={`card-row-${card.id}`}
                                    >
                                        <TableCell className="font-medium whitespace-nowrap">
                                            {card.plate_number}
                                        </TableCell>
                                        <TableCell>
                                            {card.category_label}
                                        </TableCell>
                                        <TableCell>
                                            {card.vehicle_type}
                                        </TableCell>
                                        <TableCell className="whitespace-nowrap">
                                            {card.expires_at
                                                ? fill(id.qr.expiresAt, {
                                                      date: site.date(
                                                          card.expires_at,
                                                      ),
                                                  })
                                                : id.qr.lifetime}
                                        </TableCell>
                                        <TableCell className="whitespace-nowrap">
                                            {card.reset_enabled
                                                ? fill(id.qr.resetOn, {
                                                      unit: site.resetUnit,
                                                  })
                                                : id.qr.resetOff}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {formatLiters(card.quota)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            <div
                                                className={cn(
                                                    'font-semibold',
                                                    card.depleted
                                                        ? 'text-red-600 dark:text-red-400'
                                                        : card.low
                                                          ? 'text-amber-600 dark:text-amber-400'
                                                          : 'text-emerald-600 dark:text-emerald-400',
                                                )}
                                            >
                                                {formatLiters(card.remaining)}
                                            </div>
                                            {card.depleted && (
                                                <div className="text-xs text-muted-foreground">
                                                    {card.next_reset
                                                        ? fill(id.qr.refillAt, {
                                                              time: site.dateTime(
                                                                  card.next_reset,
                                                              ),
                                                          })
                                                        : id.qr.needsTopUp}
                                                </div>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <CardStatus card={card} />
                                        </TableCell>
                                        <TableCell className="text-right whitespace-nowrap">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => setQrCard(card)}
                                            >
                                                <QrCode />
                                                {id.qr.showQr}
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    setTopUpCard(card)
                                                }
                                            >
                                                <PlusCircle />
                                                {id.qr.topUp}
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`${id.qr.deleteCard} ${card.plate_number}`}
                                                onClick={() =>
                                                    setDeleting(card)
                                                }
                                            >
                                                <Trash2 />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {cards.total > 0 && (
                            <div className="flex flex-wrap items-center justify-between gap-2 text-sm text-muted-foreground">
                                <span>
                                    {fill(id.common.showing, {
                                        from: cards.from ?? 0,
                                        to: cards.to ?? 0,
                                        total: cards.total,
                                    })}
                                </span>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={cards.current_page <= 1}
                                        onClick={() =>
                                            visit({}, cards.current_page - 1)
                                        }
                                    >
                                        {id.common.previous}
                                    </Button>
                                    <span>
                                        {fill(id.common.page, {
                                            page: cards.current_page,
                                            last: cards.last_page,
                                        })}
                                    </span>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={
                                            cards.current_page >=
                                            cards.last_page
                                        }
                                        onClick={() =>
                                            visit({}, cards.current_page + 1)
                                        }
                                    >
                                        {id.common.next}
                                    </Button>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            <CreateCardDialog
                open={createOpen}
                onOpenChange={setCreateOpen}
                vehicleTypes={vehicleTypes}
            />

            <BatchAddDialog open={importOpen} onOpenChange={setImportOpen} />

            <ShowQrDialog card={qrCard} onClose={() => setQrCard(null)} />
            <TopUpDialog card={topUpCard} onClose={() => setTopUpCard(null)} />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={id.qr.deleteTitle}
                description={
                    <p>
                        {fill(id.qr.deleteBody, {
                            plate: deleting?.plate_number ?? '',
                        })}
                    </p>
                }
                confirmLabel={id.common.delete}
                destructive
                processing={processing}
                onConfirm={confirmDelete}
            />
        </>
    );
}

QrIndex.layout = {
    breadcrumbs: [{ title: id.nav.qr, href: index() }],
};
