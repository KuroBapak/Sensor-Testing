<?php

namespace App\Http\Controllers;

use App\Exports\QrCardsExport;
use App\Models\QrCard;
use App\Services\ActivityLogger;
use App\Services\QrCardListing;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Quick export of the QR page: the card table as currently filtered and sorted. */
class QrCardExportController extends Controller
{
    public function __invoke(Request $request, QrCardListing $listing, SettingsService $settings, ActivityLogger $logger): BinaryFileResponse
    {
        $now = now();
        $logger->log('export.qr_cards');

        $rows = array_values(
            $listing->query($request->only(QrCardListing::FILTER_KEYS), $now)->get()
                ->map(fn (QrCard $card) => $listing->present($card, $now))
                ->all(),
        );

        return Excel::download(
            new QrCardsExport($rows, $listing, $settings->timezone()),
            'kartu-qr-'.$now->format('Ymd-Hi').'.xlsx',
        );
    }
}
