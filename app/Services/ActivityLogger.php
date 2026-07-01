<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Society;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log an activity to the database.
     */
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): ActivityLog
    {
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        // Determine society_id
        $societyId = null;
        if ($user && ! $user->isSuperAdmin()) {
            $societyId = $user->society_id;
        } elseif ($subject) {
            if (isset($subject->society_id)) {
                $societyId = $subject->society_id;
            } elseif ($subject instanceof Society) {
                $societyId = $subject->id;
            } elseif (method_exists($subject, 'flat') && $subject->flat && isset($subject->flat->society_id)) {
                $societyId = $subject->flat->society_id;
            }
        }

        return ActivityLog::create([
            'user_id' => $userId,
            'society_id' => $societyId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
