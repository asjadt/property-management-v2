<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyAgreementsTable extends Migration
{
    public function up()
    {
        Schema::create('property_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('landlord_id');
            $table->unsignedBigInteger('property_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('payment_arrangement', ['By_Cash', 'By_Cheque', 'Bank_Transfer']);
            $table->string('cheque_payable_to');
            $table->decimal('agent_commision', 10, 2);
            $table->decimal('management_fee', 10, 2)->nullable();
            $table->decimal('inventory_charges', 10, 2)->nullable();
            $table->text('terms_conditions');
            $table->timestamps();
            $table->softDeletes(); // To keep soft deletion history

            // Define foreign keys
            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_agreements');
    }
}
