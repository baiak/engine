<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_labor_logs', function (Blueprint $table) {
            // Remove a coluna 'data'
            $table->dropColumn('data');

            // Adiciona as colunas 'old_values' e 'new_values'
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
        });
    }

   /* public function down(): void
    {
        Schema::table('service_labor_logs', function (Blueprint $table) {
            // Reverte as alterações: remove 'old_values' e 'new_values', adiciona 'data'
            $table->dropColumn(['old_values', 'new_values']);
            $table->json('data')->nullable();
        });
    }*/
};
