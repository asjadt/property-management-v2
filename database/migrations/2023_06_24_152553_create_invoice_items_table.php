<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("description")->nullable();

            $table->integer("quantity")->default(0);
            $table->double("price")->default(0);
            $table->double("tax")->default(0);
            $table->double("amount")->default(0);

            $table->unsignedBigInteger("invoice_id");
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');

            $table->unsignedBigInteger("repair_id")->nullable();
            $table->foreign('repair_id')->references('id')->on('repairs')->onDelete('set null');

            $table->unsignedBigInteger("sale_id")->nullable();
            $table->foreign('sale_id')->references('id')->on('sale_items')->onDelete('set null');

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
        Schema::dropIfExists('invoice_items');
    }
}
