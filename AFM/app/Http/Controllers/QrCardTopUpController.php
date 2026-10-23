<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopUpRequest;
use App\Models\QrCard;
use App\Services\CardService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class QrCardTopUpController extends Controller
{
    public function store(TopUpRequest $request, QrCard $qrCard, CardService $cards): RedirectResponse
    {
        $liters = (float) $request->validated('liters');

        $cards->topUp($qrCard, $liters, $request->validated('note'), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.card.topped_up', [
            'plate' => $qrCard->plate_number,
            'liters' => number_format($liters, 1, ',', '.'),
        ])]);

        return back();
    }
}
