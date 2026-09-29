import { Head, router } from '@inertiajs/react';
import { useMemo } from 'react';
import { MapContainer, TileLayer, useMap } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { httpJson } from '@/lib/http';

interface Geofence {
    id: number;
    name: string;
    applies_to: string;
    is_active: boolean;
    coordinates: [number, number][];
}

interface Props {
    geofences: Geofence[];
}

const SITE_CENTER: [number, number] = [-1.6747, 113.3800];

function toLatLngs(coordinates: [number, number][]): L.LatLngExpression[] {
    return coordinates.map(([lat, lng]) => [lat, lng] as L.LatLngExpression);
}

function fromLatLngs(latlngs: L.LatLng[] | L.LatLng[][]): [number, number][] {
    const flat = (Array.isArray(latlngs[0]) ? (latlngs[0] as L.LatLng[]) : (latlngs as L.LatLng[]));
    return flat.map((p) => [Number(p.lat.toFixed(6)), Number(p.lng.toFixed(6))] as [number, number]);
}

function DrawManager({ geofences }: { geofences: Geofence[] }) {
    const map = useMap();

    const drawn: any = useMemo(() => {
        const group: any = new (L as any).FeatureGroup();
        geofences.forEach((g) => {
            if (!g.is_active) return;
            const polygon = L.polygon(toLatLngs(g.coordinates), {
                color: '#0ea5e9',
                weight: 2,
                fillOpacity: 0.08,
            });
            (polygon as any)._geofenceId = g.id;
            polygon.bindTooltip(g.name, { permanent: false, direction: 'top' });
            group.addLayer(polygon);
        });
        group.addTo(map);
        return group;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useMemo(() => {
        const drawControl = new (L as any).Control.Draw({
            position: 'topleft',
            draw: {
                polygon: { allowIntersection: false, shapeOptions: { color: '#0ea5e9' }, metric: true },
                rectangle: false,
                circle: false,
                marker: false,
                polyline: false,
                circlemarker: false,
            },
            edit: { featureGroup: drawn, remove: true },
        });
        map.addControl(drawControl);

        const onCreated = async (e: any) => {
            const layer = e.layer;
            const name = window.prompt('Nama geofence?', 'Zona Baru');
            if (!name) {
                layer.remove();
                return;
            }
            const appliesTo = window.prompt('Berlaku untuk (csv)?', 'browser_tank,fuel_tanker') || 'browser_tank,fuel_tanker';
            try {
                await httpJson('POST', '/admin/geofences', {
                    name,
                    applies_to: appliesTo,
                    is_active: true,
                    coordinates: fromLatLngs(layer.getLatLngs()),
                });
                router.reload();
            } catch (err: any) {
                window.alert(`Gagal menyimpan geofence: ${err.message ?? err}`);
            }
        };

        const onEdited = async (e: any) => {
            const layers: any[] = e.layers.getLayers();
            for (const layer of layers) {
                const id = (layer as any)._geofenceId;
                if (!id) continue;
                try {
                    await httpJson('PUT', `/admin/geofences/${id}`, { coordinates: fromLatLngs(layer.getLatLngs()) });
                } catch (err: any) {
                    window.alert(`Gagal update geofence: ${err.message ?? err}`);
                }
            }
            router.reload();
        };

        const onDeleted = async (e: any) => {
            const layers: any[] = e.layers.getLayers();
            for (const layer of layers) {
                const id = (layer as any)._geofenceId;
                if (!id || !window.confirm('Hapus geofence ini?')) continue;
                try {
                    await httpJson('DELETE', `/admin/geofences/${id}`);
                } catch (err: any) {
                    window.alert(`Gagal menghapus geofence: ${err.message ?? err}`);
                }
            }
            router.reload();
        };

        map.on((L as any).Draw.Event.CREATED, onCreated);
        map.on((L as any).Draw.Event.EDITED, onEdited);
        map.on((L as any).Draw.Event.DELETED, onDeleted);
        return null;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [map]);

    return null;
}


export default function GeofenceEditor({ geofences = [] }: Props) {
    return (
        <div className="space-y-6">
            <Head title="Geofence Management" />
            <div>
                <h1 className="text-2xl font-bold tracking-tight">Geofence Management</h1>
                <p className="text-sm text-muted-foreground">
                    Draw, edit, and delete virtual boundaries. Coordinates are stored as lat/lng polygons and
                    evaluated by the ingestion pipeline for entry/exit alarms.
                </p>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Map Editor</CardTitle>
                        <CardDescription>
                            Use the toolbar to draw a polygon. Click a shape to edit vertices, or the trash icon to delete.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="h-[560px] w-full overflow-hidden rounded-lg border">
                            <MapContainer center={SITE_CENTER} zoom={14} className="h-full w-full">
                                <TileLayer
                                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                />
                                <DrawManager geofences={geofences} />
                            </MapContainer>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Existing Geofences</CardTitle>
                        <CardDescription>{geofences.length} defined</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-2">
                        {geofences.map((g) => (
                            <div key={g.id} className="rounded-md border p-3 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="font-medium">{g.name}</span>
                                    <Badge variant={g.is_active ? 'default' : 'secondary'} className="uppercase text-[9px]">
                                        {g.is_active ? 'Active' : 'Disabled'}
                                    </Badge>
                                </div>
                                <div className="mt-1 text-xs text-muted-foreground">
                                    Applies to: {g.applies_to} · {g.coordinates.length} vertices
                                </div>
                            </div>
                        ))}
                        {geofences.length === 0 && (
                            <p className="text-sm text-muted-foreground">No geofences yet. Draw one on the map.</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

GeofenceEditor.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Geofence Management', href: '/admin/geofences' },
    ],
};
