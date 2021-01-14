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
            $table->string('temperature')->default('24,2');
            $table->string('hymidity')->default('36');
            $table->string('cold_water')->default('7,9');
            $table->string('hot_water')->default('62,7');
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
            $table->dropColumn('temperature');
            $table->dropColumn('hymidity');
            $table->dropColumn('cold_water');
            $table->dropColumn('hot_water');
        });
    }
}
