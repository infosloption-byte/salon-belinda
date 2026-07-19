<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Record an admin action.
     *
     * @param  string  $action  Short machine key, e.g. "order.status_updated"
     * @param  string  $description  Human-readable summary shown in the log
     * @param  Model|null  $subject  The model the action was performed on, if any
     * @param  array<string, mixed>  $properties  Extra context (e.g. old/new values)
     */
    public static function log(string $action, string $description, ?Model $subject = null, array $properties = []): void
    {
        AdminActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
        ]);
    }
}
