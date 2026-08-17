<?php

namespace App\Actions\Logs;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class LogActivity
{
    public function run(
        string $action,
        ?string $description = null,
        ?User $user = null,
        ?string $modelType = null,
        ?int $modelId = null,
        array $properties = [],
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
