import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, Trash2, Edit2 } from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    tanks: any[];
    devices: any[];
}

export default function TanksHardwareIndex({ tanks, devices }: Props) {
    const [isTankModalOpen, setIsTankModalOpen] = useState(false);
    const [editingTank, setEditingTank] = useState<any>(null);

    const { data, setData, post, put, reset, errors, processing } = useForm({
        name: '',
        division: 'browser_tank',
        capacity_liters: '',
    });
    const [isDeviceModalOpen, setIsDeviceModalOpen] = useState(false);
    const [editingDevice, setEditingDevice] = useState<any>(null);

    const { 
        data: deviceData, 
        setData: setDeviceData, 
        post: postDevice, 
        put: putDevice, 
        reset: resetDevice, 
        errors: deviceErrors, 
        processing: deviceProcessing 
    } = useForm({
        device_id: '',
        device_type: 'mobile_unit',
        tank_id: '',
        status: 'active',
    });

    const openDeviceModal = () => {
        setEditingDevice(null);
        resetDevice();
        setIsDeviceModalOpen(true);
    };

    const openEditDeviceModal = (device: any) => {
        setEditingDevice(device);
        setDeviceData({
            device_id: device.device_id,
            device_type: device.device_type,
            tank_id: device.tank_id || '',
            status: device.status,
        });
        setIsDeviceModalOpen(true);
    };

    const onDeviceSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingDevice) {
            putDevice(`/admin/hardware/${editingDevice.device_id}`, {
                onSuccess: () => {
                    toast.success('Device updated successfully');
                    setIsDeviceModalOpen(false);
                },
            });
        } else {
            postDevice('/admin/hardware', {
                onSuccess: () => {
                    // Success message is handled by flash in layout or component
                    setIsDeviceModalOpen(false);
                },
            });
        }
    };

    const deleteDevice = (deviceId: string) => {
        if (confirm('Are you sure you want to delete this device?')) {
            router.delete(`/admin/hardware/${deviceId}`, {
                onSuccess: () => toast.success('Device deleted successfully'),
            });
        }
    };

    const regenerateToken = (deviceId: string) => {
        if (confirm('Regenerate API token? The old token will stop working immediately.')) {
            router.post(`/admin/hardware/${deviceId}/regenerate-token`, {}, {
                preserveScroll: true,
            });
        }
    };


    const openCreateModal = () => {
        setEditingTank(null);
        reset();
        setIsTankModalOpen(true);
    };

    const openEditModal = (tank: any) => {
        setEditingTank(tank);
        setData({
            name: tank.name,
            division: tank.division,
            capacity_liters: tank.capacity_liters.toString(),
        });
        setIsTankModalOpen(true);
    };

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (editingTank) {
            put(`/admin/tanks/${editingTank.tank_id}`, {
                onSuccess: () => {
                    toast.success('Tank updated successfully');
                    setIsTankModalOpen(false);
                },
            });
        } else {
            post('/admin/tanks', {
                onSuccess: () => {
                    toast.success('Tank created successfully');
                    setIsTankModalOpen(false);
                },
            });
        }
    };

    const deleteTank = (tankId: string) => {
        if (confirm('Are you sure you want to delete this tank?')) {
            router.delete(`/admin/tanks/${tankId}`, {
                onSuccess: () => toast.success('Tank deleted successfully'),
            });
        }
    };
    return (
        <div className="space-y-6">
            <Head title="Tanks & Hardware" />
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Tanks & Hardware</h1>
                    <p className="text-sm text-muted-foreground">Manage storage tanks, line units, and tracking hardware.</p>
                </div>
                <div className="flex items-center gap-2">
                    <Button size="sm" onClick={openCreateModal}><Plus className="mr-2 h-4 w-4" /> Register Tank</Button>
                    <Button variant="outline" size="sm" onClick={openDeviceModal}><Plus className="mr-2 h-4 w-4" /> Register Device</Button>
                </div>
            </div>

            <div className="grid gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Registered Tanks</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {tanks.length === 0 ? (
                            <div className="text-sm text-muted-foreground text-center py-8">No tanks registered yet.</div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="border-b text-muted-foreground">
                                        <tr>
                                            <th className="h-10 px-4 font-medium">Name</th>
                                            <th className="h-10 px-4 font-medium">Division</th>
                                            <th className="h-10 px-4 font-medium">Capacity (L)</th>
                                            <th className="h-10 px-4 font-medium text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {tanks.map((tank) => (
                                            <tr key={tank.tank_id}>
                                                <td className="p-4 font-medium">{tank.name}</td>
                                                <td className="p-4 capitalize">{tank.division?.replace('_', ' ')}</td>
                                                <td className="p-4">{tank.capacity_liters?.toLocaleString()}</td>
                                                <td className="p-4 text-right">
                                                    <Button variant="ghost" size="icon" onClick={() => openEditModal(tank)}>
                                                        <Edit2 className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon" className="text-destructive" onClick={() => deleteTank(tank.tank_id)}>
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Hardware Inventory</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {devices.length === 0 ? (
                            <div className="text-sm text-muted-foreground text-center py-8">No hardware devices found.</div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="border-b text-muted-foreground">
                                        <tr>
                                            <th className="h-10 px-4 font-medium">Device ID</th>
                                            <th className="h-10 px-4 font-medium">Type</th>
                                            <th className="h-10 px-4 font-medium">Assigned Tank</th>
                                            <th className="h-10 px-4 font-medium">Status</th>
                                            <th className="h-10 px-4 font-medium text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {devices.map((device) => (
                                            <tr key={device.device_id}>
                                                <td className="p-4 font-mono">{device.device_id}</td>
                                                <td className="p-4 capitalize">{device.device_type?.replace('_', ' ')}</td>
                                                <td className="p-4">{device.tank?.name || 'Unassigned'}</td>
                                                <td className="p-4 capitalize">{device.status || 'Active'}</td>
                                                <td className="p-4 text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Button variant="outline" size="sm" onClick={() => regenerateToken(device.device_id)}>
                                                            Regen Token
                                                        </Button>
                                                        <Button variant="ghost" size="icon" onClick={() => openEditDeviceModal(device)}>
                                                            <Edit2 className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="ghost" size="icon" className="text-destructive" onClick={() => deleteDevice(device.device_id)}>
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
            </div>

            <Dialog open={isTankModalOpen} onOpenChange={setIsTankModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editingTank ? 'Edit Tank' : 'Register New Tank'}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Tank Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                            />
                            {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>Division</Label>
                            <Select
                                value={data.division}
                                onValueChange={(value) => setData('division', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select division" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="browser_tank">Browser Tank</SelectItem>
                                    <SelectItem value="fuel_tanker">Fuel Tanker</SelectItem>
                                    <SelectItem value="main_tank">Main Tank</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.division && <p className="text-sm text-destructive">{errors.division}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="capacity">Capacity (Liters)</Label>
                            <Input
                                id="capacity"
                                type="number"
                                value={data.capacity_liters}
                                onChange={(e) => setData('capacity_liters', e.target.value)}
                            />
                            {errors.capacity_liters && <p className="text-sm text-destructive">{errors.capacity_liters}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsTankModalOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {editingTank ? 'Update Tank' : 'Register Tank'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={isDeviceModalOpen} onOpenChange={setIsDeviceModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editingDevice ? 'Edit Device' : 'Register New Device'}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={onDeviceSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="device_id">Device ID / Serial Number</Label>
                            <Input
                                id="device_id"
                                value={deviceData.device_id}
                                onChange={(e) => setDeviceData('device_id', e.target.value)}
                                disabled={!!editingDevice}
                            />
                            {deviceErrors.device_id && <p className="text-sm text-destructive">{deviceErrors.device_id}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>Device Type</Label>
                            <Select
                                value={deviceData.device_type}
                                onValueChange={(value) => setDeviceData('device_type', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="main_tank_atg">Main Tank ATG</SelectItem>
                                    <SelectItem value="fill_line">Fill Line Flowmeter</SelectItem>
                                    <SelectItem value="dispense_line">Dispense Line</SelectItem>
                                    <SelectItem value="mobile_unit">Mobile Unit (Browser/Tanker)</SelectItem>
                                </SelectContent>
                            </Select>
                            {deviceErrors.device_type && <p className="text-sm text-destructive">{deviceErrors.device_type}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>Assigned Tank</Label>
                            <Select
                                value={deviceData.tank_id}
                                onValueChange={(value) => setDeviceData('tank_id', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select tank (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Unassigned</SelectItem>
                                    {tanks.map(t => (
                                        <SelectItem key={t.tank_id} value={t.tank_id.toString()}>{t.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {deviceErrors.tank_id && <p className="text-sm text-destructive">{deviceErrors.tank_id}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>Status</Label>
                            <Select
                                value={deviceData.status}
                                onValueChange={(value) => setDeviceData('status', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="spare">Spare</SelectItem>
                                    <SelectItem value="retired">Retired</SelectItem>
                                </SelectContent>
                            </Select>
                            {deviceErrors.status && <p className="text-sm text-destructive">{deviceErrors.status}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsDeviceModalOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={deviceProcessing}>
                                {editingDevice ? 'Update Device' : 'Register Device'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    );
}

TanksHardwareIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Tanks & Hardware', href: '/admin/tanks' },
    ],
};
