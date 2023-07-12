<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();


            $table->integer("reminder_date_amount")->nullable();

            $table->boolean("send_reminder")->default(0);

            $table->enum("reminder_status",['sent', 'not_sent'])->default("not_sent")->nullable();

            $table->date("reminder_date");


            $table->unsignedBigInteger("invoice_id");
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');



            $table->softDeletes();
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
        Schema::dropIfExists('invoice_reminders');
    }
}
