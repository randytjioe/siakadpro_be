<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cpmk extends Model
{
    protected $fillable = ['course_id', 'code', 'description'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(Cpl::class, 'cpmk_cpl')->withPivot('weight')->withTimestamps();
    }
}
