<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyAppointmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('job_type');
            $table->string('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->longText('description')->nullable();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
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
        Schema::dropIfExists('property_appointments');
    }
}
