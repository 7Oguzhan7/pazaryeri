import { Link, router } from '@inertiajs/react';
import Layout from '../../../Components/Layout';

export default function Index({ categories }) {
    const removeCategory = (category) => {
        if (window.confirm(`“${category.name}” kategorisini silmek istiyor musunuz?`)) {
            router.delete(`/admin/categories/${category.id}`);
        }
    };

    return (
        <Layout title="Kategori yönetimi">
            <section className="space-y-5">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h2 className="text-2xl font-bold text-gray-900">Kategori yönetimi</h2>
                    <Link href="/admin/categories/create" className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Yeni kategori
                    </Link>
                </div>
                <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50 text-left font-semibold text-gray-700">
                            <tr>
                                <th className="px-4 py-3">Kategori</th>
                                <th className="px-4 py-3">Üst kategori</th>
                                <th className="px-4 py-3">Alt kategori</th>
                                <th className="px-4 py-3">Ürün</th>
                                <th className="px-4 py-3">Sıra</th>
                                <th className="px-4 py-3">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {categories.data.map((category) => (
                                <tr key={category.id}>
                                    <td className="px-4 py-3 font-medium text-gray-900">{category.name}<span className="block text-xs text-gray-500">{category.slug}</span></td>
                                    <td className="px-4 py-3 text-gray-600">{category.parent?.name ?? '—'}</td>
                                    <td className="px-4 py-3 text-gray-600">{category.children_count}</td>
                                    <td className="px-4 py-3 text-gray-600">{category.products_count}</td>
                                    <td className="px-4 py-3 text-gray-600">{category.sort}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-3">
                                            <Link href={`/admin/categories/${category.id}/edit`} className="font-medium text-indigo-700 hover:text-indigo-900">Düzenle</Link>
                                            <button type="button" onClick={() => removeCategory(category)} className="font-medium text-red-700 hover:text-red-900">Sil</button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {categories.data.length === 0 && (
                                <tr><td colSpan="6" className="px-4 py-8 text-center text-gray-600">Henüz kategori eklenmemiş.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <nav aria-label="Sayfalama" className="flex flex-wrap gap-2">
                    {categories.links.map((link, index) => (
                        <Link key={index} href={link.url ?? '#'} preserveScroll
                            className={`rounded border px-3 py-2 text-sm ${link.active ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 bg-white text-gray-700'} ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                        >
                            {link.label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/<[^>]*>/g, '')}
                        </Link>
                    ))}
                </nav>
            </section>
        </Layout>
    );
}
