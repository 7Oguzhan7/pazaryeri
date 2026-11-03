import { Head, Link, router, usePage } from '@inertiajs/react';

export default function Layout({ children, title = 'PazarYeri' }) {
    const { auth, categories = [], flash, search = '' } = usePage().props;
    const user = auth?.user;
    const flashMessages = {
        'verification-link-sent': 'Yeni bir doğrulama bağlantısı e-posta adresinize gönderildi.',
        'profile-updated': 'Profil bilgileriniz güncellendi.',
        'password-updated': 'Parolanız güncellendi.',
        'cart-added': 'Ürün sepetinize eklendi.',
        'product-images-uploaded': 'Ürün görselleri yüklendi.',
    };
    const statusMessage = flashMessages[flash?.status] ?? flash?.status;

    return (
        <div className="flex min-h-screen flex-col bg-gray-50">
            <Head title={title} />
            <header className="border-b border-gray-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <Link href="/" className="text-xl font-bold tracking-tight text-indigo-700">
                        PazarYeri
                    </Link>
                    <form action="/" method="get" role="search" className="order-3 flex w-full flex-1 gap-2 sm:order-none sm:min-w-64">
                        <label className="sr-only" htmlFor="storefront-search">Ürün ara</label>
                        <input id="storefront-search" name="q" type="search" value={search} placeholder="Ürün veya marka ara"
                            className="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <button className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Ara
                        </button>
                    </form>
                    <div className="ml-auto flex items-center gap-3 text-sm">
                        <button type="button" disabled title="Sepete ürün ekleyebilirsiniz; sepet görüntüleme sonraki aşamada eklenecek."
                            className="cursor-not-allowed rounded-md px-2 py-2 text-gray-500">
                            Sepet <span className="rounded-full bg-gray-100 px-2 py-1">0</span>
                        </button>
                        {user ? (
                            <>
                                <Link href="/dashboard" className="text-gray-700 hover:text-indigo-700">Hesabım</Link>
                                <Link href="/profile" className="text-gray-700 hover:text-indigo-700">Profil</Link>
                                {user.isAdmin && <Link href="/admin" className="text-gray-700 hover:text-indigo-700">Yönetim</Link>}
                                {user.isApprovedSeller && <Link href="/satici" className="text-gray-700 hover:text-indigo-700">Satıcı paneli</Link>}
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
                    <p>&copy; {new Date().getFullYear()} PazarYeri. Tüm hakları saklıdır.</p>
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
