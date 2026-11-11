import { Link, router, useForm } from '@inertiajs/react';
import Layout from './Layout';

const blankVariant = () => ({
    sku: '',
    color: '',
    size: '',
    price: '',
    discount_price: '',
    stock: 0,
});

export default function SellerProductForm({ product, categories, brands, action, method, productStatus }) {
    const form = useForm({
        name: product.name,
        category_id: product.categoryId ?? '',
        brand_id: product.brandId ?? '',
        description: product.description ?? '',
        variants: product.variants.map((variant) => ({
            ...variant,
            discount_price: variant.discount_price ?? '',
        })),
    });

    function updateVariant(index, field, value) {
        form.setData('variants', form.data.variants.map((variant, variantIndex) => (
            variantIndex === index ? { ...variant, [field]: value } : variant
        )));
    }

    function submit(event) {
        event.preventDefault();
        form.submit(method, action);
    }

    return (
        <Layout title={product.id ? 'Ürünü düzenle' : 'Yeni ürün'}>
            <section className="mx-auto grid max-w-4xl gap-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <header className="grid gap-2">
                    <h2 className="text-2xl font-bold text-gray-900">{product.id ? 'Ürünü düzenle' : 'Mağazaya ürün ekle'}</h2>
                    {productStatus && <p className="text-sm text-gray-600">Yayın durumu: {productStatus}</p>}
                </header>
                <form onSubmit={submit} className="grid gap-5">
                    <label className="grid gap-1 text-sm font-medium text-gray-700">
                        Ürün adı
                        <input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)}
                            className="rounded-md border-gray-300" required maxLength={255} />
                        {form.errors.name && <span className="text-red-700">{form.errors.name}</span>}
                    </label>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <label className="grid gap-1 text-sm font-medium text-gray-700">
                            Kategori
                            <select value={form.data.category_id} onChange={(event) => form.setData('category_id', event.target.value)}
                                className="rounded-md border-gray-300" required>
                                <option value="">Kategori seçin</option>
                                {categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                            </select>
                            {form.errors.category_id && <span className="text-red-700">{form.errors.category_id}</span>}
                        </label>
                        <label className="grid gap-1 text-sm font-medium text-gray-700">
                            Marka
                            <select value={form.data.brand_id} onChange={(event) => form.setData('brand_id', event.target.value)}
                                className="rounded-md border-gray-300">
                                <option value="">Marka seçin (isteğe bağlı)</option>
                                {brands.map((brand) => <option key={brand.id} value={brand.id}>{brand.name}</option>)}
                            </select>
                            {form.errors.brand_id && <span className="text-red-700">{form.errors.brand_id}</span>}
                        </label>
                    </div>
                    <label className="grid gap-1 text-sm font-medium text-gray-700">
                        Açıklama
                        <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)}
                            rows="4" className="rounded-md border-gray-300" maxLength={10000} />
                        {form.errors.description && <span className="text-red-700">{form.errors.description}</span>}
                    </label>

                    <div className="grid gap-4">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <h3 className="text-lg font-semibold text-gray-900">Varyantlar ve stok</h3>
                            <button type="button" onClick={() => form.setData('variants', [...form.data.variants, blankVariant()])}
                                className="rounded-md border border-indigo-600 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                                Varyant ekle
                            </button>
                        </div>
                        {form.errors.variants && <p className="text-sm text-red-700">{form.errors.variants}</p>}
                        {form.data.variants.map((variant, index) => (
                            <fieldset key={variant.id ?? `new-${index}`} className="grid gap-3 rounded-lg border border-gray-200 p-4 sm:grid-cols-2 lg:grid-cols-3">
                                <legend className="px-1 text-sm font-semibold text-gray-700">Varyant {index + 1}</legend>
                                {variant.id && <input type="hidden" name={`variants[${index}][id]`} value={variant.id} />}
                                {[
                                    ['sku', 'SKU kodu', 'text'],
                                    ['color', 'Renk', 'text'],
                                    ['size', 'Beden / ölçü', 'text'],
                                    ['price', 'Fiyat (TL)', 'number'],
                                    ['discount_price', 'İndirimli fiyat (TL)', 'number'],
                                    ['stock', 'Stok adedi', 'number'],
                                ].map(([field, label, type]) => (
                                    <label key={field} className="grid gap-1 text-sm font-medium text-gray-700">
                                        {label}
                                        <input
                                            type={type}
                                            value={variant[field] ?? ''}
                                            onChange={(event) => updateVariant(index, field, event.target.value)}
                                            min={type === 'number' ? '0' : undefined}
                                            step={field === 'price' || field === 'discount_price' ? '0.01' : undefined}
                                            className="rounded-md border-gray-300"
                                            required={field === 'sku' || field === 'price' || field === 'stock'}
                                        />
                                        {form.errors[`variants.${index}.${field}`] && (
                                            <span className="text-red-700">{form.errors[`variants.${index}.${field}`]}</span>
                                        )}
                                    </label>
                                ))}
                            </fieldset>
                        ))}
                    </div>
                    <button disabled={form.processing} className="w-fit rounded-md bg-indigo-600 px-5 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                        {form.processing ? 'Kaydediliyor…' : 'Kaydet'}
                    </button>
                </form>
                {product.id && (
                    <div className="flex flex-wrap gap-3 border-t border-gray-200 pt-5">
                        <button
                            type="button"
                            onClick={() => router.patch(`/satici/urunler/${product.id}/durum`, {
                                status: productStatus === 'aktif' ? 'pasif' : 'aktif',
                            })}
                            className="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50"
                        >
                            {productStatus === 'aktif' ? 'Satışa kapat' : 'Yayına al'}
                        </button>
                        {productStatus === 'aktif' ? (
                            <Link href={`/urun/${product.slug}#product-images`} className="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Ürün görsellerini yönet
                            </Link>
                        ) : (
                            <p className="self-center text-sm text-gray-600">Görselleri yönetmek için ürünü yayına alın.</p>
                        )}
                    </div>
                )}
            </section>
        </Layout>
    );
}
