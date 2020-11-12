<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id')->unsigned();
            $table->double('amount', 16,2);
            $table->string('uuid')->nullable();
            $table->enum('type', [
                'расход', 'приход'
            ]);
            $table->enum('status', [
                'в процессе', 'подтвержденная', 'ошибка', 'отмененная'
            ]);
            $table->integer('count')->default(1);
            $table->string('comment', 4000)->nullable();
            $table->string('file')->nullable();
            $table->json('response')->nullable();
//            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
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
        Schema::dropIfExists('transactions');
    }
}
