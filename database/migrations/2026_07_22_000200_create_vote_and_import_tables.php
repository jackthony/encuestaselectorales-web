<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interactive_votes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('survey_round_id');
            $table->ulid('survey_option_id');
            $table->ulid('validated_territory_id');
            $table->string('vote_type', 20)->default('candidate');
            $table->decimal('gps_latitude', 10, 8);
            $table->decimal('gps_longitude', 11, 8);
            $table->decimal('gps_accuracy_meters', 10, 2);
            $table->string('geo_validation_method', 50);
            $table->string('geo_validation_result', 30);
            $table->unsignedInteger('interaction_time_ms');
            $table->binary('ip_ciphertext');
            $table->binary('ip_nonce');
            $table->binary('ip_auth_tag');
            $table->unsignedSmallInteger('ip_encryption_key_version');
            $table->char('ip_hmac', 64);
            $table->unsignedSmallInteger('ip_hmac_key_version');
            $table->char('device_token_hmac', 64)->nullable();
            $table->unsignedSmallInteger('device_hmac_key_version')->nullable();
            $table->char('browser_fingerprint_hmac', 64)->nullable();
            $table->unsignedSmallInteger('browser_hmac_key_version')->nullable();
            $table->string('status', 20)->default('accepted');
            $table->timestamps();

            $table->unique(
                ['survey_round_id', 'ip_hmac'],
                'interactive_votes_round_ip_unique'
            );
            $table->unique(
                ['survey_round_id', 'device_token_hmac'],
                'interactive_votes_round_device_unique'
            );
            $table->index(
                ['survey_round_id', 'browser_fingerprint_hmac', 'created_at'],
                'interactive_votes_browser_signal_index'
            );
            $table->index(
                ['survey_option_id', 'status', 'created_at'],
                'interactive_votes_option_status_index'
            );
            $table->index(
                ['validated_territory_id', 'created_at'],
                'interactive_votes_territory_index'
            );
            $table->foreign('survey_round_id')
                ->references('id')
                ->on('survey_rounds');
            $table->foreign('survey_option_id')
                ->references('id')
                ->on('survey_options');
            $table->foreign('validated_territory_id')
                ->references('id')
                ->on('electoral_territories');
        });

        Schema::create('import_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('territory_id');
            $table->string('source_system', 50);
            $table->string('source_identity', 180);
            $table->char('source_checksum', 64);
            $table->string('mapping_version', 40);
            $table->string('election_cycle', 40);
            $table->string('office_type', 80);
            $table->string('source_file', 255);
            $table->unsignedBigInteger('source_size_bytes')->nullable();
            $table->string('operator_identifier', 120)->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('created_rows')->default(0);
            $table->unsignedInteger('updated_rows')->default(0);
            $table->unsignedInteger('unchanged_rows')->default(0);
            $table->unsignedInteger('rejected_rows')->default(0);
            $table->text('failure_summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['source_system', 'source_checksum', 'territory_id', 'office_type', 'election_cycle'],
                'import_runs_idempotency_unique'
            );
            $table->index(['status', 'created_at'], 'import_runs_status_index');
            $table->index(
                ['territory_id', 'office_type', 'election_cycle'],
                'import_runs_scope_index'
            );
            $table->foreign('territory_id')->references('id')->on('electoral_territories');
        });

        Schema::create('import_rows', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('import_run_id');
            $table->unsignedInteger('source_row_number');
            $table->string('source_key', 180)->nullable();
            $table->string('status', 20);
            $table->string('action', 20)->nullable();
            $table->string('entity_type', 50)->nullable();
            $table->ulid('entity_id')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->json('diagnostics')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(
                ['import_run_id', 'source_row_number'],
                'import_rows_run_position_unique'
            );
            $table->index(['import_run_id', 'status'], 'import_rows_run_status_index');
            $table->index(['entity_type', 'entity_id'], 'import_rows_entity_index');
            $table->foreign('import_run_id')
                ->references('id')
                ->on('import_runs')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
        Schema::dropIfExists('import_runs');
        Schema::dropIfExists('interactive_votes');
    }
};
