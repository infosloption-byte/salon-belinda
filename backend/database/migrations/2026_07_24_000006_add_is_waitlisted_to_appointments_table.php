<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SALON-OPS-ENHANCEMENTS.md, "Appointments" — waitlist for fully-booked
// slots. Set at booking time (Api\AppointmentController::store) when every
// active/qualified/available staff member already has an overlapping
// appointment for the requested date+time — see
// AppointmentScheduler::isFullyBooked(). Cleared automatically once an
// admin successfully assigns a staff member (Api\Admin\AppointmentController
// ::assignStaff), since that means a slot was actually found for them.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_waitlisted')->default(false)->after('status');
            $table->index(['is_waitlisted', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['is_waitlisted', 'date']);
            $table->dropColumn('is_waitlisted');
        });
    }
};
