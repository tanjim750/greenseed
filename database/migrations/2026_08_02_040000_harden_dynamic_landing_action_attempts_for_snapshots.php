<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dynamic_landing_action_attempts')) {
            return;
        }

        Schema::table('dynamic_landing_action_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('dynamic_landing_action_attempts', 'dynamic_landing_page_version_id')) {
                $table->foreignId('dynamic_landing_page_version_id')
                    ->nullable()
                    ->after('dynamic_landing_page_component_id')
                    ->constrained(
                        table: 'dynamic_landing_page_versions',
                        indexName: 'dyn_lp_action_version_fk'
                    )
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('dynamic_landing_action_attempts', 'source_component_id')) {
                $table->unsignedBigInteger('source_component_id')
                    ->nullable()
                    ->after('dynamic_landing_page_version_id');
            }
        });

        if (Schema::hasColumn('dynamic_landing_action_attempts', 'dynamic_landing_page_component_id')) {
            DB::statement('ALTER TABLE dynamic_landing_action_attempts MODIFY dynamic_landing_page_component_id BIGINT UNSIGNED NULL');
        }

        Schema::table('dynamic_landing_action_attempts', function (Blueprint $table) {
            $table->unique(
                ['dynamic_landing_page_version_id', 'source_component_id', 'action_key', 'idempotency_key'],
                'dyn_lp_action_attempts_version_unique'
            );
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('dynamic_landing_action_attempts')) {
            return;
        }

        Schema::table('dynamic_landing_action_attempts', function (Blueprint $table) {
            $table->dropUnique('dyn_lp_action_attempts_version_unique');

            if (Schema::hasColumn('dynamic_landing_action_attempts', 'dynamic_landing_page_version_id')) {
                $table->dropForeign('dyn_lp_action_version_fk');
                $table->dropColumn('dynamic_landing_page_version_id');
            }

            if (Schema::hasColumn('dynamic_landing_action_attempts', 'source_component_id')) {
                $table->dropColumn('source_component_id');
            }
        });
    }
};
