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
        Schema::table('service_audit_logs', function (Blueprint $table) {
            // Adiciona as colunas de timestamp se elas não existirem.
            // Se 'created_at' já existe mas 'updated_at' não, você pode adicionar só a 'updated_at'.
            // O método timestamps() adiciona ambas: created_at e updated_at (ambas NULLABLE)
            // Se você já tem 'created_at', pode apenas adicionar 'updated_at'
            // $table->timestamp('updated_at')->nullable();
            // Ou, para garantir ambas no padrão Laravel:

            if (!Schema::hasColumn('service_audit_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
            // Se você quer que o Laravel gerencie e elas não podem ser nulas (comportamento padrão ao criar novas tabelas com $table->timestamps()):
            // $table->timestamps(); // Isso adicionaria created_at e updated_at não nulos.
            // Use com cuidado se a tabela já existe com dados.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_audit_logs', function (Blueprint $table) {
            // Cuidado ao remover, apenas se você realmente adicionou nesta migration
            // $table->dropColumn('updated_at');
            // $table->dropColumn('created_at'); // Se você adicionou ambas
        });
    }
};