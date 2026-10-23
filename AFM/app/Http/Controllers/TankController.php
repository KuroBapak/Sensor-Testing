<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use App\Services\TankService;
use Inertia\Inertia;
use Inertia\Response;

class TankController extends Controller
{
    public function index(TankService $tanks): Response
    {
        return Inertia::render('tank/index', [
            'tanks' => Tank::query()->orderBy('id')->get()
                ->map(fn (Tank $tank) => $tanks->panel($tank))
                ->all(),
        ]);
    }
}
