<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable per-product low-stock threshold. Falls back to the existing
     * hardcoded "<= 10" behaviour in ReportController::lowStock() when null,
     * so nothing breaks for products nobody's set a custom threshold on yet.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('reorder_point')->nullable()->after('stock_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('reorder_point');
        });
    }
};
