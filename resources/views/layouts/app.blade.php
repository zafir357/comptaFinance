<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-950 antialiased">
    <x-layouts.app.sidebar>
        {{ $slot }}
    </x-layouts.app.sidebar>

    @livewireScripts
    @fluxScripts
</body>
</html>
