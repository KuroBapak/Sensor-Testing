<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleTypeRequest;
use App\Models\VehicleType;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleTypeController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        return Inertia::render('vehicles/index', [
            'search' => $search,
            'vehicleTypes' => VehicleType::query()
                ->withCount('qrCards')
                ->search($search)
                ->orderBy('name')
                ->orderBy('id')
                ->get()
                ->map(fn (VehicleType $type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'max_capacity_liters' => $type->max_capacity_liters,
                    'quota_liters' => $type->quota_liters,
                    'cards_count' => $type->qr_cards_count,
                ])
                ->all(),
        ]);
    }

    public function store(VehicleTypeRequest $request): RedirectResponse
    {
        $type = VehicleType::query()->create($request->validated());
        $this->logger->log('vehicle_type.created', $type, $type->name);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.vehicle.created', ['name' => $type->name])]);

        return back();
    }

    public function update(VehicleTypeRequest $request, VehicleType $vehicleType): RedirectResponse
    {
        $vehicleType->update($request->validated());
        $this->logger->log('vehicle_type.updated', $vehicleType, $vehicleType->name, $vehicleType->getChanges());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.vehicle.updated', ['name' => $vehicleType->name])]);

        return back();
    }

    public function destroy(VehicleType $vehicleType): RedirectResponse
    {
        $count = $vehicleType->qrCards()->count();

        if ($count > 0) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('fuel.vehicle.in_use', ['name' => $vehicleType->name, 'count' => $count])]);

            return back();
        }

        $vehicleType->delete();
        $this->logger->log('vehicle_type.deleted', $vehicleType, $vehicleType->name);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.vehicle.deleted', ['name' => $vehicleType->name])]);

        return back();
    }
}
