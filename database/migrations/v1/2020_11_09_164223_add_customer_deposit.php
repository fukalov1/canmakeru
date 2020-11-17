<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerDeposit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->double('amount')->default(0);
            $table->double('limit')->default(0);
            $table->double('frozen_limit')->default(0);
            $table->boolean('check_online')->default(false);
            $table->enum('type', [
                'ИП','Самозанятый','Физ.лицо'
            ]);
              $table->integer('blank_price')->default(120);
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
            $table->dropColumn('amount');
            $table->dropColumn('limit');
            $table->dropColumn('frozen_limit');
            $table->dropColumn('check_online');
            $table->dropColumn('type');
            $table->dropColumn('blank_price');
        });
    }
}
