<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandlordRentPayablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_rent_payables', function (Blueprint $table) {
            $table->id();
            $table->string('generated_id')->nullable();
            $table->string("payment_method");
            $table->string('item_description')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);

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
        Schema::dropIfExists('landlord_rent_payables');
    }
}
