import type { Auth } from '@/types/auth';
import type { Branding, ImportError, Site } from '@/types/fuel';
import type { FlashToast } from '@/types/ui';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            can: { manageAdmins: boolean };
            site: Site | null;
            branding: Branding;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
        flashDataType: {
            toast?: FlashToast;
            import_errors?: ImportError[];
        };
    }
}
