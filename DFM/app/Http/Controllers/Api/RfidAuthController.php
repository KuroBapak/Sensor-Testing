<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RfidTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidAuthController extends Controller
{
    /**
     * Emergency RFID validation for tags not in the device's local cache.
     * PRD §2.A: Fail-closed enforcement fallback.
     *
     * POST /api/v1/auth/check
     * Body: { "tag_uid": "..." }
     * Response: { "allow": true/false, "reason": "..." }
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
        ]);

        $tag = RfidTag::where('tag_id', $validated['tag_uid'])->first();

        if (! $tag) {
            return response()->json([
                'allow' => false,
                'reason' => 'Tag not registered',
            ]);
        }

        if ($tag->status === 'blocked') {
            return response()->json([
                'allow' => false,
                'reason' => 'Tag is blocked',
            ]);
        }

        return response()->json([
            'allow' => true,
            'reason' => 'Tag is valid',
            'tank_id' => $tag->tank_id,
            'sector' => $tag->sector,
        ]);
    }
}
