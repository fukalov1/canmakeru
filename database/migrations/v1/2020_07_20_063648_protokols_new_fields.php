<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProtokolsNewFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('protokols', function (Blueprint $table) {
            $table->string('siType')->nullable();
            $table->string('waterType')->nullable();
            $table->string('regNumber')->nullable();
            $table->string('serialNumber')->nullable();
            $table->string('checkInterval')->nullable();
            $table->string('checkMethod')->nullable();
            $table->boolean('exported')->default(false);
            $table->timestamp('nextTest')->nullable()->default(null);
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
            $table->dropColumn('siType');
            $table->dropColumn('waterType');
            $table->dropColumn('regNumber');
            $table->dropColumn('serialNumber');
            $table->dropColumn('checkInterval');
            $table->dropColumn('checkMethod');
            $table->dropColumn('exported');
            $table->dropColumn('nextTest');
        });
    }
}
