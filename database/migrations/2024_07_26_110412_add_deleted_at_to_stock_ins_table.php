<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToStockInsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->softDeletes(); // Adds deleted_at column
        });
    }

    public function down()
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
