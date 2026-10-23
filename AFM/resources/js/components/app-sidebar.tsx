import { Link, usePage } from '@inertiajs/react';
import { BarChart3, Fuel, QrCode, Settings, Truck, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { id } from '@/lang/id';
import { index as accounts } from '@/routes/admin-accounts';
import { edit as globalSettings } from '@/routes/global-settings';
import { index as qrCards } from '@/routes/qr-cards';
import { index as tank } from '@/routes/tank';
import { index as usage } from '@/routes/usage';
import { index as vehicles } from '@/routes/vehicles';
import type { NavItem } from '@/types';

/** The four main views, then settings, then (Super Admin only) admin accounts. */
function navItems(manageAdmins: boolean): NavItem[] {
    const items: NavItem[] = [
        { title: id.nav.tank, href: tank(), icon: Fuel },
        { title: id.nav.usage, href: usage(), icon: BarChart3 },
        { title: id.nav.vehicles, href: vehicles(), icon: Truck },
        { title: id.nav.qr, href: qrCards(), icon: QrCode },
        { title: id.nav.settings, href: globalSettings(), icon: Settings },
    ];

    if (manageAdmins) {
        items.push({ title: id.nav.accounts, href: accounts(), icon: Users });
    }

    return items;
}

export function AppSidebar() {
    const { can } = usePage().props;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader className="h-16 justify-center p-2 group-data-[collapsible=icon]:h-12">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={tank()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems(can.manageAdmins)} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
