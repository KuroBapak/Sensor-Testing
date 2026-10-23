import {
    AlertTriangle,
    BatteryLow,
    CheckCircle2,
    Infinity as InfinityIcon,
    XCircle,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { id } from '@/lang/id';
import type { QrCardRow } from '@/types/fuel';

/**
 * Status badges: always a label plus an icon, never color alone. Depleted, low
 * quota and expired are the ones that need attention.
 */
export function CardStatus({ card }: { card: QrCardRow }) {
    return (
        <div className="flex flex-wrap gap-1">
            {card.expired ? (
                <Badge
                    variant="outline"
                    className="border-red-300 text-red-700 dark:text-red-400"
                >
                    <XCircle />
                    {id.qr.badges.expired}
                </Badge>
            ) : (
                <Badge
                    variant="outline"
                    className="border-green-300 text-green-700 dark:text-green-400"
                >
                    <CheckCircle2 />
                    {id.qr.badges.active}
                </Badge>
            )}
            {card.validity === 'lifetime' && (
                <Badge variant="secondary">
                    <InfinityIcon />
                    {id.qr.badges.lifetime}
                </Badge>
            )}
            {card.depleted && (
                <Badge variant="destructive">
                    <AlertTriangle />
                    {id.qr.badges.depleted}
                </Badge>
            )}
            {card.low && (
                <Badge className="border-transparent bg-amber-500 text-black hover:bg-amber-500/90">
                    <BatteryLow />
                    {id.qr.badges.low}
                </Badge>
            )}
        </div>
    );
}
