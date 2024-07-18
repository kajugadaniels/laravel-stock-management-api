<?php

// Filename: 2024_07_08_080000_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // This creates an unsigned big integer (auto-increment)
            $table->string('name', 255);
            $table->string('contact', 255);
            $table->string('position', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
