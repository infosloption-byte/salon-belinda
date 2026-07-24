<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SALON-OPS-ENHANCEMENTS.md, "Staff" — staff <-> service qualification
// mapping ("who can actually do a keratin treatment"). Plain pivot, no
// extra columns needed yet (no per-staff price override or skill level —
// that's a real future enhancement but out of scope for the initial gap).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_staff', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['service_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_staff');
    }
};
