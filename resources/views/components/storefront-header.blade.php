<header class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="text-xl font-bold tracking-tight text-indigo-700">
            {{ config('app.name', 'TıklaAl') }}
        </a>

        <form action="{{ route('home') }}" method="GET" role="search" class="order-3 flex w-full flex-1 gap-2 sm:order-none sm:min-w-64">
            <label class="sr-only" for="storefront-search">Ürün ara</label>
            <input id="storefront-search" name="q" value="{{ request('q') }}" type="search"
                   placeholder="Ürün veya marka ara"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Ara
            </button>
        </form>

        <div class="ml-auto flex items-center gap-3 text-sm">
            <button type="button" disabled aria-disabled="true" title="Sepet özelliği sonraki haftalarda eklenecek"
                    class="cursor-not-allowed rounded-md px-2 py-2 text-gray-500">
                Sepet <span class="rounded-full bg-gray-100 px-2 py-1">0</span>
            </button>

            @auth
                <details class="relative">
                    <summary class="cursor-pointer list-none rounded-md px-2 py-2 font-medium text-gray-700 hover:bg-gray-100">
                        {{ auth()->user()->name }}
                    </summary>
                    <div class="absolute right-0 z-20 mt-2 grid min-w-48 gap-1 rounded-md border border-gray-200 bg-white p-2 shadow-lg">
                        <a href="{{ route('dashboard') }}" class="rounded px-3 py-2 hover:bg-gray-100">Hesabım</a>
                        <a href="{{ route('profile') }}" class="rounded px-3 py-2 hover:bg-gray-100">Profil</a>
                        @if (auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="rounded px-3 py-2 hover:bg-gray-100">Yönetim paneli</a>
                        @elseif (auth()->user()->hasRole('seller') && auth()->user()->seller?->status === \App\Models\Seller::STATUS_APPROVED)
                            <a href="{{ route('seller.dashboard') }}" class="rounded px-3 py-2 hover:bg-gray-100">Satıcı paneli</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded px-3 py-2 text-left hover:bg-gray-100">Çıkış yap</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="rounded-md px-2 py-2 text-gray-700 hover:bg-gray-100">Giriş yap</a>
                <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-3 py-2 font-semibold text-white hover:bg-indigo-700">Kayıt ol</a>
            @endauth
        </div>
    </div>

    <nav aria-label="Ürün kategorileri" class="border-t border-gray-100">
        <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
            <details class="group relative">
                <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Tüm kategoriler
                    <span aria-hidden="true">⌄</span>
                </summary>
                <div class="absolute left-0 z-10 mt-2 grid w-[min(90vw,56rem)] gap-6 rounded-lg border border-gray-200 bg-white p-5 shadow-xl sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($categories as $category)
                        <section>
                            <h2 class="font-semibold text-gray-900">
                                <a href="{{ route('home') }}#categories" class="hover:text-indigo-700">{{ $category->name }}</a>
                            </h2>
                            @if ($category->children->isNotEmpty())
                                <ul class="mt-2 grid gap-1 text-sm text-gray-600">
                                    @foreach ($category->children as $child)
                                        <li>
                                            <a href="{{ route('home') }}#categories" class="hover:text-indigo-700">{{ $child->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @empty
                        <p class="text-sm text-gray-600">Henüz kategori eklenmemiş.</p>
                    @endforelse
                </div>
            </details>
        </div>
    </nav>
</header>
