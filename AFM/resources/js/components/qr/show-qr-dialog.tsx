import { usePage } from '@inertiajs/react';
import { Copy, Download, Printer } from 'lucide-react';
import { QRCodeCanvas } from 'qrcode.react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { useSite } from '@/hooks/use-site';
import { fill, id } from '@/lang/id';
import { formatLiters } from '@/lib/format';
import { drawQrCard, loadImage, printImage } from '@/lib/qr-card-image';
import type { QrCardRow } from '@/types/fuel';

type Props = {
    card: QrCardRow | null;
    onClose: () => void;
};

/** "x-1234 ab" becomes "X-1234-AB", safe as a file name. */
export function pngName(plate: string): string {
    return `${plate.trim().replace(/\s+/g, '-')}.png`;
}

/**
 * The card (QR, brand, vehicle details and notes) is drawn only while this popup
 * is open, so the card table stays light. The preview is the very image that is
 * copied, downloaded and printed. The QR holds a random token only; the plate and
 * rules are looked up on the server.
 */
export function ShowQrDialog({ card, onClose }: Props) {
    const site = useSite();
    const { branding } = usePage().props;
    const [qr, setQr] = useState<HTMLCanvasElement | null>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const cardCanvas = useRef<HTMLCanvasElement | null>(null);

    useEffect(() => {
        if (!card || !qr) {
            setPreview(null);

            return;
        }

        let cancelled = false;

        void (async () => {
            await document.fonts?.ready;
            const logo = branding.logoUrl
                ? await loadImage(branding.logoUrl)
                : null;

            if (cancelled) {
                return;
            }

            const canvas =
                cardCanvas.current ?? document.createElement('canvas');
            cardCanvas.current = canvas;

            const liters = formatLiters(card.quota);
            drawQrCard(canvas, {
                qr,
                brandName: branding.name,
                logo,
                vehicleType: card.vehicle_type,
                plate: card.plate_number,
                categoryLabel: card.category_label,
                details: [
                    fill(id.qr.card.capacity, {
                        liters: formatLiters(card.max_capacity),
                    }),
                    card.reset_enabled
                        ? fill(id.qr.card.quotaReset, {
                              liters,
                              unit: site.resetUnit,
                          })
                        : fill(id.qr.card.quotaPool, { liters }),
                    card.expires_at
                        ? fill(id.qr.card.validUntil, {
                              date: site.date(card.expires_at),
                          })
                        : id.qr.card.validLifetime,
                ],
                created: fill(id.qr.card.created, {
                    date: site.dateTime(card.issued_at),
                }),
                notes: [...id.qr.card.notes],
            });
            setPreview(canvas.toDataURL('image/png'));
        })();

        return () => {
            cancelled = true;
        };
    }, [
        card,
        qr,
        branding.logoUrl,
        branding.name,
        site.resetUnit,
        site.timezone,
    ]);

    const copy = () => {
        cardCanvas.current?.toBlob(async (blob) => {
            try {
                if (
                    !blob ||
                    !navigator.clipboard ||
                    typeof ClipboardItem === 'undefined'
                ) {
                    throw new Error('clipboard unavailable');
                }

                await navigator.clipboard.write([
                    new ClipboardItem({ 'image/png': blob }),
                ]);
                toast.success(id.qr.popup.copied);
            } catch {
                // Browsers allow image copy on HTTPS or localhost only.
                toast.info(id.qr.popup.copyBlocked);
            }
        }, 'image/png');
    };

    const download = () => {
        if (!preview || !card) {
            return;
        }

        const link = document.createElement('a');
        link.href = preview;
        link.download = pngName(card.plate_number);
        link.click();
        toast.success(id.qr.popup.downloaded);
    };

    const print = () => {
        if (preview && card) {
            printImage(preview, card.plate_number);
        }
    };

    return (
        <Dialog
            open={card !== null}
            onOpenChange={(open) => !open && onClose()}
        >
            <DialogContent className="sm:max-w-3xl" data-test="qr-dialog">
                <DialogHeader>
                    <DialogTitle>{id.qr.popup.title}</DialogTitle>
                    <DialogDescription>
                        {id.qr.popup.description}
                    </DialogDescription>
                </DialogHeader>

                {card && (
                    <div className="grid gap-3">
                        {/* The QR is drawn here, hidden, and copied onto the card. */}
                        <QRCodeCanvas
                            ref={setQr}
                            value={card.token}
                            size={560}
                            level="M"
                            marginSize={1}
                            className="hidden"
                        />

                        {preview ? (
                            <img
                                src={preview}
                                alt={fill(id.qr.popup.imageLabel, {
                                    plate: card.plate_number,
                                })}
                                className="mx-auto w-full max-w-2xl rounded-2xl border shadow-sm"
                                data-test="qr-card-image"
                            />
                        ) : (
                            <Skeleton className="mx-auto aspect-[672/453] w-full max-w-2xl rounded-2xl" />
                        )}
                    </div>
                )}

                <DialogFooter className="sm:justify-between">
                    <div className="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={copy}
                            disabled={!preview}
                        >
                            <Copy />
                            {id.qr.popup.copy}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={download}
                            disabled={!preview}
                        >
                            <Download />
                            {id.qr.popup.download}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={print}
                            disabled={!preview}
                        >
                            <Printer />
                            {id.qr.popup.print}
                        </Button>
                    </div>
                    <Button type="button" onClick={onClose}>
                        {id.common.close}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
