import { router } from '@inertiajs/react';
import Layout from '../../../Components/Layout';

export default function Index({ applications }) {
    return (
        <Layout title="Satıcı başvuruları">
            <section className="grid gap-6">
                <header className="grid gap-2">
                    <h2 className="text-3xl font-bold text-gray-900">Satıcı başvuruları</h2>
                    <p className="text-gray-600">Başvuru onaylandığında kullanıcıya satıcı rolü verilir ve panel erişimi açılır.</p>
                </header>
                {applications.data.length ? (
                    <div className="grid gap-4">
                        {applications.data.map((application) => (
                            <article key={application.id} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 sm:grid-cols-[1fr_auto] sm:items-center">
                                <div className="grid gap-1">
                                    <h3 className="text-lg font-semibold text-gray-900">{application.storeName}</h3>
                                    <p className="text-sm text-gray-600">{application.applicantName} · {application.applicantEmail}</p>
                                    <p className="text-sm text-gray-600">Vergi no: {application.taxNumber} · IBAN: {application.iban}</p>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        onClick={() => router.patch(`/admin/seller-applications/${application.id}/approve`)}
                                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800"
                                    >
                                        Onayla
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => router.patch(`/admin/seller-applications/${application.id}/reject`)}
                                        className="rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                                    >
                                        Reddet
                                    </button>
                                </div>
                            </article>
                        ))}
                    </div>
                ) : (
                    <p className="rounded-lg bg-white p-6 text-gray-600">Bekleyen satıcı başvurusu bulunmuyor.</p>
                )}
            </section>
        </Layout>
    );
}
