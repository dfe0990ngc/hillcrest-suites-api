<?php

namespace App\Notifications;

use App\Helpers\Util;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingCancelled extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $booking;
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $setting = Setting::first();
        return (new MailMessage)
            ->view('emails.booking-cancelled',['guest' => $notifiable,'booking' => $this->booking, 'setting' => $setting])
            ->bcc(Util::bccList())
            ->subject('Booking Cancelled');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
