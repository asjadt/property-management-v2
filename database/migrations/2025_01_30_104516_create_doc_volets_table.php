<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocVoletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('doc_volets', function (Blueprint $table) {
            $table->id();

            $table->string("title")->nullable();

            $table->string("description")->nullable();

            $table->string("date")->nullable();

            $table->string("note")->nullable();

            $table->json("files")->nullable();

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
        Schema::dropIfExists('doc_volets');
    }
}



