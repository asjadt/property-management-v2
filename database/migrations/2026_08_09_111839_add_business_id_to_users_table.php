<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Step 1: Add business_id (nullable) to users table.
     * Step 2: Backfill logic:
     *   - Case A: User IS a business owner → businesses.owner_id = users.id
     *             Set users.business_id = businesses.id
     *   - Case B: User was CREATED BY a business owner → businesses.owner_id = users.created_by
     *             Set users.business_id = that owner's business.id
     * Superadmin users (business_id stays NULL) are intentionally excluded.
     */
    public function up(): void
    {
        // STEP 1: Add nullable business_id column
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->after('created_by');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });
    }
};
