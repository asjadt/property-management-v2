<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('property_inventories', function (Blueprint $table) {
            $table->id();
            $table->string("item_name");
            $table->string("item_location");
            $table->integer("item_quantity");
            $table->string("item_condition");
            $table->longText("item_details");
            $table->foreignId("property_id")
            ->constrained("properties")
            ->onDelete("cascade");



            $table->json("files");


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
        Schema::dropIfExists('property_inventories');
    }
}



