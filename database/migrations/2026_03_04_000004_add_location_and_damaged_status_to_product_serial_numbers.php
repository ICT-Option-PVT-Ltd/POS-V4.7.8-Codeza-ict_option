<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLocationAndDamagedStatusToProductSerialNumbers extends Migration
{
    public function up()
    {
        Schema::table('product_serial_numbers', function (Blueprint $table) {
            $table->unsignedInteger('location_id')->nullable()->after('business_id');
            $table->index(['business_id', 'location_id', 'product_id', 'status'], 'psn_location_lookup_idx');
        });

        DB::statement("ALTER TABLE product_serial_numbers MODIFY status ENUM('available','sold','damaged') NOT NULL DEFAULT 'available'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE product_serial_numbers MODIFY status ENUM('available','sold') NOT NULL DEFAULT 'available'");

        Schema::table('product_serial_numbers', function (Blueprint $table) {
            $table->dropIndex('psn_location_lookup_idx');
            $table->dropColumn('location_id');
        });
    }
}
