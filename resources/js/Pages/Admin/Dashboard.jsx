import { Link } from '@inertiajs/react';
import Layout from '../../Components/Layout';

export default function Dashboard() {
    return (
        <Layout title="Yönetim paneli">
            <section className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Yönetim paneli</h2>
                <p className="mt-2 text-gray-600">Pazaryeri yönetim araçlarına hoş geldiniz.</p>
                <Link href="/admin/categories" className="mt-5 inline-flex rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                    Kategorileri yönet
                </Link>
            </section>
        </Layout>
    );
}
