<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_person_id')->constrained('employees')->onDelete('cascade');
            $table->string('requester_name');
            $table->string('request_from');
            $table->string('status')->default('Pending');
            $table->foreignId('request_for_id')->constrained('items')->onDelete('cascade')->nullable();
            $table->integer('quantity');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
}
