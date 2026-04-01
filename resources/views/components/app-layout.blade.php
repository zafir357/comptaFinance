<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="antialiased">

<div class="min-h-screen bg-gray-50">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg overflow-y-auto">
        {{-- Logo/Brand --}}
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-blue-600">ComptaFinance</h1>
            <p class="mt-1 text-sm text-gray-600">Gestion comptable</p>
        </div>

        {{-- Organization Switcher --}}
        <div class="p-4 border-b border-gray-200">
            <livewire:organization-switcher />
        </div>

        {{-- Navigation Menu --}}
        <nav class="p-4 space-y-2">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m4 0l4-4m4 0l8 8M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- Invoices --}}
            <a href="{{ route('invoices.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('invoices.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Factures</span>
            </a>

            {{-- Customers --}}
            <a href="{{ route('customers.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
                <span>Clients</span>
            </a>

            {{-- Expenses --}}
            <a href="{{ route('expenses.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Notes de frais</span>
            </a>

            {{-- Banking --}}
            <a href="{{ route('banking.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('banking.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3-3h12l3 3M3 6v12a3 3 0 003 3h12a3 3 0 003-3V6M7 12a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
                <span>Rapprochement</span>
            </a>

            {{-- Support Tickets --}}
            <a href="{{ route('tickets.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('tickets.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Support</span>
            </a>
        </nav>

        {{-- Divider --}}
        <div class="mx-4 border-t border-gray-200"></div>

        {{-- User Menu (bottom) --}}
        <div class="p-4 mt-8">
            <div class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gray-100">
                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-600 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                    Paramètres
                </a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="ml-64 p-8">
        {{-- Header with flash messages --}}
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Content --}}
        {{ $slot }}
    </main>
</div>

</body>
</html>
