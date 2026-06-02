<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyStatusHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('property_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->string('status');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['property_id', 'status']);
            $table->index(['from_date', 'to_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_status_histories');
    }
}
