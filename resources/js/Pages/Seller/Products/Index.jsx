import { Link, router } from '@inertiajs/react';
import Layout from '../../../Components/Layout';

export default function Index({ products }) {
    return (
        <Layout title="Ürünlerim">
            <section className="grid gap-6">
                <header className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 className="text-3xl font-bold text-gray-900">Ürünlerim</h2>
                        <p className="mt-1 text-gray-600">Ürün, varyant ve stok yönetimi</p>
                    </div>
                    <Link href="/satici/urunler/create" className="rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                        Yeni ürün ekle
                    </Link>
                </header>
                {products.data.length ? products.data.map((product) => (
                    <article key={product.id} className="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5">
                        <div className="grid gap-1">
                            <h3 className="font-semibold text-gray-900">{product.name}</h3>
                            <p className="text-sm text-gray-600">{product.categoryName} · {product.status} · {product.stock} adet stok · {product.variantCount} varyant</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Link href={`/satici/urunler/${product.id}/edit`} className="rounded-md border border-indigo-600 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                                Düzenle
                            </Link>
                            <button
                                type="button"
                                onClick={() => router.patch(`/satici/urunler/${product.id}/durum`, {
                                    status: product.status === 'aktif' ? 'pasif' : 'aktif',
                                })}
                                className="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                {product.status === 'aktif' ? 'Pasife al' : 'Aktifleştir'}
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    if (window.confirm('Bu ürünü mağazadan kaldırmak istediğinize emin misiniz?')) {
                                        router.delete(`/satici/urunler/${product.id}`);
                                    }
                                }}
                                className="rounded-md border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                            >
                                Sil
                            </button>
                        </div>
                    </article>
                )) : <p className="rounded-lg bg-white p-6 text-gray-600">Henüz ürün eklenmemiş.</p>}
                {products.last_page > 1 && (
                    <nav aria-label="Ürün sayfaları" className="flex flex-wrap justify-center gap-2">
                        {products.links.map((link, index) => link.url ? (
                            <Link
                                key={`${link.label}-${index}`}
                                href={link.url}
                                preserveScroll
                                className={`rounded-md border px-3 py-2 text-sm ${
                                    link.active
                                        ? 'border-indigo-600 bg-indigo-600 text-white'
                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                                }`}
                            >
                                {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                            </Link>
                        ) : null)}
                    </nav>
                )}
            </section>
        </Layout>
    );
}
