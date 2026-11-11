import { Link, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import Layout from '../../../Components/Layout';

const currency = new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
});

export default function Show({ product, canAddToCart, canUploadImages }) {
    const initialVariant = product.variants.find((variant) => variant.stock > 0) ?? product.variants[0] ?? null;
    const [selectedVariantId, setSelectedVariantId] = useState(initialVariant?.id ?? null);
    const [selectedImageIndex, setSelectedImageIndex] = useState(0);
    const selectedVariant = useMemo(
        () => product.variants.find((variant) => variant.id === selectedVariantId) ?? null,
        [product.variants, selectedVariantId],
    );
    const cartForm = useForm({
        variant_id: selectedVariant?.id ?? '',
        quantity: 1,
    });
    const imageForm = useForm({ images: [] });
    const selectedImage = product.images[selectedImageIndex];

    function selectVariant(variant) {
        setSelectedVariantId(variant.id);
        cartForm.setData('variant_id', variant.id);
    }

    function addToCart(event) {
        event.preventDefault();
        cartForm.post('/cart/items', { preserveScroll: true });
    }

    function uploadImages(event) {
        event.preventDefault();
        imageForm.post(`/urun/${product.slug}/gorseller`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => imageForm.reset(),
        });
    }

    return (
        <Layout title={product.name}>
            <div className="grid gap-10 lg:grid-cols-2">
                <section aria-label="Ürün görselleri" className="grid content-start gap-4">
                    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white">
                        {selectedImage ? (
                            <img src={selectedImage.previewUrl} alt={product.name} className="aspect-square w-full object-contain" />
                        ) : (
                            <div className="flex aspect-square items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 p-8 text-center text-lg font-medium text-gray-500">
                                Ürün görseli henüz eklenmemiş
                            </div>
                        )}
                    </div>
                    {product.images.length > 1 && (
                        <div className="flex flex-wrap gap-3">
                            {product.images.map((image, index) => (
                                <button
                                    key={image.id}
                                    type="button"
                                    aria-label={`${index + 1}. ürün görselini göster`}
                                    aria-pressed={index === selectedImageIndex}
                                    onClick={() => setSelectedImageIndex(index)}
                                    className={`overflow-hidden rounded-lg border-2 ${index === selectedImageIndex ? 'border-indigo-600' : 'border-transparent'}`}
                                >
                                    <img src={image.url} alt="" className="h-20 w-20 object-cover" />
                                </button>
                            ))}
                        </div>
                    )}
                </section>

                <section className="grid content-start gap-5">
                    <div className="grid gap-2">
                        <p className="text-sm font-medium text-indigo-700">
                            <Link href={`/kategori/${product.categorySlug}`} className="hover:underline">
                                {product.categoryName}
                            </Link>
                        </p>
                        <h2 className="text-3xl font-bold text-gray-900">{product.name}</h2>
                        <p className="text-sm text-amber-700">
                            {product.averageRating > 0
                                ? `★ ${product.averageRating} · ${product.reviewCount} değerlendirme`
                                : 'Henüz değerlendirilmedi'}
                        </p>
                    </div>

                    <p className="whitespace-pre-line leading-7 text-gray-700">{product.description}</p>

                    <p className="text-sm text-gray-700">
                        Satıcı:{' '}
                        <Link href={`/magaza/${product.sellerSlug}`} className="font-medium text-indigo-700 hover:underline">
                            {product.sellerName}
                        </Link>
                    </p>

                    {product.variants.length > 0 ? (
                        <div className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5">
                            <div className="grid gap-2">
                                <h3 className="font-semibold text-gray-900">Varyant seçin</h3>
                                <div className="flex flex-wrap gap-2">
                                    {product.variants.map((variant) => (
                                        <button
                                            key={variant.id}
                                            type="button"
                                            aria-pressed={variant.id === selectedVariant?.id}
                                            onClick={() => selectVariant(variant)}
                                            className={`rounded-md border px-3 py-2 text-sm ${
                                                variant.id === selectedVariant?.id
                                                    ? 'border-indigo-600 bg-indigo-50 text-indigo-700'
                                                    : 'border-gray-300 text-gray-700 hover:border-indigo-300'
                                            }`}
                                        >
                                            {[variant.color, variant.size].filter(Boolean).join(' / ') || variant.sku}
                                            {variant.stock === 0 && ' · Tükendi'}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {selectedVariant && (
                                <div className="grid gap-2">
                                    <p className="text-2xl font-bold text-indigo-700">
                                        {currency.format(Number(selectedVariant.activePrice))}
                                    </p>
                                    {selectedVariant.discountPrice && Number(selectedVariant.discountPrice) < Number(selectedVariant.price) && (
                                        <p className="text-sm text-gray-500 line-through">
                                            {currency.format(Number(selectedVariant.price))}
                                        </p>
                                    )}
                                    <p className={`text-sm ${selectedVariant.stock > 0 ? 'text-green-700' : 'text-red-700'}`}>
                                        {selectedVariant.stock > 0 ? `${selectedVariant.stock} adet stokta` : 'Bu varyant stokta yok'}
                                    </p>
                                </div>
                            )}

                            {canAddToCart ? (
                                <form onSubmit={addToCart} className="grid gap-3">
                                    <label className="grid max-w-32 gap-1 text-sm font-medium text-gray-700">
                                        Adet
                                        <input
                                            type="number"
                                            min="1"
                                            max={selectedVariant?.stock ?? 1}
                                            value={cartForm.data.quantity}
                                            onChange={(event) => cartForm.setData('quantity', Number(event.target.value))}
                                            className="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </label>
                                    {cartForm.errors.quantity && <p className="text-sm text-red-700">{cartForm.errors.quantity}</p>}
                                    <button
                                        type="submit"
                                        disabled={!selectedVariant || selectedVariant.stock < 1 || cartForm.processing}
                                        className="rounded-md bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                                    >
                                        {cartForm.processing ? 'Sepete ekleniyor…' : 'Sepete ekle'}
                                    </button>
                                </form>
                            ) : (
                                <p className="text-sm text-gray-600">Bu hesap türüyle alışveriş yapılamaz.</p>
                            )}
                        </div>
                    ) : (
                        <p className="rounded-lg bg-amber-50 p-4 text-amber-900">Bu ürün için henüz satın alınabilir varyant yok.</p>
                    )}

                    {canUploadImages && (
                        <form id="product-images" onSubmit={uploadImages} className="grid gap-3 rounded-xl border border-dashed border-gray-300 bg-white p-5">
                            <h3 className="font-semibold text-gray-900">Ürün görselleri ekle</h3>
                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                onChange={(event) => imageForm.setData('images', Array.from(event.target.files ?? []))}
                                className="block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700"
                            />
                            {(imageForm.errors.images || imageForm.errors['images.0']) && (
                                <p className="text-sm text-red-700">{imageForm.errors.images || imageForm.errors['images.0']}</p>
                            )}
                            <button
                                type="submit"
                                disabled={imageForm.processing || imageForm.data.images.length === 0}
                                className="w-fit rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {imageForm.processing ? 'Yükleniyor…' : 'Görselleri yükle'}
                            </button>
                        </form>
                    )}
                </section>
            </div>

            <section className="mt-12 grid gap-5">
                <h2 className="text-2xl font-bold text-gray-900">Müşteri değerlendirmeleri</h2>
                {product.reviews.length > 0 ? (
                    <div className="grid gap-4 md:grid-cols-2">
                        {product.reviews.map((review, index) => (
                            <article key={`${review.reviewer}-${index}`} className="rounded-xl border border-gray-200 bg-white p-5">
                                <p className="font-semibold text-gray-900">{review.reviewer}</p>
                                <p className="mt-1 text-sm text-amber-700">{'★'.repeat(review.rating)}</p>
                                {review.comment && <p className="mt-3 text-gray-700">{review.comment}</p>}
                                <p className="mt-3 text-xs text-gray-500">{review.createdAt}</p>
                            </article>
                        ))}
                    </div>
                ) : (
                    <p className="rounded-lg bg-white p-5 text-gray-600">Bu ürün henüz değerlendirilmemiş.</p>
                )}
            </section>
        </Layout>
    );
}
