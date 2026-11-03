import { Link } from '@inertiajs/react';

const currency = new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
});

export default function ProductCard({ product }) {
    return (
        <article className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <Link href={`/urun/${product.slug}`} className="block">
                {product.image ? (
                    <img src={product.image} alt={product.name} className="aspect-square w-full object-cover" loading="lazy" />
                ) : (
                    <div className="flex aspect-square items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 p-6 text-center text-sm font-medium text-gray-500">
                        {product.name}
                    </div>
                )}
                <div className="grid gap-2 p-4">
                    <h3 className="line-clamp-2 min-h-12 font-semibold text-gray-900">{product.name}</h3>
                    <p className="text-sm text-amber-700">
                        {product.averageRating > 0 ? `★ ${product.averageRating} (${product.reviewCount})` : 'Henüz değerlendirilmedi'}
                    </p>
                    <div className="flex flex-wrap items-baseline gap-2">
                        <p className="font-bold text-indigo-700">{currency.format(Number(product.price ?? 0))}</p>
                        {product.originalPrice && (
                            <p className="text-sm text-gray-500 line-through">
                                {currency.format(Number(product.originalPrice))}
                            </p>
                        )}
                    </div>
                </div>
            </Link>
        </article>
    );
}
