<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCustomersToFgis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('type_ideal');
            $table->string('ci_as_ideal')->nullable()->after('get');
            $table->string('ci_as_ideal_fake')->nullable()->after('ci_as_ideal');
            $table->string('notes')->nullable()->after('ci_as_ideal_fake');
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
            $table->enum('type_ideal', ['эталон','не утвержденный','СИ, как эталон']);
            $table->dropColumn('ci_as_ideal');
            $table->dropColumn('ci_as_ideal_fake');
            $table->dropColumn('notes');
        });
    }
}
