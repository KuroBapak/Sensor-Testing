import { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import { Plus, Fuel, Truck, Calendar, Clock, User, CheckCircle2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { toast } from 'sonner';

interface MainTankItem {
    tank_id: number;
    name: string;
}

interface UserItem {
    id: number;
    name: string;
}

interface VendorFillItem {
    id: number;
    tank_id: number;
    main_tank_id?: number | null;
    liters: number | string;
    started_at: string;
    ended_at: string;
    entered_by?: number | null;
    entered_by_user?: UserItem | null;
    enteredBy?: UserItem | null;
    sync_status?: string;
}

interface PaginatedFills {
    data: VendorFillItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    vendorFills: PaginatedFills;
    mainTanks: MainTankItem[];
}

export default function VendorFillsIndex({ vendorFills, mainTanks = [] }: Props) {
    const [isCreateOpen, setIsCreateOpen] = useState(false);

    const nowIsoLocal = () => {
        const d = new Date();
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    };

    const { data, setData, post, processing, errors, reset } = useForm({
        tank_id: mainTanks[0]?.tank_id?.toString() || '',
        liters: '',
        started_at: nowIsoLocal(),
        ended_at: nowIsoLocal(),
    });

    const openCreateModal = () => {
        reset();
        setData({
            tank_id: mainTanks[0]?.tank_id?.toString() || '',
            liters: '',
            started_at: nowIsoLocal(),
            ended_at: nowIsoLocal(),
        });
        setIsCreateOpen(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/vendor-fills', {
            onSuccess: () => {
                toast.success('Vendor fuel fill recorded successfully.');
                setIsCreateOpen(false);
                reset();
            },
        });
    };

    const totalLitersPage = (vendorFills?.data || []).reduce(
        (sum, item) => sum + Number(item.liters || 0),
        0
    );

    return (
        <div className="flex h-full flex-1 flex-col gap-6 p-6">
            <Head title="Vendor Inbound Fills" />

            <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Vendor Fuel Inbound</h1>
                    <p className="text-sm text-muted-foreground">
                        Record and audit bulk fuel replenishment received from external vendors into Main Tanks.
                    </p>
                </div>
                <Button onClick={openCreateModal} className="gap-2">
                    <Plus className="h-4 w-4" />
                    Record Vendor Fill
                </Button>
            </div>

            {/* Quick Metrics */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Inbound Events</CardTitle>
                        <Truck className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{vendorFills?.total ?? 0}</div>
                        <p className="text-xs text-muted-foreground mt-1">Recorded replenishment operations</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Volume On This Page</CardTitle>
                        <Fuel className="h-4 w-4 text-amber-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {totalLitersPage.toLocaleString()} L
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">Liters accounted for current view</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Target Main Tanks</CardTitle>
                        <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{mainTanks.length}</div>
                        <p className="text-xs text-muted-foreground mt-1">Configured main storage tanks</p>
                    </CardContent>
                </Card>
            </div>

            {/* Table Section */}
            <Card>
                <CardHeader>
                    <CardTitle>Inbound Replenishment Log</CardTitle>
                    <CardDescription>
                        Complete ledger of vendor fuel transfers into facility main tanks.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {!vendorFills?.data || vendorFills.data.length === 0 ? (
                        <div className="py-12 text-center text-sm text-muted-foreground">
                            No vendor fill records recorded yet.
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b text-muted-foreground">
                                    <tr>
                                        <th className="h-10 px-4 font-medium">Delivery Time Range</th>
                                        <th className="h-10 px-4 font-medium">Target Main Tank</th>
                                        <th className="h-10 px-4 font-medium">Volume (Liters)</th>
                                        <th className="h-10 px-4 font-medium">Recorded By</th>
                                        <th className="h-10 px-4 font-medium">Sync Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {vendorFills.data.map((fill) => {
                                        const tankName =
                                            mainTanks.find((t) => t.tank_id === fill.tank_id)?.name ||
                                            `Tank #${fill.tank_id}`;
                                        const operatorName =
                                            fill.entered_by_user?.name || fill.enteredBy?.name || 'Manual / Admin';

                                        return (
                                            <tr key={fill.id} className="hover:bg-muted/50">
                                                <td className="p-4">
                                                    <div className="flex flex-col">
                                                        <div className="flex items-center gap-1.5 font-medium">
                                                            <Calendar className="h-3.5 w-3.5 text-muted-foreground" />
                                                            <span>{fill.started_at?.slice(0, 10)}</span>
                                                        </div>
                                                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground mt-0.5">
                                                            <Clock className="h-3 w-3" />
                                                            <span>
                                                                {fill.started_at?.slice(11, 16)} – {fill.ended_at?.slice(11, 16)}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="p-4 font-medium">{tankName}</td>
                                                <td className="p-4">
                                                    <span className="font-semibold text-emerald-600 dark:text-emerald-400">
                                                        +{Number(fill.liters).toLocaleString()} L
                                                    </span>
                                                </td>
                                                <td className="p-4">
                                                    <div className="flex items-center gap-1.5 text-muted-foreground">
                                                        <User className="h-3.5 w-3.5" />
                                                        <span>{operatorName}</span>
                                                    </div>
                                                </td>
                                                <td className="p-4">
                                                    <Badge variant="outline" className="border-emerald-500 text-emerald-600 dark:text-emerald-400 capitalize">
                                                        {fill.sync_status || 'live'}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Pagination */}
                    {vendorFills?.links && vendorFills.links.length > 3 && (
                        <div className="flex items-center justify-between border-t pt-4 mt-4">
                            <div className="text-xs text-muted-foreground">
                                Showing page {vendorFills.current_page} of {vendorFills.last_page} ({vendorFills.total} total)
                            </div>
                            <div className="flex items-center gap-1">
                                {vendorFills.links.map((link, idx) => (
                                    <Button
                                        key={idx}
                                        asChild={!!link.url}
                                        variant={link.active ? 'default' : 'outline'}
                                        size="sm"
                                        disabled={!link.url}
                                    >
                                        {link.url ? (
                                            <Link
                                                href={link.url}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : (
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        )}
                                    </Button>
                                ))}
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Create Dialog */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Record Vendor Fuel Replenishment</DialogTitle>
                        <DialogDescription>
                            Enter vendor delivery details to officially add inbound bulk fuel into a Main Tank.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="tank-id">Target Main Tank *</Label>
                            <Select
                                value={data.tank_id}
                                onValueChange={(val) => setData('tank_id', val)}
                            >
                                <SelectTrigger id="tank-id">
                                    <SelectValue placeholder="Select main tank" />
                                </SelectTrigger>
                                <SelectContent>
                                    {mainTanks.map((tank) => (
                                        <SelectItem key={tank.tank_id} value={tank.tank_id.toString()}>
                                            {tank.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.tank_id && <p className="text-xs text-destructive">{errors.tank_id}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="liters">Received Volume (Liters) *</Label>
                            <Input
                                id="liters"
                                type="number"
                                step="0.01"
                                placeholder="e.g. 15000"
                                value={data.liters}
                                onChange={(e) => setData('liters', e.target.value)}
                                required
                            />
                            {errors.liters && <p className="text-xs text-destructive">{errors.liters}</p>}
                        </div>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="started-at">Start Time *</Label>
                                <Input
                                    id="started-at"
                                    type="datetime-local"
                                    value={data.started_at}
                                    onChange={(e) => setData('started_at', e.target.value)}
                                    required
                                />
                                {errors.started_at && (
                                    <p className="text-xs text-destructive">{errors.started_at}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="ended-at">Completion Time *</Label>
                                <Input
                                    id="ended-at"
                                    type="datetime-local"
                                    value={data.ended_at}
                                    onChange={(e) => setData('ended_at', e.target.value)}
                                    required
                                />
                                {errors.ended_at && (
                                    <p className="text-xs text-destructive">{errors.ended_at}</p>
                                )}
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Recording...' : 'Record Fill'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    );
}

VendorFillsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Vendor Fuel Inbound', href: '/admin/vendor-fills' },
    ],
};
