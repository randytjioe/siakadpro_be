<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('effective_from_year'); // berlaku untuk angkatan >= tahun ini
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained()->restrictOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->unsignedTinyInteger('credits');
            $table->unsignedTinyInteger('semester');
            $table->string('kind', 20)->default('wajib');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['curriculum_id', 'semester']);
        });

        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prerequisite_id')->constrained('courses')->restrictOnDelete();
            $table->string('min_grade', 3)->default('D');
            $table->boolean('allow_concurrent')->default(false);
            $table->timestamps();
            $table->unique(['course_id', 'prerequisite_id']);
        });

        Schema::create('cpls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('code', 20);
            $table->text('description');
            $table->timestamps();
            $table->unique(['study_program_id', 'code']);
        });

        Schema::create('cpmks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->string('code', 20);
            $table->text('description');
            $table->timestamps();
            $table->unique(['course_id', 'code']);
        });

        // Pemetaan CPMK -> CPL dengan bobot (persen).
        Schema::create('cpmk_cpl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpmk_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('weight')->default(100);
            $table->timestamps();
            $table->unique(['cpmk_id', 'cpl_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpmk_cpl');
        Schema::dropIfExists('cpmks');
        Schema::dropIfExists('cpls');
        Schema::dropIfExists('course_prerequisites');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('curricula');
    }
};
