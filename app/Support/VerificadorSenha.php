<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class VerificadorSenha
{
    public static function validar(Authenticatable $user, string $plain): bool
    {
        $stored = $user->getAuthPassword();

        if (! is_string($stored) || $stored === '') {
            return false;
        }

        if (Hash::isHashed($stored)) {
            return Hash::check($plain, $stored);
        }

        if (! hash_equals($stored, $plain)) {
            return false;
        }

        if ($user instanceof Model) {
            $user->forceFill(['password' => $plain]);
            $user->save();
        }

        return true;
    }
}
