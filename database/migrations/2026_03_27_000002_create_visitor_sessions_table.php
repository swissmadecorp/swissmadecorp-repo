<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('visitor_sessions')) {
            return;
        }

        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_profile_id')->constrained('visitor_profiles')->cascadeOnDelete();
            $table->uuid('session_token')->unique();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('country', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->text('landing_url')->nullable();
            $table->string('landing_path', 2048)->nullable();
            $table->string('landing_title', 255)->nullable();
            $table->text('current_url')->nullable();
            $table->string('current_path', 2048)->nullable();
            $table->string('current_title', 255)->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('referrer_host', 255)->nullable();
            $table->unsignedInteger('page_views')->default(1);
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['ended_at', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_sessions');
    }
};
