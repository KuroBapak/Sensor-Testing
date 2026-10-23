import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { Field } from '@/components/field';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { store, update } from '@/routes/vehicles';
import type { VehicleTypeRow } from '@/types/fuel';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    /** The type being edited, or null to add a new one. */
    vehicle: VehicleTypeRow | null;
};

type Data = { name: string; max_capacity_liters: string; quota_liters: string };

const blank: Data = { name: '', max_capacity_liters: '', quota_liters: '' };

export function VehicleFormDialog({ open, onOpenChange, vehicle }: Props) {
    const site = useSite();
    const form = useForm<Data>(blank);
    const { setData, clearErrors } = form;

    useEffect(() => {
        if (!open) {
            return;
        }

        clearErrors();
        setData(
            vehicle
                ? {
                      name: vehicle.name,
                      max_capacity_liters: String(vehicle.max_capacity_liters),
                      quota_liters: String(vehicle.quota_liters),
                  }
                : blank,
        );
    }, [open, vehicle, setData, clearErrors]);

    const quotaChanged =
        vehicle !== null &&
        Number(form.data.quota_liters) !== vehicle.quota_liters;

    const submit = () => {
        const options = {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        };

        if (vehicle) {
            form.put(update.url(vehicle.id), options);
        } else {
            form.post(store.url(), options);
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {vehicle ? id.vehicles.editTitle : id.vehicles.addTitle}
                    </DialogTitle>
                </DialogHeader>

                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        submit();
                    }}
                >
                    <Field
                        label={id.vehicles.nameLabel}
                        htmlFor="vehicle-name"
                        error={form.errors.name}
                    >
                        <Input
                            id="vehicle-name"
                            value={form.data.name}
                            placeholder={id.vehicles.namePlaceholder}
                            autoFocus
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                        />
                    </Field>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field
                            label={id.vehicles.capacityLabel}
                            htmlFor="vehicle-capacity"
                            error={form.errors.max_capacity_liters}
                        >
                            <Input
                                id="vehicle-capacity"
                                type="number"
                                inputMode="decimal"
                                min="0"
                                step="any"
                                value={form.data.max_capacity_liters}
                                onChange={(event) =>
                                    form.setData(
                                        'max_capacity_liters',
                                        event.target.value,
                                    )
                                }
                            />
                        </Field>
                        <Field
                            label={fill(id.vehicles.quotaLabel, {
                                unit: site.resetUnit,
                            })}
                            htmlFor="vehicle-quota"
                            error={form.errors.quota_liters}
                            hint={id.vehicles.quotaHint}
                        >
                            <Input
                                id="vehicle-quota"
                                type="number"
                                inputMode="decimal"
                                min="0"
                                step="any"
                                value={form.data.quota_liters}
                                onChange={(event) =>
                                    form.setData(
                                        'quota_liters',
                                        event.target.value,
                                    )
                                }
                            />
                        </Field>
                    </div>

                    {quotaChanged && vehicle.cards_count > 0 && (
                        <Alert>
                            <AlertDescription>
                                {fill(id.vehicles.quotaChangeWarning, {
                                    count: vehicle.cards_count,
                                })}
                            </AlertDescription>
                        </Alert>
                    )}

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            {id.common.cancel}
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing
                                ? id.common.saving
                                : id.common.save}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
