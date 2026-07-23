<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electoral_territories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('official_code', 12);
            $table->string('scope_type', 16);
            $table->string('name', 150);
            $table->string('canonical_name', 150);
            $table->string('slug', 180);
            $table->ulid('parent_id')->nullable();
            $table->string('source_system', 50);
            $table->string('source_key', 180);
            $table->string('publication_state', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamps();

            $table->unique(['scope_type', 'official_code'], 'electoral_territories_scope_code_unique');
            $table->unique(['source_system', 'source_key'], 'electoral_territories_source_unique');
            $table->index(['scope_type', 'canonical_name'], 'electoral_territories_scope_name_index');
            $table->index(['parent_id', 'scope_type'], 'electoral_territories_parent_scope_index');
            $table->index(['publication_state', 'scope_type'], 'electoral_territories_publication_index');
            $table->foreign('parent_id')
                ->references('id')
                ->on('electoral_territories')
                ->nullOnDelete();
        });

        Schema::create('electoral_parties', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('source_system', 50);
            $table->string('source_key', 180);
            $table->string('name', 255);
            $table->string('acronym', 50)->nullable();
            $table->text('logo_url')->nullable();
            $table->string('logo_storage_disk', 50)->nullable();
            $table->string('logo_storage_path', 500)->nullable();
            $table->string('logo_source_attribution', 255)->nullable();
            $table->text('source_url')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['source_system', 'source_key'], 'electoral_parties_source_unique');
            $table->index('name', 'electoral_parties_name_index');
            $table->index('status', 'electoral_parties_status_index');
        });

        Schema::create('electoral_candidates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('source_system', 50);
            $table->string('source_key', 180);
            $table->string('full_name', 255);
            $table->text('photo_url')->nullable();
            $table->string('photo_storage_disk', 50)->nullable();
            $table->string('photo_storage_path', 500)->nullable();
            $table->string('photo_source_attribution', 255)->nullable();
            $table->text('source_url')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['source_system', 'source_key'], 'electoral_candidates_source_unique');
            $table->index('full_name', 'electoral_candidates_name_index');
            $table->index('status', 'electoral_candidates_status_index');
        });

        Schema::create('electoral_candidacies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('candidate_id');
            $table->ulid('political_party_id');
            $table->ulid('territory_id');
            $table->string('office_type', 80);
            $table->string('election_cycle', 40);
            $table->string('source_system', 50);
            $table->string('source_key', 180);
            $table->unsignedInteger('ballot_order')->nullable();
            $table->string('status', 30)->default('active');
            $table->string('source_file', 255)->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamp('retrieved_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['candidate_id', 'political_party_id', 'territory_id', 'office_type', 'election_cycle'],
                'electoral_candidacies_natural_unique'
            );
            $table->unique(['source_system', 'source_key'], 'electoral_candidacies_source_unique');
            $table->index(
                ['territory_id', 'office_type', 'election_cycle', 'status'],
                'electoral_candidacies_roster_index'
            );
            $table->foreign('candidate_id')->references('id')->on('electoral_candidates');
            $table->foreign('political_party_id')->references('id')->on('electoral_parties');
            $table->foreign('territory_id')->references('id')->on('electoral_territories');
        });

        Schema::create('survey_rounds', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('territory_id');
            $table->unsignedSmallInteger('round_number');
            $table->string('election_cycle', 40);
            $table->string('survey_type', 40);
            $table->string('office_type', 80);
            $table->string('title', 255);
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->string('publication_state', 20)->default('draft');
            $table->string('readiness_state', 20)->default('blocked');
            $table->string('blocked_reason', 120)->nullable();
            $table->string('source_system', 50)->nullable();
            $table->string('source_key', 180)->nullable();
            $table->text('source_url')->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'territory_id',
                    'round_number',
                    'election_cycle',
                    'survey_type',
                    'office_type',
                ],
                'survey_rounds_natural_unique'
            );
            $table->unique(['source_system', 'source_key'], 'survey_rounds_source_unique');
            $table->index(
                ['publication_state', 'opens_at', 'closes_at'],
                'survey_rounds_active_window_index'
            );
            $table->index(
                ['territory_id', 'office_type', 'publication_state'],
                'survey_rounds_scope_office_index'
            );
            $table->foreign('territory_id')->references('id')->on('electoral_territories');
        });

        Schema::create('survey_options', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('survey_round_id');
            $table->ulid('candidacy_id');
            $table->unsignedSmallInteger('display_order');
            $table->string('status', 20)->default('eligible');
            $table->timestamps();

            $table->unique(
                ['survey_round_id', 'candidacy_id'],
                'survey_options_round_candidacy_unique'
            );
            $table->unique(
                ['survey_round_id', 'display_order'],
                'survey_options_round_order_unique'
            );
            $table->index(['survey_round_id', 'status'], 'survey_options_round_status_index');
            $table->foreign('survey_round_id')
                ->references('id')
                ->on('survey_rounds')
                ->cascadeOnDelete();
            $table->foreign('candidacy_id')->references('id')->on('electoral_candidacies');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('survey_rounds');
        Schema::dropIfExists('electoral_candidacies');
        Schema::dropIfExists('electoral_candidates');
        Schema::dropIfExists('electoral_parties');
        Schema::dropIfExists('electoral_territories');
    }
};
