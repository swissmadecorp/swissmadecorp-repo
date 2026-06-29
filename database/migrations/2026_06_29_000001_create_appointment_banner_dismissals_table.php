<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_banner_dismissals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('booking_id');
            $table->timestamp('booking_updated_at')->nullable();
            $table->timestamp('dismissed_at');
            $table->timestamps();

            $table->unique(['user_id', 'booking_id']);
            $table->index(['user_id', 'dismissed_at']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_banner_dismissals');
    }
};
