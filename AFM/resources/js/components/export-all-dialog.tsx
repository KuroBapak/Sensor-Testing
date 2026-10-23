import { FileSpreadsheet } from 'lucide-react';
import { useState } from 'react';
import { Field } from '@/components/field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useSite } from '@/hooks/use-site';
import { id } from '@/lang/id';
import { monthStartIso, todayIso } from '@/lib/format';
import { full } from '@/routes/export';

/** "Export Semua Data": one workbook for every user, opened from the header on any page. */
export function ExportAllDialog() {
    const { timezone } = useSite();
    const [open, setOpen] = useState(false);
    const [from, setFrom] = useState(() => monthStartIso(timezone));
    const [to, setTo] = useState(() => todayIso(timezone));

    const invalid = from === '' || to === '' || to < from;

    const submit = () => {
        if (invalid) {
            return;
        }

        // A normal navigation: the response is a file download, not an Inertia page.
        window.location.href = full.url({ query: { from, to } });
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button
                    variant="outline"
                    size="sm"
                    data-test="export-all-button"
                >
                    <FileSpreadsheet />
                    <span className="hidden sm:inline">
                        {id.exportAll.button}
                    </span>
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{id.exportAll.title}</DialogTitle>
                    <DialogDescription>
                        {id.exportAll.description}
                    </DialogDescription>
                </DialogHeader>

                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        submit();
                    }}
                >
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label={id.exportAll.from} htmlFor="export-from">
                            <Input
                                id="export-from"
                                type="date"
                                value={from}
                                max={to || undefined}
                                onChange={(event) =>
                                    setFrom(event.target.value)
                                }
                            />
                        </Field>
                        <Field
                            label={id.exportAll.to}
                            htmlFor="export-to"
                            error={
                                to !== '' && to < from
                                    ? id.exportAll.invalidRange
                                    : undefined
                            }
                        >
                            <Input
                                id="export-to"
                                type="date"
                                value={to}
                                min={from || undefined}
                                onChange={(event) => setTo(event.target.value)}
                            />
                        </Field>
                    </div>

                    <div>
                        <Button
                            type="button"
                            variant="link"
                            className="h-auto p-0"
                            onClick={() => {
                                setFrom(monthStartIso(timezone));
                                setTo(todayIso(timezone));
                            }}
                        >
                            {id.exportAll.thisMonth}
                        </Button>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setOpen(false)}
                        >
                            {id.common.cancel}
                        </Button>
                        <Button type="submit" disabled={invalid}>
                            {id.exportAll.submit}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
