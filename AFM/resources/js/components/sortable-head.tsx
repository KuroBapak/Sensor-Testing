import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { TableHead } from '@/components/ui/table';
import { cn } from '@/lib/utils';

type Props = {
    label: string;
    sortKey: string;
    sort: string;
    dir: 'asc' | 'desc';
    onSort: (sortKey: string) => void;
    align?: 'left' | 'right';
    className?: string;
};

/** A column header that sorts the table when clicked and says which way it is sorted. */
export function SortableHead({
    label,
    sortKey,
    sort,
    dir,
    onSort,
    align = 'left',
    className,
}: Props) {
    const active = sort === sortKey;
    const Icon = !active ? ArrowUpDown : dir === 'asc' ? ArrowUp : ArrowDown;

    return (
        <TableHead
            aria-sort={
                active ? (dir === 'asc' ? 'ascending' : 'descending') : 'none'
            }
            className={cn(align === 'right' && 'text-right', className)}
        >
            <Button
                variant="ghost"
                size="sm"
                className={cn(
                    '-mx-2 h-7 gap-1 px-2 text-xs font-medium',
                    align === 'right' && 'flex-row-reverse',
                )}
                onClick={() => onSort(sortKey)}
            >
                {label}
                <Icon className={cn('size-3.5', !active && 'opacity-40')} />
            </Button>
        </TableHead>
    );
}
