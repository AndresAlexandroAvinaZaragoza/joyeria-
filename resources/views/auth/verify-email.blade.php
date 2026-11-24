<x-guest-layout>
    <div class="register-page">
        <section class="register-visual">
            <img src="{{ asset('images/login-joyeria.png') }}" alt="Joyería Lesa" class="register-visual__image">
            <div class="register-visual__overlay"></div>
            <div class="register-visual__content">
                <p>Lesa Joyería</p>
                <h1>Una experiencia<br>hecha para ti.</h1>
                <span>Confirma tu correo para proteger tu cuenta y disfrutar de todo lo que tenemos para ofrecerte.</span>
            </div>
        </section>

        <section class="register-content">
            <div class="register-header">
                <p class="login-header-label">Un paso más</p>
                <h2>Verifica tu correo</h2>
                <p>Te enviamos un enlace de confirmación al correo con el que te registraste. Ábrelo para activar tu cuenta.</p>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="login-session-status" role="status" aria-live="polite">
                    Enviamos un nuevo enlace de verificación a tu correo electrónico.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="register-form">
                @csrf

                <button type="submit" class="login-button">
                    Reenviar correo de verificación <span aria-hidden="true">→</span>
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="register-login-link">
                @csrf

                <button type="submit" class="login-register-link">Cerrar sesión</button>
            </form>
        </section>
    </div>
</x-guest-layout>
