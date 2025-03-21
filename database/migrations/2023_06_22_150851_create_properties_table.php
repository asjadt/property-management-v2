<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('generated_id')->nullable();


            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();



            $table->string("address")->nullable();
            $table->string("country");
            $table->string("city");
            $table->string("postcode");
            $table->string("town")->nullable();

            $table->double("lat")->nullable();
            $table->double("long")->nullable();


            $table->string('type');

            $table->string('reference_no');






            $table->unsignedBigInteger('landlord_id')->nullable();
            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');





            $table->string('is_active')->default(false);
            $table->unsignedBigInteger("created_by");
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->date('date_of_instruction')->nullable();
            $table->string('howDetached')->nullable();  // Title is displayed
            $table->string('propertyFloor')->nullable();  // Title is displayed

            $table->enum('category', ['let_property', 'manage_property', 'sale_property'])->nullable();

            $table->string('no_of_beds');
            $table->string('no_of_baths');
            $table->boolean('is_garden');

            $table->double('price')->nullable();



            $table->string('purpose')->nullable();
            $table->string('property_door_no')->nullable();
            $table->string('property_road')->nullable();
            $table->string('county')->nullable();

            $table->boolean('is_dss')->nullable()->default(0);

            $table->softDeletes();
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
        Schema::dropIfExists('properties');
    }
}
