import { Link } from '@inertiajs/react';

const fieldClass = 'rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500';

export default function ProductFilters({
    action,
    filters = {},
    filterOptions = {},
    query = '',
    sort = 'newest',
    includeCategory = false,
}) {
    return (
        <form action={action} method="get" className="grid gap-4 rounded-xl border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
            {query && <input type="hidden" name="q" value={query} />}
            <input type="hidden" name="sort" value={sort} />

            {includeCategory && (
                <label className="grid gap-1 text-sm font-medium text-gray-700">
                    Kategori
                    <select name="category" defaultValue={filters.category ?? ''} className={fieldClass}>
                        <option value="">Tüm kategoriler</option>
                        {(filterOptions.categories ?? []).map((category) => (
                            <option key={category.id} value={category.slug}>{category.name}</option>
                        ))}
                    </select>
                </label>
            )}

            <label className="grid gap-1 text-sm font-medium text-gray-700">
                Marka
                <select name="marka" defaultValue={filters.brand ?? ''} className={fieldClass}>
                    <option value="">Tüm markalar</option>
                    {(filterOptions.brands ?? []).map((brand) => (
                        <option key={brand.id} value={brand.slug}>{brand.name}</option>
                    ))}
                </select>
            </label>

            <label className="grid gap-1 text-sm font-medium text-gray-700">
                En düşük fiyat (TL)
                <input type="number" name="min" min="0" step="0.01" defaultValue={filters.min ?? ''} className={fieldClass} />
            </label>

            <label className="grid gap-1 text-sm font-medium text-gray-700">
                En yüksek fiyat (TL)
                <input type="number" name="max" min="0" step="0.01" defaultValue={filters.max ?? ''} className={fieldClass} />
            </label>

            <label className="grid gap-1 text-sm font-medium text-gray-700">
                Renk
                <select name="renk" defaultValue={filters.color ?? ''} className={fieldClass}>
                    <option value="">Tüm renkler</option>
                    {(filterOptions.colors ?? []).map((color) => <option key={color} value={color}>{color}</option>)}
                </select>
            </label>

            <label className="grid gap-1 text-sm font-medium text-gray-700">
                Beden / ölçü
                <select name="beden" defaultValue={filters.size ?? ''} className={fieldClass}>
                    <option value="">Tüm beden ve ölçüler</option>
                    {(filterOptions.sizes ?? []).map((size) => <option key={size} value={size}>{size}</option>)}
                </select>
            </label>

            <label className="grid gap-1 text-sm font-medium text-gray-700">
                En az puan
                <select name="puan" defaultValue={filters.rating ?? ''} className={fieldClass}>
                    <option value="">Tüm puanlar</option>
                    {[5, 4, 3, 2, 1].map((rating) => (
                        <option key={rating} value={rating}>{rating} yıldız ve üzeri</option>
                    ))}
                </select>
            </label>

            <div className="flex items-end gap-3">
                <button type="submit" className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Filtrele
                </button>
                <Link href={action} className="text-sm font-medium text-gray-600 hover:text-indigo-700">
                    Temizle
                </Link>
            </div>
        </form>
    );
}
