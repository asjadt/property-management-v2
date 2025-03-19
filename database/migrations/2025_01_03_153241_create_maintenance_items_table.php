<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_inspection_id')->constrained()->onDelete('cascade');  // Foreign key to TenantInspection
            $table->foreignId('maintenance_item_type_id')->constrained("maintenance_item_types")->onDelete('cascade');  // Foreign key to TenantInspection
            $table->string('item');

            $table->enum('status', ['good', 'average', 'dirty', 'na','work_required','resolved']);
            $table->text('comment')->nullable();
            $table->date('next_follow_up_date')->nullable();
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
        Schema::dropIfExists('maintenance_items');
    }
}
