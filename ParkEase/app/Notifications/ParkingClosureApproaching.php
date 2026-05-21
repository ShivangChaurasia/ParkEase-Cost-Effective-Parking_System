<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ParkingLot;

/**
 * Notification sent to the HOST when a parking lot closure is approaching (within 3 days).
 */
class ParkingClosureApproaching extends Notification
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
            'type' => 'closure_approaching',
            'parking_lot_id' => $this->parkingLot->_id,
            'parking_lot_name' => $this->parkingLot->name,
            'scheduled_removal_date' => $this->parkingLot->scheduled_removal_date,
            'message' => 'Your parking "' . $this->parkingLot->name . '" is closing soon on ' . $this->parkingLot->scheduled_removal_date . '.',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $closureDate = $this->parkingLot->scheduled_removal_date;

        return (new MailMessage)
            ->subject('⚠️ Urgent: Parking Closure Approaching - ParkEase')
            ->greeting('Hello, ' . ($notifiable->name ?? 'Valued Host') . '!')
            ->line('This is an urgent reminder that your parking lot "' . $this->parkingLot->name . '" is scheduled to close very soon.')
            ->line('⏰ Closure Date: ' . $closureDate)
            ->line('The closure is less than 3 days away. Please ensure all necessary arrangements have been made.')
            ->line('Action Required:')
            ->line('• Review any remaining active bookings for this parking lot.')
            ->line('• Ensure affected users have been informed.')
            ->line('• Confirm or reschedule the closure from your dashboard if needed.')
            ->line('If you have any concerns, please reach out to our support team immediately.')
            ->salutation('Best Regards, ' . PHP_EOL . 'The ParkEase Support Team');
    }
}
