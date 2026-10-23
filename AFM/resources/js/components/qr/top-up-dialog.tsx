import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { Field } from '@/components/field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { fill, id } from '@/lang/id';
import { store } from '@/routes/qr-cards/top-ups';
import type { QrCardRow } from '@/types/fuel';

type Props = {
    card: QrCardRow | null;
    onClose: () => void;
};

/** Add liters to one card. With reset on it helps this period only; with reset off it joins the pool. */
export function TopUpDialog({ card, onClose }: Props) {
    const form = useForm({ liters: '', note: '' });
    const { reset, clearErrors } = form;

    useEffect(() => {
        if (card) {
            reset();
            clearErrors();
        }
    }, [card, reset, clearErrors]);

    const submit = () => {
        if (!card) {
            return;
        }

        form.post(store.url(card.id), {
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    return (
        <Dialog
            open={card !== null}
            onOpenChange={(open) => !open && onClose()}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {fill(id.qr.topUpDialog.title, {
                            plate: card?.plate_number ?? '',
                        })}
                    </DialogTitle>
                    <DialogDescription>
                        {card?.reset_enabled
                            ? id.qr.topUpDialog.helpOn
                            : id.qr.topUpDialog.helpOff}
                    </DialogDescription>
                </DialogHeader>

                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        submit();
                    }}
                >
                    <Field
                        label={id.qr.topUpDialog.liters}
                        htmlFor="topup-liters"
                        error={form.errors.liters}
                    >
                        <Input
                            id="topup-liters"
                            type="number"
                            inputMode="decimal"
                            min="0"
                            step="any"
                            autoFocus
                            value={form.data.liters}
                            onChange={(event) =>
                                form.setData('liters', event.target.value)
                            }
                        />
                    </Field>
                    <Field
                        label={id.qr.topUpDialog.note}
                        htmlFor="topup-note"
                        error={form.errors.note}
                    >
                        <Input
                            id="topup-note"
                            maxLength={255}
                            value={form.data.note}
                            onChange={(event) =>
                                form.setData('note', event.target.value)
                            }
                        />
                    </Field>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            {id.common.cancel}
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing
                                ? id.common.saving
                                : id.qr.topUpDialog.submit}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
