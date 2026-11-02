@php
    $isEditing = $category->exists;
    $formAction = $isEditing
        ? route('admin.categories.update', $category)
        : route('admin.categories.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="grid gap-5 rounded-lg bg-white p-6 shadow-sm">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Kategori adı</label>
        <input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="255"
               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
        <input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="255"
               placeholder="Boş bırakılırsa kategori adından üretilir"
               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('slug')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="parent_id" class="block text-sm font-medium text-gray-700">Üst kategori</label>
        <select id="parent_id" name="parent_id"
                class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Ana kategori</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="sort" class="block text-sm font-medium text-gray-700">Sıralama</label>
            <input id="sort" name="sort" type="number" min="0" max="2147483647"
                   value="{{ old('sort', $category->sort ?? 0) }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('sort')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="commission_rate" class="block text-sm font-medium text-gray-700">Komisyon oranı (%)</label>
            <input id="commission_rate" name="commission_rate" type="number" min="0" max="100" step="0.01"
                   value="{{ old('commission_rate', $category->commission_rate ?? '10.00') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('commission_rate')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ $isEditing ? 'Değişiklikleri kaydet' : 'Kategori oluştur' }}
        </button>
        <a href="{{ route('admin.categories.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            İptal
        </a>
    </div>
</form>
