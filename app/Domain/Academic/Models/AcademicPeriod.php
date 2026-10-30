<?php

declare(strict_types=1);

namespace App\Domain\Academic\Models;

use App\Domain\Academic\Enums\PeriodStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPeriod extends Model
{
    protected $fillable = ['code', 'name', 'year', 'term', 'status', 'closed_at'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'term' => 'integer',
            'status' => PeriodStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function dates(): HasMany
    {
        return $this->hasMany(PeriodDate::class);
    }
}
