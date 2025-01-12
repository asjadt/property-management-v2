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
            $table->date('landlord_sign_date')->nullable();
            $table->date('agency_sign_date')->nullable();

            $table->enum('type', ['let_property', 'manage_property', 'sale_property'])->nullable();

            $table->enum('payment_arrangement', ['By_Cash', 'By_Cheque', 'Bank_Transfer'])->nullable();

            $table->string('cheque_payable_to')->nullable();
            $table->decimal('agent_commission', 10, 2)->nullable();
            $table->decimal('management_fee', 10, 2)->nullable();
            $table->decimal('inventory_charges', 10, 2)->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('legal_representative')->nullable();
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->string('agency_type')->nullable();

            $table->json('files')->nullable();
            $table->json('landlord_sign_images')->nullable();
            $table->json('agency_sign_images')->nullable();

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
