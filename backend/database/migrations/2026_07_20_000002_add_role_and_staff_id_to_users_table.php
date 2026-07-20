<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Existing rows default to 'admin' — no access change for current logins.
            $table->enum('role', ['admin', 'staff'])->default('admin')->after('is_admin');
            $table->foreignId('staff_id')->nullable()->after('role')->constrained('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_id');
            $table->dropColumn('role');
        });
    }
};
