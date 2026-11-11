import { Link } from '@inertiajs/react';
import Layout from '../../Components/Layout';

export default function Dashboard() {
    return (
        <Layout title="Yönetim paneli">
            <section className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Yönetim paneli</h2>
                <p className="mt-2 text-gray-600">Pazaryeri yönetim araçlarına hoş geldiniz.</p>
                <div className="mt-5 flex flex-wrap gap-3">
                    <Link href="/admin/categories" className="inline-flex rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                        Kategorileri yönet
                    </Link>
                    <Link href="/admin/seller-applications" className="inline-flex rounded-md border border-indigo-600 px-4 py-2 font-semibold text-indigo-700 hover:bg-indigo-50">
                        Satıcı başvurularını incele
                    </Link>
                </div>
            </section>
        </Layout>
    );
}
