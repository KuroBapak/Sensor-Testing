import { useForm } from '@inertiajs/react';
import { useEffect, useMemo } from 'react';
import { Field } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { expiryDate, formatLiters } from '@/lib/format';
import { store } from '@/routes/qr-cards';
import type { Category, VehicleOption } from '@/types/fuel';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    vehicleTypes: VehicleOption[];
};

type Data = {
    category: Category;
    vehicle_type_id: string;
    plate_number: string;
    validity: 'lifetime' | 'temporary';
    valid_days: string;
    reset_enabled: boolean;
};

/** One form, in order: category, vehicle type, plate number, validity (and reset). */
export function CreateCardDialog({ open, onOpenChange, vehicleTypes }: Props) {
    const site = useSite();
    const form = useForm<Data>({
        category: 'internal',
        vehicle_type_id: '',
        plate_number: '',
        validity: 'lifetime',
        valid_days: '0',
        reset_enabled: true,
    });
    const { setData, clearErrors } = form;

    useEffect(() => {
        if (!open) {
            return;
        }

        clearErrors();
        setData({
            category: 'internal',
            vehicle_type_id: '',
            plate_number: '',
            validity: 'lifetime',
            valid_days: '0',
            reset_enabled: true,
        });
    }, [open, setData, clearErrors]);

    const vehicle = vehicleTypes.find(
        (type) => String(type.id) === form.data.vehicle_type_id,
    );
    const days = Number.parseInt(form.data.valid_days, 10);
    // Valid days start at 0 and are required for a Temporary card: warn and block saving until set.
    const daysMissing = form.data.validity === 'temporary' && !(days >= 1);

    // The expiry date shown updates as the inputs change.
    const expiry = useMemo(
        () =>
            form.data.validity === 'temporary' && days >= 1
                ? expiryDate(new Date(), days, site.timezone)
                : null,
        [form.data.validity, days, site.timezone],
    );

    const submit = () => {
        form.transform((data) => ({
            ...data,
            vehicle_type_id:
                data.vehicle_type_id === '' ? '' : Number(data.vehicle_type_id),
            valid_days: data.validity === 'temporary' ? data.valid_days : null,
        }));
        form.post(store.url(), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{id.qr.createTitle}</DialogTitle>
                    <DialogDescription>{id.qr.createHelp}</DialogDescription>
                </DialogHeader>

                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        submit();
                    }}
                >
                    <Field label={id.qr.category} error={form.errors.category}>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={form.data.category}
                            className="w-fit"
                            onValueChange={(value) =>
                                value &&
                                form.setData('category', value as Category)
                            }
                        >
                            <ToggleGroupItem value="internal" className="px-4">
                                {id.categories.internal}
                            </ToggleGroupItem>
                            <ToggleGroupItem value="external" className="px-4">
                                {id.categories.external}
                            </ToggleGroupItem>
                        </ToggleGroup>
                    </Field>

                    <Field
                        label={id.qr.vehicleType}
                        htmlFor="card-vehicle"
                        error={form.errors.vehicle_type_id}
                    >
                        <Select
                            value={form.data.vehicle_type_id}
                            onValueChange={(value) =>
                                form.setData('vehicle_type_id', value)
                            }
                        >
                            <SelectTrigger id="card-vehicle" className="w-full">
                                <SelectValue
                                    placeholder={id.qr.vehiclePlaceholder}
                                />
                            </SelectTrigger>
                            <SelectContent>
                                {vehicleTypes.length === 0 && (
                                    <div className="px-2 py-1.5 text-sm text-muted-foreground">
                                        {id.qr.noVehicleTypes}
                                    </div>
                                )}
                                {vehicleTypes.map((type) => (
                                    <SelectItem
                                        key={type.id}
                                        value={String(type.id)}
                                    >
                                        {type.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </Field>

                    <Field
                        label={id.qr.plate}
                        htmlFor="card-plate"
                        error={form.errors.plate_number}
                    >
                        <Input
                            id="card-plate"
                            value={form.data.plate_number}
                            placeholder={id.qr.platePlaceholder}
                            className="uppercase"
                            onChange={(event) =>
                                form.setData('plate_number', event.target.value)
                            }
                        />
                    </Field>

                    <Field label={id.qr.validity} error={form.errors.validity}>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={form.data.validity}
                            className="w-fit"
                            onValueChange={(value) =>
                                value &&
                                form.setData(
                                    'validity',
                                    value as Data['validity'],
                                )
                            }
                        >
                            <ToggleGroupItem value="lifetime" className="px-4">
                                {id.validity.lifetime}
                            </ToggleGroupItem>
                            <ToggleGroupItem value="temporary" className="px-4">
                                {id.validity.temporary}
                            </ToggleGroupItem>
                        </ToggleGroup>
                    </Field>

                    {form.data.validity === 'temporary' ? (
                        <Field
                            label={id.qr.validDays}
                            htmlFor="card-days"
                            error={form.errors.valid_days}
                            hint={
                                daysMissing ? (
                                    <span
                                        className="font-medium text-amber-700 dark:text-amber-400"
                                        role="alert"
                                        data-test="days-warning"
                                    >
                                        {id.qr.validDaysWarning}
                                    </span>
                                ) : expiry ? (
                                    fill(id.qr.expiresOn, { date: expiry })
                                ) : undefined
                            }
                        >
                            <Input
                                id="card-days"
                                type="number"
                                min="1"
                                max="3650"
                                step="1"
                                className="w-32"
                                value={form.data.valid_days}
                                onChange={(event) =>
                                    form.setData(
                                        'valid_days',
                                        event.target.value,
                                    )
                                }
                            />
                        </Field>
                    ) : (
                        <p className="-mt-2 text-xs text-muted-foreground">
                            {id.qr.lifetimeNote}
                        </p>
                    )}

                    <div className="grid gap-1 rounded-md bg-muted/50 px-3 py-2 text-sm">
                        <span className="text-xs text-muted-foreground">
                            {id.qr.quota}
                        </span>
                        <span
                            className="font-medium tabular-nums"
                            data-test="card-quota"
                        >
                            {vehicle
                                ? fill(id.qr.quotaValue, {
                                      liters: formatLiters(
                                          vehicle.quota_liters,
                                      ),
                                      unit: site.resetUnit,
                                  })
                                : '–'}
                        </span>
                    </div>

                    <div className="grid gap-1">
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="card-reset"
                                checked={form.data.reset_enabled}
                                onCheckedChange={(checked) =>
                                    form.setData(
                                        'reset_enabled',
                                        checked === true,
                                    )
                                }
                            />
                            <Label htmlFor="card-reset">
                                {fill(id.qr.resetLabel, {
                                    unit: site.resetUnit,
                                })}
                            </Label>
                        </div>
                        <p className="pl-6 text-xs text-muted-foreground">
                            {form.data.reset_enabled
                                ? fill(id.qr.resetHelpOn, {
                                      unit: site.resetUnit,
                                  })
                                : id.qr.resetHelpOff}
                        </p>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            {id.common.cancel}
                        </Button>
                        <Button
                            type="submit"
                            disabled={form.processing || daysMissing}
                            data-test="save-card"
                        >
                            {form.processing
                                ? id.common.saving
                                : id.qr.saveCard}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
