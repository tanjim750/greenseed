<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dynamic_landing_pages')) {
            Schema::create('dynamic_landing_pages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('status')->default('draft');
                $table->json('theme')->nullable();
                $table->json('seo')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dynamic_landing_page_components')) {
            Schema::create('dynamic_landing_page_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dynamic_landing_page_id')
                    ->constrained('dynamic_landing_pages')
                    ->cascadeOnDelete();
                $table->string('component_key');
                $table->string('instance_scope')->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('config');
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();

                $table->index(['dynamic_landing_page_id', 'sort_order'], 'dynamic_lp_components_page_order_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_landing_page_components');
        Schema::dropIfExists('dynamic_landing_pages');
    }
};
