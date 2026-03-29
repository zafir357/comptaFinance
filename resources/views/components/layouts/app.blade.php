<div class="min-h-screen bg-slate-950 text-slate-900">
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
