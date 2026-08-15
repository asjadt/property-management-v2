<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyStatusHistoriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('property_status_histories')) {
            // ALTER EXISTING TABLE TO PRESERVE DATA
            Schema::table('property_status_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('property_status_histories', 'status')) {
                    $table->string('status')->nullable()->after('property_id');
                }
                if (!Schema::hasColumn('property_status_histories', 'from_date')) {
                    $table->date('from_date')->nullable()->after('status');
                }
                if (!Schema::hasColumn('property_status_histories', 'to_date')) {
                    $table->date('to_date')->nullable()->after('from_date');
                }
                if (!Schema::hasColumn('property_status_histories', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('property_status_histories', 'changed_by')) {
                    $table->unsignedBigInteger('changed_by')->nullable();
                }
            });

            // Add foreign key constraint separately to avoid duplicate keys if running multiple times
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $tableIndexes = $sm->listTableForeignKeys('property_status_histories');
            $hasChangedByFk = false;
            foreach ($tableIndexes as $fk) {
                if (in_array('changed_by', $fk->getLocalColumns())) {
                    $hasChangedByFk = true;
                    break;
                }
            }
            if (!$hasChangedByFk) {
                Schema::table('property_status_histories', function (Blueprint $table) {
                    $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
                });
            }

            // Copy data over from old columns
            \Illuminate\Support\Facades\DB::statement("UPDATE property_status_histories SET from_date = DATE(start_date) WHERE start_date IS NOT NULL AND from_date IS NULL");
            \Illuminate\Support\Facades\DB::statement("UPDATE property_status_histories SET to_date = DATE(end_date) WHERE end_date IS NOT NULL AND to_date IS NULL");

        } else {
            // CREATE FRESH TABLE IF IT DOESN'T EXIST
            Schema::create('property_status_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id');
                $table->string('status')->nullable();
                $table->date('from_date')->nullable();
                $table->date('to_date')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->timestamps();

                $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
                $table->index(['property_id', 'status']);
                $table->index(['from_date', 'to_date']);
            });
        }
    }

    public function down()
    {
        // Leaving this empty or reversing changes if necessary. 
        // We shouldn't drop the table on down() if we altered it.
    }
}
