<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParamsActs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acts', function (Blueprint $table) {
            $table->string('temperature')->default('24');
            $table->string('hymidity')->default('32');
            $table->string('cold_water')->default('10');
            $table->string('hot_water')->default('60');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acts', function (Blueprint $table) {
            //
        });
    }
}
