<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `duration` stays as-is (free text like "1 hr 15 min", "5-6 hrs",
     * "1 hr x 4" for multi-session courses) since it's what's shown to
     * customers on the public site. `duration_minutes` is a separate,
     * optional, machine-readable value used only for scheduling math
     * (appointment overlap checks) — nullable because not every existing
     * service has a sensible single-sitting duration (e.g. a 4-session
     * facial course), and we don't want to force a bad guess.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};
