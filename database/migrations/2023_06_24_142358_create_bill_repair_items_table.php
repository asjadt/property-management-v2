<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillRepairItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_repair_items', function (Blueprint $table) {
            $table->id();


            $table->string("item");
            $table->string("description")->nullable();
            $table->double("amount")->default(0);
            $table->unsignedBigInteger("repair_id")->nullable();
            $table->foreign('repair_id')->references('id')->on('repairs')->onDelete('set null');

            $table->unsignedBigInteger("bill_id");
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');


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
        Schema::dropIfExists('bill_repair_items');
    }
}
