import { Link } from '@inertiajs/react';
import Layout from '../../../Components/Layout';
import ProductCard from '../../../Components/ProductCard';

export default function Show({ store, products }) {
    return (
        <Layout title={store.name}>
            <section className="grid gap-6">
                <header className="grid gap-2 rounded-2xl bg-indigo-700 p-8 text-white">
                    <p className="text-sm font-semibold uppercase tracking-wide text-indigo-100">TıklaAl mağazası</p>
                    <h2 className="text-3xl font-bold">{store.name}</h2>
                    <p>{products.total} ürün</p>
                </header>
                {products.data.length ? (
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        {products.data.map((product) => <ProductCard key={product.id} product={product} />)}
                    </div>
                ) : (
                    <p className="rounded-lg bg-white p-8 text-center text-gray-600">Bu mağazada şu anda stokta ürün bulunmuyor.</p>
                )}
                {products.last_page > 1 && (
                    <nav aria-label="Mağaza ürün sayfaları" className="flex flex-wrap justify-center gap-2">
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
