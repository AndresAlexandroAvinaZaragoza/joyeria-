<x-guest-layout>
    <div class="register-page">
        <section class="register-visual">
            <img src="{{ asset('images/login-joyeria.png') }}" alt="Joyería Lesa" class="register-visual__image">
            <div class="register-visual__overlay"></div>
            <div class="register-visual__content">
                <p>Lesa Joyería</p>
                <h1>Tu próxima pieza<br>te espera.</h1>
                <span>Recupera el acceso a tu cuenta y vuelve a disfrutar de tus colecciones favoritas.</span>
            </div>
        </section>

        <section class="register-content">
            <div class="register-header">
                <p class="login-header-label">Atención al cliente</p>
                <h2>Recupera tu contraseña</h2>
                <p>Escribe el correo electrónico de tu cuenta y te enviaremos un enlace para crear una nueva contraseña.</p>
            </div>

            @if (session('status'))
                <div class="login-session-status" role="status" aria-live="polite">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="register-form">
                @csrf

                <div class="register-field">
                    <label for="email">Correo electrónico</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="cliente@correo.com"
                    >
                    @error('email')
                        <span class="register-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="login-button">
                    Enviarme el enlace <span aria-hidden="true">→</span>
                </button>
            </form>

            <p class="register-login-link">
                ¿Ya recordaste tu contraseña?
                <a href="{{ route('login') }}">Volver a iniciar sesión</a>
            </p>
        </section>
    </div>
</x-guest-layout>
