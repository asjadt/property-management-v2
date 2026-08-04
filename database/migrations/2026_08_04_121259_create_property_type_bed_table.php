<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_type_bed', function (Blueprint $table) {
            $table->foreignId('property_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->primary(['property_type_id', 'bed_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_type_bed');
    }
};
