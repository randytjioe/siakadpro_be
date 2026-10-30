<?php

declare(strict_types=1);

namespace App\Domain\People\Models;

use App\Domain\Identity\Models\User;
use App\Domain\People\Enums\ImportBatchStatus;
use App\Domain\People\Enums\ImportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = [
        'ulid', 'user_id', 'type', 'original_name', 'status',
        'total_rows', 'processed_rows', 'summary', 'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ImportType::class,
            'status' => ImportBatchStatus::class,
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'summary' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class);
    }
}
