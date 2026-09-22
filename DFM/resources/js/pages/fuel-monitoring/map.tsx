import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { 
    Truck, 
    Layers, 
    RefreshCw, 
    Radio, 
    AlertTriangle, 
    Shield, 
    Fuel, 
    MapPin, 
    Search,
    Clock,
    Activity
} from 'lucide-react';
import { useEffect, useState, useMemo } from 'react';
import FleetMapLeaflet, { type TankMarkerData, type GeofenceData } from '@/components/fuel-monitoring/FleetMapLeaflet';

interface Props {
    tanks: TankMarkerData[];
    geofences: GeofenceData[];
}

export default function FleetMap({ tanks = [], geofences = [] }: Props) {
    const [isPolling, setIsPolling] = useState(true);
    const [showGeofences, setShowGeofences] = useState(true);
    const [selectedTank, setSelectedTank] = useState<TankMarkerData | null>(null);
    const [searchQuery, setSearchQuery] = useState('');

    useEffect(() => {
        if (!isPolling) return;

        const interval = setInterval(() => {
            router.reload({ only: ['tanks'] });
        }, 5000);

        return () => clearInterval(interval);
    }, [isPolling]);

    const filteredTanks = useMemo(() => {
        if (!searchQuery.trim()) return tanks;
        const q = searchQuery.toLowerCase();
        return tanks.filter(
            (t) =>
                t.name.toLowerCase().includes(q) ||
                String(t.id).toLowerCase().includes(q) ||
                t.type.toLowerCase().includes(q)
        );
    }, [tanks, searchQuery]);

    const stats = useMemo(() => {
        const total = tanks.length;
        const active = tanks.filter((t) => t.status === 'active').length;
        const signalLost = tanks.filter((t) => t.status === 'signal_lost').length;
        const moving = tanks.filter((t) => t.speed > 0).length;
        return { total, active, signalLost, moving, geofences: geofences.length };
    }, [tanks, geofences]);


    return (
        <div className="space-y-6">
            <Head title="Live Fleet Map" />
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Live Fleet Map</h1>
                    <p className="text-sm text-muted-foreground">
                        Interactive OpenStreetMap tracking for Browser Tanks and Fuel Tankers with active geofence boundaries.
                    </p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    <Button 
                        variant={isPolling ? "default" : "outline"} 
                        size="sm" 
                        onClick={() => setIsPolling(!isPolling)}
                        className="gap-1.5"
                    >
                        <RefreshCw className={`h-3.5 w-3.5 ${isPolling ? 'animate-spin' : ''}`} />
                        {isPolling ? 'Live Updates: ON' : 'Live Updates: OFF'}
                    </Button>
                    <Button 
                        variant={showGeofences ? "secondary" : "outline"} 
                        size="sm"
                        onClick={() => setShowGeofences(!showGeofences)}
                        className="gap-1.5"
                    >
                        <Layers className="h-4 w-4" /> 
                        {showGeofences ? 'Geofences Visible' : 'Geofences Hidden'}
                    </Button>
                </div>
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-xs font-medium text-muted-foreground">Total Fleet</CardTitle>
                        <Truck className="h-4 w-4 text-primary" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.total}</div>
                        <p className="text-[11px] text-muted-foreground mt-0.5">
                            {stats.moving} currently moving
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-xs font-medium text-muted-foreground">Online Units</CardTitle>
                        <Radio className="h-4 w-4 text-emerald-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {stats.active}
                        </div>
                        <p className="text-[11px] text-muted-foreground mt-0.5">
                            Real-time GPS telemetry active
                        </p>
                    </CardContent>
                </Card>

                <Card className={stats.signalLost > 0 ? "border-amber-500/50 bg-amber-500/5" : ""}>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-xs font-medium text-muted-foreground">Signal Lost</CardTitle>
                        <AlertTriangle className={`h-4 w-4 ${stats.signalLost > 0 ? 'text-amber-500' : 'text-muted-foreground'}`} />
                    </CardHeader>
                    <CardContent>
                        <div className={`text-2xl font-bold ${stats.signalLost > 0 ? 'text-amber-600 dark:text-amber-400' : ''}`}>
                            {stats.signalLost}
                        </div>
                        <p className="text-[11px] text-muted-foreground mt-0.5">
                            Fix older than 15 minutes
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-xs font-medium text-muted-foreground">Active Geofences</CardTitle>
                        <Shield className="h-4 w-4 text-sky-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-sky-600 dark:text-sky-400">
                            {stats.geofences}
                        </div>
                        <p className="text-[11px] text-muted-foreground mt-0.5">
                            Server-side boundary monitoring
                        </p>
                    </CardContent>
                </Card>
            </div>

            </div>
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div className="lg:col-span-3">
                    <Card className="p-1 overflow-hidden">
                        <FleetMapLeaflet
                            tanks={tanks}
                            geofences={geofences}
                            showGeofences={showGeofences}
                            onToggleGeofences={() => setShowGeofences(!showGeofences)}
                            selectedTankId={selectedTank?.id}
                            onSelectTank={(tank) => setSelectedTank(tank)}
                        />
                    </Card>
                </div>
                <div className="lg:col-span-1 space-y-4">
                    <Card className="h-[650px] flex flex-col">
                        <CardHeader className="p-4 pb-2 border-b">
                            <CardTitle className="text-sm font-semibold flex items-center justify-between">
                                <span>Fleet Units</span>
                                <Badge variant="secondary" className="text-xs font-mono">
                                    {filteredTanks.length}
                                </Badge>
                            </CardTitle>
                            <CardDescription className="text-xs">
                                Click a vehicle to focus on the map
                            </CardDescription>
                            <div className="relative mt-2">
                                <Search className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-muted-foreground" />
                                <Input
                                    placeholder="Filter vehicle name / ID..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="pl-8 h-8 text-xs"
                                />
                            </div>
                        </CardHeader>

                        <CardContent className="p-2 flex-1 overflow-y-auto space-y-2">
                            {filteredTanks.map((tank) => {
                                const isSelected = selectedTank?.id === tank.id;
                                const isSignalLost = tank.status === 'signal_lost';

                                return (
                                    <div
                                        key={tank.id}
                                        onClick={() => setSelectedTank(tank)}
                                        className={`p-3 rounded-lg border text-xs cursor-pointer transition-all ${
                                            isSelected
                                                ? 'border-primary bg-primary/5 ring-1 ring-primary shadow-sm'
                                                : 'hover:bg-accent/50 border-border'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between mb-1">
                                            <div className="font-semibold text-foreground flex items-center gap-1.5">
                                                <MapPin className={`h-3.5 w-3.5 ${
                                                    isSignalLost 
                                                        ? 'text-slate-400' 
                                                        : tank.type === 'fuel_tanker' 
                                                            ? 'text-sky-500' 
                                                            : 'text-emerald-500'
                                                }`} />
                                                <span>{tank.name}</span>
                                            </div>
                                            <Badge
                                                variant={isSignalLost ? 'destructive' : 'secondary'}
                                                className="text-[9px] uppercase px-1.5 py-0"
                                            >
                                                {isSignalLost ? 'Signal Lost' : 'Online'}
                                            </Badge>
                                        </div>

                                        <div className="grid grid-cols-2 gap-1 text-[11px] text-muted-foreground mt-2">
                                            <div className="flex items-center gap-1">
                                                <Fuel className="h-3 w-3" />
                                                <span>{tank.capacity?.toLocaleString()} L</span>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <Activity className="h-3 w-3" />
                                                <span>{tank.speed || 0} km/h</span>
                                            </div>
                                        </div>

                                        <div className="flex items-center justify-between text-[10px] text-muted-foreground mt-2 pt-1.5 border-t border-border/50">
                                            <span className="capitalize">{tank.type?.replace('_', ' ')}</span>
                                            <span className="flex items-center gap-1">
                                                <Clock className="h-2.5 w-2.5" />
                                                {new Date(tank.last_updated).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                            </span>
                                        </div>
                                    </div>
                                );
                            })}

                            {filteredTanks.length === 0 && (
                                <div className="text-center py-8 text-xs text-muted-foreground">
                                    No fleet units matched your search.
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>


        </div>
    );
}

FleetMap.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Live Fleet Map', href: '/fuel-monitoring/map' },
    ],
};
