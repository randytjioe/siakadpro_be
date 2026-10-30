<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** Jejak audit. Hanya ditambah; tidak dapat diubah atau dihapus lewat aplikasi (rules/18). */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'active_role', 'action', 'subject_type', 'subject_id', 'properties', 'ip'];

    protected function casts(): array
    {
        return ['properties' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Log audit tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Log audit tidak dapat dihapus.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
