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



            $table->string('name')->nullable();
            $table->string('image')->nullable();



            $table->string("address")->nullable();
            $table->string("country");
            $table->string("city");
            $table->string("postcode");

            $table->string("lat")->nullable();
            $table->string("long")->nullable();



            $table->string('type');
            $table->string('reference_no')->unique();;






            $table->unsignedBigInteger('landlord_id')->nullable();
            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');





            $table->string('is_active')->default(false);
            $table->unsignedBigInteger("created_by");
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');


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
