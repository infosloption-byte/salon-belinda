<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — tags, points, and
// birthday/anniversary reminders, in that sub-order. This migration covers
// the first (tags — a plain JSON column against a fixed set validated in
// CustomerController, not a new table; there's no need to query "all VIP
// customers" via a join yet) and lays the columns the other two need:
// date_of_birth/anniversary_date (source data for the reminder command),
// points_balance (cached running total, same "cache + ledger" shape as
// jobs_salon.total_tips — recomputing it by summing customer_points on every
// read would be wasteful), and the two last-sent columns that make the
// reminder command idempotent per calendar year without a separate log table.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('notes');
            $table->date('date_of_birth')->nullable()->after('tags');
            $table->date('anniversary_date')->nullable()->after('date_of_birth');
            $table->integer('points_balance')->default(0)->after('anniversary_date');
            $table->date('last_birthday_reminder_sent_at')->nullable()->after('points_balance');
            $table->date('last_anniversary_reminder_sent_at')->nullable()->after('last_birthday_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'tags',
                'date_of_birth',
                'anniversary_date',
                'points_balance',
                'last_birthday_reminder_sent_at',
                'last_anniversary_reminder_sent_at',
            ]);
        });
    }
};
