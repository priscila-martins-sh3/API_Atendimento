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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('nome_pessoa');
            $table->string('nome_cliente');
            $table->string('area');
            $table->string('tipo_atendimento');
            $table->string('nome_suporte');
            $table->boolean('retorno');
            $table->string('informacoes');
            $table->date('data_service');
            $table->time('hora_service');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
