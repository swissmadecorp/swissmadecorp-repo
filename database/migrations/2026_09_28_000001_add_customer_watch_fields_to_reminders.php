<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 80)->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('watch_model')->nullable();
            $table->string('watch_reference', 100)->nullable();
            $table->string('case_size', 40)->nullable();
            $table->string('dial_color', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('matched_product_id')->nullable()->index();
            $table->timestamp('matched_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['matched_product_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['customer_name', 'customer_email', 'customer_phone', 'category_id',
                'watch_model', 'watch_reference', 'case_size', 'dial_color', 'notes',
                'matched_product_id', 'matched_at', 'contacted_at']);
        });
    }
};
