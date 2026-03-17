<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransferSerialNumbersTable extends Migration
{
    public function up()
    {
        Schema::create('stock_transfer_serial_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('stock_transfer_id');
            $table->unsignedInteger('sell_line_id')->nullable();
            $table->unsignedBigInteger('product_serial_number_id');
            $table->timestamps();

            $table->index(['stock_transfer_id', 'sell_line_id'], 'stsn_transfer_line_idx');
            $table->unique(['stock_transfer_id', 'product_serial_number_id'], 'stsn_transfer_serial_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfer_serial_numbers');
    }
}
