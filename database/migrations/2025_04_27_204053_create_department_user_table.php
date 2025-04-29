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
        Schema::create('department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_responsible')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('admission_date')->useCurrent(); // Data de admissão padrão como a data atual
            $table->timestamp('dismissal_date')->nullable(); // Data de demissão opcional
            $table->timestamps();
            
            // Garante que cada combinação departamento-usuário seja única
            $table->unique(['department_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_user');
    }
};
