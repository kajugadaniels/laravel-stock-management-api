<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionStockInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_stock_ins', function (Blueprint $table) {
            $table->id();  // This is the Stock IN ID
            $table->string('finished_product');
            $table->string('package_type');
            $table->integer('quantity');
            $table->integer('total_sacks');
            $table->integer('remaining_kg');
            $table->integer('remaining_sacks');
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
        Schema::dropIfExists('production_stock_ins');
    }
}
