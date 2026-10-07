import { Head, useForm, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Database, Play, CheckCircle, Save, AlertCircle, Loader2, CheckCheck } from 'lucide-react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { useState } from 'react';
import { useEffect } from 'react';
import { XCircle, RefreshCw, Clock } from 'lucide-react';

function formatBytes(bytes: number | null | undefined): string {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${(bytes / Math.pow(k, i)).toFixed(1)} ${sizes[i]}`;
}

function formatDuration(start: string, end: string | null | undefined): string | null {
    if (!end) return null;
    const s = new Date(start).getTime();
    const e = new Date(end).getTime();
    const sec = Math.max(0, Math.round((e - s) / 1000));
    if (sec < 60) return `${sec}s`;
    const min = Math.floor(sec / 60);
    return `${min}m ${sec % 60}s`;
}

export default function BackupIndex({ backupSettings, runs }: { backupSettings: any; runs: any[] }) {
    const [testingConnection, setTestingConnection] = useState(false);
    const [testingBackup, setTestingBackup] = useState(false);

    const { data, setData, post, processing, errors: formErrors } = useForm({
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
    const pageErrors = usePage<{ errors: Record<string, string> }>().props.errors || {};
    const errors = { ...formErrors, ...pageErrors } as Record<string, string | undefined>;


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

    // Poll if any run is running
    useEffect(() => {
        if (!runs.some(r => r.status === 'running')) return;

        const interval = setInterval(() => {
            router.reload({ only: ['runs', 'backupSettings'] });
        }, 3000);

        return () => clearInterval(interval);
    }, [runs]);

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

            {errors.test_connection && (
                <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <div className="flex gap-2">
                        <AlertCircle className="h-5 w-5 flex-shrink-0" />
                        <p>{errors.test_connection}</p>
                    </div>
                </div>
            )}

            {errors.test_backup && (
                <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <div className="flex gap-2">
                        <AlertCircle className="h-5 w-5 flex-shrink-0" />
                        <p>{errors.test_backup}</p>
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
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                    <div>
                        <CardTitle>Recent Backup Runs</CardTitle>
                        <CardDescription>Last 10 backup executions</CardDescription>
                    </div>
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        onClick={() => router.reload({ only: ['runs', 'backupSettings'] })}
                        className="text-xs text-muted-foreground hover:text-foreground"
                    >
                        <RefreshCw className="mr-1.5 h-3.5 w-3.5" /> Refresh
                    </Button>
                </CardHeader>
                <CardContent>
                    {runs.length === 0 ? (
                        <p className="text-sm text-muted-foreground py-4 text-center">No backup runs recorded yet.</p>
                    ) : (
                        <div className="divide-y divide-border">
                            {runs.map(r => {
                                const duration = formatDuration(r.started_at, r.finished_at);
                                return (
                                    <div key={r.id} className="py-3 first:pt-0 last:pb-0 space-y-1.5 text-sm">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <div className="flex items-center gap-2">
                                                <span className="font-medium text-foreground">
                                                    {new Date(r.started_at).toLocaleString()}
                                                </span>
                                                <span className="inline-flex items-center rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground uppercase">
                                                    {r.trigger}
                                                </span>
                                                {duration && (
                                                    <span className="inline-flex items-center text-xs text-muted-foreground">
                                                        <Clock className="mr-1 h-3 w-3" /> {duration}
                                                    </span>
                                                )}
                                            </div>

                                            <div className="flex items-center gap-3">
                                                {r.size_bytes ? (
                                                    <span className="text-xs font-mono text-muted-foreground">
                                                        {formatBytes(r.size_bytes)}
                                                    </span>
                                                ) : null}

                                                {r.status === 'running' && (
                                                    <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-yellow-600 bg-yellow-50 dark:bg-yellow-950/40 px-2 py-0.5 rounded-full border border-yellow-200 dark:border-yellow-800">
                                                        <Loader2 className="h-3 w-3 animate-spin" /> Running...
                                                    </span>
                                                )}

                                                {r.status === 'success' && (
                                                    <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-950/40 px-2 py-0.5 rounded-full border border-green-200 dark:border-green-800">
                                                        <CheckCircle className="h-3 w-3" /> Success
                                                    </span>
                                                )}

                                                {r.status === 'failed' && (
                                                    <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-950/40 px-2 py-0.5 rounded-full border border-red-200 dark:border-red-800">
                                                        <XCircle className="h-3 w-3" /> Failed
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        {r.object_key && (
                                            <p className="text-xs font-mono text-muted-foreground truncate">
                                                Key: {r.object_key}
                                            </p>
                                        )}

                                        {r.status === 'failed' && r.error_message && (
                                            <div className="rounded bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 p-2 text-xs text-red-700 dark:text-red-400 mt-1">
                                                <span className="font-semibold">Error: </span>
                                                {r.error_message}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
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
