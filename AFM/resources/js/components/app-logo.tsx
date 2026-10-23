import { usePage } from '@inertiajs/react';

import AppLogoIcon from '@/components/app-logo-icon';

/**
 * The sidebar brand: the uploaded logo and the site name from global settings.
 * Until a logo is uploaded the generic icon is shown; until a name is set, the app name.
 */
export default function AppLogo() {
    const { branding } = usePage().props;

    return (
        <>
            {branding.logoUrl ? (
                <img
                    src={branding.logoUrl}
                    alt=""
                    className="size-9 shrink-0 rounded-md object-contain"
                    data-test="brand-logo"
                />
            ) : (
                <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                    <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                </div>
            )}
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span
                    className="mb-0.5 truncate leading-tight font-semibold"
                    data-test="brand-name"
                >
                    {branding.name}
                </span>
            </div>
        </>
    );
}
