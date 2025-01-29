<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccreditationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('accreditations', function (Blueprint $table) {
            $table->id();



            $table->string("name");

            $table->string("description")->nullable();

            $table->date("accreditation_start_date");

            $table->date("accreditation_expiry_date");

            $table->string("logo")->nullable();

        

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
        Schema::dropIfExists('accreditations');
    }
}



