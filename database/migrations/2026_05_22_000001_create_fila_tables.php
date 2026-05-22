<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome', 150);
            $table->string('cnpj', 18)->nullable()->unique();
            $table->boolean('ativo')->default(true);
            $table->string('hora_inicio', 5)->default('07:00');
            $table->string('hora_fim', 5)->default('19:00');
            $table->string('ticker', 500)->nullable();
            $table->string('reinicio_hora', 5)->default('00:00');
            $table->string('som', 20)->default('beep');
            $table->json('notificacoes')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
        });

        Schema::create('servicos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome', 100);
            $table->string('prefixo', 2);
            $table->string('ala', 50)->nullable();
            $table->unsignedSmallInteger('tempo_medio_minutos')->default(10);
            $table->string('cor', 7)->default('#2563eb');
            $table->string('icone', 10)->default('🏥');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'prefixo'], 'servicos_empresa_prefixo_uniq');
            $table->index(['empresa_id', 'ativo'], 'servicos_empresa_ativo_idx');
        });

        Schema::create('guiches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero');
            $table->string('descricao', 100)->nullable();
            $table->foreignUuid('servico_padrao_id')->nullable()->constrained('servicos')->nullOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'numero'], 'guiches_empresa_numero_uniq');
            $table->index('empresa_id', 'guiches_empresa_idx');
        });

        Schema::create('senha_contadores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignUuid('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->date('data');
            $table->unsignedInteger('ultimo_numero')->default(0);

            $table->unique(['empresa_id', 'servico_id', 'data'], 'senha_contadores_uniq');
        });

        Schema::create('senhas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('codigo', 10);
            $table->foreignUuid('servico_id')->constrained('servicos')->restrictOnDelete();
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
                ['empresa_id', 'servico_id', 'status', 'is_preferencial', 'emitida_em'],
                'senhas_fila_idx',
            );
            $table->index(['empresa_id', 'status'], 'senhas_empresa_status_idx');
        });

        Schema::create('chamadas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignUuid('senha_id')->constrained('senhas')->cascadeOnDelete();
            $table->foreignUuid('guiche_id')->constrained('guiches')->restrictOnDelete();
            $table->foreignId('operador_id')->constrained('users')->restrictOnDelete();
            $table->timestampTz('chamada_em');
            $table->unsignedSmallInteger('rechamada_vezes')->default(0);

            $table->index(['empresa_id', 'chamada_em'], 'chamadas_empresa_data_idx');
        });

        Schema::create('agendamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('paciente_nome', 150);
            $table->string('paciente_celular', 20)->nullable();
            $table->foreignUuid('servico_id')->constrained('servicos')->restrictOnDelete();
            $table->timestampTz('data_hora');
            $table->string('status', 30)->default('agendado');
            $table->timestamps();

            $table->index(['empresa_id', 'data_hora'], 'agendamentos_empresa_data_idx');
        });

        Schema::create('regras_intercalacao', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignUuid('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->unsignedSmallInteger('normais_por_ciclo')->default(2);
            $table->unsignedSmallInteger('preferenciais_por_ciclo')->default(1);
            $table->unsignedInteger('ciclo_atual')->default(0);

            $table->unique(['empresa_id', 'servico_id'], 'regras_intercalacao_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regras_intercalacao');
        Schema::dropIfExists('agendamentos');
        Schema::dropIfExists('chamadas');
        Schema::dropIfExists('senhas');
        Schema::dropIfExists('senha_contadores');
        Schema::dropIfExists('guiches');
        Schema::dropIfExists('servicos');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
        });

        Schema::dropIfExists('empresas');
    }
};
