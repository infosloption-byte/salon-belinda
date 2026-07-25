<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Job-level discount, distinct from the existing per-item discount on
     * job_items. Folded into SalonJob::recalculateTotals() to adjust
     * balance_due; subtotal itself stays as the raw sum of item prices so
     * the "before discount" figure is never lost.
     */
    public function up(): void
    {
        Schema::table('jobs_salon', function (Blueprint $table) {
            $table->string('discount_type')->default('none')->after('notes');
            $table->decimal('discount_value', 8, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('jobs_salon', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
