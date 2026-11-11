import { Link, useForm } from '@inertiajs/react';
import Layout from '../../Components/Layout';

export default function Application({ application }) {
    const form = useForm({
        store_name: application?.storeName ?? '',
        tax_number: application?.taxNumber ?? '',
        iban: application?.iban ?? '',
    });

    function submit(event) {
        event.preventDefault();
        form.post('/satici-basvurusu');
    }

    if (application?.status === 'beklemede') {
        return (
            <Layout title="Satıcı başvurusu">
                <section className="mx-auto grid max-w-2xl gap-4 rounded-xl border border-amber-200 bg-amber-50 p-6">
                    <h2 className="text-2xl font-bold text-gray-900">Başvurunuz inceleniyor</h2>
                    <p className="text-gray-700">
                        {application.storeName} mağazası için başvurunuz yönetici onayı bekliyor.
                    </p>
                </section>
            </Layout>
        );
    }

    if (application?.status === 'onayli') {
        return (
            <Layout title="Satıcı başvurusu">
                <section className="mx-auto grid max-w-2xl gap-4 rounded-xl border border-green-200 bg-green-50 p-6">
                    <h2 className="text-2xl font-bold text-gray-900">Mağazanız onaylandı</h2>
                    <Link href="/satici" className="w-fit rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                        Satıcı paneline git
                    </Link>
                </section>
            </Layout>
        );
    }

    return (
        <Layout title="Satıcı başvurusu">
            <section className="mx-auto grid max-w-2xl gap-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <header className="grid gap-2">
                    <h2 className="text-2xl font-bold text-gray-900">Satıcı başvurusu</h2>
                    <p className="text-gray-600">Mağaza bilgilerinizi gönderin. Ürün yönetimi, başvurunuz onaylandıktan sonra açılır.</p>
                    {application?.status === 'reddedildi' && (
                        <p role="status" className="rounded-md bg-red-50 p-3 text-sm text-red-800">
                            Önceki başvurunuz reddedildi. Bilgilerinizi güncelleyip yeniden başvurabilirsiniz.
                        </p>
                    )}
                </header>
                <form onSubmit={submit} className="grid gap-4">
                    <label className="grid gap-1 text-sm font-medium text-gray-700">
                        Mağaza adı
                        <input value={form.data.store_name} onChange={(event) => form.setData('store_name', event.target.value)}
                            className="rounded-md border-gray-300" required maxLength={255} />
                        {form.errors.store_name && <span className="text-red-700">{form.errors.store_name}</span>}
                    </label>
                    <label className="grid gap-1 text-sm font-medium text-gray-700">
                        Vergi numarası
                        <input value={form.data.tax_number} onChange={(event) => form.setData('tax_number', event.target.value)}
                            inputMode="numeric" className="rounded-md border-gray-300" required />
                        {form.errors.tax_number && <span className="text-red-700">{form.errors.tax_number}</span>}
                    </label>
                    <label className="grid gap-1 text-sm font-medium text-gray-700">
                        IBAN
                        <input value={form.data.iban} onChange={(event) => form.setData('iban', event.target.value)}
                            placeholder="TR000000000000000000000000" className="rounded-md border-gray-300" required />
                        {form.errors.iban && <span className="text-red-700">{form.errors.iban}</span>}
                    </label>
                    <button disabled={form.processing} className="w-fit rounded-md bg-indigo-600 px-5 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                        {form.processing ? 'Gönderiliyor…' : 'Başvuru gönder'}
                    </button>
                </form>
            </section>
        </Layout>
    );
}
