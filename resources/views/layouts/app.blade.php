<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Pinora — Timeless Jewellery')</title>
    <meta name="description" content="@yield('meta_description', 'Shop certified gold, silver & diamond jewellery from trusted artisan vendors across India.')">

    <script>
        window.PinoraConfig = {
            baseUrl: "{{ url('/') }}",
            shopUrl: "{{ route('shop.index') }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-secondary bg-dark-bg text-text-light leading-relaxed pb-20 md:pb-0">

    @include('layouts.partials.navbar')

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="alert alert-error">{{ session('error') }}</div>
        </div>
    @endif
    @if(session('warning'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="alert alert-warning">{{ session('warning') }}</div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
