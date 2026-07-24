<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interactive_votes', function (Blueprint $table) {
            $table->binary('device_token_ciphertext')->nullable()->after('ip_hmac_key_version');
            $table->binary('device_token_nonce')->nullable()->after('device_token_ciphertext');
            $table->binary('device_token_auth_tag')->nullable()->after('device_token_nonce');
            $table->binary('browser_fingerprint_ciphertext')->nullable()->after('device_hmac_key_version');
            $table->binary('browser_fingerprint_nonce')->nullable()->after('browser_fingerprint_ciphertext');
            $table->binary('browser_fingerprint_auth_tag')->nullable()->after('browser_fingerprint_nonce');
        });
    }

    public function down(): void
    {
        Schema::table('interactive_votes', function (Blueprint $table) {
            $table->dropColumn([
                'device_token_ciphertext',
                'device_token_nonce',
                'device_token_auth_tag',
                'browser_fingerprint_ciphertext',
                'browser_fingerprint_nonce',
                'browser_fingerprint_auth_tag',
            ]);
        });
    }
};
