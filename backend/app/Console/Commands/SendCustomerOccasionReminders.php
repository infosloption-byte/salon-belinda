<?php

namespace App\Console\Commands;

use App\Mail\CustomerOccasionReminder;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — birthday/anniversary
 * reminders, the biggest sub-item of the tags/points/reminders bundle.
 * Scheduled daily (see routes/console.php). Idempotent per calendar year
 * via Customer::scopeBirthdayToday()/scopeAnniversaryToday(), which exclude
 * anyone whose last_*_reminder_sent_at is already this year — so running
 * this command twice in one day (or the scheduler catching up after
 * downtime) never double-sends.
 */
class SendCustomerOccasionReminders extends Command
{
    protected $signature = 'customers:send-occasion-reminders';

    protected $description = 'Email customers a birthday or anniversary reminder, once per year each.';

    public function handle(): int
    {
        $birthdaysSent = $this->sendFor('birthday', Customer::query()->birthdayToday()->get());
        $anniversariesSent = $this->sendFor('anniversary', Customer::query()->anniversaryToday()->get());

        $this->info("Sent {$birthdaysSent} birthday and {$anniversariesSent} anniversary reminder(s).");

        return self::SUCCESS;
    }

    /**
     * @param  'birthday'|'anniversary'  $occasion
     * @param  \Illuminate\Support\Collection<int, Customer>  $customers
     */
    private function sendFor(string $occasion, $customers): int
    {
        $sent = 0;
        $column = $occasion === 'birthday' ? 'last_birthday_reminder_sent_at' : 'last_anniversary_reminder_sent_at';

        foreach ($customers as $customer) {
            try {
                Mail::to($customer->email)->send(new CustomerOccasionReminder($customer, $occasion));
                $customer->forceFill([$column => now()->toDateString()])->save();
                $sent++;
            } catch (\Throwable $e) {
                // One bad address shouldn't stop the rest of the run —
                // same "never let mail break the operation" stance as
                // Api\AppointmentController::store.
                Log::error("Failed to send {$occasion} reminder", ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            }
        }

        return $sent;
    }
}
