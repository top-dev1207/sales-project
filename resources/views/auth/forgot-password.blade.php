{{-- @dd(url(env('LOGO_ISO_CUADRADO_NEGRO'))) --}}
<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="{{ url('/')}}">
                {{-- <x-application-logo class="w-20 h-20 fill-current text-gray-500" /> --}}
                <img class="c-sidebar-brand-full" src="{{ url(env('LOGO_ISO_CUADRADO_NEGRO', 'images/crm2.jpg')) }}" width="200" height="56" alt='{{ env('NOMBRE_LOCAL') }}'>
            </a>

        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Olvidó su contraseña? No hay problema. Indique su email y le enviaremos un link para que pueda ingresar una nueva.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Email Password Reset Link') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
