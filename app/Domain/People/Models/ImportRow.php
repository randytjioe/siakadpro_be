<?php

declare(strict_types=1);

namespace App\Domain\People\Models;

use App\Domain\People\Enums\ImportRowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    protected $fillable = ['import_batch_id', 'row_no', 'key', 'data', 'status', 'errors', 'result'];

    protected function casts(): array
    {
        return [
            'row_no' => 'integer',
            'data' => 'encrypted:array', // memuat NIK
            'status' => ImportRowStatus::class,
            'errors' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
