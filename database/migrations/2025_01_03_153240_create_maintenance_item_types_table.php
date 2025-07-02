<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceItemTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('maintenance_item_types', function (Blueprint $table) {
            $table->id();

            $table->string("name");
            $table->boolean("is_active")->default(false);
            $table->foreignId("created_by")
            ->constrained("users")
            ->onDelete("cascade");

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('maintenance_item_types');
    }
}



