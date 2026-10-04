<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Pinora — Rajkot\'s Nose Pin Specialist | 22K Gold Jewellery')</title>
    <meta name="description" content="@yield('meta_description', 'Discover certified 22K gold nose pins and timeless fine jewellery from Rajkot\'s specialist artisan craftspeople. Every piece BIS hallmarked.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        window.PinoraConfig = {
            baseUrl: "{{ url('/') }}",
            shopUrl: "{{ route('shop.index') }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-secondary bg-[#FAF8F5] text-[#142E25] min-h-screen flex flex-col pb-16 md:pb-0 antialiased selection:bg-[#C59B27]/20 selection:text-[#083B2B]">

    @include('layouts.partials.navbar')

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 w-full">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 w-full">
            <div class="alert alert-error">{{ session('error') }}</div>
        </div>
    @endif
    @if(session('warning'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 w-full">
            <div class="alert alert-warning">{{ session('warning') }}</div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    {{-- Mobile Bottom Navigation Bar --}}
    @include('layouts.partials.mobile-bottom-nav')

    {{-- Floating WhatsApp Support Button (matching mockups) --}}
    <a href="https://wa.me/919999999999?text=Hello%20PINORA,%20I%20have%20an%20inquiry%20about%20your%20nose%20pins" 
       target="_blank" 
       rel="noopener noreferrer" 
       class="fixed bottom-20 md:bottom-6 right-4 md:right-6 z-40 w-12 h-12 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-lg hover:scale-110 active:scale-95 transition-all duration-300 group"
       aria-label="Chat on WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>
    </a>

    @stack('scripts')
</body>
</html>
