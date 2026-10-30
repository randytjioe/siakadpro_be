<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursePrerequisite extends Model
{
    protected $fillable = ['course_id', 'prerequisite_id', 'min_grade', 'allow_concurrent'];

    protected function casts(): array
    {
        return ['allow_concurrent' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_id');
    }
}
