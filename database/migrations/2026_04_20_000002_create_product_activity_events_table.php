<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_activity_events');

        Schema::create('product_activity_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('product_id')->nullable()->index();
            $table->string('action', 20);
            $table->string('product_title')->nullable();
            $table->string('product_image')->nullable();
            $table->json('changed_fields')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_activity_events');
    }
};
