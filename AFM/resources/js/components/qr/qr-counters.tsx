import { Card, CardContent } from '@/components/ui/card';
import { id } from '@/lang/id';
import { cn } from '@/lib/utils';
import type { Counters, QrFilters } from '@/types/fuel';

type Counter = {
    key: keyof Counters;
    label: string;
    filter: Partial<QrFilters>;
    isActive: (filters: QrFilters) => boolean;
};

const counters: Counter[] = [
    {
        key: 'active',
        label: id.qr.counters.active,
        filter: { state: 'active', validity: undefined },
        isActive: (f) => f.state === 'active' && !f.validity,
    },
    {
        key: 'lifetime',
        label: id.qr.counters.lifetime,
        filter: { validity: 'lifetime', state: undefined },
        isActive: (f) => f.validity === 'lifetime' && !f.state,
    },
    {
        key: 'expired',
        label: id.qr.counters.expired,
        filter: { state: 'expired', validity: undefined },
        isActive: (f) => f.state === 'expired' && !f.validity,
    },
    {
        key: 'depleted',
        label: id.qr.counters.depleted,
        filter: { state: 'depleted', validity: undefined },
        isActive: (f) => f.state === 'depleted' && !f.validity,
    },
];

type Props = {
    values: Counters;
    filters: QrFilters;
    /** Called with the filter a counter stands for, or the cleared filter when it is clicked again. */
    onSelect: (filter: Partial<QrFilters>) => void;
};

/** The four counter cards; clicking one filters the table below to that state. */
export function QrCounters({ values, filters, onSelect }: Props) {
    return (
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            {counters.map((counter) => {
                const active = counter.isActive(filters);

                return (
                    <button
                        key={counter.key}
                        type="button"
                        aria-pressed={active}
                        title={id.qr.counters.hint}
                        data-test={`counter-${counter.key}`}
                        className="text-left"
                        onClick={() =>
                            onSelect(
                                active
                                    ? { state: undefined, validity: undefined }
                                    : counter.filter,
                            )
                        }
                    >
                        <Card
                            className={cn(
                                'transition-colors hover:bg-muted/50',
                                active &&
                                    'border-primary ring-2 ring-primary/30',
                            )}
                        >
                            <CardContent className="py-1">
                                <div className="text-sm text-muted-foreground">
                                    {counter.label}
                                </div>
                                <div className="text-3xl font-semibold tabular-nums">
                                    {values[counter.key]}
                                </div>
                            </CardContent>
                        </Card>
                    </button>
                );
            })}
        </div>
    );
}
