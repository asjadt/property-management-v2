<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInTenancyAgreements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenancy_agreements', function (Blueprint $table) {
            $table->string('agency_witness_name')->nullable()->change();
            $table->string('tenant_witness_name')->nullable()->change();
            $table->string('agency_witness_address')->nullable()->change();
            $table->string('tenant_witness_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenancy_agreements', function (Blueprint $table) {
            $table->string('agency_witness_name')->nullable(false)->change();
            $table->string('tenant_witness_name')->nullable(false)->change();
            $table->string('agency_witness_address')->nullable(false)->change();
            $table->string('tenant_witness_address')->nullable(false)->change();
        });
    }
}
