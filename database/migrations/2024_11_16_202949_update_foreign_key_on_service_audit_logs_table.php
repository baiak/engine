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
            /*$table->dropForeign(['service_id']);
            $table->foreign('service_id')
                ->references('id')->on('services')
                ->nullOnDelete(); // Permite valores nulos ao deletar*/
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_audit_logs', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->foreign('service_id')
                ->references('id')->on('services')
                ->cascadeOnDelete(); // Restaura o comportamento original
        });
    }
};
