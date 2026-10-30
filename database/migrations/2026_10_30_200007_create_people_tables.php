<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('nidn', 20)->nullable()->unique();
            $table->string('nip', 25)->nullable()->unique();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('nim', 20)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('cohort'); // angkatan
            $table->string('status', 30)->default('aktif');
            // Data pribadi: dienkripsi (cast `encrypted`); kebutuhan enkripsi kolom = verifikasi (rules/12).
            $table->text('nik')->nullable();
            $table->text('mother_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();
            $table->index(['study_program_id', 'status']);
            $table->index(['study_program_id', 'cohort']);
        });

        Schema::create('advisor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('lecturer_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'ends_on']);
            $table->index(['lecturer_id', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advisor_assignments');
        Schema::dropIfExists('students');
        Schema::dropIfExists('lecturers');
    }
};
