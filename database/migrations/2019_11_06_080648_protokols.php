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
            $table->id();
            $table->unsignedBigInteger('act_id');
            $table->unsignedBigInteger('customer_id');
            $table->bigInteger('protokol_num');
            $table->integer('pin');
            $table->string('protokol_photo', 400);
            $table->string('protokol_photo1', 400);
            $table->string('meter_photo', 400);
            $table->double('lat');
            $table->double('lng');
            $table->dateTime('protokol_dt')->nullable();
            $table->string('siType')->nullable();
            $table->string('waterType')->nullable();
            $table->string('regNumber')->nullable();
            $table->string('serialNumber')->nullable();
            $table->string('checkInterval')->nullable();
            $table->string('checkMethod')->nullable();
            $table->integer('exported')->default(0);
            $table->timestamp('nextTest')->nullable()->default(null);
            $table->foreign('act_id')->references('id')->on('acts')->onDelete('cascade');
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
