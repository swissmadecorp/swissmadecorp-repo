<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_chats')) {
            return;
        }

        Schema::create('customer_chats', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $table->string('status', 30)->default('waiting')->index();
            $table->string('visitor_name', 120)->nullable();
            $table->string('visitor_email')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('last_customer_message_at')->nullable();
            $table->timestamp('last_staff_message_at')->nullable();
            $table->timestamp('customer_last_seen_at')->nullable();
            $table->timestamp('staff_last_seen_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_chats');
    }
};
