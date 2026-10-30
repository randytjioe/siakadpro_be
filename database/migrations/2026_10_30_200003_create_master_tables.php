<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->restrictOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('degree', 10)->default('S1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('capacity');
            $table->string('kind', 20)->default('kelas');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Lingkup kaprodi: prodi mana yang boleh dikelola/dilihat (rules/05).
        Schema::create('study_program_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'study_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_program_scopes');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('faculties');
    }
};
