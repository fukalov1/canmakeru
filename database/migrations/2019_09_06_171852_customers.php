<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 200)->unique();
            $table->integer('partner_code')->default(0);
            $table->string('name', 1000);
            $table->string('comment', 255)->nullable();
            $table->integer('enabled')->default(1);
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('export_fgis')->default(true);
            $table->integer('hour_zone')->default(0);
            $table->string('ideal')->nullable();
            $table->string('get')->nullable();
            $table->string('ci_as_ideal')->nullable();
            $table->string('ci_as_ideal_fake')->nullable();
            $table->string('notes')->nullable();
            $table->double('amount')->default(0);
            $table->double('limit')->default(0);
            $table->double('frozen_limit')->default(0);
            $table->boolean('check_online')->default(false);
            $table->enum('type', [
                'ИП','Самозанятый','Физ.лицо'
            ]);
            $table->integer('blank_price')->default(120);
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
