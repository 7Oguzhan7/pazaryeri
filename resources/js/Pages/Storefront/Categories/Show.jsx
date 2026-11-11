import { Link, router } from '@inertiajs/react';
import ProductFilters from '../../../Components/ProductFilters';
import Layout from '../../../Components/Layout';
import ProductCard from '../../../Components/ProductCard';

const sortOptions = [
    { value: 'newest', label: 'En yeni' },
    { value: 'price_asc', label: 'Fiyat: artan' },
    { value: 'price_desc', label: 'Fiyat: azalan' },
    { value: 'rating', label: 'En çok değerlendirilen' },
];

export default function Show({ category, products, sort, filters, filterOptions }) {
    function changeSort(event) {
        const query = Object.fromEntries(new URLSearchParams(window.location.search));
        router.get(`/kategori/${category.slug}`, { ...query, sort: event.target.value }, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    return (
        <Layout title={category.name}>
            <section className="grid gap-6">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="grid gap-2">
                        <p className="text-sm font-medium text-indigo-700">
                            <Link href="/" className="hover:underline">Ana sayfa</Link> / Kategoriler
                        </p>
                        <h2 className="text-3xl font-bold text-gray-900">{category.name}</h2>
                        {category.children.length > 0 && (
                            <nav aria-label="Alt kategoriler" className="flex flex-wrap gap-2">
                                {category.children.map((child) => (
                                    <Link
                                        key={child.slug}
                                        href={`/kategori/${child.slug}`}
                                        className="rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                                    >
                                        {child.name}
                                    </Link>
                                ))}
                            </nav>
                        )}
                    </div>
                    <label className="grid gap-1 text-sm font-medium text-gray-700">
                        Sırala
                        <select
                            value={sort}
                            onChange={changeSort}
                            className="min-w-48 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            {sortOptions.map((option) => (
                                <option key={option.value} value={option.value}>{option.label}</option>
                            ))}
                        </select>
                    </label>
                </header>

                <ProductFilters
                    action={`/kategori/${category.slug}`}
                    filters={filters}
                    filterOptions={filterOptions}
                    sort={sort}
                />

                <p className="text-sm text-gray-600">{products.total} ürün</p>
                {products.data.length > 0 ? (
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        {products.data.map((product) => <ProductCard key={product.id} product={product} />)}
                    </div>
                ) : (
                    <p className="rounded-lg bg-white p-8 text-center text-gray-600">
                        Bu kategoride şu anda stokta ürün bulunmuyor.
                    </p>
                )}

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
                        ) : (
                            <span
                                key={`${link.label}-${index}`}
                                className="rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-400"
                            >
                                {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                            </span>
                        ))}
                    </nav>
                )}
            </section>
        </Layout>
    );
}
