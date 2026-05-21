<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ParkingLot;

/**
 * Notification sent to the HOST when a parking lot has been auto-deactivated by the scheduler.
 */
class ParkingDeactivated extends Notification
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
            'type' => 'parking_deactivated',
            'parking_lot_id' => $this->parkingLot->_id,
            'parking_lot_name' => $this->parkingLot->name,
            'message' => 'Your parking "' . $this->parkingLot->name . '" has been deactivated as scheduled.',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Parking Lot Deactivated - ParkEase')
            ->greeting('Hello, ' . ($notifiable->name ?? 'Valued Host') . '!')
            ->line('This is to confirm that your parking lot "' . $this->parkingLot->name . '" has been successfully deactivated as per the scheduled closure.')
            ->line('Deactivation Summary:')
            ->line('• Parking Name: ' . $this->parkingLot->name)
            ->line('• Status: Deactivated')
            ->line('The parking lot is no longer visible to users and will not accept new bookings.')
            ->line('If you would like to reactivate this parking lot in the future, you can do so from your host dashboard.')
            ->line('Thank you for being a valued host on ParkEase!')
            ->salutation('Best Regards, ' . PHP_EOL . 'The ParkEase Support Team');
    }
}
