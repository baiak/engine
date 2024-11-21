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
        Schema::create('service_labor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_labor_id')->constrained()->onDelete('cascade');
            $table->string('event'); // Ex: created, updated, deleted
            $table->json('data')->nullable(); // Dados relevantes sobre o evento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_labor_logs');
    }
};
