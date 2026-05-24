<?php

namespace Tests\Unit\Support;

use App\Models\Medico;
use App\Support\VerificadorSenha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerificadorSenhaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_senha_com_hash_bcrypt(): void
    {
        $medico = Medico::factory()->create(['password' => 'senha123']);

        $this->assertTrue(VerificadorSenha::validar($medico, 'senha123'));
        $this->assertFalse(VerificadorSenha::validar($medico, 'errada'));
    }

    #[Test]
    public function rehash_senha_em_texto_puro_no_login(): void
    {
        $medico = Medico::factory()->create();
        $medico->forceFill(['password' => 'senha123'])->saveQuietly();

        $this->assertTrue(VerificadorSenha::validar($medico->fresh(), 'senha123'));
        $this->assertTrue(Hash::isHashed($medico->fresh()->getAuthPassword()));
    }
}
