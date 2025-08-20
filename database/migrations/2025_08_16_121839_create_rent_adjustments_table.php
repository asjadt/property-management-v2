<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rent_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('landlord_rent_payable_id')->nullable();
            $table->foreign('landlord_rent_payable_id')->references('id')->on('landlord_rent_payables')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('description')->nullable();

            $table->unsignedBigInteger('expense_id')->nullable()->after('id');
            $table->foreign('expense_id')
                  ->references('id')
                  ->on('expenses')
                  ->onDelete('set null');

          $table->unsignedBigInteger("repair_id")->nullable();
            $table->foreign('repair_id')->references('id')->on('repairs')->onDelete('set null');
            

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
        Schema::dropIfExists('rent_adjustments');
    }
}
