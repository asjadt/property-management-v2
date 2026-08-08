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
        Schema::table('maintenance_item_types', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('name');
            $table->foreignId('business_id')->nullable()->after('is_default')->constrained('businesses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_item_types', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn(['is_default', 'business_id']);
        });
    }
};
