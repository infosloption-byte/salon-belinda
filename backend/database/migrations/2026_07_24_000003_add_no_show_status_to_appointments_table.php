<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Raw ALTER instead of Schema::table()->enum() / changeColumn() —
     * changeColumn() needs doctrine/dbal, which isn't installed, and
     * pulling it in just for one enum tweak isn't worth the dependency.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE appointments MODIFY status ENUM('pending','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        DB::statement("UPDATE appointments SET status = 'cancelled' WHERE status = 'no_show'");
        DB::statement(
            "ALTER TABLE appointments MODIFY status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending'"
        );
    }
};

