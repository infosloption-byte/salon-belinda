<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SALON-OPS-ENHANCEMENTS.md, "Inventory" (Tier 3) — inventory movement
// ledger. One row per stock change on a product: signed `quantity_change`
// (negative for sales/losses, positive for restocks) plus the running
// `balance_after`, so the full history of a product's stock is a single
// ordered query rather than something reconstructed after the fact from
// order_items and manual edits. `reference_type`/`reference_id` are a loose
// polymorphic pointer (e.g. 'order' + order id for a sale) — no formal
// morph relation needed since nothing currently queries "all movements for
// this order", only "all movements for this product".
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['sale', 'restock', 'adjustment', 'correction']);
            $table->integer('quantity_change');
            $table->integer('balance_after');
            $table->string('reason', 255)->nullable();
            $table->string('reference_type', 40)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
