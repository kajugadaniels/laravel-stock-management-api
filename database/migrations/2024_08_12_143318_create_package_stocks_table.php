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
        Schema::create('package_stocks', function (Blueprint $table) {
            Schema::create('package_stocks', function (Blueprint $table) {
                $table->id();
                $table->string('item_name');
                $table->string('category');
                $table->string('type');
                $table->string('capacity');
                $table->string('unit');
                $table->integer('quantity');
                $table->timestamps();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_stocks');
    }
};
