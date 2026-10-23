import { useEffect, useMemo } from 'react';
import { Field } from '@/components/field';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { id } from '@/lang/id';

type Props = {
    /** The logo saved today, or null when there is none. */
    currentUrl: string | null;
    file: File | null;
    onFileChange: (file: File | null) => void;
    remove: boolean;
    onRemoveChange: (remove: boolean) => void;
    error?: string;
    /** Change it to clear the file input after a save. */
    inputKey: number;
};

/** Upload, preview and remove the site logo. */
export function LogoField({
    currentUrl,
    file,
    onFileChange,
    remove,
    onRemoveChange,
    error,
    inputKey,
}: Props) {
    // A picked file is previewed right away; otherwise the saved logo is shown.
    const picked = useMemo(
        () => (file ? URL.createObjectURL(file) : null),
        [file],
    );

    useEffect(() => {
        return () => {
            if (picked) {
                URL.revokeObjectURL(picked);
            }
        };
    }, [picked]);

    const preview = picked ?? (remove ? null : currentUrl);

    return (
        <Field
            label={id.settings.identity.logo}
            htmlFor="site-logo"
            error={error}
            hint={id.settings.identity.logoHint}
        >
            <div className="flex items-center gap-4">
                <div
                    className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-muted/40"
                    data-test="logo-preview"
                >
                    {preview ? (
                        <img
                            src={preview}
                            alt={id.settings.identity.logoPreviewAlt}
                            className="size-full object-contain"
                        />
                    ) : (
                        <span className="px-1 text-center text-[10px] text-muted-foreground">
                            {id.settings.identity.logoNone}
                        </span>
                    )}
                </div>

                <div className="grid min-w-0 flex-1 gap-2">
                    <Input
                        key={inputKey}
                        id="site-logo"
                        type="file"
                        accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml"
                        onChange={(event) => {
                            onFileChange(event.target.files?.[0] ?? null);
                            onRemoveChange(false);
                        }}
                    />
                    {currentUrl && !file && (
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remove-logo"
                                checked={remove}
                                onCheckedChange={(checked) =>
                                    onRemoveChange(checked === true)
                                }
                            />
                            <Label htmlFor="remove-logo" className="text-sm">
                                {id.settings.identity.logoRemove}
                            </Label>
                        </div>
                    )}
                </div>
            </div>
        </Field>
    );
}
