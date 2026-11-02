import { Link, useForm } from '@inertiajs/react';
import Field from '../../../Components/Field';
import Layout from '../../../Components/Layout';

export default function CategoryForm({ category, parents, editing = false }) {
    const form = useForm({
        name: category.name ?? '',
        slug: category.slug ?? '',
        parent_id: category.parent_id ?? '',
        sort: category.sort ?? 0,
        commission_rate: category.commission_rate ?? '0.00',
    });

    const submit = (event) => {
        event.preventDefault();

        if (editing) {
            form.put(`/admin/categories/${category.id}`);
        } else {
            form.post('/admin/categories');
        }
    };

    return (
        <Layout title={editing ? 'Kategori düzenle' : 'Yeni kategori'}>
            <section className="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">{editing ? 'Kategori düzenle' : 'Yeni kategori'}</h2>
                <form onSubmit={submit} className="mt-6 grid gap-5">
                    <Field label="Kategori adı" name="name" value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)} error={form.errors.name} required />
                    <Field label="Bağlantı adresi (slug)" name="slug" value={form.data.slug}
                        onChange={(event) => form.setData('slug', event.target.value)} error={form.errors.slug}
                        placeholder="Boş bırakılırsa isimden oluşturulur" />
                    <div>
                        <label htmlFor="parent_id" className="mb-1 block text-sm font-medium text-gray-700">Üst kategori</label>
                        <select id="parent_id" value={form.data.parent_id}
                            onChange={(event) => form.setData('parent_id', event.target.value)}
                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Ana kategori</option>
                            {parents.map((parent) => <option key={parent.id} value={parent.id}>{parent.name}</option>)}
                        </select>
                        {form.errors.parent_id && <p className="mt-1 text-sm text-red-600">{form.errors.parent_id}</p>}
                    </div>
                    <Field label="Sıralama" name="sort" type="number" min="0" value={form.data.sort}
                        onChange={(event) => form.setData('sort', event.target.value)} error={form.errors.sort} required />
                    <Field label="Komisyon oranı (%)" name="commission_rate" type="number" min="0" max="100" step="0.01"
                        value={form.data.commission_rate}
                        onChange={(event) => form.setData('commission_rate', event.target.value)}
                        error={form.errors.commission_rate} required />
                    {form.errors.category && <p className="text-sm text-red-600">{form.errors.category}</p>}
                    <div className="flex items-center gap-3">
                        <button disabled={form.processing} className="rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                            {form.processing ? 'Kaydediliyor...' : 'Kaydet'}
                        </button>
                        <Link href="/admin/categories" className="text-sm font-medium text-gray-600 hover:text-gray-900">İptal</Link>
                    </div>
                </form>
            </section>
        </Layout>
    );
}
