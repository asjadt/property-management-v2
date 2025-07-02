<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHolderEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('holder_entities', function (Blueprint $table) {
            $table->id();

            $table->string("name");
            $table->string("description")->nullable();

            $table->boolean("is_active")->default(true);

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
        Schema::dropIfExists('holder_entities');
    }
}



