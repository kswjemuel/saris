<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category');
            $table->string('tx_code')->unique();
            $table->string('clientAccount')->nullable();
            $table->string('source')->nullable();
            $table->string('sourceType')->nullable();
            $table->string('direction')->nullable();
            $table->string('status')->nullable();
            $table->float('amount')->nullable();
            $table->dateTime('tx_date')->nullable();
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
