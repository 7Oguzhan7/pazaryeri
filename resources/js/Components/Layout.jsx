import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Layout({ children, title = 'TıklaAl' }) {
    const { auth, cartCount = 0, categories = [], flash, search = '' } = usePage().props;
    const [searchQuery, setSearchQuery] = useState(search);
    const [suggestions, setSuggestions] = useState([]);
    const [suggestionError, setSuggestionError] = useState('');
    const user = auth?.user;
    const canShop = !user || user.canShop;
    const flashMessages = {
        'verification-link-sent': 'Yeni bir doğrulama bağlantısı e-posta adresinize gönderildi.',
        'profile-updated': 'Profil bilgileriniz güncellendi.',
        'password-updated': 'Parolanız güncellendi.',
        'cart-added': 'Ürün sepetinize eklendi.',
        'product-images-uploaded': 'Ürün görselleri yüklendi.',
        'seller-application-submitted': 'Satıcı başvurunuz alındı. Yönetici onayından sonra mağazanız açılacaktır.',
        'seller-application-approved': 'Satıcı başvurusu onaylandı.',
        'seller-application-rejected': 'Satıcı başvurusu reddedildi.',
        'seller-product-created': 'Ürün mağazanıza eklendi.',
        'seller-product-updated': 'Ürün bilgileriniz güncellendi.',
        'seller-product-activated': 'Ürün mağazanızda yayına alındı.',
        'seller-product-deactivated': 'Ürün satışa kapatıldı.',
        'seller-product-deleted': 'Ürün mağazanızdan silindi.',
        'seller-product-kept-for-order-history': 'Geçmiş siparişleri korumak için ürün silinmedi ve satışa kapatıldı.',
    };
    const statusMessage = flashMessages[flash?.status] ?? flash?.status;

    useEffect(() => {
        setSearchQuery(search);
    }, [search]);

    useEffect(() => {
        const query = searchQuery.trim();

        if (query.length < 2) {
            setSuggestions([]);
            setSuggestionError('');
            return undefined;
        }

        const controller = new AbortController();
        const timer = window.setTimeout(async () => {
            try {
                const response = await fetch(`/arama/oneri?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error('Arama önerileri şu anda yüklenemiyor.');
                }

                setSuggestions(await response.json());
                setSuggestionError('');
            } catch (error) {
                if (error.name !== 'AbortError') {
                    setSuggestions([]);
                    setSuggestionError('Arama önerileri yüklenemedi. Aramayı göndermeyi deneyin.');
                }
            }
        }, 250);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [searchQuery]);

    return (
        <div className="flex min-h-screen flex-col bg-gray-50">
            <Head title={title === 'TıklaAl' ? title : `${title} | TıklaAl`} />
            <header className="border-b border-gray-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <Link href="/" className="text-xl font-bold tracking-tight text-indigo-700">
                        TıklaAl
                    </Link>
                    <form action="/" method="get" role="search" className="relative order-3 flex w-full flex-1 gap-2 sm:order-none sm:min-w-64">
                        <label className="sr-only" htmlFor="storefront-search">Ürün ara</label>
                        <input
                            id="storefront-search"
                            name="q"
                            type="search"
                            autoComplete="off"
                            value={searchQuery}
                            onChange={(event) => setSearchQuery(event.target.value)}
                            aria-controls="storefront-suggestions"
                            aria-expanded={suggestions.length > 0}
                            placeholder="Ürün veya marka ara"
                            className="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <button className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Ara
                        </button>
                        {(suggestions.length > 0 || suggestionError) && (
                            <div
                                id="storefront-suggestions"
                                role="listbox"
                                aria-label="Arama önerileri"
                                className="absolute left-0 right-14 top-full z-30 mt-1 overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"
                            >
                                {suggestions.map((suggestion) => (
                                    <Link
                                        key={suggestion.slug}
                                        href={`/urun/${suggestion.slug}`}
                                        role="option"
                                        className="block border-b border-gray-100 px-4 py-3 text-sm text-gray-800 last:border-0 hover:bg-indigo-50"
                                    >
                                        <span className="font-medium">{suggestion.name}</span>
                                        {suggestion.brand && <span className="ml-2 text-gray-500">{suggestion.brand}</span>}
                                    </Link>
                                ))}
                                {suggestionError && <p role="status" className="px-4 py-3 text-sm text-red-700">{suggestionError}</p>}
                            </div>
                        )}
                    </form>
                    <div className="ml-auto flex items-center gap-3 text-sm">
                        {canShop && (
                            <Link href="/sepet" className="rounded-md px-2 py-2 text-gray-700 hover:bg-gray-100">
                                Sepet <span className="rounded-full bg-gray-100 px-2 py-1">{cartCount}</span>
                            </Link>
                        )}
                        {user ? (
                            <>
                                <Link href="/dashboard" className="text-gray-700 hover:text-indigo-700">Hesabım</Link>
                                <Link href="/profile" className="text-gray-700 hover:text-indigo-700">Profil</Link>
                                {user.isAdmin && <Link href="/admin" className="text-gray-700 hover:text-indigo-700">Yönetim</Link>}
                                {user.isApprovedSeller && <Link href="/satici" className="text-gray-700 hover:text-indigo-700">Satıcı paneli</Link>}
                                {user.canShop && <Link href="/satici-basvurusu" className="text-gray-700 hover:text-indigo-700">Satıcı ol</Link>}
                                <button type="button" onClick={() => router.post('/logout')}
                                    className="rounded-md px-2 py-2 text-gray-700 hover:bg-gray-100">Çıkış yap</button>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="rounded-md px-2 py-2 text-gray-700 hover:bg-gray-100">Giriş yap</Link>
                                <Link href="/register" className="rounded-md bg-indigo-600 px-3 py-2 font-semibold text-white hover:bg-indigo-700">Kayıt ol</Link>
                            </>
                        )}
                    </div>
                </div>
                <nav aria-label="Ürün kategorileri" className="border-t border-gray-100">
                    <div className="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
                        <details className="group relative">
                            <summary className="inline-flex cursor-pointer list-none items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                                Tüm kategoriler <span aria-hidden="true">⌄</span>
                            </summary>
                            <div className="absolute left-0 z-10 mt-2 grid w-[min(90vw,56rem)] gap-6 rounded-lg border border-gray-200 bg-white p-5 shadow-xl sm:grid-cols-2 lg:grid-cols-3">
                                {categories.length ? categories.map((category) => (
                                    <section key={category.id}>
                                        <h2 className="font-semibold text-gray-900">
                                            <Link href={`/kategori/${category.slug}`} className="hover:text-indigo-700">{category.name}</Link>
                                        </h2>
                                        {category.children?.length > 0 && (
                                            <ul className="mt-2 grid gap-1 text-sm text-gray-600">
                                                {category.children.map((child) => (
                                                    <li key={child.id}><Link href={`/kategori/${child.slug}`} className="hover:text-indigo-700">{child.name}</Link></li>
                                                ))}
                                            </ul>
                                        )}
                                    </section>
                                )) : <p className="text-sm text-gray-600">Henüz kategori eklenmemiş.</p>}
                            </div>
                        </details>
                    </div>
                </nav>
            </header>
            {statusMessage && (
                <div role="status" className="mx-auto mt-5 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <p className="rounded-md bg-green-50 p-3 text-sm font-medium text-green-800">{statusMessage}</p>
                </div>
            )}
            <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="sr-only">{title}</h1>
                {children}
            </main>
            <footer className="border-t border-gray-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-6 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p>&copy; {new Date().getFullYear()} TıklaAl. Tüm hakları saklıdır.</p>
                    <nav aria-label="Alt menü" className="flex gap-4">
                        <Link href="/" className="hover:text-indigo-700">Ana sayfa</Link>
                        {!user && <Link href="/login" className="hover:text-indigo-700">Giriş yap</Link>}
                        {!user && <Link href="/register" className="hover:text-indigo-700">Kayıt ol</Link>}
                    </nav>
                </div>
            </footer>
        </div>
    );
}
