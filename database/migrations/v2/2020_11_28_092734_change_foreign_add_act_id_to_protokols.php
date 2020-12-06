<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeForeignAddActIdToProtokols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //alter table protokols  change protokol_dt protokol_dt timestamp null;
//        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//        Schema::dropIfExists('customer_id');
//        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('protokols', function (Blueprint $table) {

//            $table->dateTime('protokol_dt')->default(null)->nullable()->change();
            $table->bigInteger('act_id')->unsigned();
            $table->foreign('act_id')->references('id')->on('acts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('act_id');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('protokols', function (Blueprint $table) {
            $table->dropColumn('act_id');
        });
    }
}
