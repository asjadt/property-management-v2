<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('generated_id')->nullable();
            $table->string('first_Name')->nullable();
            $table->string('last_Name')->nullable();
            $table->string('company_name')->nullable();

            $table->string('image')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string("address_line_1")->nullable();
            $table->string("address_line_2")->nullable();
            $table->string("country");
            $table->string("city")->nullable();
            $table->string("postcode");
            $table->string("lat")->nullable();
            $table->string("long")->nullable();

            $table->string('is_active')->default(false);

            $table->unsignedBigInteger("created_by");
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
        Schema::dropIfExists('clients');
    }
}
