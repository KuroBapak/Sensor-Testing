/**
 * Draws the printable QR card: the QR with its creation time on the left, the
 * brand (site logo and name from global settings) on top, the vehicle details on
 * the right and a list of notes along the bottom. The card is drawn on a canvas,
 * so what the popup previews is exactly what is copied, downloaded or printed.
 */

/** Logical size of the card; the canvas is drawn at SCALE times this for sharp output. */
export const CARD_WIDTH = 672;
export const CARD_HEIGHT = 453;
export const SCALE = 2;

const FONT =
    '"Instrument Sans", system-ui, -apple-system, "Segoe UI", sans-serif';

export type QrCardImageInput = {
    /** The QR code, already drawn on a canvas. */
    qr: CanvasImageSource;
    /** Site name from global settings, or the app name. */
    brandName: string;
    /** Site logo, or null to show the name only. */
    logo: HTMLImageElement | null;
    vehicleType: string;
    plate: string;
    categoryLabel: string;
    /** Detail lines under the plate, e.g. tank capacity, quota, validity. */
    details: string[];
    /** "Dibuat 22/10/2026 14:30", printed under the QR. */
    created: string;
    /** Notes along the bottom of the card. */
    notes: string[];
};

/** Load an image from the same origin; null when it cannot be loaded. */
export function loadImage(url: string): Promise<HTMLImageElement | null> {
    return new Promise((resolve) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => resolve(null);
        image.src = url;
    });
}

/** Split a text into lines that fit a width with the font currently set on the context. */
export function wrapText(
    ctx: CanvasRenderingContext2D,
    text: string,
    maxWidth: number,
): string[] {
    const lines: string[] = [];
    let line = '';

    for (const word of text.split(/\s+/).filter(Boolean)) {
        const candidate = line === '' ? word : `${line} ${word}`;

        if (line !== '' && ctx.measureText(candidate).width > maxWidth) {
            lines.push(line);
            line = word;
        } else {
            line = candidate;
        }
    }

    if (line !== '') {
        lines.push(line);
    }

    return lines;
}

/** Shrink the font until the text fits, then cut it with an ellipsis if it still does not. */
export function fitText(
    ctx: CanvasRenderingContext2D,
    text: string,
    maxWidth: number,
    weight: string,
    startSize: number,
    minSize: number,
): { text: string; size: number } {
    let size = startSize;

    for (; size >= minSize; size -= 1) {
        ctx.font = `${weight} ${size}px ${FONT}`;
        if (ctx.measureText(text).width <= maxWidth) {
            return { text, size };
        }
    }

    size = minSize;
    ctx.font = `${weight} ${size}px ${FONT}`;
    let cut = text;
    while (cut.length > 1 && ctx.measureText(`${cut}…`).width > maxWidth) {
        cut = cut.slice(0, -1);
    }

    return { text: `${cut}…`, size };
}

function roundRect(
    ctx: CanvasRenderingContext2D,
    x: number,
    y: number,
    width: number,
    height: number,
    radii: [number, number, number, number],
): void {
    ctx.beginPath();
    ctx.roundRect(x, y, width, height, radii);
}

export function drawQrCard(
    canvas: HTMLCanvasElement,
    input: QrCardImageInput,
): void {
    canvas.width = CARD_WIDTH * SCALE;
    canvas.height = CARD_HEIGHT * SCALE;

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    ctx.setTransform(SCALE, 0, 0, SCALE, 0, 0);
    ctx.textBaseline = 'alphabetic';

    // Background with soft shapes, clipped to the rounded card.
    ctx.save();
    roundRect(ctx, 0, 0, CARD_WIDTH, CARD_HEIGHT, [22, 22, 22, 22]);
    ctx.clip();

    const background = ctx.createLinearGradient(0, 0, CARD_WIDTH, CARD_HEIGHT);
    background.addColorStop(0, '#d4e6f8');
    background.addColorStop(1, '#eaf3fc');
    ctx.fillStyle = background;
    ctx.fillRect(0, 0, CARD_WIDTH, CARD_HEIGHT);

    ctx.fillStyle = 'rgba(160, 200, 240, 0.35)';
    ctx.beginPath();
    ctx.arc(CARD_WIDTH - 40, CARD_HEIGHT - 70, 150, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = 'rgba(190, 220, 248, 0.5)';
    ctx.beginPath();
    ctx.arc(CARD_WIDTH - 150, 120, 90, 0, Math.PI * 2);
    ctx.fill();

    // Notes band along the bottom.
    const bandTop = 382;
    ctx.fillStyle = 'rgba(255, 255, 255, 0.82)';
    ctx.fillRect(0, bandTop, CARD_WIDTH, CARD_HEIGHT - bandTop);
    ctx.restore();

    drawBrandTab(ctx, input);
    drawQrPanel(ctx, input);
    drawDetails(ctx, input);
    drawNotes(ctx, input, bandTop);
}

function drawBrandTab(
    ctx: CanvasRenderingContext2D,
    input: QrCardImageInput,
): void {
    const x = 376;
    const width = CARD_WIDTH - x;
    const height = 74;

    ctx.fillStyle = '#ffffff';
    roundRect(ctx, x, 0, width, height, [0, 0, 0, 26]);
    ctx.fill();

    let textX = x + 22;

    if (input.logo) {
        const natural = {
            width: input.logo.naturalWidth || 1,
            height: input.logo.naturalHeight || 1,
        };
        const box = { width: 70, height: 46 };
        const ratio = Math.min(
            box.width / natural.width,
            box.height / natural.height,
        );
        const drawWidth = natural.width * ratio;
        const drawHeight = natural.height * ratio;

        ctx.drawImage(
            input.logo,
            textX,
            (height - drawHeight) / 2,
            drawWidth,
            drawHeight,
        );
        textX += drawWidth + 12;
    }

    const maxWidth = CARD_WIDTH - 18 - textX;
    const fitted = fitText(
        ctx,
        input.brandName.toUpperCase(),
        maxWidth,
        '800',
        24,
        12,
    );

    ctx.fillStyle = '#0f3d6e';
    ctx.font = `800 ${fitted.size}px ${FONT}`;
    ctx.fillText(fitted.text, textX, height / 2 + fitted.size * 0.35);
}

function drawQrPanel(
    ctx: CanvasRenderingContext2D,
    input: QrCardImageInput,
): void {
    ctx.save();
    ctx.shadowColor = 'rgba(15, 61, 110, 0.18)';
    ctx.shadowBlur = 14;
    ctx.shadowOffsetY = 4;
    ctx.fillStyle = '#ffffff';
    roundRect(ctx, 24, 26, 316, 340, [24, 24, 24, 24]);
    ctx.fill();
    ctx.restore();

    ctx.drawImage(input.qr, 42, 40, 280, 280);

    ctx.fillStyle = '#334155';
    ctx.font = `500 14px ${FONT}`;
    ctx.textAlign = 'center';
    ctx.fillText(input.created, 24 + 316 / 2, 346);
    ctx.textAlign = 'left';
}

function drawDetails(
    ctx: CanvasRenderingContext2D,
    input: QrCardImageInput,
): void {
    const x = 366;
    const maxWidth = CARD_WIDTH - x - 26;
    let y = 112;

    // Vehicle type: the big title, wrapped over at most two lines.
    ctx.fillStyle = '#0f172a';
    ctx.font = `700 26px ${FONT}`;
    const title = wrapText(ctx, input.vehicleType, maxWidth).slice(0, 2);
    for (const line of title) {
        ctx.fillText(line, x, y);
        y += 31;
    }

    // Plate number, a divider, and the category.
    y += 4;
    ctx.font = `600 21px ${FONT}`;
    ctx.fillStyle = '#1e293b';
    ctx.fillText(input.plate, x, y);
    const plateWidth = ctx.measureText(input.plate).width;

    ctx.fillStyle = '#1e293b';
    ctx.fillRect(x + plateWidth + 14, y - 20, 2, 26);
    ctx.font = `500 21px ${FONT}`;
    ctx.fillText(input.categoryLabel, x + plateWidth + 28, y);
    y += 14;

    // Detail lines.
    ctx.fillStyle = '#334155';
    ctx.font = `500 16px ${FONT}`;
    for (const detail of input.details) {
        for (const line of wrapText(ctx, detail, maxWidth)) {
            y += 22;
            ctx.fillText(line, x, y);
        }
    }
}

function drawNotes(
    ctx: CanvasRenderingContext2D,
    input: QrCardImageInput,
    bandTop: number,
): void {
    ctx.fillStyle = '#334155';
    ctx.font = `500 11.5px ${FONT}`;

    let y = bandTop + 20;
    for (const note of input.notes) {
        for (const line of wrapText(ctx, note, CARD_WIDTH - 48)) {
            ctx.fillText(line, 24, y);
            y += 16;
        }
        y += 2;
    }
}

/** Print an image on its own page, from a hidden frame so the app around it is not printed. */
export function printImage(dataUrl: string, title: string): void {
    const frame = document.createElement('iframe');
    frame.style.cssText =
        'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden';
    document.body.appendChild(frame);

    const doc = frame.contentDocument;
    const view = frame.contentWindow;
    if (!doc || !view) {
        frame.remove();

        return;
    }

    doc.open();
    doc.write(
        `<!doctype html><html><head><meta charset="utf-8"><title>${title}</title>` +
            '<style>@page{margin:12mm}html,body{margin:0}' +
            'img{display:block;width:120mm;max-width:100%;margin:0 auto}</style></head>' +
            `<body><img src="${dataUrl}" alt=""></body></html>`,
    );
    doc.close();

    const cleanup = () => window.setTimeout(() => frame.remove(), 1000);
    const image = doc.querySelector('img');
    const run = () => {
        view.focus();
        view.print();
        cleanup();
    };

    if (image && !image.complete) {
        image.onload = run;
        image.onerror = cleanup;
    } else {
        run();
    }
}
