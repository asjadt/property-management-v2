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
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('property_type_id')->nullable()->constrained('property_types')->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->foreignId('bath_id')->nullable()->constrained('baths')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['property_type_id']);
            $table->dropColumn('property_type_id');
            $table->dropForeign(['bed_id']);
            $table->dropColumn('bed_id');
            $table->dropForeign(['bath_id']);
            $table->dropColumn('bath_id');
        });
    }
};
