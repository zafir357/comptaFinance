<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ComptaFinance') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>
<body class="min-h-screen bg-slate-950 antialiased">
    @fluxStyles

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <x-layouts.app.sidebar>
            <flux:main class="px-4 py-6 lg:px-8">
                <div class="mx-auto w-full max-w-7xl space-y-6">
                    {{-- Flash Messages --}}
                    @if (session('success'))
                        <div class="rounded-2xl border border-emerald-600 bg-emerald-900 px-4 py-3 text-emerald-100 shadow-lg shadow-emerald-900/20">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="rounded-2xl border border-rose-600 bg-rose-900 px-4 py-3 text-rose-100 shadow-lg shadow-rose-900/20">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </flux:main>
        </x-layouts.app.sidebar>
    </div>

    @livewireScripts
    @fluxScripts
</body>
</html>
