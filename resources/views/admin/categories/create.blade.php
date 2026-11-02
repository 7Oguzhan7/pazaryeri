<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Yeni kategori oluştur</h1>
    </x-slot>

    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        @include('admin.categories.form')
    </main>
</x-app-layout>
