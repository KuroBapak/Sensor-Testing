<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportRequest;
use App\Imports\BatchAddImporter;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/** Batch Add: vehicle types and QR cards from one Excel file, all or nothing. */
class BatchAddController extends Controller
{
    public function store(ImportRequest $request, BatchAddImporter $importer, ActivityLogger $logger): RedirectResponse
    {
        $result = $importer->import($request->file('file'), $request->user());

        if ($result->failed()) {
            Inertia::flash('import_errors', $result->errors);

            throw ValidationException::withMessages([
                'file' => __('fuel.import.failed', ['count' => count($result->errors)]),
            ]);
        }

        $logger->log('import.batch_add', null, null, ['vehicle_types' => $result->vehicles, 'qr_cards' => $result->cards]);

        Inertia::flash('toast', ['type' => 'success', 'message' => match (true) {
            $result->vehicles > 0 && $result->cards > 0 => __('fuel.import.success_both', ['vehicles' => $result->vehicles, 'cards' => $result->cards]),
            $result->cards > 0 => __('fuel.import.success_cards', ['count' => $result->cards]),
            default => __('fuel.import.success_vehicles', ['count' => $result->vehicles]),
        }]);

        return back();
    }
}
