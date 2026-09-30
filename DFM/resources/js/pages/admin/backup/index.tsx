import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Database, Play, CheckCircle, Save, AlertCircle, Loader2, CheckCheck } from 'lucide-react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { useState } from 'react';

export default function BackupIndex({ backupSettings, runs }: { backupSettings: any; runs: any[] }) {
    const [testingConnection, setTestingConnection] = useState(false);
    const [testingBackup, setTestingBackup] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        provider: backupSettings?.provider || 'S3',
        endpoint: backupSettings?.endpoint || '',
        region: backupSettings?.region || 'us-east-1',
        bucket: backupSettings?.bucket || '',
        path_prefix: backupSettings?.path_prefix || '',
        use_path_style: backupSettings?.use_path_style ?? true,
        access_key_id: backupSettings?.access_key_id || '',
        secret_access_key: backupSettings?.secret_access_key || '',
        schedule_time: backupSettings?.schedule_time || '02:00',
        retention_days: backupSettings?.retention_days || 30,
        enabled: backupSettings?.enabled ?? false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/backup');
    };

    const handleTestConnection = () => {
        setTestingConnection(true);
        router.post('/admin/backup/test-connection', {}, {
            preserveScroll: true,
            onFinish: () => setTestingConnection(false),
        });
    };

    const handleTestBackup = () => {
        setTestingBackup(true);
        router.post('/admin/backup/test-backup', {}, {
            preserveScroll: true,
            onFinish: () => setTestingBackup(false),
        });
    };

    const handleTrigger = () => {
        router.post('/admin/backup/trigger');
    };

    const connectionTested = backupSettings?.last_connection_test_ok && backupSettings?.last_connection_test_at;
    const testBackupDone = backupSettings?.last_test_backup_at;
    const fullyTested = connectionTested && testBackupDone 
        && new Date(backupSettings.updated_at) <= new Date(backupSettings.last_connection_test_at)
        && new Date(backupSettings.updated_at) <= new Date(backupSettings.last_test_backup_at);

    return (
        <div className="space-y-6">
            <Head title="Backup & Storage" />
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-2xl font-bold">Backup & Storage</h1>
                    <p className="text-sm text-muted-foreground">S3-compatible backup configuration and history.</p>
                </div>
                <Button 
                    size="sm" 
                    onClick={handleTrigger}
                    disabled={!fullyTested}
                >
                    <Play className="mr-2 h-4 w-4" /> Run Backup Now
                </Button>
            </div>

            {!fullyTested && backupSettings && (
                <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                    <div className="flex gap-2">
                        <AlertCircle className="h-5 w-5 flex-shrink-0" />
                        <div>
                            <p className="font-medium">Setup Required</p>
                            <p className="mt-1">Complete Test Connection and Test Backup before running backups.</p>
                        </div>
                    </div>
                </div>
            )}

            {errors.trigger && (
                <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <div className="flex gap-2">
                        <AlertCircle className="h-5 w-5 flex-shrink-0" />
                        <p>{errors.trigger}</p>
                    </div>
                </div>
            )}

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
                                <Label>Endpoint URL *</Label>
                                <Input value={data.endpoint} onChange={e => setData('endpoint', e.target.value)} />
                                {errors.endpoint && <p className="text-sm text-red-600 mt-1">{errors.endpoint}</p>}
                            </div>
                            <div>
                                <Label>Bucket Name *</Label>
                                <Input value={data.bucket} onChange={e => setData('bucket', e.target.value)} />
                                {errors.bucket && <p className="text-sm text-red-600 mt-1">{errors.bucket}</p>}
                            </div>
                            <div>
                                <Label>Access Key ID *</Label>
                                <Input value={data.access_key_id} onChange={e => setData('access_key_id', e.target.value)} />
                                {errors.access_key_id && <p className="text-sm text-red-600 mt-1">{errors.access_key_id}</p>}
                            </div>
                            <div>
                                <Label>Secret Access Key *</Label>
                                <Input type="password" value={data.secret_access_key} onChange={e => setData('secret_access_key', e.target.value)} />
                                {errors.secret_access_key && <p className="text-sm text-red-600 mt-1">{errors.secret_access_key}</p>}
                            </div>
                        </div>

                        <div className="flex gap-2 pt-4">
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 h-4 w-4" /> Save
                            </Button>
                            
                            <Button 
                                type="button" 
                                variant="outline" 
                                onClick={handleTestConnection}
                                disabled={testingConnection || !backupSettings}
                            >
                                {testingConnection ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <CheckCircle className="mr-2 h-4 w-4" />}
                                Test Connection
                            </Button>

                            <Button 
                                type="button" 
                                variant="outline" 
                                onClick={handleTestBackup}
                                disabled={testingBackup || !connectionTested}
                            >
                                {testingBackup ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <CheckCheck className="mr-2 h-4 w-4" />}
                                Test Backup
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>

            <Card>
                <CardHeader>
                    <CardTitle>Recent Backup Runs</CardTitle>
                    <CardDescription>Last 10 backup executions</CardDescription>
                </CardHeader>
                <CardContent>
                    {runs.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No backup runs yet.</p>
                    ) : (
                        <div className="space-y-2">
                            {runs.map(r => (
                                <div key={r.id} className="flex justify-between items-center border-b pb-2 text-sm">
                                    <div>
                                        <span className="font-medium">{new Date(r.started_at).toLocaleString()}</span>
                                        <span className="text-muted-foreground ml-2">({r.trigger})</span>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        {r.size_bytes && (
                                            <span className="text-muted-foreground">
                                                {(r.size_bytes / 1024 / 1024).toFixed(2)} MB
                                            </span>
                                        )}
                                        <span className={`font-semibold capitalize ${
                                            r.status === 'success' ? 'text-green-600' : 
                                            r.status === 'failed' ? 'text-red-600' : 
                                            'text-yellow-600'
                                        }`}>
                                            {r.status}
                                        </span>
                                    </div>
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
