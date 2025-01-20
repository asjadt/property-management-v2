<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("about")->nullable();
            $table->string("web_page")->nullable();
            $table->string("phone")->nullable();
            $table->string("email")->unique();
            $table->text("additional_information")->nullable();
            $table->string("address_line_1");
            $table->string("address_line_2")->nullable();
            $table->string("lat")->nullable();
            $table->string("long")->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->string("postcode")->nullable();
            $table->string("currency")->nullable();
            $table->string("logo")->nullable();

            $table->string("invoice_title")->nullable();
            $table->string("footer_text")->nullable();
            $table->boolean("is_reference_manual")->default(0);
            $table->text('receipt_footer')->nullable();

            $table->string("account_name")->nullable();
            $table->string("account_number")->nullable();
            $table->string("sort_code")->nullable();

            $table->boolean("send_email_alert")->nullable()->default(1);



            $table->string("pin");

            $table->string("image")->nullable();






            $table->string('status')->default("pending");
            // $table->enum('status', ['status1', 'status2',  'status3'])->default("status1");
            $table->boolean("is_active")->default(false);


            $table->enum('type', ['other', 'property_dealer'])->default("other");


            $table->unsignedBigInteger("owner_id");
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger("created_by")->nullable(true);
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
        Schema::dropIfExists('businesses');
    }
}
