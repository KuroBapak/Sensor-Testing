import { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Edit, Plus, Trash2, UserCog } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import type { Auth } from '@/types';

interface UserRole {
    id: number;
    name: string;
    is_system: boolean;
}

interface UserData {
    id: number;
    name: string;
    email: string;
    role_id: number | null;
    role: UserRole | null;
    created_at: string | null;
}

interface Props {
    users: UserData[];
    roles: UserRole[];
}


export default function UsersIndex({ users, roles }: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const currentUserId = auth.user.id;

    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<UserData | null>(null);
    const [deletingUser, setDeletingUser] = useState<UserData | null>(null);

    const createForm = useForm({
        name: '',
        email: '',
        password: '',
        role_id: '',
    });

    const editForm = useForm({
        name: '',
        email: '',
        password: '',
        role_id: '',
    });

    const deleteForm = useForm();

    const handleOpenCreate = () => {
        createForm.reset();
        createForm.setData({ name: '', email: '', password: '', role_id: '' });
        setIsCreateOpen(true);
    };

    const handleOpenEdit = (user: UserData) => {
        setEditingUser(user);
        editForm.setData({
            name: user.name,
            email: user.email,
            password: '',
            role_id: user.role_id?.toString() ?? '',
        });
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/admin/users', {
            onSuccess: () => { setIsCreateOpen(false); createForm.reset(); },
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingUser) return;
        editForm.put(`/admin/users/${editingUser.id}`, {
            onSuccess: () => { setEditingUser(null); editForm.reset(); },
        });
    };

    const handleDeleteSubmit = () => {
        if (!deletingUser) return;
        deleteForm.delete(`/admin/users/${deletingUser.id}`, {
            onSuccess: () => setDeletingUser(null),
        });
    };

    const formatDate = (iso: string | null) => {
        if (!iso) return '—';
        return new Date(iso).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    return (
        <div className="flex h-full flex-1 flex-col gap-6 p-6">
            <Head title="User Management" />

            <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">User Management</h1>
                    <p className="text-sm text-muted-foreground">
                        Create, edit, and assign roles to system users.
                    </p>
                </div>
                <Button onClick={handleOpenCreate} className="gap-2">
                    <Plus className="h-4 w-4" />
                    <span>Add User</span>
                </Button>
            </div>

            {/* Users table */}
            <div className="rounded-lg border">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50">
                                <th className="px-4 py-3 text-left font-medium">Name</th>
                                <th className="px-4 py-3 text-left font-medium">Email</th>
                                <th className="px-4 py-3 text-left font-medium">Role</th>
                                <th className="px-4 py-3 text-left font-medium">Created</th>
                                <th className="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr key={user.id} className="border-b last:border-0 hover:bg-muted/30">
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-2">
                                            <UserCog className="h-4 w-4 text-muted-foreground" />
                                            <span className="font-medium">{user.name}</span>
                                            {user.id === currentUserId && (
                                                <Badge variant="outline" className="text-[10px]">You</Badge>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">{user.email}</td>
                                    <td className="px-4 py-3">
                                        {user.role ? (
                                            <Badge variant={user.role.is_system ? 'default' : 'secondary'}>
                                                {user.role.name}
                                            </Badge>
                                        ) : (
                                            <span className="text-xs italic text-muted-foreground">No role</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">{formatDate(user.created_at)}</td>
                                    <td className="px-4 py-3 text-right">
                                        <div className="flex items-center justify-end gap-2">
                                            <Button variant="outline" size="sm" onClick={() => handleOpenEdit(user)} className="gap-1.5">
                                                <Edit className="h-3.5 w-3.5" /><span>Edit</span>
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                disabled={user.id === currentUserId}
                                                onClick={() => setDeletingUser(user)}
                                                className="gap-1.5"
                                                title={user.id === currentUserId ? 'Cannot delete your own account' : 'Delete user'}
                                            >
                                                <Trash2 className="h-3.5 w-3.5" /><span>Delete</span>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {users.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">No users found.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>


            {/* Create User Dialog */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Add New User</DialogTitle>
                        <DialogDescription>Create a new user account and assign a role.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleCreateSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="create-name">Full Name</Label>
                            <Input
                                id="create-name"
                                value={createForm.data.name}
                                onChange={(e) => createForm.setData('name', e.target.value)}
                                placeholder="John Doe"
                                required
                            />
                            {createForm.errors.name && <p className="text-xs text-destructive">{createForm.errors.name}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="create-email">Email</Label>
                            <Input
                                id="create-email"
                                type="email"
                                value={createForm.data.email}
                                onChange={(e) => createForm.setData('email', e.target.value)}
                                placeholder="john@example.com"
                                required
                            />
                            {createForm.errors.email && <p className="text-xs text-destructive">{createForm.errors.email}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="create-password">Password</Label>
                            <Input
                                id="create-password"
                                type="password"
                                value={createForm.data.password}
                                onChange={(e) => createForm.setData('password', e.target.value)}
                                placeholder="Min. 8 characters"
                                required
                            />
                            {createForm.errors.password && <p className="text-xs text-destructive">{createForm.errors.password}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="create-role">Role</Label>
                            <Select value={createForm.data.role_id} onValueChange={(v) => createForm.setData('role_id', v)}>
                                <SelectTrigger id="create-role">
                                    <SelectValue placeholder="Select a role" />
                                </SelectTrigger>
                                <SelectContent>
                                    {roles.map((role) => (
                                        <SelectItem key={role.id} value={role.id.toString()}>
                                            {role.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {createForm.errors.role_id && <p className="text-xs text-destructive">{createForm.errors.role_id}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>Cancel</Button>
                            <Button type="submit" disabled={createForm.processing}>
                                {createForm.processing ? 'Creating...' : 'Create User'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>


            {/* Edit User Dialog */}
            <Dialog open={!!editingUser} onOpenChange={(open) => !open && setEditingUser(null)}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Edit User: {editingUser?.name}</DialogTitle>
                        <DialogDescription>Update user details. Leave password blank to keep unchanged.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEditSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="edit-name">Full Name</Label>
                            <Input
                                id="edit-name"
                                value={editForm.data.name}
                                onChange={(e) => editForm.setData('name', e.target.value)}
                                required
                            />
                            {editForm.errors.name && <p className="text-xs text-destructive">{editForm.errors.name}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-email">Email</Label>
                            <Input
                                id="edit-email"
                                type="email"
                                value={editForm.data.email}
                                onChange={(e) => editForm.setData('email', e.target.value)}
                                required
                            />
                            {editForm.errors.email && <p className="text-xs text-destructive">{editForm.errors.email}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-password">Password</Label>
                            <Input
                                id="edit-password"
                                type="password"
                                value={editForm.data.password}
                                onChange={(e) => editForm.setData('password', e.target.value)}
                                placeholder="Leave blank to keep unchanged"
                            />
                            {editForm.errors.password && <p className="text-xs text-destructive">{editForm.errors.password}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-role">Role</Label>
                            <Select value={editForm.data.role_id} onValueChange={(v) => editForm.setData('role_id', v)}>
                                <SelectTrigger id="edit-role">
                                    <SelectValue placeholder="Select a role" />
                                </SelectTrigger>
                                <SelectContent>
                                    {roles.map((role) => (
                                        <SelectItem key={role.id} value={role.id.toString()}>
                                            {role.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {editForm.errors.role_id && <p className="text-xs text-destructive">{editForm.errors.role_id}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setEditingUser(null)}>Cancel</Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {editForm.processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete User Dialog */}
            <Dialog open={!!deletingUser} onOpenChange={(open) => !open && setDeletingUser(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete User</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete &quot;{deletingUser?.name}&quot;? This action cannot be undone.
                            {deletingUser?.role?.is_system && (
                                <span className="mt-2 block text-amber-600 dark:text-amber-400">
                                    Warning: This user has a Super Admin role. Deleting them may leave the system without an administrator.
                                </span>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setDeletingUser(null)}>Cancel</Button>
                        <Button variant="destructive" onClick={handleDeleteSubmit} disabled={deleteForm.processing}>
                            {deleteForm.processing ? 'Deleting...' : 'Delete User'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'User Management', href: '/admin/users' },
    ],
};

