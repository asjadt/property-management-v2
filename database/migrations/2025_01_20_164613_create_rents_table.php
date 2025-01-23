<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('rents', function (Blueprint $table) {
            $table->id();

            $table->foreignId("tenancy_agreement_id")
            ->constrained("tenancy_agreements")
            ->onDelete("cascade");

            $table->date("payment_date");

            $table->string("payment_status");

            $table->string("rent_taken_by");
            $table->text("remarks");

            $table->decimal("rent_amount",10,2);

            $table->decimal("paid_amount",10,2);

            $table->string("rent_reference");
            $table->string("payment_method");




            $table->integer("month");

            $table->integer("year");

            $table->unsignedBigInteger("created_by");
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('rents');
    }
}



