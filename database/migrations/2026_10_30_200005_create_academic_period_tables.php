<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // 20261 = Ganjil 2026/2027
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('term'); // 1 ganjil, 2 genap
            $table->string('status', 30)->default('draft');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('period_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('type', 30);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
            $table->unique(['academic_period_id', 'study_program_id', 'type']);
            $table->index(['study_program_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_dates');
        Schema::dropIfExists('academic_periods');
    }
};
