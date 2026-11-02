import { useForm } from '@inertiajs/react';
import Field from '../../Components/Field';
import Layout from '../../Components/Layout';

export default function ForgotPassword() {
    const form = useForm({ email: '' });

    const submit = (event) => {
        event.preventDefault();
        form.post('/forgot-password');
    };

    return (
        <Layout title="Parola sıfırlama">
            <section className="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Parolanızı mı unuttunuz?</h2>
                <p className="mt-3 text-sm text-gray-600">Parolanızı sıfırlamanız için e-posta adresinize bir bağlantı göndereceğiz.</p>
                <form onSubmit={submit} className="mt-6 grid gap-4">
                    <Field label="E-posta" name="email" type="email" autoComplete="username" value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)} error={form.errors.email} required />
                    <button disabled={form.processing} className="justify-self-end rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                        Sıfırlama bağlantısı gönder
                    </button>
                </form>
            </section>
        </Layout>
    );
}
