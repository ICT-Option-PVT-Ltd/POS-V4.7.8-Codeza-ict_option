<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductSerialNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_serial_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->index();
            $table->integer('product_id')->index();
            $table->integer('variation_id')->nullable()->index();
            $table->string('serial_number');
            $table->enum('status', ['available', 'sold'])->default('available')->index();
            $table->integer('sold_transaction_id')->nullable()->index();
            $table->integer('sold_sell_line_id')->nullable()->index();
            $table->dateTime('sold_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'serial_number'], 'business_serial_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_serial_numbers');
    }
}

