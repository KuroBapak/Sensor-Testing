import { usePage } from '@inertiajs/react';
import type { User } from '@/types';

export function usePermission(permission: string): boolean {
    const { auth } = usePage<{ auth: { user: User | null } }>().props;

    if (!auth.user) {
        return false;
    }

    return auth.user.permissions.includes(permission);
}

export function usePermissions(permissions: string[]): boolean {
    const { auth } = usePage<{ auth: { user: User | null } }>().props;

    if (!auth.user) {
        return false;
    }

    return permissions.every((permission) =>
        auth.user!.permissions.includes(permission)
    );
}

export function useAnyPermission(permissions: string[]): boolean {
    const { auth } = usePage<{ auth: { user: User | null } }>().props;

    if (!auth.user) {
        return false;
    }

    return permissions.some((permission) =>
        auth.user!.permissions.includes(permission)
    );
}
