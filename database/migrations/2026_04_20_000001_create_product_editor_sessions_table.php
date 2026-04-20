<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_editor_sessions');

        Schema::create('product_editor_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('product_id')->nullable()->index();
            $table->string('mode', 20);
            $table->string('product_title')->nullable();
            $table->string('product_image')->nullable();
            $table->json('changed_fields')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_editor_sessions');
    }
};
