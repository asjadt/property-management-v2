<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenancyAgreementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenancy_agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreed_rent');
            $table->string('security_deposit_hold');
            $table->string('rent_payment_option');
            $table->string('tenant_contact_duration');
            $table->date('date_of_moving');
            $table->date('let_only_agreement_expired_date')->nullable();
            $table->date('tenant_contact_expired_date')->nullable();
            $table->date('rent_due_date');
            $table->string('no_of_occupants');
            $table->string('renewal_fee');
            $table->string('housing_act');
            $table->string('let_type');
            $table->text('terms_and_conditions')->nullable();
            $table->string('agency_name');
            $table->string('landlord_name');
            $table->string('agency_witness_name');
            $table->string('tenant_witness_name');
            $table->string('agency_witness_address');
            $table->string('tenant_witness_address');
            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_address')->nullable();
            $table->json('files')->nullable();

            $table->foreignId('property_id')->constrained("properties")->onDelete('cascade');
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
        Schema::dropIfExists('tenancy_agreements');
    }
}
