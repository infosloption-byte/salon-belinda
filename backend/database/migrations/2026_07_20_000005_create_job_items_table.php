<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_salon')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('service_name');
            $table->foreignId('staff_id')->constrained('staff')->restrictOnDelete();
            $table->unsignedInteger('base_price');
            $table->enum('discount_type', ['none', 'percent', 'fixed'])->default('none');
            $table->decimal('discount_value', 8, 2)->nullable();
            $table->unsignedInteger('final_price');
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->unsignedInteger('commission_amount')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_items');
    }
};
