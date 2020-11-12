<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProtokolsExported extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('protokols', function (Blueprint $table) {
           $table->integer('exported')->default(0)->after('checkMethod');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('protokols', function (Blueprint $table) {
           $table->dropColumn('exported');
        });
    }
}
