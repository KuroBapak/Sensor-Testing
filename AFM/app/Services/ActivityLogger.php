<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Audit log (section 6): who did what, and when. */
class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function log(string $action, ?Model $subject = null, ?string $label = null, array $details = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $label,
            'details' => $details ?: null,
        ]);
    }
}
