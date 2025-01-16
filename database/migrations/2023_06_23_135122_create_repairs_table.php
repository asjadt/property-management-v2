<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('generated_id')->nullable();
            $table->unsignedBigInteger('property_id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');


            $table->unsignedBigInteger("repair_category_id");
            $table->foreign('repair_category_id')->references('id')->on('repair_categories')->onDelete('restrict');

            $table->string('item_description')->nullable();
            $table->text('receipt')->nullable();
            $table->double('price')->default(0);;
            $table->date('create_date');

            $table->string('status');



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
        Schema::dropIfExists('repairs');
    }
}
