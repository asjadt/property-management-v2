<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyAgreementLandlordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_agreement_landlords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("property_agreement_id");
            $table->foreign('property_agreement_id')->references('id')->on('property_agreements')->onDelete('cascade');
            $table->unsignedBigInteger("landlord_id");
            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');
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
        Schema::dropIfExists('property_agreement_landlords');
    }
}
