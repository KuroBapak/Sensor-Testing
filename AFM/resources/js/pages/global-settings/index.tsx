import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { Field } from '@/components/field';
import { PageHeading } from '@/components/page-heading';
import { LogoField } from '@/components/settings/logo-field';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { fill, id } from '@/lang/id';
import { resetRulesChanged } from '@/lib/settings';
import { edit, update } from '@/routes/global-settings';
import type { GlobalSettings, ResetPeriod, TankSetting } from '@/types/fuel';

type Props = {
    settings: GlobalSettings;
    tanks: TankSetting[];
    timezones: Record<string, string>;
    resetCardCount: number;
};

export default function GlobalSettingsPage({
    settings,
    tanks,
    timezones,
    resetCardCount,
}: Props) {
    const form = useForm({
        reset_period: settings.reset_period,
        reset_time: settings.reset_time,
        week_start_day: settings.week_start_day,
        month_reset_day: settings.month_reset_day,
        timezone: settings.timezone,
        low_quota_percent: settings.low_quota_percent,
        site_name: settings.site_name,
        logo: null as File | null,
        remove_logo: false,
        tanks: tanks.map((tank) => ({
            id: tank.id,
            capacity_liters: String(tank.capacity_liters),
        })),
        confirm_reset_change: false,
    });
    const [confirming, setConfirming] = useState(false);
    const [logoInputKey, setLogoInputKey] = useState(0);
    const { branding } = usePage().props;

    const needsConfirmation = resetRulesChanged(settings, form.data);
    const unit = id.periods[form.data.reset_period].toLowerCase();

    const save = (confirmed: boolean) => {
        form.transform((data) => ({
            ...data,
            confirm_reset_change: confirmed,
        }));
        form.put(update.url(), {
            preserveScroll: true,
            onSuccess: () => {
                setConfirming(false);
                // The saved logo is shown from now on: forget the picked file.
                form.setData((data) => ({
                    ...data,
                    logo: null,
                    remove_logo: false,
                }));
                setLogoInputKey((key) => key + 1);
            },
        });
    };

    const setCapacity = (index: number, value: string) =>
        form.setData(
            'tanks',
            form.data.tanks.map((tank, position) =>
                position === index ? { ...tank, capacity_liters: value } : tank,
            ),
        );

    const error = (key: string) =>
        (form.errors as Record<string, string | undefined>)[key];

    return (
        <>
            <Head title={id.settings.title} />

            <form
                className="space-y-6 p-4 md:p-6"
                onSubmit={(event) => {
                    event.preventDefault();

                    if (needsConfirmation) {
                        setConfirming(true);
                    } else {
                        save(false);
                    }
                }}
            >
                <PageHeading
                    title={id.settings.title}
                    subtitle={id.settings.subtitle}
                    actions={
                        <Button
                            type="submit"
                            disabled={form.processing}
                            data-test="save-settings"
                        >
                            {form.processing
                                ? id.common.saving
                                : id.settings.save}
                        </Button>
                    }
                />

                <div className="grid gap-6 xl:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>{id.settings.identity.title}</CardTitle>
                            <CardDescription>
                                {id.settings.identity.description}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <Field
                                label={id.settings.site.siteName}
                                htmlFor="site-name"
                                error={form.errors.site_name}
                            >
                                <Input
                                    id="site-name"
                                    value={form.data.site_name}
                                    placeholder={
                                        id.settings.site.siteNamePlaceholder
                                    }
                                    onChange={(event) =>
                                        form.setData(
                                            'site_name',
                                            event.target.value,
                                        )
                                    }
                                />
                            </Field>
                            <LogoField
                                currentUrl={branding.logoUrl}
                                file={form.data.logo}
                                onFileChange={(file) =>
                                    form.setData('logo', file)
                                }
                                remove={form.data.remove_logo}
                                onRemoveChange={(remove) =>
                                    form.setData('remove_logo', remove)
                                }
                                error={form.errors.logo}
                                inputKey={logoInputKey}
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{id.settings.reset.title}</CardTitle>
                            <CardDescription>
                                {id.settings.reset.description}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label={id.settings.reset.period}
                                htmlFor="reset-period"
                                error={form.errors.reset_period}
                            >
                                <Select
                                    value={form.data.reset_period}
                                    onValueChange={(value) =>
                                        form.setData(
                                            'reset_period',
                                            value as ResetPeriod,
                                        )
                                    }
                                >
                                    <SelectTrigger
                                        id="reset-period"
                                        className="w-full"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {(
                                            ['day', 'week', 'month'] as const
                                        ).map((period) => (
                                            <SelectItem
                                                key={period}
                                                value={period}
                                            >
                                                {id.periods[period]}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field
                                label={id.settings.reset.time}
                                htmlFor="reset-time"
                                error={form.errors.reset_time}
                            >
                                <Input
                                    id="reset-time"
                                    type="time"
                                    value={form.data.reset_time}
                                    onChange={(event) =>
                                        form.setData(
                                            'reset_time',
                                            event.target.value,
                                        )
                                    }
                                />
                            </Field>

                            {form.data.reset_period === 'week' && (
                                <Field
                                    label={id.settings.reset.weekStart}
                                    htmlFor="week-start"
                                    error={form.errors.week_start_day}
                                >
                                    <Select
                                        value={String(form.data.week_start_day)}
                                        onValueChange={(value) =>
                                            form.setData(
                                                'week_start_day',
                                                Number(value),
                                            )
                                        }
                                    >
                                        <SelectTrigger
                                            id="week-start"
                                            className="w-full"
                                        >
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(id.weekdays).map(
                                                ([value, label]) => (
                                                    <SelectItem
                                                        key={value}
                                                        value={value}
                                                    >
                                                        {label}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                </Field>
                            )}

                            {form.data.reset_period === 'month' && (
                                <Field
                                    label={id.settings.reset.monthDay}
                                    htmlFor="month-day"
                                    error={form.errors.month_reset_day}
                                >
                                    <Input
                                        id="month-day"
                                        type="number"
                                        min="1"
                                        max="28"
                                        value={form.data.month_reset_day}
                                        onChange={(event) =>
                                            form.setData(
                                                'month_reset_day',
                                                Number(event.target.value),
                                            )
                                        }
                                    />
                                </Field>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{id.settings.site.title}</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label={id.settings.site.timezone}
                                htmlFor="timezone"
                                error={form.errors.timezone}
                            >
                                <Select
                                    value={form.data.timezone}
                                    onValueChange={(value) =>
                                        form.setData('timezone', value)
                                    }
                                >
                                    <SelectTrigger
                                        id="timezone"
                                        className="w-full"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(timezones).map(
                                            ([value, label]) => (
                                                <SelectItem
                                                    key={value}
                                                    value={value}
                                                >
                                                    {label}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectContent>
                                </Select>
                            </Field>

                            {tanks.map((tank, index) => (
                                <Field
                                    key={tank.id}
                                    label={fill(id.settings.site.tankCapacity, {
                                        name: tank.name,
                                    })}
                                    htmlFor={`tank-${tank.id}`}
                                    error={error(
                                        `tanks.${index}.capacity_liters`,
                                    )}
                                >
                                    <Input
                                        id={`tank-${tank.id}`}
                                        type="number"
                                        min="1"
                                        step="any"
                                        value={
                                            form.data.tanks[index]
                                                .capacity_liters
                                        }
                                        onChange={(event) =>
                                            setCapacity(
                                                index,
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            ))}
                        </CardContent>
                    </Card>

                    <Card className="xl:col-span-2">
                        <CardHeader>
                            <CardTitle>{id.settings.defaults.title}</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:max-w-md">
                            <Field
                                label={id.settings.defaults.lowQuota}
                                htmlFor="low-quota"
                                error={form.errors.low_quota_percent}
                                hint={id.settings.defaults.lowQuotaHint}
                            >
                                <Input
                                    id="low-quota"
                                    type="number"
                                    min="1"
                                    max="100"
                                    value={form.data.low_quota_percent}
                                    onChange={(event) =>
                                        form.setData(
                                            'low_quota_percent',
                                            Number(event.target.value),
                                        )
                                    }
                                />
                            </Field>
                        </CardContent>
                    </Card>
                </div>
            </form>

            <ConfirmDialog
                open={confirming}
                onOpenChange={setConfirming}
                title={id.settings.confirmTitle}
                description={
                    <>
                        <p>
                            {fill(id.settings.confirmBody, {
                                count: resetCardCount,
                            })}
                        </p>
                        <p>{fill(id.settings.confirmQuota, { unit })}</p>
                        {form.errors.confirm_reset_change && (
                            <p className="text-destructive">
                                {form.errors.confirm_reset_change}
                            </p>
                        )}
                    </>
                }
                confirmLabel={id.settings.confirmSubmit}
                processing={form.processing}
                onConfirm={() => save(true)}
            />
        </>
    );
}

GlobalSettingsPage.layout = {
    breadcrumbs: [{ title: id.nav.settings, href: edit() }],
};
