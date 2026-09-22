import { Link, usePage } from '@inertiajs/react';
import {
    Activity,
    AlertTriangle,
    Database,
    Droplet,
    FileSpreadsheet,
    Fuel,
    HardDrive,
    LayoutGrid,
    MapPin,
    Radio,
    Settings,
    Shield,
    Sliders,
    Users,
} from 'lucide-react';
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
import { dashboard } from '@/routes';
import type { NavItem, User } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<{ auth: { user: User | null } }>().props;
    const permissions = auth.user?.permissions || [];

    const has = (key: string) => permissions.includes(key);

    const monitoringItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...(has('main_tank.view')
            ? [
                  {
                      title: 'Main Tank Monitor',
                      href: '/fuel-monitoring/main-tank',
                      icon: Fuel,
                  },
              ]
            : []),
        ...(has('mobile_tanks.view')
            ? [
                  {
                      title: 'Mobile Tanks Monitor',
                      href: '/fuel-monitoring/mobile-tanks',
                      icon: Droplet,
                  },
              ]
            : []),
        ...(has('map.view')
            ? [
                  {
                      title: 'Live Fleet Map',
                      href: '/fuel-monitoring/map',
                      icon: MapPin,
                  },
              ]
            : []),
        ...(has('alarms.view')
            ? [
                  {
                      title: 'Alarm Log',
                      href: '/fuel-monitoring/alarms',
                      icon: AlertTriangle,
                  },
              ]
            : []),
        ...(has('reports.export')
            ? [
                  {
                      title: 'Reports & Export',
                      href: '/reports',
                      icon: FileSpreadsheet,
                  },
              ]
            : []),
    ];

    const fleetItems: NavItem[] = [
        ...(has('tanks.manage') || has('hardware.manage') || has('rfid.manage')
            ? [
                  {
                      title: 'Tanks & Hardware',
                      href: '/admin/tanks',
                      icon: HardDrive,
                  },
              ]
            : []),
    ];

    const systemItems: NavItem[] = [
        ...(has('users.manage')
            ? [
                  {
                      title: 'Users',
                      href: '/admin/users',
                      icon: Users,
                  },
              ]
            : []),
        ...(has('roles.manage')
            ? [
                  {
                      title: 'Roles & Permissions',
                      href: '/admin/roles',
                      icon: Shield,
                  },
              ]
            : []),
        ...(has('sensitivity.manage')
            ? [
                  {
                      title: 'Alert Sensitivity',
                      href: '/admin/sensitivity',
                      icon: Sliders,
                  },
              ]
            : []),
        ...(has('backup.manage')
            ? [
                  {
                      title: 'Backup & Storage',
                      href: '/admin/backup',
                      icon: Database,
                  },
              ]
            : []),
        ...(has('settings.manage')
            ? [
                  {
                      title: 'Site Settings',
                      href: '/admin/settings',
                      icon: Settings,
                  },
              ]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={monitoringItems} label="Monitoring" />
                {fleetItems.length > 0 && <NavMain items={fleetItems} label="Fleet Management" />}
                {systemItems.length > 0 && <NavMain items={systemItems} label="System Administration" />}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

