<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_mappings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('source_table', 64);
            $table->string('legacy_id', 191);
            $table->string('target_table', 64);
            $table->ulid('target_id');
            $table->timestamps();

            $table->unique(
                ['source_table', 'legacy_id', 'target_table'],
                'legacy_mappings_source_target_unique'
            );
            $table->index(
                ['target_table', 'target_id'],
                'legacy_mappings_target_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_mappings');
    }
};
