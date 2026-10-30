<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use App\Domain\Curriculum\Enums\CourseKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['curriculum_id', 'code', 'name', 'credits', 'semester', 'kind', 'is_active'];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'semester' => 'integer',
            'kind' => CourseKind::class,
            'is_active' => 'boolean',
        ];
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function prerequisites(): HasMany
    {
        return $this->hasMany(CoursePrerequisite::class);
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(Cpmk::class);
    }
}
