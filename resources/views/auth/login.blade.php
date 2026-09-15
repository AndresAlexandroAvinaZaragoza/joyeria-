<x-guest-layout>

    <div class="login-wrapper">
        
        {{-- HERO / IMAGEN SUPERIOR --}}
        <section class="login-hero">

            <img
                src="{{ asset('images/login-joyeria.png') }}"
                alt="Joyería Lesa"
                class="login-hero-image"
            >

            <div class="login-hero-overlay"></div>


            {{-- Nombre de la joyería --}}
            <div class="login-hero-title">

                <h1 class="login-logo">
                    Joyeria Lesa
                </h1>

                <div class="login-founded">
                    Mayor Calidad en Mejor Precio         
                </div>

            </div>

        </section>


        {{-- CONTENIDO --}}
        <section class="login-content">

            <div class="login-form-wrapper">


                {{-- Encabezado --}}
                <div class="login-header">

                    <h2>
                        Iniciar Sesión
                    </h2>

                </div>


                {{-- ESTADO DE SESIÓN --}}
                @if (session('status'))

                    <div class="login-session-status">
                        {{ session('status') }}
                    </div>

                @endif


                {{-- ERROR GENERAL --}}
                @if ($errors->any())

                    <div class="login-alert">

                        <strong>
                            No fue posible iniciar sesión.
                        </strong>

                        <div>
                            Verifica tu correo y contraseña.
                        </div>

                    </div>

                @endif



                {{-- FORMULARIO --}}
                <form method="POST" action="{{ route('login') }}">

                    @csrf


                    {{-- CORREO --}}
                    <div class="login-group">

                        <div class="login-label-row">

                            <label
                                for="email"
                                class="login-label"
                            >
                                Corrreo electrónico
                            </label>


                        </div>


                        <div class="login-input-wrapper">

                            {{-- Icono correo --}}
                            <svg
                                class="login-input-icon"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.7"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1a3 3 0 006 0v-1a10 10 0 10-4 8"
                                />
                            </svg>


                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="cliente@joyerialesa.com"
                                class="login-input"
                            >

                        </div>


                        {{-- Error del email --}}
                        @error('email')

                            <div class="login-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- CONTRASEÑA --}}
                    <div class="login-group">

                        <div class="login-label-row">

                            <label
                                for="password"
                                class="login-label"
                            >
                                Contraseña
                            </label>


                            @if (Route::has('password.request'))

                                <a
                                    href="{{ route('password.request') }}"
                                    class="login-forgot"
                                >
                                    ¿Olvidó su clave?
                                </a>

                            @endif

                        </div>


                        <div class="login-input-wrapper">

                            {{-- Icono candado --}}
                            <svg
                                class="login-input-icon"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.7"
                                    d="M8 11V7a4 4 0 118 0v4m-8 0h8a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6a2 2 0 012-2z"
                                />
                            </svg>


                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••••••"
                                class="login-input"
                            >


                            {{-- Botón mostrar contraseña --}}
                            <button
                                type="button"
                                id="togglePassword"
                                class="password-toggle"
                                aria-label="Mostrar contraseña"
                            >

                                <svg
                                    id="eyeIcon"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>

                            </button>

                        </div>


                        {{-- Error contraseña --}}
                        @error('password')

                            <div class="login-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- RECORDAR SESIÓN --}}
                    <div class="login-remember">

                        <label for="remember_me">

                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                            >

                            <span>
                                Mantener sesión iniciada en este dispositivo
                            </span>

                        </label>

                    </div>


                    {{-- BOTÓN LOGIN --}}
                    <button
                        type="submit"
                        class="login-button"
                    >

                        <span>
                            Ingresar
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>

                    </button>

                </form>


                {{-- CREAR CUENTA --}}
                <div class="login-register">

                    <p class="login-register-text">
                        ¿Aún no posee una cuenta?
                    </p>


                    @if (Route::has('register'))

                        <a
                            href="{{ route('register') }}"
                            class="login-register-link"
                        >
                            Registrarse 
                        </a>

                    @endif

                </div>

            </div>

        </section>

    </div>


    {{-- JAVASCRIPT MOSTRAR / OCULTAR CONTRASEÑA --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const passwordInput =
                document.getElementById('password');

            const togglePassword =
                document.getElementById('togglePassword');


            if (!passwordInput || !togglePassword) {
                return;
            }


            togglePassword.addEventListener('click', function () {

                const passwordVisible =
                    passwordInput.type === 'text';


                passwordInput.type =
                    passwordVisible
                        ? 'password'
                        : 'text';


                togglePassword.setAttribute(
                    'aria-label',
                    passwordVisible
                        ? 'Mostrar contraseña'
                        : 'Ocultar contraseña'
                );

            });

        });

    </script>

</x-guest-layout>