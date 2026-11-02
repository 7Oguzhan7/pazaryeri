import { useForm } from '@inertiajs/react';
import Field from '../../Components/Field';
import Layout from '../../Components/Layout';

export default function ConfirmPassword() {
    const form = useForm({ password: '' });

    const submit = (event) => {
        event.preventDefault();
        form.post('/confirm-password');
    };

    return (
        <Layout title="Parolayı doğrula">
            <section className="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Parolanızı doğrulayın</h2>
                <p className="mt-3 text-sm text-gray-600">Bu güvenli alanı açmak için lütfen mevcut parolanızı girin.</p>
                <form onSubmit={submit} className="mt-6 grid gap-4">
                    <Field label="Parola" name="password" type="password" autoComplete="current-password" autoFocus
                        value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} error={form.errors.password} required />
                    <button disabled={form.processing} className="justify-self-end rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Onayla</button>
                </form>
            </section>
        </Layout>
    );
}
