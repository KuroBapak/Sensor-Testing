import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import { edit as editAppearance } from '@/routes/appearance';
import { id } from '@/lang/id';

export default function Appearance() {
    return (
        <>
            <Head title={id.account.appearanceTitle} />

            <h1 className="sr-only">{id.account.appearanceTitle}</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={id.account.appearanceTitle}
                    description={id.account.appearanceDescription}
                />
                <AppearanceTabs />
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: id.account.appearanceTitle,
            href: editAppearance(),
        },
    ],
};
