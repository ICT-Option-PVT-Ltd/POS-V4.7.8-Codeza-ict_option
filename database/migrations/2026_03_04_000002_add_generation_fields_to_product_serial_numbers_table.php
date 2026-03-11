<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGenerationFieldsToProductSerialNumbersTable extends Migration
{
    public function up()
    {
        Schema::table('product_serial_numbers', function (Blueprint $table) {
            $table->unsignedBigInteger('generation_id')->nullable()->after('variation_id');
            $table->unsignedInteger('serial_order')->nullable()->after('serial_number');
            $table->index(['generation_id', 'serial_order'], 'psn_generation_order_idx');
        });

        Schema::create('product_serial_number_generations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id')->nullable();
            $table->string('prefix')->nullable();
            $table->string('middle_fix')->nullable();
            $table->string('post_fix')->nullable();
            $table->unsignedInteger('start_from')->default(1);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('number_padding')->default(4);
            $table->string('barcode_type')->default('C128');
            $table->decimal('sticker_width', 8, 3)->default(2.000);
            $table->decimal('sticker_height', 8, 3)->default(1.000);
            $table->decimal('label_width', 8, 3)->default(4.000);
            $table->decimal('label_height', 8, 3)->default(1.000);
            $table->unsignedInteger('labels_in_row')->default(2);
            $table->unsignedInteger('generated_count')->default(0);
            $table->unsignedInteger('created_by');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_serial_number_generations');
        Schema::table('product_serial_numbers', function (Blueprint $table) {
            $table->dropIndex('psn_generation_order_idx');
            $table->dropColumn(['generation_id', 'serial_order']);
        });
    }
}
