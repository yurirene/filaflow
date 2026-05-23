<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('cnpj', 18)->nullable()->unique();
            $table->boolean('ativo')->default(true);
            $table->string('hora_inicio', 5)->default('07:00');
            $table->string('hora_fim', 5)->default('19:00');
            $table->string('ticker', 500)->nullable();
            $table->string('reinicio_hora', 5)->default('00:00');
            $table->string('som', 20)->default('beep');
            $table->timestamps();
        });

        Schema::create('alas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100)->unique();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ala_id')->constrained('alas')->restrictOnDelete();
            $table->string('nome', 100);
            $table->string('prefixo', 2)->unique();
            $table->string('cor', 7)->default('#2563eb');
            $table->string('icone', 10)->default('🏥');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('ativo', 'servicos_ativo_idx');
        });

        Schema::create('operadores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('cpf', 11)->unique();
            $table->string('password');
            $table->string('status', 20)->default('ativo');
            $table->rememberToken();
            $table->timestamps();

            $table->index('status', 'operadores_status_idx');
        });

        Schema::create('guiches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ala_id')->constrained('alas')->restrictOnDelete();
            $table->unsignedSmallInteger('numero');
            $table->string('descricao', 100)->nullable();
            $table->foreignId('servico_padrao_id')->nullable()->constrained('servicos')->nullOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['ala_id', 'numero'], 'guiches_ala_numero_uniq');
        });

        Schema::create('consultorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ala_id')->constrained('alas')->restrictOnDelete();
            $table->unsignedSmallInteger('numero');
            $table->string('responsavel', 150);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['ala_id', 'numero'], 'consultorios_ala_numero_uniq');
        });

        Schema::create('consultorio_servico', function (Blueprint $table) {
            $table->foreignId('consultorio_id')->constrained('consultorios')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();

            $table->primary(['consultorio_id', 'servico_id']);
        });

        Schema::create('senha_contadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->date('data');
            $table->unsignedInteger('ultimo_numero')->default(0);

            $table->unique(['servico_id', 'data'], 'senha_contadores_uniq');
        });

        Schema::create('senhas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10);
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->nullOnDelete();
            $table->foreignId('consultorio_id')->nullable()->constrained('consultorios')->nullOnDelete();
            $table->string('prioridade', 20)->default('normal');
            $table->boolean('is_preferencial')->default(false);
            $table->boolean('is_agendado')->default(false);
            $table->string('status', 20)->default('aguardando');
            $table->string('paciente_celular', 20)->nullable();
            $table->timestampTz('emitida_em');
            $table->timestampTz('chamada_em')->nullable();
            $table->timestampTz('finalizada_em')->nullable();
            $table->unsignedInteger('ordem_fila')->default(0);

            $table->index(
                ['servico_id', 'consultorio_id', 'status', 'is_preferencial', 'emitida_em'],
                'senhas_fila_idx',
            );
            $table->index(
                ['consultorio_id', 'status', 'ordem_fila'],
                'senhas_consultorio_fila_idx',
            );
            $table->index('status', 'senhas_status_idx');
        });

        Schema::create('chamadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('senha_id')->constrained('senhas')->cascadeOnDelete();
            $table->foreignId('guiche_id')->nullable()->constrained('guiches')->nullOnDelete();
            $table->foreignId('consultorio_id')->nullable()->constrained('consultorios')->nullOnDelete();
            $table->foreignId('operador_id')->constrained('operadores')->restrictOnDelete();
            $table->timestampTz('chamada_em');
            $table->unsignedSmallInteger('rechamada_vezes')->default(0);

            $table->index('chamada_em', 'chamadas_data_idx');
        });

        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->string('paciente_nome', 150);
            $table->string('paciente_celular', 20)->nullable();
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->nullOnDelete();
            $table->timestampTz('data_hora');
            $table->string('status', 30)->default('agendado');
            $table->timestamps();

            $table->index('data_hora', 'agendamentos_data_idx');
        });

        Schema::create('regras_intercalacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->unsignedSmallInteger('normais_por_ciclo')->default(2);
            $table->unsignedSmallInteger('preferenciais_por_ciclo')->default(1);
            $table->unsignedInteger('ciclo_atual')->default(0);

            $table->unique('servico_id', 'regras_intercalacao_servico_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regras_intercalacao');
        Schema::dropIfExists('agendamentos');
        Schema::dropIfExists('chamadas');
        Schema::dropIfExists('operadores');
        Schema::dropIfExists('senhas');
        Schema::dropIfExists('senha_contadores');
        Schema::dropIfExists('consultorio_servico');
        Schema::dropIfExists('consultorios');
        Schema::dropIfExists('guiches');
        Schema::dropIfExists('servicos');
        Schema::dropIfExists('alas');
        Schema::dropIfExists('empresas');
    }
};
