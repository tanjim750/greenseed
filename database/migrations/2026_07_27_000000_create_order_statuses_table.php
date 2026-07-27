<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_statuses')) {
            return;
        }

        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 70)->unique();
            $table->string('status_group', 30)->default('active');
            $table->string('badge_class', 80)->default('bg-secondary');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('counts_as_active')->default(false);
            $table->boolean('counts_as_delivered')->default(false);
            $table->boolean('counts_as_cancelled')->default(false);
            $table->boolean('counts_as_return')->default(false);
            $table->boolean('counts_as_shipped')->default(false);
            $table->boolean('marks_payment_paid')->default(false);
            $table->boolean('restores_stock')->default(false);
            $table->boolean('reduces_stock')->default(false);
            $table->string('sms_key', 50)->nullable();
            $table->timestamps();
        });

        $now = now();
        $base = [
            'status_group' => 'active',
            'badge_class' => 'bg-secondary',
            'sort_order' => 999,
            'is_active' => true,
            'is_default' => true,
            'counts_as_active' => false,
            'counts_as_delivered' => false,
            'counts_as_cancelled' => false,
            'counts_as_return' => false,
            'counts_as_shipped' => false,
            'marks_payment_paid' => false,
            'restores_stock' => false,
            'reduces_stock' => false,
            'sms_key' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $statuses = array_map(fn ($row) => array_merge($base, $row), \App\Models\OrderStatus::defaults());
        DB::table('order_statuses')->insert($statuses);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
