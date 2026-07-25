<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cached sum of job_payments.tip_amount, same caching pattern as the
     * existing subtotal/total_paid/balance_due — refreshed in
     * SalonJob::recalculateTotals(). Deliberately not folded into
     * total_paid/balance_due (see the job_payments migration note).
     */
    public function up(): void
    {
        Schema::table('jobs_salon', function (Blueprint $table) {
            $table->unsignedInteger('total_tips')->default(0)->after('balance_due');
        });
    }

    public function down(): void
    {
        Schema::table('jobs_salon', function (Blueprint $table) {
            $table->dropColumn('total_tips');
        });
    }
};
