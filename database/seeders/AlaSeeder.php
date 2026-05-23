<?php

namespace Database\Seeders;

use App\Models\Ala;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AlaSeeder extends Seeder
{
    /** @return Collection<string, Ala> */
    public function run(): Collection
    {
        $alas = collect();

        foreach ($this->dados() as $dado) {
            $alas[$dado['nome']] = Ala::query()->updateOrCreate(
                ['nome' => $dado['nome']],
                ['ativo' => $dado['ativo']],
            );
        }

        return $alas;
    }

    /** @return list<array{nome: string, ativo: bool}> */
    protected function dados(): array
    {
        return [
            ['nome' => 'Ala A — Recepção e Triagem', 'ativo' => true],
            ['nome' => 'Ala B — Laboratório', 'ativo' => true],
            ['nome' => 'Ala C — Imagem', 'ativo' => true],
            ['nome' => 'Ala D — Administrativo', 'ativo' => true],
        ];
    }
}
