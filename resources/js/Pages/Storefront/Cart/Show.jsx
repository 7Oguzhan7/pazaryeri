import { Link } from '@inertiajs/react';
import Layout from '../../../Components/Layout';

const currency = new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
});

export default function Show({ items, subtotal, cartCount }) {
    return (
        <Layout title="Sepetim">
            <section className="grid gap-6">
                <header className="flex flex-wrap items-end justify-between gap-3">
                    <div className="grid gap-2">
                        <h2 className="text-3xl font-bold text-gray-900">Sepetim</h2>
                        <p className="text-sm text-gray-600">{cartCount} ürün</p>
                    </div>
                    <Link href="/" className="text-sm font-semibold text-indigo-700 hover:underline">
                        Alışverişe devam et
                    </Link>
                </header>

                {items.length > 0 ? (
                    <div className="grid items-start gap-6 lg:grid-cols-[1fr_20rem]">
                        <div className="grid gap-3">
                            {items.map((item) => (
                                <article key={item.id} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 sm:grid-cols-[1fr_auto]">
                                    <div className="grid content-start gap-2">
                                        <Link href={`/urun/${item.productSlug}`} className="font-semibold text-gray-900 hover:text-indigo-700">
                                            {item.productName}
                                        </Link>
                                        <p className="text-sm text-gray-600">Satıcı: {item.sellerName}</p>
                                        {item.variant && <p className="text-sm text-gray-600">Seçenek: {item.variant}</p>}
                                        <p className="text-sm text-gray-600">
                                            {currency.format(Number(item.unitPrice))} × {item.quantity}
                                        </p>
                                    </div>
                                    <p className="font-bold text-gray-900 sm:text-right">
                                        {currency.format(Number(item.lineTotal))}
                                    </p>
                                </article>
                            ))}
                        </div>

                        <aside className="grid gap-3 rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="font-semibold text-gray-900">Sipariş özeti</h3>
                            <div className="flex justify-between gap-3 text-sm text-gray-700">
                                <span>Ara toplam</span>
                                <span>{currency.format(Number(subtotal))}</span>
                            </div>
                            <p className="border-t border-gray-200 pt-3 text-sm text-gray-500">
                                Kargo ve ödeme adımları sonraki aşamada eklenecektir.
                            </p>
                        </aside>
                    </div>
                ) : (
                    <div className="grid justify-items-center gap-4 rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
                        <p className="text-lg font-semibold text-gray-900">Sepetiniz şu anda boş.</p>
                        <p className="max-w-md text-sm text-gray-600">Ürünleri inceleyip sepete eklediğinizde burada görebilirsiniz.</p>
                        <Link href="/" className="rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                            Ürünleri keşfet
                        </Link>
                    </div>
                )}
            </section>
        </Layout>
    );
}
