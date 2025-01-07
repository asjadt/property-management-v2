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
            $table->foreignId('property_id')->constrained("properties")->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained("tenants")->onDelete('cascade');  // Assumes a foreign key reference to a tenants table
            $table->string('address_line_1');
            $table->string('inspected_by');
            $table->string('phone');
            $table->date('date');
            $table->date('next_inspection_date');
            $table->json('files')->nullable();
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
