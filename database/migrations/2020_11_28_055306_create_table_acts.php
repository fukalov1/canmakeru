<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableActs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('act_id');
            $table->string('name')->nullable();
            $table->string('number_act')->unique();
            $table->double('lat')->default(0);
            $table->double('lng')->default(0);
            $table->string('address')->nullable();
            $table->date('date');
            $table->enum('type', ['пригодны','непригодны','испорчен']);
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
        Schema::dropIfExists('acts');
    }
}
