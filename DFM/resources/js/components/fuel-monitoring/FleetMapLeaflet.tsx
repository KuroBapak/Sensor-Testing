import { useEffect, useState, useMemo } from 'react';
import { MapContainer, TileLayer, Marker, Popup, Tooltip, Polygon, useMap } from 'react-leaflet';
import L from 'leaflet';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
    Truck, 
    Fuel, 
    Navigation, 
    Clock, 
    Radio, 
    AlertTriangle, 
    Crosshair, 
    Layers, 
    Shield
} from 'lucide-react';

export interface TankMarkerData {
    id: string | number;
    name: string;
    type: 'browser_tank' | 'fuel_tanker' | string;
    capacity: number;
    status: 'active' | 'signal_lost' | 'offline' | string;
    lat: number;
    lng: number;
    heading: number;
    speed: number;
    last_updated: string;
}

export interface GeofenceData {
    id: number | string;
    name: string;
    applies_to?: string;
    is_active?: boolean;
    coordinates: [number, number][] | [number, number][][];
}

interface FleetMapLeafletProps {
    tanks: TankMarkerData[];
    geofences: GeofenceData[];
    showGeofences?: boolean;
    onToggleGeofences?: () => void;
    selectedTankId?: string | number | null;
    onSelectTank?: (tank: TankMarkerData) => void;
}

// Controller component to smoothly pan/zoom map when actions trigger
function MapCameraController({
    center,
    zoom,
    bounds,
    triggerCenter,
}: {
    center?: [number, number];
    zoom?: number;
    bounds?: L.LatLngBoundsExpression;
    triggerCenter?: number;
}) {
    const map = useMap();

    useEffect(() => {
        if (bounds) {
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16, animate: true });
        } else if (center && zoom) {
            map.flyTo(center, zoom, { duration: 1.2 });
        }
    }, [bounds, center, zoom, triggerCenter, map]);

    return null;
}

// Helper to create custom Leaflet DivIcon for tanks
function createTankDivIcon(tank: TankMarkerData, isSelected: boolean): L.DivIcon {
    const isSignalLost = tank.status === 'signal_lost';
    const isFuelTanker = tank.type === 'fuel_tanker';

    const primaryColor = isSignalLost 
        ? '#64748b' 
        : isFuelTanker 
            ? '#0284c7' 
            : '#10b981';

    const ringColor = isSelected 
        ? '#f59e0b' 
        : isSignalLost 
            ? '#94a3b8' 
            : isFuelTanker 
                ? '#38bdf8' 
                : '#34d399';

    const iconSvg = isFuelTanker
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M15 10h4a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-4"/><circle cx="10" cy="18" r="1"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>`;

    const html = `
        <div class="relative flex flex-col items-center cursor-pointer" style="transform: translate(-50%, -50%);">
            ${!isSignalLost && tank.speed > 0 ? `
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color: ${primaryColor};"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3" style="background-color: ${primaryColor};"></span>
                </span>
            ` : ''}

            <div 
                class="flex items-center justify-center rounded-full shadow-lg text-white"
                style="
                    background-color: ${primaryColor}; 
                    width: ${isSelected ? '44px' : '38px'}; 
                    height: ${isSelected ? '44px' : '38px'};
                    border: 2.5px solid ${ringColor};
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                "
            >
                ${iconSvg}
            </div>

            ${tank.heading !== undefined ? `
                <div 
                    class="absolute -top-2 flex items-center justify-center pointer-events-none"
                    style="
                        transform: rotate(${tank.heading}deg) translateY(-14px);
                        transition: transform 0.5s ease-out;
                    "
                >
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="${primaryColor}">
                        <polygon points="12,2 22,22 12,17 2,22" />
                    </svg>
                </div>
            ` : ''}

            <div 
                class="mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold whitespace-nowrap shadow-sm border backdrop-blur-sm pointer-events-none"
                style="
                    background-color: rgba(15, 23, 42, 0.88);
                    color: #ffffff;
                    border-color: ${ringColor};
                "
            >
                ${tank.name}
            </div>

            ${isSignalLost ? `
                <div class="mt-0.5 px-1 py-0.2 rounded bg-amber-500 text-white text-[8px] font-bold uppercase tracking-wider">
                    Signal Lost
                </div>
            ` : ''}
        </div>
    `;

    return L.divIcon({
        className: 'fleet-leaflet-custom-marker',
        html: html,
        iconSize: [40, 50],
        iconAnchor: [20, 25],
        popupAnchor: [0, -25],
    });
}


export default function FleetMapLeaflet({
    tanks,
    geofences,
    showGeofences = true,
    onToggleGeofences,
    selectedTankId,
    onSelectTank,
}: FleetMapLeafletProps) {
    const [mounted, setMounted] = useState(false);
    const [centerTrigger, setCenterTrigger] = useState(0);
    const [activeTile, setActiveTile] = useState<'osm' | 'voyager' | 'dark'>('voyager');

    useEffect(() => {
        setMounted(true);
    }, []);

    const mapBounds = useMemo(() => {
        if (!tanks || tanks.length === 0) return null;

        const points: [number, number][] = tanks.map((t) => [t.lat, t.lng]);
        
        if (showGeofences && geofences && geofences.length > 0) {
            geofences.forEach((geo) => {
                if (Array.isArray(geo.coordinates)) {
                    geo.coordinates.forEach((pt: any) => {
                        if (Array.isArray(pt) && typeof pt[0] === 'number') {
                            points.push([pt[0], pt[1]]);
                        }
                    });
                }
            });
        }

        if (points.length === 0) return null;
        return L.latLngBounds(points);
    }, [tanks, geofences, showGeofences]);

    const defaultCenter: [number, number] = useMemo(() => {
        if (tanks && tanks.length > 0) {
            return [tanks[0].lat, tanks[0].lng];
        }
        return [-2.6000, 118.0000];
    }, [tanks]);

    const handleCenterFleet = () => {
        setCenterTrigger((prev) => prev + 1);
    };

    if (!mounted) {
        return (
            <Card className="h-[650px] w-full flex flex-col items-center justify-center bg-muted/20 border-dashed animate-pulse">
                <div className="flex flex-col items-center gap-2 text-muted-foreground">
                    <Radio className="h-8 w-8 animate-spin text-primary" />
                    <span className="text-sm font-medium">Initializing Interactive Map...</span>
                </div>
            </Card>
        );
    }

    const tileUrls = {
        osm: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        voyager: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
        dark: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    };

    return (
        <div className="relative w-full h-[650px] rounded-xl overflow-hidden border shadow-inner isolate">
            {/* Overlay Map Controls */}
            <div className="absolute top-4 right-4 z-[1000] flex flex-col gap-2 pointer-events-auto">
                <div className="flex items-center gap-1 bg-background/90 backdrop-blur-md p-1.5 rounded-lg border shadow-md">
                    <Button
                        variant={activeTile === 'voyager' ? 'default' : 'ghost'}
                        size="sm"
                        className="h-7 text-xs px-2"
                        onClick={() => setActiveTile('voyager')}
                    >
                        Map
                    </Button>
                    <Button
                        variant={activeTile === 'dark' ? 'default' : 'ghost'}
                        size="sm"
                        className="h-7 text-xs px-2"
                        onClick={() => setActiveTile('dark')}
                    >
                        Dark
                    </Button>
                    <Button
                        variant={activeTile === 'osm' ? 'default' : 'ghost'}
                        size="sm"
                        className="h-7 text-xs px-2"
                        onClick={() => setActiveTile('osm')}
                    >
                        OSM
                    </Button>
                </div>

                <div className="flex flex-col gap-1.5 bg-background/90 backdrop-blur-md p-1.5 rounded-lg border shadow-md">
                    <Button
                        variant="secondary"
                        size="sm"
                        className="h-8 text-xs justify-start gap-1.5"
                        onClick={handleCenterFleet}
                        title="Fit all fleet units into view"
                    >
                        <Crosshair className="h-3.5 w-3.5 text-primary" />
                        <span>Center Fleet</span>
                    </Button>

                    {onToggleGeofences && (
                        <Button
                            variant={showGeofences ? 'secondary' : 'outline'}
                            size="sm"
                            className="h-8 text-xs justify-start gap-1.5"
                            onClick={onToggleGeofences}
                            title="Toggle geofence overlays"
                        >
                            <Layers className="h-3.5 w-3.5 text-primary" />
                            <span>{showGeofences ? 'Hide Geofences' : 'Show Geofences'}</span>
                        </Button>
                    )}
                </div>
            </div>

            {/* Bottom Status Legend */}
            <div className="absolute bottom-4 left-4 z-[1000] flex flex-wrap items-center gap-2 pointer-events-auto">
                <div className="flex items-center gap-3 bg-background/90 backdrop-blur-md px-3 py-1.5 rounded-lg border text-xs shadow-md">
                    <div className="flex items-center gap-1.5">
                        <span className="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span className="font-medium text-foreground">Browser Tank</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                        <span className="font-medium text-foreground">Fuel Tanker</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="h-2.5 w-2.5 rounded-full bg-slate-500"></span>
                        <span className="font-medium text-foreground">Signal Lost</span>
                    </div>
                    {showGeofences && geofences.length > 0 && (
                        <div className="flex items-center gap-1.5 border-l pl-2.5 text-muted-foreground">
                            <Shield className="h-3.5 w-3.5 text-primary" />
                            <span>{geofences.length} Geofences Active</span>
                        </div>
                    )}
                </div>
            </div>


            {/* Leaflet MapContainer */}
            <MapContainer
                center={defaultCenter}
                zoom={14}
                scrollWheelZoom={true}
                className="h-full w-full z-0"
            >
                <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/">CARTO</a>'
                    url={tileUrls[activeTile]}
                    maxZoom={19}
                />

                <MapCameraController
                    bounds={mapBounds || undefined}
                    center={mapBounds ? undefined : defaultCenter}
                    zoom={mapBounds ? undefined : 14}
                    triggerCenter={centerTrigger}
                />

                {/* Geofence Polygons */}
                {showGeofences &&
                    geofences.map((geo, index) => {
                        const coords = geo.coordinates as [number, number][];
                        if (!coords || !Array.isArray(coords) || coords.length < 3) return null;

                        const isDepot = geo.name.toLowerCase().includes('depot') || geo.name.toLowerCase().includes('workshop');
                        const color = isDepot ? '#f59e0b' : '#3b82f6';

                        return (
                            <Polygon
                                key={`geofence-${geo.id || index}`}
                                positions={coords}
                                pathOptions={{
                                    color: color,
                                    fillColor: color,
                                    fillOpacity: 0.15,
                                    weight: 2,
                                    dashArray: '4, 6',
                                }}
                            >
                                <Tooltip sticky direction="top" opacity={0.9}>
                                    <div className="font-semibold text-xs flex items-center gap-1">
                                        <Shield className="h-3 w-3 text-primary" />
                                        {geo.name}
                                    </div>
                                    <div className="text-[10px] text-muted-foreground">
                                        Applies to: {geo.applies_to || 'All Mobile Units'}
                                    </div>
                                </Tooltip>
                                <Popup>
                                    <div className="p-1 space-y-1.5 min-w-[180px]">
                                        <div className="font-bold text-sm text-foreground flex items-center gap-1.5">
                                            <Shield className="h-4 w-4 text-primary" />
                                            {geo.name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            Target: <span className="font-medium text-foreground">{geo.applies_to || 'All Mobile Units'}</span>
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            Status: <Badge variant="secondary" className="text-[10px] py-0">Active Boundary</Badge>
                                        </div>
                                    </div>
                                </Popup>
                            </Polygon>
                        );
                    })}


                {/* Tank Fleet Markers */}
                {tanks.map((tank) => {
                    const isSelected = selectedTankId === tank.id;
                    const icon = createTankDivIcon(tank, isSelected);

                    return (
                        <Marker
                            key={`tank-${tank.id}`}
                            position={[tank.lat, tank.lng]}
                            icon={icon}
                            eventHandlers={{
                                click: () => onSelectTank?.(tank),
                            }}
                        >
                            <Popup className="fleet-leaflet-popup">
                                <div className="p-1 min-w-[220px] space-y-2 text-slate-800 dark:text-slate-100">
                                    <div className="flex items-center justify-between border-b pb-1.5">
                                        <div>
                                            <h4 className="font-bold text-sm leading-tight">{tank.name}</h4>
                                            <span className="text-[10px] text-muted-foreground uppercase font-medium">
                                                ID: {tank.id}
                                            </span>
                                        </div>
                                        <Badge
                                            variant={tank.status === 'signal_lost' ? 'destructive' : 'default'}
                                            className="text-[10px] uppercase font-bold"
                                        >
                                            {tank.status === 'signal_lost' ? 'Signal Lost' : 'Online'}
                                        </Badge>
                                    </div>

                                    <div className="grid grid-cols-2 gap-1.5 text-xs">
                                        <div className="flex items-center gap-1.5 bg-muted/40 p-1.5 rounded">
                                            <Fuel className="h-3.5 w-3.5 text-primary shrink-0" />
                                            <div>
                                                <div className="text-[9px] text-muted-foreground uppercase">Capacity</div>
                                                <div className="font-semibold">{tank.capacity?.toLocaleString()} L</div>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-1.5 bg-muted/40 p-1.5 rounded">
                                            <Truck className="h-3.5 w-3.5 text-primary shrink-0" />
                                            <div>
                                                <div className="text-[9px] text-muted-foreground uppercase">Speed</div>
                                                <div className="font-semibold">{tank.speed || 0} km/h</div>
                                            </div>
                                        </div>


                                        <div className="flex items-center gap-1.5 bg-muted/40 p-1.5 rounded">
                                            <Navigation className="h-3.5 w-3.5 text-primary shrink-0" />
                                            <div>
                                                <div className="text-[9px] text-muted-foreground uppercase">Heading</div>
                                                <div className="font-semibold">{tank.heading || 0}°</div>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-1.5 bg-muted/40 p-1.5 rounded">
                                            <Radio className="h-3.5 w-3.5 text-primary shrink-0" />
                                            <div>
                                                <div className="text-[9px] text-muted-foreground uppercase">Type</div>
                                                <div className="font-semibold capitalize">{tank.type?.replace('_', ' ')}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-1 text-[11px] text-muted-foreground border-t pt-1.5">
                                        <div className="flex justify-between">
                                            <span>Coordinates:</span>
                                            <span className="font-mono text-[10px] text-foreground">
                                                {tank.lat.toFixed(5)}, {tank.lng.toFixed(5)}
                                            </span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span>Last Update:</span>
                                            <span className="text-[10px] text-foreground flex items-center gap-1">
                                                <Clock className="h-2.5 w-2.5" />
                                                {new Date(tank.last_updated).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
                                            </span>
                                        </div>
                                    </div>

                                    {tank.status === 'signal_lost' && (
                                        <div className="bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 p-1.5 rounded text-[10px] flex items-center gap-1">
                                            <AlertTriangle className="h-3.5 w-3.5 shrink-0" />
                                            GPS telemetry is older than 15 minutes.
                                        </div>
                                    )}
                                </div>
                            </Popup>
                        </Marker>
                    );
                })}
            </MapContainer>
        </div>
    );
}


