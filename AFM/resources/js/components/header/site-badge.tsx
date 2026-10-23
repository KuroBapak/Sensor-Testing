import { MapPin } from 'lucide-react';
import { useSite } from '@/hooks/use-site';
import { id } from '@/lang/id';

/** The site name from global settings; nothing is shown until a name is set. */
export function SiteBadge() {
    const site = useSite();

    if (!site.name) {
        return null;
    }

    return (
        <div
            className="hidden items-center gap-2 rounded-md border px-2.5 py-1.5 text-xs font-medium lg:flex"
            title={id.header.site}
            data-test="site-name"
        >
            <MapPin className="size-3.5 text-emerald-600 dark:text-emerald-400" />
            <span className="max-w-56 truncate">{site.name}</span>
        </div>
    );
}
