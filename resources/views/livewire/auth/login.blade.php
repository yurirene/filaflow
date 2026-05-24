<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Faça o login para continuar')" :description="__('Informe seu email e senha para acessar o sistema')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="seu@email.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Senha')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Senha')"
                    viewable
                />
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Manter conectado')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Entrar') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
