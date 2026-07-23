<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_runs', function (Blueprint $table) {
            $table->dropUnique('import_runs_idempotency_unique');
            $table->unique(
                [
                    'source_system',
                    'source_checksum',
                    'mapping_version',
                    'territory_id',
                    'office_type',
                    'election_cycle',
                ],
                'import_runs_idempotency_mapping_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('import_runs', function (Blueprint $table) {
            $table->dropUnique('import_runs_idempotency_mapping_unique');
            $table->unique(
                ['source_system', 'source_checksum', 'territory_id', 'office_type', 'election_cycle'],
                'import_runs_idempotency_unique',
            );
        });
    }
};
