import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Edit, Plus, Shield, Trash2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface RoleData {
    id: number;
    name: string;
    is_system: boolean;
    users_count: number;
    permissions: string[];
}

interface Props {
    roles: RoleData[];
    permissionCatalog: Record<string, Record<string, string>>;
    allPermissions: string[];
}

function PermissionChecklist({
    permissionCatalog,
    selectedPerms,
    onToggle,
    idPrefix,
}: {
    permissionCatalog: Record<string, Record<string, string>>;
    selectedPerms: string[];
    onToggle: (key: string) => void;
    idPrefix: string;
}) {
    return (
        <div className="space-y-4">
            <Label className="text-sm font-semibold">Permission Checklist</Label>
            {Object.entries(permissionCatalog).map(([group, perms]) => (
                <div key={group} className="rounded-lg border p-3">
                    <h4 className="mb-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">{group}</h4>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        {Object.entries(perms).map(([key, label]) => (
                            <div key={key} className="flex items-start space-x-2">
                                <Checkbox
                                    id={`${idPrefix}-${key}`}
                                    checked={selectedPerms.includes(key)}
                                    onCheckedChange={() => onToggle(key)}
                                />
                                <div className="grid gap-0.5 leading-none">
                                    <label htmlFor={`${idPrefix}-${key}`} className="cursor-pointer text-xs font-medium">
                                        {label}
                                    </label>
                                    <span className="font-mono text-[10px] text-muted-foreground">{key}</span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
}
function RoleCard({ role, onEdit, onDelete }: { role: RoleData; onEdit: () => void; onDelete: () => void }) {
    return (
        <Card className="relative flex flex-col justify-between border">
            <CardHeader>
                <div className="flex items-start justify-between gap-2">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <CardTitle className="text-lg">{role.name}</CardTitle>
                            {role.is_system && (
                                <Badge variant="secondary" className="bg-amber-500/10 text-amber-600 dark:text-amber-400">System</Badge>
                            )}
                        </div>
                        <CardDescription>{role.users_count} {role.users_count === 1 ? 'user' : 'users'} assigned</CardDescription>
                    </div>
                    <Shield className="h-5 w-5 text-muted-foreground" />
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                <div>
                    <h4 className="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Permissions ({role.is_system ? 'All' : role.permissions.length})
                    </h4>
                    <div className="flex flex-wrap gap-1">
                        {role.is_system ? (
                            <Badge variant="outline" className="text-xs">All System Permissions Granted</Badge>
                        ) : role.permissions.length > 0 ? (
                            role.permissions.map((p) => <Badge key={p} variant="secondary" className="text-xs">{p}</Badge>)
                        ) : (
                            <span className="text-xs italic text-muted-foreground">No permissions</span>
                        )}
                    </div>
                </div>
                <div className="flex items-center justify-end gap-2 border-t pt-4">
                    {role.is_system ? (
                        <span className="text-xs italic text-muted-foreground">System role locked</span>
                    ) : (
                        <>
                            <Button variant="outline" size="sm" onClick={onEdit} className="gap-1.5">
                                <Edit className="h-3.5 w-3.5" /><span>Edit</span>
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                disabled={role.users_count > 0}
                                onClick={onDelete}
                                className="gap-1.5"
                                title={role.users_count > 0 ? 'Cannot delete role with assigned users' : 'Delete'}
                            >
                                <Trash2 className="h-3.5 w-3.5" /><span>Delete</span>
                            </Button>
                        </>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}



export default function RolesIndex({ roles, permissionCatalog }: Props) {
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [editingRole, setEditingRole] = useState<RoleData | null>(null);
    const [deletingRole, setDeletingRole] = useState<RoleData | null>(null);

    const createForm = useForm({ name: '', permissions: [] as string[] });
    const editForm = useForm({ name: '', permissions: [] as string[] });
    const deleteForm = useForm();

    const handleOpenCreate = () => {
        createForm.reset();
        createForm.setData({ name: '', permissions: [] });
        setIsCreateOpen(true);
    };

    const handleOpenEdit = (role: RoleData) => {
        setEditingRole(role);
        editForm.setData({ name: role.name, permissions: [...role.permissions] });
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/admin/roles', {
            onSuccess: () => { setIsCreateOpen(false); createForm.reset(); },
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingRole) return;
        editForm.put(`/admin/roles/${editingRole.id}`, {
            onSuccess: () => { setEditingRole(null); editForm.reset(); },
        });
    };

    const handleDeleteSubmit = () => {
        if (!deletingRole) return;
        deleteForm.delete(`/admin/roles/${deletingRole.id}`, {
            onSuccess: () => setDeletingRole(null),
        });
    };

    const toggle = (perms: string[], key: string) =>
        perms.includes(key) ? perms.filter((p) => p !== key) : [...perms, key];

    return (
        <div className="flex h-full flex-1 flex-col gap-6 p-6">
            <Head title="Role & Permission Management" />

            <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Roles & Permissions</h1>
                    <p className="text-sm text-muted-foreground">
                        Configure dynamic role access levels and permission checklists across all modules.
                    </p>
                </div>
                <Button onClick={handleOpenCreate} className="gap-2">
                    <Plus className="h-4 w-4" />
                    <span>Create Role</span>
                </Button>
            </div>

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {roles.map((role) => (
                    <RoleCard
                        key={role.id}
                        role={role}
                        onEdit={() => handleOpenEdit(role)}
                        onDelete={() => setDeletingRole(role)}
                    />
                ))}
            </div>

            {/* Create Dialog */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Create New Role</DialogTitle>
                        <DialogDescription>Define a new role and choose permissions per module.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleCreateSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="create-name">Role Name</Label>
                            <Input
                                id="create-name"
                                value={createForm.data.name}
                                onChange={(e) => createForm.setData('name', e.target.value)}
                                placeholder="e.g. Site Supervisor"
                                required
                            />
                            {createForm.errors.name && <p className="text-xs text-destructive">{createForm.errors.name}</p>}
                        </div>
                        <PermissionChecklist
                            permissionCatalog={permissionCatalog}
                            selectedPerms={createForm.data.permissions}
                            onToggle={(key) => createForm.setData('permissions', toggle(createForm.data.permissions, key))}
                            idPrefix="create"
                        />
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>Cancel</Button>
                            <Button type="submit" disabled={createForm.processing}>
                                {createForm.processing ? 'Creating...' : 'Create Role'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>


            {/* Edit Dialog */}
            <Dialog open={!!editingRole} onOpenChange={(open) => !open && setEditingRole(null)}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Edit Role: {editingRole?.name}</DialogTitle>
                        <DialogDescription>Update role title and permissions.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEditSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="edit-name">Role Name</Label>
                            <Input
                                id="edit-name"
                                value={editForm.data.name}
                                onChange={(e) => editForm.setData('name', e.target.value)}
                                required
                            />
                            {editForm.errors.name && <p className="text-xs text-destructive">{editForm.errors.name}</p>}
                        </div>
                        <PermissionChecklist
                            permissionCatalog={permissionCatalog}
                            selectedPerms={editForm.data.permissions}
                            onToggle={(key) => editForm.setData('permissions', toggle(editForm.data.permissions, key))}
                            idPrefix="edit"
                        />
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setEditingRole(null)}>Cancel</Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {editForm.processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Dialog */}
            <Dialog open={!!deletingRole} onOpenChange={(open) => !open && setDeletingRole(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Role</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete the role &quot;{deletingRole?.name}&quot;? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setDeletingRole(null)}>Cancel</Button>
                        <Button variant="destructive" onClick={handleDeleteSubmit} disabled={deleteForm.processing}>
                            {deleteForm.processing ? 'Deleting...' : 'Delete'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

RolesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Roles & Permissions', href: '/admin/roles' },
    ],
};

