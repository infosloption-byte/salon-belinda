<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tip is tracked per-payment (whoever takes the payment enters it at
     * the same time) but deliberately kept OUT of `amount` — `amount` feeds
     * `SalonJob::recalculateTotals()`'s `total_paid`/`balance_due` math, and
     * a tip isn't part of the service price owed, so mixing it in would
     * make balance_due wrong. Tips are summed separately into
     * `jobs_salon.total_tips` — see the companion migration.
     */
    public function up(): void
    {
        Schema::table('job_payments', function (Blueprint $table) {
            $table->unsignedInteger('tip_amount')->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('job_payments', function (Blueprint $table) {
            $table->dropColumn('tip_amount');
        });
    }
};
