<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdStockOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prod_stock_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_stock_in_id')->constrained('prod_stock_ins')->onDelete('cascade');
            $table->string('finished_stockout_location');
            // $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Assuming you have an 'employees' table
            $table->string('plate');
            $table->string('contact');
            $table->boolean('loading_payment_status')->default(false);
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
        Schema::dropIfExists('prod_stock_outs');
    }
}
