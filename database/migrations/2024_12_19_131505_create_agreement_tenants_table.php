<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgreementTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agreement_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained("tenants")->onDelete('cascade'); // Assuming you have a tenants table
            $table->foreignId('tenancy_agreement_id')->constrained('tenancy_agreements')->onDelete('cascade');
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
        Schema::dropIfExists('agreement_tenants');
    }
}
