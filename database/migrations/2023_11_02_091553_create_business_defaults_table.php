<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessDefaultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_defaults', function (Blueprint $table) {
            $table->id();
            $table->string("entity_type");
            $table->unsignedBigInteger("entity_id");
            $table->unsignedBigInteger("business_owner_id");
            $table->foreign('business_owner_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('business_defaults');
    }
}
