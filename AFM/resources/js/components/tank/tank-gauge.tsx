import { useId } from 'react';
import { id } from '@/lang/id';
import { fill } from '@/lang/id';
import { formatLiters, formatPercent } from '@/lib/format';
import type { Reading } from '@/types/fuel';

/** Fill styles shared by the gauge, its legend and the history chart. */
export const FUEL_COLOR = '#d97706';
export const WATER_COLOR = '#1d4ed8';
export const EMPTY_COLOR = '#e5e7eb';

/**
 * Pattern definitions. Fuel is solid, water is hatched and empty is dotted, so
 * the three are told apart by pattern and label, not by color alone.
 */
export function LevelPatterns({ prefix }: { prefix: string }) {
    return (
        <defs>
            <pattern
                id={`${prefix}-fuel`}
                width="8"
                height="8"
                patternUnits="userSpaceOnUse"
            >
                <rect width="8" height="8" fill={FUEL_COLOR} />
            </pattern>
            <pattern
                id={`${prefix}-water`}
                width="8"
                height="8"
                patternUnits="userSpaceOnUse"
                patternTransform="rotate(45)"
            >
                <rect width="8" height="8" fill={WATER_COLOR} />
                <line
                    x1="0"
                    y1="0"
                    x2="0"
                    y2="8"
                    stroke="#bfdbfe"
                    strokeWidth="3"
                />
            </pattern>
            <pattern
                id={`${prefix}-empty`}
                width="8"
                height="8"
                patternUnits="userSpaceOnUse"
            >
                <rect width="8" height="8" fill={EMPTY_COLOR} />
                <circle cx="4" cy="4" r="1" fill="#9ca3af" />
            </pattern>
        </defs>
    );
}

function Swatch({
    prefix,
    kind,
}: {
    prefix: string;
    kind: 'fuel' | 'water' | 'empty';
}) {
    return (
        <svg
            width="16"
            height="16"
            aria-hidden="true"
            className="shrink-0 rounded-sm border"
        >
            <LevelPatterns prefix={`${prefix}-sw`} />
            <rect width="16" height="16" fill={`url(#${prefix}-sw-${kind})`} />
        </svg>
    );
}

type Props = {
    name: string;
    reading: Reading;
};

/** A tank drawn as stacked water / fuel / empty, with the figures written beside it. */
export function TankGauge({ name, reading }: Props) {
    const prefix = useId().replace(/:/g, '');
    const top = 12;
    const height = 200;
    const unit = height / 100;

    const water = reading.water_pct * unit;
    const fuel = reading.fuel_pct * unit;
    const empty = height - water - fuel;

    const rows = [
        {
            kind: 'fuel' as const,
            label: id.tank.fuel,
            pct: reading.fuel_pct,
            liters: reading.fuel_liters,
        },
        {
            kind: 'water' as const,
            label: id.tank.water,
            pct: reading.water_pct,
            liters: reading.water_liters,
        },
        {
            kind: 'empty' as const,
            label: id.tank.empty,
            pct: reading.empty_pct,
            liters: null,
        },
    ];

    return (
        <div className="flex items-center gap-5">
            <svg
                width="104"
                height="224"
                viewBox="0 0 104 224"
                role="img"
                aria-label={fill(id.tank.gaugeLabel, {
                    name,
                    fuel: formatPercent(reading.fuel_pct),
                    water: formatPercent(reading.water_pct),
                    empty: formatPercent(reading.empty_pct),
                })}
            >
                <LevelPatterns prefix={prefix} />
                <clipPath id={`${prefix}-clip`}>
                    <rect x="12" y={top} width="80" height={height} rx="12" />
                </clipPath>
                <g clipPath={`url(#${prefix}-clip)`}>
                    <rect
                        x="12"
                        y={top}
                        width="80"
                        height={empty}
                        fill={`url(#${prefix}-empty)`}
                    />
                    <rect
                        x="12"
                        y={top + empty}
                        width="80"
                        height={fuel}
                        fill={`url(#${prefix}-fuel)`}
                    />
                    <rect
                        x="12"
                        y={top + empty + fuel}
                        width="80"
                        height={water}
                        fill={`url(#${prefix}-water)`}
                    />
                </g>
                <rect
                    x="12"
                    y={top}
                    width="80"
                    height={height}
                    rx="12"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    className="text-foreground/70"
                />
            </svg>

            <div className="space-y-3">
                <div>
                    <div className="text-xs text-muted-foreground">
                        {id.tank.total}
                    </div>
                    <div
                        className="text-3xl font-semibold tabular-nums"
                        data-test="tank-total"
                    >
                        {formatPercent(reading.total_pct)}%
                    </div>
                    <div className="text-sm text-muted-foreground tabular-nums">
                        {formatLiters(reading.total_liters)}{' '}
                        {id.common.litersUnit}
                    </div>
                </div>
                <ul className="space-y-1.5 text-sm">
                    {rows.map((row) => (
                        <li key={row.kind} className="flex items-center gap-2">
                            <Swatch prefix={prefix} kind={row.kind} />
                            <span className="w-14">{row.label}</span>
                            <span className="font-medium tabular-nums">
                                {formatPercent(row.pct)}%
                            </span>
                            {row.liters !== null && (
                                <span className="text-muted-foreground tabular-nums">
                                    · {formatLiters(row.liters)}{' '}
                                    {id.common.litersUnit}
                                </span>
                            )}
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
}
