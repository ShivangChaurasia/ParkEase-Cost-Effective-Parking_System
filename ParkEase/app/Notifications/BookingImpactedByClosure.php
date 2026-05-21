<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Models\ParkingLot;

/**
 * Notification sent to USERS whose bookings are impacted by an upcoming parking lot closure.
 */
class BookingImpactedByClosure extends Notification
{
    use Queueable;

    public $booking;
    public $parkingLot;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, ParkingLot $parkingLot)
    {
        $this->booking = $booking;
        $this->parkingLot = $parkingLot;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'booking_impacted',
            'booking_id' => $this->booking->booking_id,
            'parking_lot_name' => $this->parkingLot->name,
            'scheduled_removal_date' => $this->parkingLot->scheduled_removal_date,
            'message' => 'The parking area "' . $this->parkingLot->name . '" where you have booking #' . $this->booking->booking_id . ' is scheduled for closure on ' . $this->parkingLot->scheduled_removal_date . '.',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $closureDate = $this->parkingLot->scheduled_removal_date;

        return (new MailMessage)
            ->subject('Booking Impact Notice - ParkEase')
            ->greeting('Hello, ' . ($notifiable->name ?? 'Valued Customer') . '!')
            ->line('We are writing to inform you that the parking area "' . $this->parkingLot->name . '" is scheduled for closure.')
            ->line('Your Affected Booking:')
            ->line('• Booking ID: #' . $this->booking->booking_id)
            ->line('• Parking Location: ' . $this->parkingLot->name)
            ->line('• Scheduled Closure Date: ' . $closureDate)
            ->line('Please review your booking and make alternative arrangements if necessary. If your booking falls on or after the closure date, it may be automatically cancelled.')
            ->line('We apologize for any inconvenience and recommend exploring other available parking options on ParkEase.')
            ->salutation('Best Regards, ' . PHP_EOL . 'The ParkEase Support Team');
    }
}
