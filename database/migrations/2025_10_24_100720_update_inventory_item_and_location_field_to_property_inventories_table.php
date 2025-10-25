<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInventoryItemAndLocationFieldToPropertyInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_inventories', function (Blueprint $table) {
            // 1️⃣ Rename old columns (make sure they exist)
            $table->renameColumn('item_name', 'inventory_item_id');
            $table->renameColumn('item_location', 'inventory_location_id');
        });

        Schema::table('property_inventories', function (Blueprint $table) {
            // 2️⃣ Change column types to match foreign keys (if needed)
            $table->unsignedBigInteger('inventory_item_id')->change();
            $table->unsignedBigInteger('inventory_location_id')->change();

            // 3️⃣ Add foreign key constraints
            $table->foreign('inventory_item_id')
                ->references('id')
                ->on('inventory_items')
                ->cascadeOnDelete();

            $table->foreign('inventory_location_id')
                ->references('id')
                ->on('inventory_locations')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('property_inventories', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['inventory_item_id']);
            $table->dropForeign(['inventory_location_id']);

            // Rename columns back
            $table->renameColumn('inventory_item_id', 'item_name');
            $table->renameColumn('inventory_location_id', 'item_location');
        });
    }
}
