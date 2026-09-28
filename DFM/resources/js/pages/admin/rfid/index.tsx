import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { Plus, Tag, Search, Edit2, Trash2, CheckCircle2, ShieldAlert } from 'lucide-react';
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

interface TankItem {
    tank_id: number;
    name: string;
    division: string;
}

interface RfidTagItem {
    tag_id: string;
    tank_id: number | null;
    sector: string | null;
    status: 'active' | 'blocked';
    created_at?: string;
    tank?: TankItem | null;
}

interface Props {
    tags: RfidTagItem[];
    tanks: TankItem[];
}

export default function RfidIndex({ tags = [], tanks = [] }: Props) {
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState<'all' | 'active' | 'blocked'>('all');
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [editingTag, setEditingTag] = useState<RfidTagItem | null>(null);
    const [deletingTag, setDeletingTag] = useState<RfidTagItem | null>(null);

    const createForm = useForm({
        tag_id: '',
        tank_id: '',
        sector: '',
        status: 'active',
    });

    const editForm = useForm({
        tank_id: '',
        sector: '',
        status: 'active',
    });

    const openCreateModal = () => {
        createForm.reset();
        setIsCreateOpen(true);
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/admin/rfid', {
            onSuccess: () => {
                toast.success('RFID tag registered successfully.');
                setIsCreateOpen(false);
                createForm.reset();
            },
        });
    };

    const openEditModal = (tag: RfidTagItem) => {
        setEditingTag(tag);
        editForm.setData({
            tank_id: tag.tank_id ? tag.tank_id.toString() : '',
            sector: tag.sector || '',
            status: tag.status,
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingTag) return;

        editForm.put(`/admin/rfid/${editingTag.tag_id}`, {
            onSuccess: () => {
                toast.success('RFID tag updated successfully.');
                setEditingTag(null);
            },
        });
    };

    const handleDeleteSubmit = () => {
        if (!deletingTag) return;

        router.delete(`/admin/rfid/${deletingTag.tag_id}`, {
            onSuccess: () => {
                toast.success('RFID tag deleted.');
                setDeletingTag(null);
            },
        });
    };

    const filteredTags = tags.filter((t) => {
        const matchesQuery =
            t.tag_id.toLowerCase().includes(searchQuery.toLowerCase()) ||
            (t.sector && t.sector.toLowerCase().includes(searchQuery.toLowerCase())) ||
            (t.tank?.name && t.tank.name.toLowerCase().includes(searchQuery.toLowerCase()));

        const matchesStatus = statusFilter === 'all' || t.status === statusFilter;
        return matchesQuery && matchesStatus;
    });

    const activeCount = tags.filter((t) => t.status === 'active').length;
    const blockedCount = tags.filter((t) => t.status === 'blocked').length;
    const assignedCount = tags.filter((t) => t.tank_id !== null).length;

    return (
        <div className="flex h-full flex-1 flex-col gap-6 p-6">
            <Head title="RFID Tag Management" />

            <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">RFID Tag Management</h1>
                    <p className="text-sm text-muted-foreground">
                        Manage RFID tags linked to mobile browser tanks, tankers, and authorization sectors.
                    </p>
                </div>
                <Button onClick={openCreateModal} className="gap-2">
                    <Plus className="h-4 w-4" />
                    Register RFID Tag
                </Button>
            </div>

            {/* Quick Metrics */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Tags</CardTitle>
                        <Tag className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{tags.length}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Active Tags</CardTitle>
                        <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{activeCount}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Blocked Tags</CardTitle>
                        <ShieldAlert className="h-4 w-4 text-destructive" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-destructive">{blockedCount}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Assigned to Tanks</CardTitle>
                        <Tag className="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{assignedCount}</div>
                    </CardContent>
                </Card>
            </div>

            {/* Filter Bar */}
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="relative flex-1 max-w-sm">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        placeholder="Search Tag ID, sector, or tank..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="pl-9"
                    />
                </div>
                <div className="flex items-center gap-2">
                    <Label className="text-sm text-muted-foreground whitespace-nowrap">Status:</Label>
                    <Select value={statusFilter} onValueChange={(val: 'all' | 'active' | 'blocked') => setStatusFilter(val)}>
                        <SelectTrigger className="w-[140px]">
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="active">Active Only</SelectItem>
                            <SelectItem value="blocked">Blocked Only</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            {/* Table Section */}
            <Card>
                <CardHeader>
                    <CardTitle>RFID Inventory</CardTitle>
                    <CardDescription>
                        List of all physical RFID tags enrolled in the system. Changes bump the site tag list version.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {filteredTags.length === 0 ? (
                        <div className="py-12 text-center text-sm text-muted-foreground">
                            No RFID tags found matching your filters.
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b text-muted-foreground">
                                    <tr>
                                        <th className="h-10 px-4 font-medium">Tag ID / UID</th>
                                        <th className="h-10 px-4 font-medium">Assigned Tank</th>
                                        <th className="h-10 px-4 font-medium">Sector</th>
                                        <th className="h-10 px-4 font-medium">Status</th>
                                        <th className="h-10 px-4 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {filteredTags.map((tag) => (
                                        <tr key={tag.tag_id} className="hover:bg-muted/50">
                                            <td className="p-4 font-mono font-medium">{tag.tag_id}</td>
                                            <td className="p-4">
                                                {tag.tank ? (
                                                    <div className="flex flex-col">
                                                        <span className="font-medium">{tag.tank.name}</span>
                                                        <span className="text-xs text-muted-foreground capitalize">
                                                            {tag.tank.division.replace('_', ' ')}
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground italic">Unassigned</span>
                                                )}
                                            </td>
                                            <td className="p-4">{tag.sector || '-'}</td>
                                            <td className="p-4">
                                                {tag.status === 'active' ? (
                                                    <Badge variant="outline" className="border-emerald-500 text-emerald-600 dark:text-emerald-400">
                                                        Active
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="destructive">Blocked</Badge>
                                                )}
                                            </td>
                                            <td className="p-4 text-right">
                                                <div className="flex items-center justify-end gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => openEditModal(tag)}
                                                    >
                                                        <Edit2 className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-destructive"
                                                        onClick={() => setDeletingTag(tag)}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Create Dialog */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Register New RFID Tag</DialogTitle>
                        <DialogDescription>
                            Enter the unique RFID hardware UID and assign it to a tank or sector.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleCreateSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="create-tag-id">Tag ID / UID *</Label>
                            <Input
                                id="create-tag-id"
                                placeholder="e.g. RFID-882319-A"
                                value={createForm.data.tag_id}
                                onChange={(e) => createForm.setData('tag_id', e.target.value)}
                                required
                            />
                            {createForm.errors.tag_id && (
                                <p className="text-xs text-destructive">{createForm.errors.tag_id}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="create-tank-id">Assigned Tank</Label>
                            <Select
                                value={createForm.data.tank_id || 'none'}
                                onValueChange={(val) => createForm.setData('tank_id', val === 'none' ? '' : val)}
                            >
                                <SelectTrigger id="create-tank-id">
                                    <SelectValue placeholder="Select tank (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">None (Unassigned)</SelectItem>
                                    {tanks.map((tank) => (
                                        <SelectItem key={tank.tank_id} value={tank.tank_id.toString()}>
                                            {tank.name} ({tank.division.replace('_', ' ')})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {createForm.errors.tank_id && (
                                <p className="text-xs text-destructive">{createForm.errors.tank_id}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="create-sector">Sector / Department</Label>
                            <Input
                                id="create-sector"
                                placeholder="e.g. Mining Area Pit 4"
                                value={createForm.data.sector}
                                onChange={(e) => createForm.setData('sector', e.target.value)}
                            />
                            {createForm.errors.sector && (
                                <p className="text-xs text-destructive">{createForm.errors.sector}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="create-status">Status *</Label>
                            <Select
                                value={createForm.data.status}
                                onValueChange={(val) => createForm.setData('status', val)}
                            >
                                <SelectTrigger id="create-status">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Active (Permitted)</SelectItem>
                                    <SelectItem value="blocked">Blocked (Unauthorized)</SelectItem>
                                </SelectContent>
                            </Select>
                            {createForm.errors.status && (
                                <p className="text-xs text-destructive">{createForm.errors.status}</p>
                            )}
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={createForm.processing}>
                                {createForm.processing ? 'Registering...' : 'Register Tag'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Edit Dialog */}
            <Dialog open={!!editingTag} onOpenChange={(open) => !open && setEditingTag(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit RFID Tag: {editingTag?.tag_id}</DialogTitle>
                        <DialogDescription>
                            Update the assigned tank, sector, or authorization status.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEditSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="edit-tank-id">Assigned Tank</Label>
                            <Select
                                value={editForm.data.tank_id || 'none'}
                                onValueChange={(val) => editForm.setData('tank_id', val === 'none' ? '' : val)}
                            >
                                <SelectTrigger id="edit-tank-id">
                                    <SelectValue placeholder="Select tank (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">None (Unassigned)</SelectItem>
                                    {tanks.map((tank) => (
                                        <SelectItem key={tank.tank_id} value={tank.tank_id.toString()}>
                                            {tank.name} ({tank.division.replace('_', ' ')})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {editForm.errors.tank_id && (
                                <p className="text-xs text-destructive">{editForm.errors.tank_id}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="edit-sector">Sector / Department</Label>
                            <Input
                                id="edit-sector"
                                placeholder="e.g. Pit 4 North"
                                value={editForm.data.sector}
                                onChange={(e) => editForm.setData('sector', e.target.value)}
                            />
                            {editForm.errors.sector && (
                                <p className="text-xs text-destructive">{editForm.errors.sector}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="edit-status">Status</Label>
                            <Select
                                value={editForm.data.status}
                                onValueChange={(val) => editForm.setData('status', val)}
                            >
                                <SelectTrigger id="edit-status">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Active (Permitted)</SelectItem>
                                    <SelectItem value="blocked">Blocked (Unauthorized)</SelectItem>
                                </SelectContent>
                            </Select>
                            {editForm.errors.status && (
                                <p className="text-xs text-destructive">{editForm.errors.status}</p>
                            )}
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setEditingTag(null)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {editForm.processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Dialog */}
            <Dialog open={!!deletingTag} onOpenChange={(open) => !open && setDeletingTag(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete RFID Tag</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete tag &quot;{deletingTag?.tag_id}&quot;? If this tag was used in
                            dispensing, it will no longer authorize transactions.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setDeletingTag(null)}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleDeleteSubmit}>
                            Delete Tag
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

RfidIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'RFID Management', href: '/admin/rfid' },
    ],
};
