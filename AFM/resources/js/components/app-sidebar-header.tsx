import { Breadcrumbs } from '@/components/breadcrumbs';
import { ExportAllDialog } from '@/components/export-all-dialog';
import { LiveClock } from '@/components/header/live-clock';
import { SiteBadge } from '@/components/header/site-badge';
import { ThemeToggle } from '@/components/header/theme-toggle';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

/**
 * Top bar of every screen: where you are, the site, the site time, the theme
 * switch and the full export. There is no notification bell: phase 1 has no
 * notification system.
 */
export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <div className="ml-auto flex items-center gap-2">
                <SiteBadge />
                <LiveClock />
                <ThemeToggle />
                <ExportAllDialog />
            </div>
        </header>
    );
}
