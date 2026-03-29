<x-app-layout>
    <div class="rounded-lg bg-white p-8 shadow">
        <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
        <p class="mt-4 text-gray-600">Email: {{ $customer->email }}</p>
    </div>
</x-app-layout>
