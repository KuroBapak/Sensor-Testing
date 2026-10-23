<?php

namespace App\Http\Controllers;

use App\Exports\VehicleTypesExport;
use App\Models\VehicleType;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Quick export of the Vehicles page, respecting the current search. */
class VehicleTypeExportController extends Controller
{
    public function __invoke(Request $request, ActivityLogger $logger): BinaryFileResponse
    {
        $logger->log('export.vehicle_types');

        return Excel::download(
            new VehicleTypesExport(VehicleType::query()->withCount('qrCards')->search($request->string('search')->trim()->toString())->orderBy('name')->orderBy('id')->get()),
            'kendaraan-'.now()->format('Ymd-Hi').'.xlsx',
        );
    }
}
