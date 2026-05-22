<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_documentacao(): void
    {
        $this->get(route('documentacao'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_documentacao(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('documentacao'))
            ->assertOk()
            ->assertSee(__('Configuração passo a passo'));
    }
}
