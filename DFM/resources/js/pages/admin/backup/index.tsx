import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Database, Play, CheckCircle, Save } from 'lucide-react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

export default function BackupIndex({ backupSettings, runs }: { backupSettings: any; runs: any[] }) {
    const { data, setData, post, processing } = useForm({
        provider: backupSettings?.provider || 'S3',
        endpoint: backupSettings?.endpoint || '',
        region: backupSettings?.region || '',
        bucket: backupSettings?.bucket || '',
        path_prefix: backupSettings?.path_prefix || '',
        access_key_id: backupSettings?.access_key_id || '',
        secret_access_key: backupSettings?.secret_access_key || '',
        schedule_time: backupSettings?.schedule_time || '02:00',
        retention_days: backupSettings?.retention_days || 30,
        enabled: backupSettings?.enabled ?? true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/backup');
    };

    return (
        <div className="space-y-6">
            <Head title="Backup & Storage" />
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-2xl font-bold">Backup & Storage</h1>
                    <p className="text-sm text-muted-foreground">Automated S3 backups configuration.</p>
                </div>
                <Button size="sm" onClick={() => post('/admin/backup/trigger')}>
                    <Play className="mr-2 h-4 w-4" /> Trigger Now
                </Button>
            </div>

            <form onSubmit={submit}>
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Database className="h-5 w-5" /> Storage Settings
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label>Endpoint</Label>
                                <Input value={data.endpoint} onChange={e => setData('endpoint', e.target.value)} />
                            </div>
                            <div>
                                <Label>Bucket</Label>
                                <Input value={data.bucket} onChange={e => setData('bucket', e.target.value)} />
                            </div>
                            <div>
                                <Label>Access Key ID</Label>
                                <Input value={data.access_key_id} onChange={e => setData('access_key_id', e.target.value)} />
                            </div>
                            <div>
                                <Label>Secret Access Key</Label>
                                <Input type="password" value={data.secret_access_key} onChange={e => setData('secret_access_key', e.target.value)} />
                            </div>
                        </div>
                        <Button type="submit" disabled={processing} className="mt-4">
                            <Save className="mr-2 h-4 w-4" /> Save
                        </Button>
                    </CardContent>
                </Card>
            </form>

            <Card>
                <CardHeader>
                    <CardTitle>Recent Runs</CardTitle>
                </CardHeader>
                <CardContent>
                    {runs.length === 0 ? <p className="text-sm text-muted-foreground">No recent runs.</p> : (
                        <div className="space-y-2">
                            {runs.map(r => (
                                <div key={r.id} className="text-sm flex justify-between border-b py-2">
                                    <span>{new Date(r.started_at).toLocaleString()}</span>
                                    <span className="font-semibold capitalize">{r.status}</span>
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}

BackupIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Backup & Storage', href: '/admin/backup' },
    ],
};
