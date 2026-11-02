<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Satıcı Paneli</h1>
    </x-slot>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="text-gray-700">
            {{ auth()->user()->seller->store_name }} mağazası için satıcı paneline hoş geldiniz.
        </p>
    </main>
</x-app-layout>
