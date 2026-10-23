import { Head, router } from '@inertiajs/react';
import { Download, FileUp, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { BatchAddDialog } from '@/components/batch-add-dialog';
import { PageHeading } from '@/components/page-heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { VehicleFormDialog } from '@/components/vehicles/vehicle-form-dialog';
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { formatLiters } from '@/lib/format';
import { destroy, exportMethod, index } from '@/routes/vehicles';
import type { VehicleTypeRow } from '@/types/fuel';

type Props = {
    search: string;
    vehicleTypes: VehicleTypeRow[];
};

export default function VehiclesIndex({ search, vehicleTypes }: Props) {
    const site = useSite();
    const [term, setTerm] = useState(search);
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<VehicleTypeRow | null>(null);
    const [deleting, setDeleting] = useState<VehicleTypeRow | null>(null);
    const [importOpen, setImportOpen] = useState(false);
    const [processing, setProcessing] = useState(false);
    const firstRender = useRef(true);

    // Search the list as the user types, without adding history entries.
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timer = setTimeout(() => {
            router.get(
                index.url({ query: term ? { search: term } : {} }),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    only: ['vehicleTypes', 'search'],
                },
            );
        }, 300);

        return () => clearTimeout(timer);
    }, [term]);

    const openForm = (vehicle: VehicleTypeRow | null) => {
        setEditing(vehicle);
        setFormOpen(true);
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

    return (
        <>
            <Head title={id.vehicles.title} />

            <div className="space-y-6 p-4 md:p-6">
                <PageHeading
                    title={id.vehicles.title}
                    subtitle={id.vehicles.subtitle}
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
                                <a
                                    href={exportMethod.url({
                                        query: term ? { search: term } : {},
                                    })}
                                >
                                    <Download />
                                    {id.common.export}
                                </a>
                            </Button>
                            <Button
                                size="sm"
                                onClick={() => openForm(null)}
                                data-test="add-vehicle"
                            >
                                <Plus />
                                {id.vehicles.add}
                            </Button>
                        </>
                    }
                />

                <div className="relative max-w-sm">
                    <Search className="pointer-events-none absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                    <Input
                        className="pl-8"
                        value={term}
                        placeholder={id.vehicles.search}
                        aria-label={id.common.search}
                        onChange={(event) => setTerm(event.target.value)}
                    />
                </div>

                <Card>
                    <CardContent className="px-2 md:px-4">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{id.vehicles.name}</TableHead>
                                    <TableHead className="text-right">
                                        {id.vehicles.capacity}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {fill(id.vehicles.quota, {
                                            unit: site.resetUnit,
                                        })}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {id.vehicles.cards}
                                    </TableHead>
                                    <TableHead className="w-24 text-right">
                                        {id.common.actions}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {vehicleTypes.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            {search
                                                ? id.vehicles.noResults
                                                : id.vehicles.empty}
                                        </TableCell>
                                    </TableRow>
                                )}
                                {vehicleTypes.map((vehicle) => (
                                    <TableRow key={vehicle.id}>
                                        <TableCell className="font-medium">
                                            {vehicle.name}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {formatLiters(
                                                vehicle.max_capacity_liters,
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {formatLiters(vehicle.quota_liters)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {vehicle.cards_count}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`${id.common.edit} ${vehicle.name}`}
                                                onClick={() =>
                                                    openForm(vehicle)
                                                }
                                            >
                                                <Pencil />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`${id.common.delete} ${vehicle.name}`}
                                                onClick={() =>
                                                    setDeleting(vehicle)
                                                }
                                            >
                                                <Trash2 />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <VehicleFormDialog
                open={formOpen}
                onOpenChange={setFormOpen}
                vehicle={editing}
            />

            <BatchAddDialog open={importOpen} onOpenChange={setImportOpen} />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={id.vehicles.deleteTitle}
                description={
                    <>
                        <p>
                            {fill(id.vehicles.deleteBody, {
                                name: deleting?.name ?? '',
                            })}
                        </p>
                        {deleting && deleting.cards_count > 0 && (
                            <p className="font-medium text-destructive">
                                {fill(id.vehicles.deleteBlocked, {
                                    count: deleting.cards_count,
                                })}
                            </p>
                        )}
                    </>
                }
                confirmLabel={id.common.delete}
                destructive
                processing={processing}
                onConfirm={confirmDelete}
            />
        </>
    );
}

VehiclesIndex.layout = {
    breadcrumbs: [{ title: id.nav.vehicles, href: index() }],
};
