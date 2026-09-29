import { useState } from 'react';

export interface AlarmRow {
    id: number;
    rfid: string;
    waktu_kejadian: string;
    jumlah_liter: number;
    anomaly_type?: string;
    tank_name?: string | null;
    status?: string;
    resolved_by?: string | null;
    is_read?: boolean;
    latitude?: number | null;
    longitude?: number | null;
    observed_percent?: number | null;
    threshold_percent?: number | null;
    sensitivity_mode?: string | null;
}

type StatusValue = 'open' | 'investigating' | 'resolved' | 'false_positive';

interface AlarmTableProps {
    data: AlarmRow[];
    isPulsing: boolean;
    canManage?: boolean;
    onMarkRead?: (id: number) => Promise<void>;
    onStatusChange?: (id: number, status: StatusValue, resolutionNote?: string) => Promise<void>;
}

const noop = async () => {};

const STATUS_STYLES: Record<string, string> = {
    open: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    investigating: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    resolved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    false_positive: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
};

// PRD §6 Page 3: Anomaly type badge styling
const ANOMALY_TYPE_BADGES: Record<string, { label: string; className: string }> = {
    sudden_change_theft: {
        label: 'Theft Alarm',
        className: 'bg-red-600 text-white font-bold animate-pulse',
    },
    calibration_needed: {
        label: 'Sensor Needs Calibration',
        className: 'bg-amber-500 text-white font-semibold',
    },
    unauthorized_scan: {
        label: 'Unauthorized Scan',
        className: 'bg-slate-500 text-white font-medium',
    },
    geofence_exit: {
        label: 'Geofence Exit',
        className: 'bg-slate-600 text-white font-medium',
    },
};

const getAnomalyBadge = (type?: string) => {
    const config = ANOMALY_TYPE_BADGES[type || ''] || { label: type || 'Unknown', className: 'bg-slate-400 text-white' };
    return config;
};

export default function AlarmTable({ data, isPulsing, canManage = false, onMarkRead = noop, onStatusChange = noop }: AlarmTableProps) {
    const [busyId, setBusyId] = useState<number | null>(null);
    const [noteId, setNoteId] = useState<number | null>(null);
    const [note, setNote] = useState('');

    const run = async (id: number, fn: () => Promise<void>) => {
        setBusyId(id);
        try {
            await fn();
        } finally {
            setBusyId(null);
        }
    };

    const submitResolution = async (id: number, status: StatusValue) => {
        await run(id, async () => {
            await onStatusChange(id, status, note.trim() || undefined);
            setNoteId(null);
            setNote('');
        });
    };

    return (
        <section className={`bg-slate-50 dark:bg-slate-800 rounded-lg p-6 border-2 ${isPulsing ? 'border-red-600 animate-pulse' : 'border-slate-200 dark:border-slate-700'}`}>
            <h2 className="text-2xl font-bold mb-4 text-red-600 dark:text-red-400">Critical Alerts</h2>

            <div className="overflow-auto max-h-[32rem] bg-white dark:bg-slate-950 rounded border border-slate-200 dark:border-slate-700">
                <table className="w-full text-sm font-mono">
                    <thead className="bg-red-100 dark:bg-red-900/50 sticky top-0">
                        <tr className="border-b border-red-200 dark:border-red-700">
                            <th className="p-3 text-left text-red-700 dark:text-red-300">Status</th>
                            <th className="p-3 text-left text-red-700 dark:text-red-300">Tank / RFID</th>
                            <th className="p-3 text-left text-red-700 dark:text-red-300">Anomaly</th>
                            <th className="p-3 text-left text-red-700 dark:text-red-300">Waktu Kejadian</th>
                            <th className="p-3 text-right text-red-700 dark:text-red-300">Jumlah Liter</th>
                            <th className="p-3 text-left text-red-700 dark:text-red-300">Resolved By</th>
                            {canManage && <th className="p-3 text-left text-red-700 dark:text-red-300">Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {data.length === 0 ? (
                            <tr>
                                <td colSpan={canManage ? 7 : 6} className="p-6 text-center text-slate-500 dark:text-slate-400">
                                    No alarms detected
                                </td>
                            </tr>
                        ) : (
                            data.map((row) => {
                                const status = (row.status ?? 'open') as StatusValue;
                                const pending = status === 'open' || status === 'investigating';
                                const busy = busyId === row.id;
                                return (
                                    <tr
                                        key={row.id}
                                        className={`border-b border-slate-200 dark:border-slate-800 ${
                                            row.is_read ? '' : 'bg-red-50 dark:bg-red-950/20 font-semibold'
                                        } hover:bg-slate-100 dark:hover:bg-slate-800/50`}
                                    >
                                        <td className="p-3">
                                            <span className={`inline-block rounded px-2 py-0.5 text-[11px] uppercase ${STATUS_STYLES[status] ?? STATUS_STYLES.open}`}>
                                                {status.replace('_', ' ')}
                                            </span>
                                        </td>
                                        <td className="p-3">
                                            <div>{row.tank_name ?? '-'}</div>
                                            <div className="text-[11px] text-slate-500">{row.rfid}</div>
                                        </td>
                                        <td className="p-3">
                                            {(() => {
                                                const badge = getAnomalyBadge(row.anomaly_type);
                                                return (
                                                    <div className="flex flex-col gap-1 items-start">
                                                        <span className={`px-2 py-0.5 rounded text-xs tracking-wide uppercase ${badge.className}`}>
                                                            {badge.label}
                                                        </span>
                                                        {row.latitude && row.longitude && (
                                                            <a
                                                                href={`https://www.google.com/maps?q=${row.latitude},${row.longitude}`}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                                className="text-[11px] text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1"
                                                            >
                                                                📍 Open in map
                                                            </a>
                                                        )}
                                                    </div>
                                                );
                                            })()}
                                        </td>
                                        <td className="p-3">{row.waktu_kejadian}</td>
                                        <td className="p-3 text-right">{row.jumlah_liter.toLocaleString()}</td>
                                        <td className="p-3 text-xs">{row.resolved_by ?? '-'}</td>
                                        {canManage && (
                                            <td className="p-3">
                                                <div className="flex flex-wrap items-center gap-1">
                                                    {!row.is_read && (
                                                        <button
                                                            disabled={busy}
                                                            onClick={() => run(row.id, () => onMarkRead(row.id))}
                                                            className="rounded border px-2 py-0.5 text-[11px] hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-50"
                                                        >
                                                            Mark Read
                                                        </button>
                                                    )}
                                                    {pending && (
                                                        <button
                                                            disabled={busy}
                                                            onClick={() => run(row.id, () => onStatusChange(row.id, 'investigating'))}
                                                            className="rounded border border-amber-400 px-2 py-0.5 text-[11px] text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/30 disabled:opacity-50"
                                                        >
                                                            Investigate
                                                        </button>
                                                    )}
                                                    {pending && (
                                                        <button
                                                            disabled={busy}
                                                            onClick={() => { setNoteId(row.id); setNote(''); }}
                                                            className="rounded border border-emerald-500 px-2 py-0.5 text-[11px] text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 disabled:opacity-50"
                                                        >
                                                            Resolve
                                                        </button>
                                                    )}
                                                    {pending && (
                                                        <button
                                                            disabled={busy}
                                                            onClick={() => run(row.id, () => onStatusChange(row.id, 'false_positive'))}
                                                            className="rounded border border-slate-400 px-2 py-0.5 text-[11px] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-50"
                                                        >
                                                            False +
                                                        </button>
                                                    )}
                                                    {!pending && (
                                                        <button
                                                            disabled={busy}
                                                            onClick={() => run(row.id, () => onStatusChange(row.id, 'open'))}
                                                            className="rounded border border-red-400 px-2 py-0.5 text-[11px] text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 disabled:opacity-50"
                                                        >
                                                            Reopen
                                                        </button>
                                                    )}
                                                </div>

                                                {noteId === row.id && (
                                                    <div className="mt-2 flex items-center gap-1">
                                                        <input
                                                            autoFocus
                                                            value={note}
                                                            onChange={(e) => setNote(e.target.value)}
                                                            placeholder="Catatan resolusi..."
                                                            className="w-40 rounded border px-2 py-1 text-[11px] bg-white dark:bg-slate-900"
                                                        />
                                                        <button
                                                            disabled={busy}
                                                            onClick={() => submitResolution(row.id, 'resolved')}
                                                            className="rounded bg-emerald-600 px-2 py-1 text-[11px] text-white hover:bg-emerald-700 disabled:opacity-50"
                                                        >
                                                            Save
                                                        </button>
                                                        <button
                                                            disabled={busy}
                                                            onClick={() => setNoteId(null)}
                                                            className="rounded border px-2 py-1 text-[11px] hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-50"
                                                        >
                                                            Cancel
                                                        </button>
                                                    </div>
                                                )}
                                            </td>
                                        )}
                                    </tr>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

