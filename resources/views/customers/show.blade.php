<x-layouts.app>
    <div class="rounded-lg bg-slate-900 border border-slate-700 p-8 shadow-lg">
        <h1 class="text-3xl font-bold text-white">{{ $customer->name }}</h1>
        <p class="mt-4 text-slate-400">Email: {{ $customer->email }}</p>
    </div>
</x-layouts.app>
