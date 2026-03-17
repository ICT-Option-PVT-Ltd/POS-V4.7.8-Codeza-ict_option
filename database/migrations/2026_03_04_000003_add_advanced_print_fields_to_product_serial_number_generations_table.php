<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdvancedPrintFieldsToProductSerialNumberGenerationsTable extends Migration
{
    public function up()
    {
        Schema::table('product_serial_number_generations', function (Blueprint $table) {
            $table->string('separator', 5)->nullable()->after('prefix');
            $table->decimal('paper_width_mm', 8, 2)->default(76)->after('barcode_type');
            $table->string('label_position', 10)->default('RIGHT')->after('label_height');
            $table->decimal('x_offset_mm', 8, 2)->default(0)->after('label_position');
            $table->decimal('y_offset_mm', 8, 2)->default(0)->after('x_offset_mm');
            $table->decimal('gap_mm', 8, 2)->default(2)->after('y_offset_mm');
            $table->unsignedInteger('copies')->default(1)->after('gap_mm');
            $table->decimal('barcode_height_mm', 8, 2)->default(6.5)->after('copies');
            $table->decimal('barcode_margin_mm', 8, 2)->default(0)->after('barcode_height_mm');
            $table->unsignedTinyInteger('barcode_font')->default(0)->after('barcode_margin_mm');
        });
    }

    public function down()
    {
        Schema::table('product_serial_number_generations', function (Blueprint $table) {
            $table->dropColumn([
                'separator',
                'paper_width_mm',
                'label_position',
                'x_offset_mm',
                'y_offset_mm',
                'gap_mm',
                'copies',
                'barcode_height_mm',
                'barcode_margin_mm',
                'barcode_font',
            ]);
        });
    }
}
