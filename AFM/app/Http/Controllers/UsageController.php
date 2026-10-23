<?php

namespace App\Http\Controllers;

use App\Enums\CardCategory;
use App\Enums\ResetPeriod;
use App\Models\QrCard;
use App\Services\UsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsageController extends Controller
{
    public function index(Request $request, UsageService $usage): Response
    {
        $side = CardCategory::tryFrom((string) $request->query('side')) ?? CardCategory::Internal;
        $period = ResetPeriod::tryFrom((string) $request->query('period')) ?? ResetPeriod::Day;

        [$from, $to] = $usage->range($period);

        return Inertia::render('usage/index', [
            'side' => $side->value,
            'period' => $period->value,
            'range' => ['from' => $from->toIso8601String(), 'to' => $to->toIso8601String()],
            'totals' => [
                'internal' => $usage->total(CardCategory::Internal, $from, $to),
                'external' => $usage->total(CardCategory::External, $from, $to),
            ],
            'chart' => $usage->totalSeries($side, $period),
            'groups' => $usage->groupedRows($side, $from, $to),
        ]);
    }

    /** Vehicle detail: usage graphs for day, week and month plus recent fuelings. */
    public function show(QrCard $card, UsageService $usage): JsonResponse
    {
        return response()->json([
            'card' => [
                'id' => $card->id,
                'plate_number' => $card->plate_number,
                'category' => $card->category->value,
                'vehicle_type' => $card->vehicleType->name,
                'deleted' => $card->trashed(),
            ],
            'series' => [
                'day' => $usage->series($card, ResetPeriod::Day),
                'week' => $usage->series($card, ResetPeriod::Week),
                'month' => $usage->series($card, ResetPeriod::Month),
            ],
            'fuelings' => $usage->recentFuelings($card)->map(fn ($tx) => [
                'id' => $tx->id,
                'dispensed_at' => $tx->dispensed_at->toIso8601String(),
                'liters' => $tx->liters,
            ])->all(),
        ]);
    }
}
