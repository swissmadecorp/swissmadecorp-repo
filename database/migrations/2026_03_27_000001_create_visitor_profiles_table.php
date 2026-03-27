<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('visitor_profiles')) {
            return;
        }

        Schema::create('visitor_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_key')->unique();
            $table->string('display_name', 120)->nullable();
            $table->string('email')->nullable();
            $table->string('last_known_ip', 45)->nullable();
            $table->string('country', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->unsignedInteger('visit_count')->default(0);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('last_identified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_profiles');
    }
};
