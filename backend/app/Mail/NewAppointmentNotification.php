<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAppointmentNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function build(): self
    {
        $mail = $this->subject("New Appointment — {$this->appointment->name} ({$this->appointment->date->format('d M')})")
            ->view('emails.appointments.new-for-salon');

        if ($this->appointment->email) {
            $mail->replyTo($this->appointment->email, $this->appointment->name);
        }

        return $mail;
    }
}
