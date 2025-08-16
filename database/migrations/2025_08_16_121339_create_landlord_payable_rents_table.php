<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandlordPayableRentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_payable_rents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('rent_id')->nullable();
            $table->foreign('rent_id')->references('id')->on('rents')->onDelete('cascade');

            $table->unsignedBigInteger('landlord_rent_payable_id')->nullable();
            $table->foreign('landlord_rent_payable_id')->references('id')->on('landlord_rent_payables')->onDelete('cascade');

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
        Schema::dropIfExists('landlord_payable_rents');
    }
}
