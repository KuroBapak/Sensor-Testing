import type { ReactNode } from 'react';

type Props = {
    title: string;
    subtitle?: string;
    actions?: ReactNode;
};

/** Title, short subtitle and optional actions at the top of every screen. */
export function PageHeading({ title, subtitle, actions }: Props) {
    return (
        <div className="flex flex-wrap items-start justify-between gap-3">
            <div className="space-y-1">
                <h1 className="text-xl font-semibold tracking-tight">
                    {title}
                </h1>
                {subtitle && (
                    <p className="max-w-3xl text-sm text-muted-foreground">
                        {subtitle}
                    </p>
                )}
            </div>
            {actions && (
                <div className="flex flex-wrap items-center gap-2">
                    {actions}
                </div>
            )}
        </div>
    );
}
