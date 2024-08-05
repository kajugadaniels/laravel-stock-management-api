<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionStockOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_stock_out', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prod_stock_in_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('location');
            $table->string('plate');
            $table->string('contact');
            $table->boolean('loading_payment_status')->default(false);
            $table->text('comment')->nullable();
            $table->integer('quantity')->unsigned(); 
            $table->timestamps();

            $table->foreign('prod_stock_in_id')->references('id')->on('product_stock_ins')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_stock_out');
    }
}
