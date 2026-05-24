<?php

namespace App\Models;

use App\Fila\Enums\StatusOperador;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Medico extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'medicos';

    protected $fillable = [
        'nome',
        'cpf',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => StatusOperador::class,
        ];
    }

    public function consultorio(): HasOne
    {
        return $this->hasOne(Consultorio::class);
    }

    public static function normalizarCpf(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf) ?? '';
    }

    public function iniciais(): string
    {
        $partes = preg_split('/\s+/', trim($this->nome)) ?: [];

        if (count($partes) === 0) {
            return 'MD';
        }

        if (count($partes) === 1) {
            return strtoupper(substr($partes[0], 0, 2));
        }

        return strtoupper(substr($partes[0], 0, 1).substr($partes[array_key_last($partes)], 0, 1));
    }

    public function cpfFormatado(): string
    {
        $cpf = $this->cpf;

        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3).'.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-'.substr($cpf, 9, 2);
    }

    public function isAtivo(): bool
    {
        return $this->status->isAtivo();
    }

    public function temConsultorioVinculado(): bool
    {
        return $this->relationLoaded('consultorio')
            ? $this->consultorio !== null
            : $this->consultorio()->exists();
    }
}
