<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomersToFgis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('export_fgis')->default(true);
            $table->integer('hour_zone')->default(0);
            $table->string('ideal')->nullable();
            $table->string('get')->nullable();
            $table->enum('type_ideal', ['эталон','не утвержденный','СИ, как эталон']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('export_fgis');
            $table->dropColumn('hour_zone');
            $table->dropColumn('ideal');
            $table->dropColumn('get');
            $table->dropColumn('type_ideal');
        });
    }
}
