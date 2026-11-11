<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'TıklaAl') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="flex min-h-screen flex-col bg-gray-50">
            @include('components.storefront-header')

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-10 sm:px-6 lg:px-8">
                <section class="rounded-2xl bg-indigo-700 px-6 py-12 text-white sm:px-10">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-100">TıklaAl'a hoş geldiniz</p>
                    <h1 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
                        Aradığınız ürünleri güvenle keşfedin.
                    </h1>
                    <p class="mt-4 max-w-2xl text-indigo-100">
                        Farklı mağazaların ürünlerini tek bir pazaryerinde inceleyin.
                    </p>
                    <a href="#categories" class="mt-6 inline-flex rounded-md bg-white px-4 py-2 font-semibold text-indigo-700 hover:bg-indigo-50">
                        Kategorileri keşfet
                    </a>
                </section>

                <section id="categories" class="mt-10">
                    <h2 class="text-2xl font-bold text-gray-900">Alışveriş kategorileri</h2>
                    <p class="mt-2 text-gray-600">Bu menüdeki kategoriler veritabanından yüklenir.</p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($categories as $category)
                            <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                @if ($category->children->isNotEmpty())
                                    <ul class="mt-3 grid gap-2 text-sm text-gray-600">
                                        @foreach ($category->children as $child)
                                            <li>{{ $child->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-lg bg-white p-5 text-gray-600">Henüz kategori eklenmemiş.</p>
                        @endforelse
                    </div>
                </section>
            </main>

            @include('components.storefront-footer')
        </div>
    </body>
</html>
