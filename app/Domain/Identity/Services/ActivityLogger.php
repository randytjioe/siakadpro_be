<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Models\ActivityLog;
use App\Domain\Identity\Models\User;
use App\Support\LogScrubber;
use App\Support\RequestContext;
use Illuminate\Database\Eloquent\Model;

/** Satu pintu menulis `activity_logs` (rules/18). Properti disaring dari rahasia/NIK. */
final class ActivityLogger
{
    public function __construct(private readonly RequestContext $context) {}

    /**
     * @param  array<string, mixed>  $properties  mis. ['before' => [...], 'after' => [...]]
     */
    public function record(string $action, ?User $actor = null, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $actor?->id,
            'active_role' => $actor?->activeRole()?->value,
            'action' => $action,
            'subject_type' => $subject?->getTable(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : LogScrubber::scrubArray($properties),
            'ip' => $this->context->ip,
        ]);
    }
}
