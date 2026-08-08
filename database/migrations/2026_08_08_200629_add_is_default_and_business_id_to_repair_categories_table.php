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
        Schema::table('repair_categories', function (Blueprint $table) {
            $table->boolean('is_default')->default(0);
            $table->unsignedBigInteger('business_id')->nullable();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_categories', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
            $table->dropColumn('is_default');
        });
    }
};
