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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // No foreign key for anonymous users
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->text('message');
            $table->enum('sender_type', ['user', 'agent']);
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->string('attachment_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
