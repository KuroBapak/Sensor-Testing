import { Head, router, useForm } from '@inertiajs/react';
import { KeyRound, Pencil, Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { Field } from '@/components/field';
import { PageHeading } from '@/components/page-heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
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
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { destroy, index, store, update } from '@/routes/admin-accounts';
import { password as passwordRoute } from '@/routes/admin-accounts';
import type { AccountRow } from '@/types/fuel';

function AccountFormDialog({
    account,
    open,
    onOpenChange,
}: {
    account: AccountRow | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const form = useForm({ name: '', email: '', password: '' });
    const { setData, clearErrors } = form;

    useEffect(() => {
        if (open) {
            clearErrors();
            setData({
                name: account?.name ?? '',
                email: account?.email ?? '',
                password: '',
            });
        }
    }, [open, account, setData, clearErrors]);

    const submit = () => {
        const options = {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        };

        if (account) {
            form.transform(({ name, email }) => ({ name, email }));
            form.put(update.url(account.id), options);
        } else {
            form.transform((data) => data);
            form.post(store.url(), options);
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {account ? id.accounts.editTitle : id.accounts.addTitle}
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
                        label={id.accounts.name}
                        htmlFor="account-name"
                        error={form.errors.name}
                    >
                        <Input
                            id="account-name"
                            autoFocus
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                        />
                    </Field>
                    <Field
                        label={id.accounts.email}
                        htmlFor="account-email"
                        error={form.errors.email}
                    >
                        <Input
                            id="account-email"
                            type="email"
                            autoComplete="off"
                            value={form.data.email}
                            onChange={(event) =>
                                form.setData('email', event.target.value)
                            }
                        />
                    </Field>
                    {!account && (
                        <Field
                            label={id.accounts.password}
                            htmlFor="account-password"
                            error={form.errors.password}
                            hint={id.accounts.passwordHelp}
                        >
                            <Input
                                id="account-password"
                                type="password"
                                autoComplete="new-password"
                                value={form.data.password}
                                onChange={(event) =>
                                    form.setData('password', event.target.value)
                                }
                            />
                        </Field>
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

function ResetPasswordDialog({
    account,
    onClose,
}: {
    account: AccountRow | null;
    onClose: () => void;
}) {
    const form = useForm({ password: '' });
    const { reset, clearErrors } = form;

    useEffect(() => {
        if (account) {
            reset();
            clearErrors();
        }
    }, [account, reset, clearErrors]);

    return (
        <Dialog
            open={account !== null}
            onOpenChange={(open) => !open && onClose()}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {fill(id.accounts.resetTitle, {
                            name: account?.name ?? '',
                        })}
                    </DialogTitle>
                </DialogHeader>
                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        if (account) {
                            form.put(passwordRoute.url(account.id), {
                                preserveScroll: true,
                                onSuccess: onClose,
                            });
                        }
                    }}
                >
                    <Field
                        label={id.accounts.newPassword}
                        htmlFor="reset-password"
                        error={form.errors.password}
                        hint={id.accounts.passwordHelp}
                    >
                        <Input
                            id="reset-password"
                            type="password"
                            autoComplete="new-password"
                            autoFocus
                            value={form.data.password}
                            onChange={(event) =>
                                form.setData('password', event.target.value)
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
                                : id.accounts.resetPassword}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminAccountsIndex({
    accounts,
}: {
    accounts: AccountRow[];
}) {
    const site = useSite();
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<AccountRow | null>(null);
    const [resetting, setResetting] = useState<AccountRow | null>(null);
    const [deleting, setDeleting] = useState<AccountRow | null>(null);
    const [processing, setProcessing] = useState(false);

    const openForm = (account: AccountRow | null) => {
        setEditing(account);
        setFormOpen(true);
    };

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(destroy.url(deleting.id), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    };

    return (
        <>
            <Head title={id.accounts.title} />

            <div className="space-y-6 p-4 md:p-6">
                <PageHeading
                    title={id.accounts.title}
                    subtitle={id.accounts.subtitle}
                    actions={
                        <Button
                            size="sm"
                            onClick={() => openForm(null)}
                            data-test="add-account"
                        >
                            <Plus />
                            {id.accounts.add}
                        </Button>
                    }
                />

                <Card>
                    <CardContent className="px-2 md:px-4">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{id.accounts.name}</TableHead>
                                    <TableHead>{id.accounts.email}</TableHead>
                                    <TableHead>
                                        {id.accounts.createdAt}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {id.common.actions}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {accounts.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            {id.accounts.empty}
                                        </TableCell>
                                    </TableRow>
                                )}
                                {accounts.map((account) => (
                                    <TableRow key={account.id}>
                                        <TableCell className="font-medium">
                                            {account.name}
                                        </TableCell>
                                        <TableCell>{account.email}</TableCell>
                                        <TableCell>
                                            {account.created_at
                                                ? site.date(account.created_at)
                                                : '–'}
                                        </TableCell>
                                        <TableCell className="text-right whitespace-nowrap">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`${id.common.edit} ${account.name}`}
                                                onClick={() =>
                                                    openForm(account)
                                                }
                                            >
                                                <Pencil />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`${id.accounts.resetPassword} ${account.name}`}
                                                onClick={() =>
                                                    setResetting(account)
                                                }
                                            >
                                                <KeyRound />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label={`${id.common.delete} ${account.name}`}
                                                onClick={() =>
                                                    setDeleting(account)
                                                }
                                            >
                                                <Trash2 />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <AccountFormDialog
                account={editing}
                open={formOpen}
                onOpenChange={setFormOpen}
            />
            <ResetPasswordDialog
                account={resetting}
                onClose={() => setResetting(null)}
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={id.accounts.deleteTitle}
                description={
                    <p>
                        {fill(id.accounts.deleteBody, {
                            name: deleting?.name ?? '',
                            email: deleting?.email ?? '',
                        })}
                    </p>
                }
                confirmLabel={id.common.delete}
                destructive
                processing={processing}
                onConfirm={confirmDelete}
            />
        </>
    );
}

AdminAccountsIndex.layout = {
    breadcrumbs: [{ title: id.nav.accounts, href: index() }],
};
