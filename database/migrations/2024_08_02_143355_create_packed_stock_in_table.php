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
        Schema::create('packed_stock_in', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->integer('capacity');
            $table->integer('quantity');
            $table->integer('total_sacks');
            $table->integer('remaining_kg');
            $table->integer('remaining_sacks');
        
            // Ensure that packages table is already created
            if (Schema::hasTable('packages')) {
                $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            }
            
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packed_stock_in');
    }
};
