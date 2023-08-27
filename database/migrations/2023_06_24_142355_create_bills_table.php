<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('generated_id')->nullable();
            $table->string("shareable_link")->nullable();
            $table->dateTime("create_date");

            $table->unsignedBigInteger("property_id")->nullable();
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');

            $table->unsignedBigInteger("landlord_id")->nullable();
            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('set null');


            $table->string("payment_mode");
            $table->double("payabble_amount");
            $table->double("deduction");

            $table->string("remarks")->nullable();


            
            $table->unsignedBigInteger("created_by");
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('bills');
    }
}
