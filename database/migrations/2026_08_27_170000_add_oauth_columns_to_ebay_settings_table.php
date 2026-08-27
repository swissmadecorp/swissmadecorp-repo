<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ebay_settings', function (Blueprint $table) {
            $table->text('oauth_access_token')->nullable();
            $table->text('oauth_refresh_token')->nullable();
            $table->timestamp('oauth_access_token_expires_at')->nullable();
            $table->timestamp('oauth_refresh_token_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ebay_settings', function (Blueprint $table) {
            $table->dropColumn([
                'oauth_access_token',
                'oauth_refresh_token',
                'oauth_access_token_expires_at',
                'oauth_refresh_token_expires_at',
            ]);
        });
    }
};
