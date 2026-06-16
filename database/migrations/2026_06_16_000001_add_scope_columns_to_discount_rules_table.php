<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('discount_rules', 'scope_type')) {
                $table->string('scope_type')->default('product')->after('discount_code');
            }

            if (! Schema::hasColumn('discount_rules', 'brand_ids')) {
                $table->longText('brand_ids')->nullable()->after('scope_type');
            }
        });

        DB::table('discount_rules')
            ->whereNull('scope_type')
            ->update(['scope_type' => 'product']);
    }

    public function down(): void
    {
        Schema::table('discount_rules', function (Blueprint $table) {
            if (Schema::hasColumn('discount_rules', 'brand_ids')) {
                $table->dropColumn('brand_ids');
            }

            if (Schema::hasColumn('discount_rules', 'scope_type')) {
                $table->dropColumn('scope_type');
            }
        });
    }
};
