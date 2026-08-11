<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable user_id FK to landlords.
     * Nullable because existing rows have no User yet — backfilled separately
     * by the `landlord:migrate-users` Artisan command.
     *
     * After the backfill is verified, the column can be made NOT NULL
     * via a follow-up migration.
     */
    public function up(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            // ADD NULLABLE user_id FK — existing landlords have no user yet
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // INDEX for fast "find landlord by logged-in user" lookups
            $table->index('user_id', 'landlords_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('landlords_user_id_index');
            $table->dropColumn('user_id');
        });
    }
};
