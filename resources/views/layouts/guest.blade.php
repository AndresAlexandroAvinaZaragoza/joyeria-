<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >

        <meta
            name="csrf-token"
            content="{{ csrf_token() }}"
        >

        <title>
            {{ config('app.name', 'JoyeriaLesa') }}
        </title>

        <link rel="preconnect" href="https://fonts.bunny.net">

        <link
            href="https://fonts.bunny.net/css?family=manrope:400,500,600,700|playfair-display:500,600,700"
            rel="stylesheet"
        >
        

        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>

    <body>

        <main class="login-page">

            <div class="login-container">

                {{ $slot }}

            </div>

        </main>

    </body>
</html>