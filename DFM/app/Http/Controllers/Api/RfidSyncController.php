<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RfidTag;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidSyncController extends Controller
{
    /**
     * RFID list sync for RUT956 devices.
     * Returns 304 Not Modified if device already has the latest version.
     */
    public function sync(Request $request): JsonResponse
    {
        $site = Site::firstOrFail();
        $deviceVersion = (int) $request->header('X-RFID-Version', 0);

        if ($deviceVersion >= $site->rfid_list_version) {
            return response()->json(null, 304);
        }

        $tags = RfidTag::where('status', 'active')
            ->get(['tag_id', 'tank_id', 'sector'])
            ->map(fn (RfidTag $tag) => [
                'tag_id' => $tag->tag_id,
                'tank_id' => $tag->tank_id,
                'sector' => $tag->sector,
            ]);

        return response()->json([
            'version' => $site->rfid_list_version,
            'tags' => $tags,
        ]);
    }
}
