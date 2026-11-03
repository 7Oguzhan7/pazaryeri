import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Layout from '../../Components/Layout';
import ProductCard from '../../Components/ProductCard';

export default function Home({
    banners = [],
    bestSellers = [],
    featuredCategories = [],
    newArrivals = [],
    search,
}) {
    const [activeBannerIndex, setActiveBannerIndex] = useState(0);
    const activeBanner = banners[activeBannerIndex];

    useEffect(() => {
        if (banners.length < 2) {
            return undefined;
        }

        const timer = window.setInterval(() => {
            setActiveBannerIndex((index) => (index + 1) % banners.length);
        }, 6000);

        return () => window.clearInterval(timer);
    }, [banners.length]);

    return (
        <Layout title="Ana sayfa">
            <section aria-label="Kampanyalar" className="relative overflow-hidden rounded-2xl bg-indigo-700 text-white">
                {activeBanner?.imageUrl && (
                    <img src={activeBanner.imageUrl} alt="" className="absolute inset-0 h-full w-full object-cover opacity-30" />
                )}
                <div className="relative grid min-h-72 content-center gap-5 px-6 py-12 sm:px-10">
                    <p className="text-sm font-semibold uppercase tracking-wide text-indigo-100">
                        {activeBanner?.title ?? 'PazarYeri’ne hoş geldiniz'}
                    </p>
                    <h2 className="max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
                        Aradığınız ürünleri güvenle keşfedin.
                    </h2>
                    <p className="max-w-2xl text-indigo-100">
                        Farklı mağazaların ürünlerini tek bir pazaryerinde inceleyin.
                    </p>
                    <Link
                        href={activeBanner?.url ?? '#categories'}
                        className="w-fit rounded-md bg-white px-4 py-2 font-semibold text-indigo-700 hover:bg-indigo-50"
                    >
                        {activeBanner?.url ? 'Kampanyayı keşfet' : 'Kategorileri keşfet'}
                    </Link>
                </div>
                {banners.length > 1 && (
                    <div className="absolute bottom-4 right-5 flex items-center gap-2" aria-label="Kampanya seçimi">
                        {banners.map((banner, index) => (
                            <button
                                key={banner.id}
                                type="button"
                                aria-label={`${index + 1}. kampanyayı göster`}
                                aria-current={index === activeBannerIndex}
                                onClick={() => setActiveBannerIndex(index)}
                                className={`h-3 w-3 rounded-full ring-2 ring-white ${index === activeBannerIndex ? 'bg-white' : 'bg-white/40'}`}
                            />
                        ))}
                    </div>
                )}
            </section>

            <section id="categories" className="mt-10 grid gap-4">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Öne çıkan kategoriler</h2>
                    <p className="mt-2 text-gray-600">
                        {search ? `“${search}” araması için ürünleri keşfedin.` : 'Alışverişe kategorilerden başlayın.'}
                    </p>
                </div>
                {featuredCategories.length ? (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {featuredCategories.map((category) => (
                            <Link
                                key={category.id}
                                href={`/kategori/${category.slug}`}
                                className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow"
                            >
                                <h3 className="font-semibold text-gray-900">{category.name}</h3>
                                <p className="mt-2 text-sm text-gray-600">{category.active_products_count} ürün</p>
                            </Link>
                        ))}
                    </div>
                ) : (
                    <p className="rounded-lg bg-white p-5 text-gray-600">Henüz ürün bulunan kategori yok.</p>
                )}
            </section>

            <ProductSection title="Çok satanlar" products={bestSellers} emptyMessage="Henüz tamamlanmış satış bulunmuyor." />
            <ProductSection title="Yeni gelenler" products={newArrivals} emptyMessage="Henüz listelenecek ürün yok." />
        </Layout>
    );
}

function ProductSection({ title, products, emptyMessage }) {
    return (
        <section className="mt-12 grid gap-5">
            <h2 className="text-2xl font-bold text-gray-900">{title}</h2>
            {products.length ? (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    {products.map((product) => <ProductCard key={product.id} product={product} />)}
                </div>
            ) : (
                <p className="rounded-lg bg-white p-5 text-gray-600">{emptyMessage}</p>
            )}
        </section>
    );
}
