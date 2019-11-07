<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Protokols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protokols', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('protokol_num');
            $table->integer('pin');
            $table->string('protokol_photo', 400);
            $table->string('protokol_photo1', 400);
            $table->string('meter_photo', 400);
            $table->bigInteger('customer_id')->unsigned();
            $table->double('lat');
            $table->double('lng');
            $table->timestamp('updated_dt');
            $table->timestamp('protokol_dt')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
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
        Schema::dropIfExists('protokols');
    }
}
