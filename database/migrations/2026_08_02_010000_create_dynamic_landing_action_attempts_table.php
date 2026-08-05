<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dynamic_landing_action_attempts')) {
            Schema::create('dynamic_landing_action_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dynamic_landing_page_component_id')
                    ->constrained(
                        table: 'dynamic_landing_page_components',
                        indexName: 'dyn_lp_action_component_fk'
                    )
                    ->cascadeOnDelete();
                $table->string('action_key', 100);
                $table->string('idempotency_key', 120);
                $table->string('request_hash', 64);
                $table->string('status', 30)->default('pending');
                $table->foreignId('order_id')
                    ->nullable()
                    ->constrained(table: 'orders', indexName: 'dyn_lp_action_order_fk')
                    ->nullOnDelete();
                $table->json('response')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->unique(
                    ['dynamic_landing_page_component_id', 'action_key', 'idempotency_key'],
                    'dynamic_landing_action_attempts_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_landing_action_attempts');
    }
};
