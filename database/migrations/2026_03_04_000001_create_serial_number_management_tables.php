<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialNumberManagementTables extends Migration
{
    public function up()
    {
        Schema::create('product_serial_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id')->nullable();
            $table->string('serial_number')->unique();
            $table->enum('status', ['available', 'sold'])->default('available');
            $table->unsignedInteger('sold_transaction_id')->nullable();
            $table->unsignedInteger('sold_sell_line_id')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'product_id', 'variation_id', 'status'], 'psn_lookup_idx');
        });

        Schema::create('sell_line_serial_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('sell_line_id');
            $table->unsignedBigInteger('product_serial_number_id');
            $table->string('serial_number');
            $table->timestamps();

            $table->unique('product_serial_number_id', 'slsn_serial_unique');
            $table->index(['transaction_id', 'sell_line_id'], 'slsn_transaction_line_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sell_line_serial_numbers');
        Schema::dropIfExists('product_serial_numbers');
    }
}
