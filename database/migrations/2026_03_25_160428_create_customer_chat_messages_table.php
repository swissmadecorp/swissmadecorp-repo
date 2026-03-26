<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_chat_messages')) {
            return;
        }

        Schema::create('customer_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_chat_id')->constrained('customer_chats')->cascadeOnDelete();
            $table->string('sender_type', 20)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('message');
            $table->boolean('is_auto_response')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_chat_messages');
    }
};
