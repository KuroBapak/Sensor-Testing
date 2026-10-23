import { useForm } from '@inertiajs/react';
import { FileSpreadsheet } from 'lucide-react';
import { useState } from 'react';
import { Field } from '@/components/field';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { id } from '@/lang/id';
import { example, store } from '@/routes/batch-add';
import type { ImportError } from '@/types/fuel';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

/**
 * Upload an .xlsx for a batch add. The server checks the whole file first; when
 * any row fails nothing is imported and the errors are listed with row numbers.
 */
export function BatchAddDialog({ open, onOpenChange }: Props) {
    const form = useForm<{ file: File | null }>({ file: null });
    const [errors, setErrors] = useState<ImportError[]>([]);

    const close = (next: boolean) => {
        if (!next) {
            form.reset();
            form.clearErrors();
            setErrors([]);
        }
        onOpenChange(next);
    };

    const submit = () => {
        setErrors([]);
        form.post(store.url(), {
            forceFormData: true,
            preserveScroll: true,
            onFlash: (flash) => setErrors(flash.import_errors ?? []),
            onSuccess: () => close(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={close}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{id.batchAdd.title}</DialogTitle>
                    <DialogDescription>{id.batchAdd.help}</DialogDescription>
                </DialogHeader>

                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        submit();
                    }}
                >
                    <div className="grid gap-3 rounded-md border bg-muted/40 p-3 text-sm">
                        <p>{id.batchAdd.exampleHelp}</p>
                        <div className="flex flex-wrap gap-2">
                            <Button size="sm" asChild>
                                <a
                                    href={example.url()}
                                    data-test="batch-example"
                                >
                                    <FileSpreadsheet />
                                    {id.batchAdd.example}
                                </a>
                            </Button>
                        </div>
                        <p className="text-xs text-muted-foreground">
                            {id.batchAdd.removeExample}
                        </p>
                    </div>

                    <Field
                        label={id.batchAdd.file}
                        htmlFor="import-file"
                        error={form.errors.file}
                    >
                        <Input
                            id="import-file"
                            type="file"
                            accept=".xlsx"
                            onChange={(event) =>
                                form.setData(
                                    'file',
                                    event.target.files?.[0] ?? null,
                                )
                            }
                        />
                    </Field>

                    {errors.length > 0 && (
                        <Alert variant="destructive">
                            <AlertTitle>{id.batchAdd.errorsTitle}</AlertTitle>
                            <AlertDescription>
                                <div
                                    className="mt-2 max-h-56 overflow-y-auto rounded-md border bg-background text-foreground"
                                    data-test="import-errors"
                                >
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>
                                                    {id.batchAdd.sheet}
                                                </TableHead>
                                                <TableHead className="w-16">
                                                    {id.batchAdd.row}
                                                </TableHead>
                                                <TableHead>
                                                    {id.batchAdd.column}
                                                </TableHead>
                                                <TableHead>
                                                    {id.batchAdd.message}
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {errors.map((error, index) => (
                                                <TableRow key={index}>
                                                    <TableCell>
                                                        {error.sheet}
                                                    </TableCell>
                                                    <TableCell className="tabular-nums">
                                                        {error.row}
                                                    </TableCell>
                                                    <TableCell>
                                                        {error.column}
                                                    </TableCell>
                                                    <TableCell className="whitespace-normal">
                                                        {error.message}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </AlertDescription>
                        </Alert>
                    )}

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => close(false)}
                        >
                            {id.common.close}
                        </Button>
                        <Button
                            type="submit"
                            disabled={form.processing || !form.data.file}
                        >
                            {form.processing
                                ? id.batchAdd.importing
                                : id.batchAdd.submit}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
