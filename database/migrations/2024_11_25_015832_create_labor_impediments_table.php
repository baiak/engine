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
        Schema::create('labor_impediments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_labor_id');
            $table->unsignedBigInteger('complainant_id'); // ID do reclamante (usuário autenticado)
            $table->unsignedBigInteger('complained_id');  // ID do reclamado (usuário selecionado)
            $table->text('reason');
            $table->enum('status', ['visualizado', 'em aberto', 'resolvido', 'cancelado', 'sem solução'])->default('em aberto');
            $table->text('observations')->nullable();
            $table->json('logs')->nullable(); // Armazena as mudanças como JSON
            $table->timestamps();

            $table->foreign('service_labor_id')->references('id')->on('service_labors')->onDelete('cascade');
            $table->foreign('complainant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('complained_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_impediments');
    }
};
