<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ParkingLot;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessParkingClosures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parking:process-closures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled parking lot closures and lifecycle transitions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting parking closure processing...');

        // Part A: Transition active lots to closing_soon
        $closingSoonLots = ParkingLot::where('status', 'active')
            ->whereNotNull('scheduled_removal_date')
            ->where('scheduled_removal_date', '<=', Carbon::today('Asia/Kolkata')->addDays(7))
            ->get();

        foreach ($closingSoonLots as $lot) {
            $lot->status = 'closing_soon';
            $lot->save();

            $this->info("Parking lot [{$lot->_id}] '{$lot->name}' transitioned to closing_soon.");
            Log::info("Parking lot [{$lot->_id}] '{$lot->name}' transitioned from active to closing_soon.", [
                'parking_lot_id' => $lot->_id,
                'scheduled_removal_date' => $lot->scheduled_removal_date,
            ]);
        }

        $this->info("Processed {$closingSoonLots->count()} lot(s) for closing_soon transition.");

        // Part B: Transition scheduled_for_removal/closing_soon lots to inactive
        $deactivationLots = ParkingLot::whereIn('status', ['scheduled_for_removal', 'closing_soon'])
            ->where('scheduled_removal_date', '<=', Carbon::today('Asia/Kolkata'))
            ->get();

        $deactivatedCount = 0;
        $skippedCount = 0;

        foreach ($deactivationLots as $lot) {
            $activeBookings = Booking::where('parking_lot_id', $lot->_id)
                ->whereIn('status', ['confirmed', 'pending', 'upcoming', 'active', 'attended'])
                ->where('date', '>=', Carbon::today('Asia/Kolkata')->format('Y-m-d'))
                ->count();

            if ($activeBookings > 0) {
                $this->warn("Parking lot [{$lot->_id}] '{$lot->name}' has {$activeBookings} active booking(s). Skipping deactivation.");
                Log::warning("Parking lot [{$lot->_id}] '{$lot->name}' deactivation skipped due to {$activeBookings} active booking(s).", [
                    'parking_lot_id' => $lot->_id,
                    'active_bookings' => $activeBookings,
                    'scheduled_removal_date' => $lot->scheduled_removal_date,
                ]);
                $skippedCount++;
                continue;
            }

            $lot->status = 'inactive';
            $lot->removed_at = Carbon::now('Asia/Kolkata')->toDateTimeString();
            $lot->is_accepting_bookings = false;
            $lot->save();

            $this->info("Parking lot [{$lot->_id}] '{$lot->name}' has been deactivated.");
            Log::info("Parking lot [{$lot->_id}] '{$lot->name}' transitioned to inactive.", [
                'parking_lot_id' => $lot->_id,
                'removed_at' => $lot->removed_at,
            ]);
            $deactivatedCount++;
        }

        $this->info("Deactivation complete: {$deactivatedCount} deactivated, {$skippedCount} skipped.");
        $this->info('Parking closure processing finished.');
    }
}
