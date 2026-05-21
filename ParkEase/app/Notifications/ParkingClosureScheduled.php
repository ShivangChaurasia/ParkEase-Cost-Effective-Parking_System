<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ParkingLot;

/**
 * Notification sent to the HOST when they schedule a parking lot closure.
 */
class ParkingClosureScheduled extends Notification
{
    use Queueable;

    public $parkingLot;

    /**
     * Create a new notification instance.
     */
    public function __construct(ParkingLot $parkingLot)
    {
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
            'type' => 'closure_scheduled',
            'parking_lot_id' => $this->parkingLot->_id,
            'parking_lot_name' => $this->parkingLot->name,
            'scheduled_removal_date' => $this->parkingLot->scheduled_removal_date,
            'message' => 'Your parking "' . $this->parkingLot->name . '" has been scheduled for closure on ' . $this->parkingLot->scheduled_removal_date . '.',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $closureDate = $this->parkingLot->scheduled_removal_date;
        $reason = $this->parkingLot->closure_reason ?? null;

        $mail = (new MailMessage)
            ->subject('Parking Closure Scheduled - ParkEase')
            ->greeting('Hello, ' . ($notifiable->name ?? 'Valued Host') . '!')
            ->line('Your parking lot "' . $this->parkingLot->name . '" has been scheduled for closure.')
            ->line('Closure Details:')
            ->line('• Parking Name: ' . $this->parkingLot->name)
            ->line('• Scheduled Closure Date: ' . $closureDate);

        if ($reason) {
            $mail->line('• Reason: ' . $reason);
        }

        $mail->line('All active bookings near the closure date will be notified automatically.')
            ->line('If you wish to modify or cancel this scheduled closure, please visit your dashboard.')
            ->salutation('Best Regards, ' . PHP_EOL . 'The ParkEase Support Team');

        return $mail;
    }
}
