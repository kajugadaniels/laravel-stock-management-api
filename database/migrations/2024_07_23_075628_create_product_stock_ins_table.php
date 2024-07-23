<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_stock_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_product_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->integer('item_qty');
            $table->string('package_type');
            $table->integer('quantity');
            $table->string('status');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_ins');
    }
};
