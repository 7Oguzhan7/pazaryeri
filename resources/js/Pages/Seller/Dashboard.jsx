import { Link } from '@inertiajs/react';
import Layout from '../../Components/Layout';

export default function Dashboard({ products = [], storeUrl }) {
    return (
        <Layout title="Satıcı paneli">
            <section className="grid gap-6">
                <header className="grid gap-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 className="text-2xl font-bold text-gray-900">Satıcı paneli</h2>
                    <p className="text-gray-600">Ürünlerinizi, varyantlarını ve stok miktarlarını yönetin.</p>
                    <div className="flex flex-wrap gap-3">
                        <Link href="/satici/urunler" className="w-fit rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                            Ürünleri yönet
                        </Link>
                        <Link href="/satici/urunler/create" className="w-fit rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Yeni ürün ekle
                        </Link>
                        <Link href={storeUrl} className="w-fit rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                            Mağazamı görüntüle
                        </Link>
                    </div>
                </header>

                <div className="grid gap-4">
                    <h3 className="text-xl font-semibold text-gray-900">Ürünlerim</h3>
                    {products.length > 0 ? products.map((product) => (
                        <article key={product.id} className="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5">
                            <div className="grid gap-1">
                                <h4 className="font-semibold text-gray-900">{product.name}</h4>
                                <p className="text-sm text-gray-600">{product.categoryName} · {product.status}</p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Link href={`/satici/urunler/${product.id}/edit`} className="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                                    Ürünü düzenle
                                </Link>
                                {product.status === 'aktif' && (
                                    <Link href={`/urun/${product.slug}#product-images`} className="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        Görselleri yönet
                                    </Link>
                                )}
                            </div>
                        </article>
                    )) : (
                        <p className="rounded-lg bg-white p-5 text-gray-600">Henüz mağazanıza ürün eklenmemiş.</p>
                    )}
                </div>
            </section>
        </Layout>
    );
}
