<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nosso Hub</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#12081f">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|sora:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen font-sans">
    @hasSection('hide_navbar')
        {{-- Home: sem navbar superior --}}
    @else
        <x-navbar />
    @endif

    <main @class([
        'nh-container',
        'py-8 sm:py-12' => ! View::hasSection('hide_navbar'),
        'px-4 sm:px-6' => View::hasSection('hide_navbar'),
    ])>
        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 4200)"
                x-transition.opacity
                class="mb-6 rounded-2xl border border-[var(--brand-yellow)]/30 bg-[var(--brand-yellow)]/10 px-4 py-3 text-sm text-[var(--brand-yellow-soft)]"
            >
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
