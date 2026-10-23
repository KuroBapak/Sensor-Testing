<?php

namespace App\Http\Controllers;

use App\Http\Requests\QrCardStoreRequest;
use App\Models\QrCard;
use App\Models\VehicleType;
use App\Services\CardService;
use App\Services\QrCardListing;
use App\Services\QuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrCardController extends Controller
{
    public function __construct(
        private readonly CardService $cards,
        private readonly QrCardListing $listing,
    ) {}

    public function index(Request $request, QuotaService $quota): Response
    {
        $now = now();
        $filters = $request->only(QrCardListing::FILTER_KEYS);

        $page = $this->listing->query($filters, $now)->paginate(25)->withQueryString();

        return Inertia::render('qr/index', [
            'filters' => $filters + ['sort' => 'remaining', 'dir' => 'asc'],
            'counters' => $quota->counters($now),
            'cards' => [
                'data' => $page->getCollection()->map(fn (QrCard $card) => $this->listing->present($card, $now))->all(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'from' => $page->firstItem(),
                'to' => $page->lastItem(),
            ],
            'vehicleTypes' => VehicleType::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'quota_liters'])
                ->map(fn (VehicleType $type) => ['id' => $type->id, 'name' => $type->name, 'quota_liters' => $type->quota_liters])
                ->all(),
            'lowQuotaPercent' => $quota->lowQuotaPercent(),
        ]);
    }

    public function store(QrCardStoreRequest $request): RedirectResponse
    {
        $card = $this->cards->create($request->validated(), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.card.created', ['plate' => $card->plate_number])]);

        return back();
    }

    public function destroy(QrCard $qrCard): RedirectResponse
    {
        $this->cards->delete($qrCard);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.card.deleted', ['plate' => $qrCard->plate_number])]);

        return back();
    }
}
