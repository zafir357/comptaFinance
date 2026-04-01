<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-950">
        <flux:sidebar sticky stashable class="border-r border-slate-700 bg-slate-900 text-slate-100 shadow-lg shadow-slate-950/50">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="mr-5 flex items-center gap-3 py-1" wire:navigate>
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-600/30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </span>
                <span class="text-xl font-bold tracking-tight text-white">ComptaFinance</span>
            </a>

            {{-- Organization Switcher --}}
            <div class="mt-4 rounded-2xl border border-slate-700 bg-slate-800 p-3">
                <livewire:organization-switcher />
            </div>

            {{-- Main Navigation --}}
            <flux:navlist variant="outline" class="mt-4">
                <flux:navlist.group heading="Navigation" class="grid text-slate-400">
                    <flux:navlist.item class="text-slate-300 hover:text-white hover:bg-slate-800" icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Dashboard
                    </flux:navlist.item>
                    <flux:navlist.item class="text-slate-300 hover:text-white hover:bg-slate-800" icon="document-text" :href="route('invoices.index')" :current="request()->routeIs('invoices.*')" wire:navigate>
                        Factures
                    </flux:navlist.item>
                    <flux:navlist.item class="text-slate-300 hover:text-white hover:bg-slate-800" icon="users" :href="route('customers.index')" :current="request()->routeIs('customers.*')" wire:navigate>
                        Clients
                    </flux:navlist.item>
                    <flux:navlist.item class="text-slate-300 hover:text-white hover:bg-slate-800" icon="banknotes" :href="route('expenses.index')" :current="request()->routeIs('expenses.*')" wire:navigate>
                        Notes de frais
                    </flux:navlist.item>
                    <flux:navlist.item class="text-slate-300 hover:text-white hover:bg-slate-800" icon="building-library" :href="route('banking.index')" :current="request()->routeIs('banking.*')" wire:navigate>
                        Rapprochement
                    </flux:navlist.item>
                    <flux:navlist.item class="text-slate-300 hover:text-white hover:bg-slate-800" icon="ticket" :href="route('tickets.index')" :current="request()->routeIs('tickets.*')" wire:navigate>
                        Support
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            {{-- User profile dropdown at bottom --}}
            <flux:dropdown position="bottom" align="start">
                <div class="rounded-2xl border border-slate-700 bg-slate-800 px-3 py-3 shadow-lg">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevrons-up-down"
                        class="text-white"
                    />
                </div>
                <flux:menu class="w-[220px] bg-slate-800 border border-slate-700">
                    <div class="px-2 py-1.5 text-xs text-slate-400">{{ auth()->user()->email }}</div>
                    <flux:menu.separator class="bg-slate-700" />
                    <flux:menu.item href="{{ route('settings.profile') }}" icon="cog" wire:navigate class="text-slate-300 hover:text-white hover:bg-slate-700">
                        Paramètres
                    </flux:menu.item>
                    <flux:menu.separator class="bg-slate-700" />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-slate-300 hover:text-white hover:bg-slate-700">
                            Déconnexion
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        {{-- Mobile header --}}
        <flux:header class="border-b border-slate-700 bg-slate-900 backdrop-blur lg:hidden text-white">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <span class="font-semibold tracking-tight text-white">ComptaFinance</span>
            <flux:spacer />
        </flux:header>

        {{-- Main content area (passed from layouts/app.blade.php) --}}
        {{ $slot }}

    </body>
</html>
