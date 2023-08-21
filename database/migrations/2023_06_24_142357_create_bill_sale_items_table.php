<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_sale_items', function (Blueprint $table) {
            $table->id();

            $table->string("item");
            $table->string("description")->nullable();
            $table->double("amount")->default(0);
            $table->unsignedBigInteger("sale_id")->nullable();
            $table->foreign('sale_id')->references('id')->on('sale_items')->onDelete('set null');

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
        Schema::dropIfExists('bill_sale_items');
    }
}
