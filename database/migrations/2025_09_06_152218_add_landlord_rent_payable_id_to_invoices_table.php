<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLandlordRentPayableIdToInvoicesTable extends Migration
{
     /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('landlord_rent_payable_id')->nullable()->after('id');

            // If you want to add a foreign key constraint:
            $table->foreign('landlord_rent_payable_id')
                  ->references('id')
                  ->on('landlord_rent_payables')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key if added
            // $table->dropForeign(['landlord_rent_payable_id']);

            $table->dropColumn('landlord_rent_payable_id');
        });
    }
}
