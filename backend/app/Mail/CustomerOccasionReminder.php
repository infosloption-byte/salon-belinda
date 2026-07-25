<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — birthday/anniversary
 * reminders, sent to the customer (not the salon) by
 * Console\Commands\SendCustomerOccasionReminders. $occasion picks the
 * subject/greeting; the view is shared since the two only differ in a
 * couple of lines of copy.
 */
class CustomerOccasionReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  'birthday'|'anniversary'  $occasion
     */
    public function __construct(public Customer $customer, public string $occasion)
    {
    }

    public function build(): self
    {
        $subject = $this->occasion === 'anniversary'
            ? 'Happy anniversary from '.config('app.name').'! 🎉'
            : 'Happy birthday from '.config('app.name').'! 🎂';

        return $this->subject($subject)
            ->view('emails.customers.occasion-reminder');
    }
}
