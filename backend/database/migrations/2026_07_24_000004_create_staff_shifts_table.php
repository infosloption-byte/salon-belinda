<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SALON-OPS-ENHANCEMENTS.md, "Staff" — working-hours/shift/leave table.
// One row per staff member per date: a 'work' row carries start/end hours,
// a 'leave' row (start/end left null — full day) marks them off that day.
// Deliberately one table for both rather than a separate leave table: "who's
// on today" is then a single query (shifts WHERE date = today), not a join
// across two tables with different shapes.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('type', ['work', 'leave'])->default('work');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_shifts');
    }
};
