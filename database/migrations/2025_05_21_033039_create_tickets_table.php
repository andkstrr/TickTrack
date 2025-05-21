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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // mendefinisikan relasi antar table
            $table->string('title');
            $table->string('code')->unique();
            $table->longText('description');
            $table->enum('status', ['open', 'on_progress', 'resolved', 'rejected'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->timestamps(); // created_at, updated_at
            $table->timestamp('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
