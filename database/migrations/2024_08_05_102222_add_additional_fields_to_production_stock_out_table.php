<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToProductionStockOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production_stock_out', function (Blueprint $table) {
            $table->string('batch')->nullable(); 
            $table->string('client_name'); 
            $table->string('item_name'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_stock_out', function (Blueprint $table) {
            $table->dropColumn(['batch', 'client_name', 'item_name']);
        });
    }
}
