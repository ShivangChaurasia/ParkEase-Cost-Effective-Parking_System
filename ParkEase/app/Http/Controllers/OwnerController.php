<?php

namespace App\Http\Controllers;

use App\Models\ParkingLot;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    public function storeParkingLot(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'price_per_slot' => 'required|numeric|min:0',
            'layout_type' => 'required|in:grid,blueprint',
            'total_rows' => 'required_if:layout_type,grid|integer|min:1',
            'slots_per_row' => 'required_if:layout_type,grid|integer|min:1',
        ]);

        $parkingLot = ParkingLot::create(array_merge($validated, [
            'owner_id' => Auth::id(),
        ]));

        if ($validated['layout_type'] === 'grid') {
            $this->generateGridSlots($parkingLot, $validated['total_rows'], $validated['slots_per_row']);
        }

        return response()->json([
            'message' => 'Parking lot created successfully',
            'parking_lot' => $parkingLot
        ]);
    }

    private function generateGridSlots(ParkingLot $parkingLot, $rows, $cols)
    {
        // A, B, C...
        $rowLabels = range('A', 'Z');
        
        $slots = [];
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 1; $c <= $cols; $c++) {
                $rowLabel = $rowLabels[$r % 26] . ($r >= 26 ? (int)($r/26) : '');
                $slotNumber = $rowLabel . $c;

                $slots[] = [
                    'parking_lot_id' => $parkingLot->_id,
                    'slot_number' => $slotNumber,
                    'row' => $r,
                    'column' => $c,
                    'x_position' => null,
                    'y_position' => null,
                    'slot_type' => 'standard',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Slot::insert($slots);
    }
}
