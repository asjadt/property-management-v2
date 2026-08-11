<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_items', 'is_default')) {
                $table->boolean('is_default')->default(0)->after('created_by');
            }
            if (!Schema::hasColumn('bill_items', 'business_id')) {
                $table->foreignId('business_id')->nullable()->after('is_default')->constrained('businesses')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            if (Schema::hasColumn('bill_items', 'business_id')) {
                $table->dropForeign(['business_id']);
                $table->dropColumn('business_id');
            }
            if (Schema::hasColumn('bill_items', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
