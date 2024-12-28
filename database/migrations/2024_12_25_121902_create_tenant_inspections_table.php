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
            $table->string('garden')->nullable();
            $table->string('entrance')->nullable();
            $table->string('exterior_paintwork')->nullable();
            $table->string('windows_outside')->nullable();
            $table->string('kitchen_floor')->nullable();
            $table->string('oven')->nullable();
            $table->string('sink')->nullable();
            $table->string('lounge')->nullable();
            $table->string('downstairs_carpet')->nullable();
            $table->string('upstairs_carpet')->nullable();
            $table->string('window_1')->nullable();
            $table->string('window_2')->nullable();
            $table->string('window_3')->nullable();
            $table->string('window_4')->nullable();
            $table->string('windows_inside')->nullable();
            $table->string('wc')->nullable();
            $table->string('shower')->nullable();
            $table->string('bath')->nullable();
            $table->string('hand_basin')->nullable();
            $table->string('smoke_detective')->nullable();
            $table->string('general_paintwork')->nullable();
            $table->string('carbon_monoxide')->nullable();
            $table->string('overall_cleanliness')->nullable();
            $table->string('comments')->nullable();
            $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_inspections');
    }
}
