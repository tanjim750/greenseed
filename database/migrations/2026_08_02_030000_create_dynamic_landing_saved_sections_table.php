<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dynamic_landing_saved_sections')) {
            Schema::create('dynamic_landing_saved_sections', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->nullable();
                $table->json('components');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['category', 'name'], 'dynamic_lp_saved_sections_category_name_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_landing_saved_sections');
    }
};
