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
        Schema::create('package_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_request_id')->constrained('package_requests')->onDelete('cascade');
            $table->foreignId('stock_in_id')->constrained('stock_ins')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_request_items');
    }
};
