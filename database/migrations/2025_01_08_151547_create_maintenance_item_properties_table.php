<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceItemPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_item_properties', function (Blueprint $table) {
            $table->id();

            $table
            ->foreignId('maintenance_item_type_id')
            ->constrained("maintenance_item_types")
            ->onDelete('cascade');

            $table
            ->foreignId('property_id')
            ->constrained("properties")
            ->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenance_item_properties');
    }
}
