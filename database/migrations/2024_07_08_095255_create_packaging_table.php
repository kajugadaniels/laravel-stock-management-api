<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packaging_table', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_product_id')->constrained('finished_products')->onDelete('cascade');
            $table->foreignId('stock_out_id')->constrained('stock_outs')->onDelete('cascade');
            $table->integer('n_packages');
            $table->decimal('qty_in_kgs', 8, 2);
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
        Schema::dropIfExists('packaging_table');
    }
}
