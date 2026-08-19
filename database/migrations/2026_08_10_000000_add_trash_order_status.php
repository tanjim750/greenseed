<?php

use App\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_statuses')) {
            return;
        }

        $status = collect(OrderStatus::defaults())->firstWhere('slug', 'trash');

        DB::table('order_statuses')->updateOrInsert(
            ['slug' => 'trash'],
            array_merge($status, [
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );

        OrderStatus::clearStatusCache();
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_statuses')) {
            return;
        }

        DB::table('order_statuses')
            ->where('slug', 'trash')
            ->where('is_default', true)
            ->delete();

        OrderStatus::clearStatusCache();
    }
};
