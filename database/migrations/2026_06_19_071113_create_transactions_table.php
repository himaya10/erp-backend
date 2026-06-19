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
        Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Accountant
    $table->string('type'); // income (විකිණුම්), expense (මිලදී ගැනීම්/නිෂ්පාදන)
    $table->decimal('amount', 10, 2);
    $table->string('reference_id')->nullable(); // Sale ID හෝ Purchase ID එක
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
