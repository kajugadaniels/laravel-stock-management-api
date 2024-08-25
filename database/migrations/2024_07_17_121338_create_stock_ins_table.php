<?php

// Filename: 2024_07_17_121338_create_stock_ins_table.php

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
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('init_qty');
            $table->string('plate_number');
            $table->string('batch_number')->nullable();
            $table->text('comment')->nullable();
            $table->date('date');
            $table->foreignId('registered_by')->constrained('users')->onDelete('cascade');
            $table->boolean('loading_payment_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};
