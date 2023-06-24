<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string("logo")->nullable();
            $table->string("invoice_title");
            $table->string("invoice_summary")->nullable();
            $table->string("business_name");
            $table->string("business_address");
            $table->string("invoice_payment_due")->nullable()->default(0);
            $table->date("invoice_date");
            $table->string("footer_text")->nullable();

            $table->unsignedBigInteger("property_id")->nullable();
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');

            $table->unsignedBigInteger("landlord_id")->nullable();
            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');

            $table->unsignedBigInteger("tenant_id");
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

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
        Schema::dropIfExists('invoices');
    }
}
