<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'pending' => 'PENDING',
            'paid' => 'PAID',
            'processing' => 'PROCESSING',
            'completed' => 'COMPLETED',
            'cancelled' => 'CANCELLED',
        ] as $oldStatus => $newStatus) {
            DB::table('orders')
                ->where('status', $oldStatus)
                ->update(['status' => $newStatus]);
        }
    }

    public function down(): void
    {
        foreach ([
            'PENDING' => 'pending',
            'PAID' => 'paid',
            'PROCESSING' => 'processing',
            'COMPLETED' => 'completed',
            'CANCELLED' => 'cancelled',
        ] as $oldStatus => $newStatus) {
            DB::table('orders')
                ->where('status', $oldStatus)
                ->update(['status' => $newStatus]);
        }
    }
};
