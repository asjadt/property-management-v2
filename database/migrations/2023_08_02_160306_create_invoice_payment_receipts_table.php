<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payment_receipts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("invoice_id");
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');

            $table->unsignedBigInteger("invoice_payment_id");
            $table->foreign('invoice_payment_id')->references('id')->on('invoice_payments')->onDelete('cascade');



            $table->string("from");
            $table->text("to");
            $table->string("subject");
            $table->string("message");
            $table->boolean("copy_to_myself");
            $table->string("shareable_link")->nullable();



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
        Schema::dropIfExists('invoice_payment_receipts');
    }
}
