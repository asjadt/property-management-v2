<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_status_histories')) {
            // Drop foreign key constraint on status_id if it exists
            try {
                Schema::table('property_status_histories', function (Blueprint $table) {
                    $table->dropForeign('property_status_histories_status_id_foreign');
                });
            } catch (\Throwable $e) {
                try {
                    Schema::table('property_status_histories', function (Blueprint $table) {
                        $table->dropForeign(['status_id']);
                    });
                } catch (\Throwable $e2) {
                    // Foreign key already dropped or does not exist
                }
            }

            // Drop the obsolete status_id column
            if (Schema::hasColumn('property_status_histories', 'status_id')) {
                Schema::table('property_status_histories', function (Blueprint $table) {
                    $table->dropColumn('status_id');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('property_status_histories')) {
            if (!Schema::hasColumn('property_status_histories', 'status_id')) {
                Schema::table('property_status_histories', function (Blueprint $table) {
                    $table->unsignedBigInteger('status_id')->nullable()->after('property_id');
                });
            }
        }
    }
};
