<footer class="border-t border-gray-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-6 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <p>&copy; {{ now()->year }} {{ config('app.name', 'TıklaAl') }}. Tüm hakları saklıdır.</p>
        <nav aria-label="Alt menü" class="flex gap-4">
            <a href="{{ route('home') }}" class="hover:text-indigo-700">Ana sayfa</a>
            @guest
                <a href="{{ route('login') }}" class="hover:text-indigo-700">Giriş yap</a>
                <a href="{{ route('register') }}" class="hover:text-indigo-700">Kayıt ol</a>
            @endguest
        </nav>
    </div>
</footer>
