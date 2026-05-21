<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->table('parking_lots', function ($collection) {
            $collection->index(['status', 'scheduled_removal_date']);
            $collection->index(['is_accepting_bookings', 'status']);
            $collection->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('parking_lots', function ($collection) {
            $collection->dropIndex(['status', 'scheduled_removal_date']);
            $collection->dropIndex(['is_accepting_bookings', 'status']);
            $collection->dropIndex('status');
        });
    }
};
