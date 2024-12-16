<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();

            $table->string("customer_name");


            $table->string("customer_phone");


            $table->decimal("min_price",10,2);


            $table->decimal("max_price",10,2);


            $table->string("address_line_1");



            $table->decimal("latitude",10,2)->nullable();


            $table->decimal("longitude",10,2)->nullable();


            $table->decimal("radius",10,2)->nullable();


            $table->string("property_type");


            $table->string("no_of_beds");

            $table->string("no_of_baths");

            $table->string("deadline_to_move")->nullable();
            $table->string("working")->nullable();
            $table->string("job_title")->nullable();

            $table->boolean("is_dss");
            $table->boolean("is_active")->default(false);

            $table->unsignedBigInteger("created_by");
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
        Schema::dropIfExists('applicants');
    }
}



