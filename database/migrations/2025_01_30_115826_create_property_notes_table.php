<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('property_notes', function (Blueprint $table) {
            $table->id();



            $table->string("title")->nullable();





            $table->string("description")->nullable();





            $table->foreignId("property_id")

            ->constrained("properties")
            ->onDelete("cascade");


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
        Schema::dropIfExists('property_notes');
    }
}



