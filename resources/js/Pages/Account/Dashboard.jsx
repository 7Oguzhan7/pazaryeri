import { Link, usePage } from '@inertiajs/react';
import Layout from '../../Components/Layout';

export default function Dashboard() {
    const { auth } = usePage().props;

    return (
        <Layout title="Hesabım">
            <section className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Merhaba, {auth.user.name}</h2>
                <p className="mt-2 text-gray-600">Hesabınıza hoş geldiniz. Profil bilgilerinizi buradan yönetebilirsiniz.</p>
                <Link href="/profile" className="mt-5 inline-flex rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                    Profilimi düzenle
                </Link>
            </section>
        </Layout>
    );
}
