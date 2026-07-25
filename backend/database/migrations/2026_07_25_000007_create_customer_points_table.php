<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — loyalty points ledger.
// Same shape as stock_movements: one row per balance change with a signed
// `points` delta and the running `balance_after`, so a customer's full
// points history is a single ordered query rather than reconstructed after
// the fact. `type` covers automatic earning (from job payments — see
// Customer::earnPoints(), called from JobController::addPayment), plus
// admin-triggered redemption and manual adjustment (goodwill bonus, or a
// correction). `reference_type`/`reference_id` loosely point back at what
// caused an automatic entry (e.g. 'job' + job id) — same loose-pointer
// choice as stock_movements, no formal morph relation needed yet.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'adjustment']);
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('reason', 255)->nullable();
            $table->string('reference_type', 40)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_points');
    }
};
