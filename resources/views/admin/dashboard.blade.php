<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Yönetim Paneli</h1>
    </x-slot>

    <main class="mx-auto max-w-7xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
        <p class="text-gray-700">Pazaryeri yönetim alanına hoş geldiniz.</p>
        <a class="inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
           href="{{ route('admin.categories.index') }}">
            Kategorileri yönet
        </a>
    </main>
</x-app-layout>
