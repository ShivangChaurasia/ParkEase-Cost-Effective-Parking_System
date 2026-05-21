<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

#[Signature('bookings:migrate-times')]
#[Description('Backfill existing bookings with booking_start_datetime and booking_end_datetime')]
class MigrateBookingTimes extends Command
{
    public function handle()
    {
        $this->info('Starting booking times migration...');

        $bookings = Booking::whereNull('booking_start_datetime')->get();
        $count = 0;

        foreach ($bookings as $booking) {
            if ($booking->date && $booking->time_slot_id) {
                $times = explode('-', $booking->time_slot_id);
                if (count($times) === 2) {
                    $startStr = trim($times[0]);
                    $endStr = trim($times[1]);
                    
                    try {
                        $start = Carbon::parse($booking->date . ' ' . $startStr, 'Asia/Kolkata');
                        $end = Carbon::parse($booking->date . ' ' . $endStr, 'Asia/Kolkata');
                        
                        // Handle overnight bookings if any (end time < start time)
                        if ($end->lt($start)) {
                            $end->addDay();
                        }
                        
                        $duration = $start->diffInMinutes($end);
                        
                        $booking->booking_start_datetime = $start;
                        $booking->booking_end_datetime = $end;
                        $booking->booking_duration_minutes = $duration;
                        $booking->save();
                        
                        $count++;
                    } catch (\Exception $e) {
                        $this->warn("Failed to parse time for booking ID {$booking->_id}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Successfully migrated {$count} bookings.");
    }
}
