import Layout from '../../Components/Layout';

export default function Dashboard() {
    return (
        <Layout title="Satıcı paneli">
            <section className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Satıcı paneli</h2>
                <p className="mt-2 text-gray-600">Satıcı hesabınız onaylandı. Ürün ve mağaza yönetimi yakında burada olacak.</p>
            </section>
        </Layout>
    );
}
