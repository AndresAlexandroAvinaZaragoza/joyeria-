<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>AURA | Alta joyeria</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="store-page">
        @include('layouts.navigation')
        @if (session('cart_status'))<div class="store-notice">{{ session('cart_status') }}</div>@endif

        <main>
            <section class="store-hero"><p class="eyebrow">Universo AURA · Piezas con historia</p><h1>Obras maestras en pulseras<br>&amp; brazaletes</h1><p>Joyas seleccionadas para acompañar el pulso diario con discreta magnificencia.</p></section>
            <section class="product-section" aria-labelledby="product-title">
                <div class="section-heading"><div><p class="eyebrow">Coleccion disponible</p><h2 id="product-title">Piezas para llevar siempre</h2></div>@if (request('search') || request('categoria'))<a href="{{ route('home') }}" class="clear-filter">Ver todo</a>@endif</div>
                <div class="product-grid">
                    @forelse ($productos as $producto)
                        @php($image = $producto->imagenes->first()?->url_img)
                        <article class="product-card">
                            <div class="product-card__image">
                                @if ($image)<img src="{{ str_starts_with($image, 'http') ? $image : Storage::disk('public')->url($image) }}" alt="{{ $producto->nombre }}">@else<div class="product-placeholder" aria-hidden="true">AURA</div>@endif
                                <span class="product-tag">Pieza de autor</span>
                            </div>
                            <div class="product-card__body"><p class="product-category">{{ $producto->categoria?->nombre ?? 'Alta joyeria' }}</p><h3>{{ $producto->nombre }}</h3><p class="product-description">{{ $producto->descripcion ?: 'Una pieza de presencia serena y acabado impecable.' }}</p><div class="product-card__footer"><strong>${{ number_format($producto->precio_venta, 2) }}</strong><form method="POST" action="{{ route('cart.add', $producto) }}">@csrf<button type="submit" class="add-button">+ Anadir</button></form></div></div>
                        </article>
                    @empty
                        <div class="empty-products"><h3>No encontramos esa pieza</h3><p>Prueba con otro termino o vuelve a ver toda la coleccion.</p><a href="{{ route('home') }}" class="primary-button">Ver catalogo</a></div>
                    @endforelse
                </div>
                @if ($productos->hasPages())<div class="store-pagination">{{ $productos->links() }}</div>@endif
            </section>
            <section class="collections-section"><div class="section-heading"><div><p class="eyebrow">Inspiracion AURA</p><h2>Detalles que permanecen</h2></div></div><div class="collection-grid"><article><span>01</span><h3>Heritage</h3><p>Tradicion gemologica reinterpretada.</p></article><article><span>02</span><h3>Emerald Royale</h3><p>Color, luz y caracter en equilibrio.</p></article><article><span>03</span><h3>Constellation</h3><p>Diamantes para momentos irrepetibles.</p></article></div></section>
        </main>

        <footer class="store-footer"><div class="footer-brand"><strong>AURA</strong><p>Alta joyeria creada con oficio, calma y atencion al detalle.</p></div><div><span class="footer-title">Maison &amp; creacion</span><p>Grandes colecciones<br>Piezas unicas<br>Ediciones privadas</p></div><div><span class="footer-title">Asistencia</span><p>Consulta de certificados<br>Cita privada<br>Cuidado de joyas</p></div><div><span class="footer-title">La Gazette Privee</span><p>Proximamente: novedades y piezas de temporada.</p></div></footer>
    </body>
</html>
