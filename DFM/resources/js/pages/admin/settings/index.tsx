import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Settings, Save } from 'lucide-react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

interface Props {
    settings: any;
    site: any;
}

export default function SiteSettingsIndex({ settings, site }: Props) {
    const { data, setData, post, processing } = useForm({
        timezone: site?.timezone || 'Asia/Jakarta',
        settings: {
            settle_seconds: settings?.settle_seconds || 180,
            window_minutes: settings?.window_minutes || 30,
            rfid_poll_seconds: settings?.rfid_poll_seconds || 300,
            eval_grace_minutes: settings?.eval_grace_minutes || 5,
            reading_retention_days: settings?.reading_retention_days || 90,
            position_max_age_minutes: settings?.position_max_age_minutes || 15,
            geofence_exit_confirm_readings: settings?.geofence_exit_confirm_readings || 2,
        },
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/settings');
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <Head title="Site Settings" />
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-2xl font-bold">Site Settings</h1>
                    <p className="text-sm text-muted-foreground">Global algorithm parameters and site timezones.</p>
                </div>
                <Button size="sm" type="submit" disabled={processing}>
                    <Save className="mr-2 h-4 w-4" /> Save Settings
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Settings className="h-5 w-5" /> General Configuration
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div>
                        <Label htmlFor="timezone">Timezone</Label>
                        <Input
                            id="timezone"
                            value={data.timezone}
                            onChange={(e) => setData('timezone', e.target.value)}
                        />
                    </div>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label>Settle Duration (Seconds)</Label>
                            <Input
                                type="number"
                                value={data.settings.settle_seconds}
                                onChange={(e) => setData('settings', { ...data.settings, settle_seconds: Number(e.target.value) })}
                            />
                        </div>
                        <div>
                            <Label>Window Duration (Minutes)</Label>
                            <Input
                                type="number"
                                value={data.settings.window_minutes}
                                onChange={(e) => setData('settings', { ...data.settings, window_minutes: Number(e.target.value) })}
                            />
                        </div>
                        <div>
                            <Label>Data Retention (Days)</Label>
                            <Input
                                type="number"
                                value={data.settings.reading_retention_days}
                                onChange={(e) => setData('settings', { ...data.settings, reading_retention_days: Number(e.target.value) })}
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </form>
    );
}

SiteSettingsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Site Settings', href: '/admin/settings' },
    ],
};
