<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentStatusToPropertiesTable extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'current_status')) {
                $table->string('current_status')->default('available')->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'current_status')) {
                $table->dropColumn('current_status');
            }
        });
    }
}
