<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dynamic_landing_page_versions')) {
            Schema::create('dynamic_landing_page_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dynamic_landing_page_id')
                    ->constrained(
                        table: 'dynamic_landing_pages',
                        indexName: 'dyn_lp_versions_page_fk'
                    )
                    ->cascadeOnDelete();
                $table->unsignedInteger('version_number');
                $table->json('snapshot');
                $table->string('status', 30)->default('published');
                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained(table: 'users', indexName: 'dyn_lp_versions_creator_fk')
                    ->nullOnDelete();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->unique(['dynamic_landing_page_id', 'version_number'], 'dyn_lp_versions_number_unique');
                $table->index(['dynamic_landing_page_id', 'status'], 'dyn_lp_versions_page_status_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_landing_page_versions');
    }
};
