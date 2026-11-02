<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-gray-800">Kategori yönetimi</h1>
            <a href="{{ route('admin.categories.create') }}"
               class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Yeni kategori
            </a>
        </div>
    </x-slot>

    <main class="mx-auto max-w-7xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <p role="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</p>
        @endif

        @if ($errors->has('category'))
            <p role="alert" class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ $errors->first('category') }}</p>
        @endif

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Sıra</th>
                        <th class="px-4 py-3">Komisyon (%)</th>
                        <th class="px-4 py-3">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-3">
                                @if ($category->parent)
                                    <span class="text-gray-500">{{ $category->parent->name }} /</span>
                                @endif
                                {{ $category->name }}
                            </td>
                            <td class="px-4 py-3">{{ $category->slug }}</td>
                            <td class="px-4 py-3">{{ $category->sort }}</td>
                            <td class="px-4 py-3">{{ $category->commission_rate }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium text-indigo-700 hover:underline">
                                        Düzenle
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-700 hover:underline">Sil</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Henüz kategori yok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $categories->links() }}
    </main>
</x-app-layout>
