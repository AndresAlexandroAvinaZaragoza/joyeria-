<x-guest-layout>
    <div class="register-page">
        <section class="register-visual">
            <img src="{{ asset('images/login-joyeria.png') }}" alt="Lesa Joyería" class="register-visual__image">
            <div class="register-visual__overlay"></div>
            <div class="register-visual__content">
                <p>Lesa Joyería</p>
                <h1>Una pieza especial<br>comienza aquí.</h1>
                <span>Descubre colecciones creadas para acompañar tus momentos importantes.</span>
            </div>
        </section>

        <section class="register-content">
            <div class="register-header">
                <p class="login-header-label">Bienvenido a Lesa</p>
                <h2>Crear tu cuenta</h2>
                <p>Regístrate como cliente para guardar tus datos y preparar tu próxima selección.</p>
            </div>

            @if ($errors->any())
                <div class="login-alert">Revisa los campos marcados para continuar.</div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="register-form">
                @csrf

                <div class="register-fields register-fields--two">
                    <div class="register-field">
                        <label for="name">Nombre</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="given-name" placeholder="Tu nombre">
                        @error('name')<span class="register-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="register-field">
                        <label for="apellidos">Apellidos</label>
                        <input id="apellidos" name="apellidos" type="text" value="{{ old('apellidos') }}" required autocomplete="family-name" placeholder="Tus apellidos">
                        @error('apellidos')<span class="register-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="register-fields register-fields--two">
                    <div class="register-field">
                        <label for="telefono">Teléfono</label>
                        <input id="telefono" name="telefono" type="tel" value="{{ old('telefono') }}" required autocomplete="tel" placeholder="55 0000 0000">
                        @error('telefono')<span class="register-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="register-field">
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" placeholder="cliente@correo.com">
                        @error('email')<span class="register-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="register-fields register-fields--two">
                    <div class="register-field">
                        <label for="password">Contraseña</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                        @error('password')<span class="register-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="register-field">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Repite tu contraseña">
                    </div>
                </div>

                <button type="submit" class="login-button">Crear cuenta <span aria-hidden="true">→</span></button>
            </form>

            <p class="register-login-link">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </section>
    </div>
</x-guest-layout>
