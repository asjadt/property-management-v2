<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipt_sale_items', function (Blueprint $table) {
            $table->id();
            $table->string("item");
            $table->string("description")->nullable();
            $table->double("amount")->default(0);
            $table->unsignedBigInteger("sale_id")->nullable();
            $table->foreign('sale_id')->references('id')->on('sale_items')->onDelete('set null');

            $table->unsignedBigInteger("receipt_id");
            $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('cascade');
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
        Schema::dropIfExists('receipt_sale_items');
    }
}
