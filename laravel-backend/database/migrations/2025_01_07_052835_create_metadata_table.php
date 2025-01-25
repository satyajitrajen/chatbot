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
        Schema::create('metadata', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); 
            $table->string('ip_address')->nullable(); 
            $table->text('user_agent')->nullable(); 
            $table->timestamp('timestamp')->nullable(); 
            $table->string('country')->nullable(); 
            $table->string('region')->nullable(); 
            $table->string('city')->nullable(); 
            $table->decimal('latitude', 10, 6)->nullable(); 
            $table->decimal('longitude', 10, 6)->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadata');
    }
};
