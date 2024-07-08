<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdStockInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prod_stock_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_id')->constrained('packaging_table')->onDelete('cascade');
            // $table->foreignId('employees_id')->constrained('employees')->onDelete('cascade'); 
            $table->date('date');
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('prod_stock_ins');
    }
}
