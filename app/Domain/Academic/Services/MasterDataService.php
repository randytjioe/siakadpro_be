<?php

declare(strict_types=1);

namespace App\Domain\Academic\Services;

use App\Domain\Academic\Models\Faculty;
use App\Domain\Academic\Models\Room;
use App\Domain\Academic\Models\StudyProgram;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\ActivityLogger;
use App\Support\Conflict;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Master fakultas/prodi/ruang. Tidak ada penghapusan: nonaktifkan dengan pemeriksaan keterpakaian (rules/04).
 */
final class MasterDataService
{
    public function __construct(private readonly ActivityLogger $audit) {}

    /** @param  class-string<Faculty|StudyProgram|Room>  $class */
    public function create(string $class, array $data, User $actor): Model
    {
        $model = $class::create($data);
        $this->audit->record($this->action($model, 'created'), $actor, $model, ['after' => $model->only(array_keys($data))]);

        return $model;
    }

    public function update(Model $model, array $data, User $actor): Model
    {
        if (array_key_exists('is_active', $data) && $data['is_active'] === false && $model->is_active) {
            $this->assertCanDeactivate($model);
        }
        $before = $model->only(array_keys($data));
        $model->update($data);
        $this->audit->record($this->action($model, 'updated'), $actor, $model, [
            'before' => $before, 'after' => $model->only(array_keys($data)),
        ]);

        return $model;
    }

    private function assertCanDeactivate(Model $model): void
    {
        if ($model instanceof Faculty && $model->studyPrograms()->where('is_active', true)->exists()) {
            throw new Conflict('Fakultas masih memiliki program studi aktif.');
        }
        if ($model instanceof StudyProgram) {
            // students dimiliki domain People; pemeriksaan keterpakaian lewat tabel agar tidak mengimpor modelnya.
            $active = DB::table('students')->where('study_program_id', $model->id)->where('status', 'aktif')->exists();
            if ($active) {
                throw new Conflict('Program studi masih memiliki mahasiswa aktif.');
            }
        }
    }

    private function action(Model $model, string $verb): string
    {
        return 'master.'.$model->getTable().'.'.$verb;
    }
}
