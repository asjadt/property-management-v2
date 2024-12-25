<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantInspectionsTable extends Migration
{
    public function up()
    {
        Schema::create('tenant_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');  // Assumes a foreign key reference to a tenants table
            $table->string('address_line_1');
            $table->string('inspected_by');
            $table->string('phone');
            $table->string('date');
            $table->string('garden');
            $table->string('entrance');
            $table->string('exterior_paintwork');
            $table->string('windows_outside');
            $table->string('kitchen_floor');
            $table->string('oven');
            $table->string('sink');
            $table->string('lounge');
            $table->string('downstairs_carpet');
            $table->string('upstairs_carpet');
            $table->string('window_1');
            $table->string('window_2');
            $table->string('window_3');
            $table->string('window_4');
            $table->string('windows_inside');
            $table->string('wc');
            $table->string('shower');
            $table->string('bath');
            $table->string('hand_basin');
            $table->string('smoke_detective');
            $table->string('general_paintwork');
            $table->string('carbon_monoxide');
            $table->string('overall_cleanliness');
            $table->string('comments');
            $table->unsignedBigInteger("created_by");
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_inspections');
    }
}
