<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_chat_messages')) {
            return;
        }

        Schema::table('customer_chat_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_chat_messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
            }

            if (! Schema::hasColumn('customer_chat_messages', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_path');
            }

            if (! Schema::hasColumn('customer_chat_messages', 'attachment_mime_type')) {
                $table->string('attachment_mime_type', 120)->nullable()->after('attachment_name');
            }

            if (! Schema::hasColumn('customer_chat_messages', 'attachment_size')) {
                $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime_type');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_chat_messages')) {
            return;
        }

        Schema::table('customer_chat_messages', function (Blueprint $table) {
            foreach (['attachment_size', 'attachment_mime_type', 'attachment_name', 'attachment_path'] as $column) {
                if (Schema::hasColumn('customer_chat_messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
